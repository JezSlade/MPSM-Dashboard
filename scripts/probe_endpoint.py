#!/usr/bin/env python3
"""
Probe individual endpoints to discover working payloads and responses.
"""
import json
import sys
import time
from pathlib import Path
from typing import Dict, Any, List, Optional, Tuple

# Add parent directory to path for imports
sys.path.insert(0, str(Path(__file__).parent))

from utils.env_loader import load_env
from utils.http_client import HTTPClient
from utils.payload_gen import PayloadGenerator
from utils.validator import SchemaValidator, resolve_schema_ref
from utils.redact import redact, redact_url, redact_headers
from utils.error_analyzer import ErrorAnalyzer, apply_suggestions


class EndpointProber:
    """Probes endpoints to discover working payloads."""

    def __init__(self, config: Dict[str, Any], max_attempts: int = 12):
        """
        Initialize endpoint prober.

        Args:
            config: Configuration from env_loader
            max_attempts: Maximum attempts per endpoint
        """
        self.config = config
        self.max_attempts = max_attempts
        self.http_client = HTTPClient(config)
        self.domain_seeds = self._load_domain_seeds()
        self.payload_gen = PayloadGenerator(self.domain_seeds, config)
        self.validator = SchemaValidator()
        self.error_analyzer = ErrorAnalyzer()
        self.results = []
        self.log_file = None

    def _load_domain_seeds(self) -> Dict[str, Any]:
        """Load existing domain seeds if available."""
        project_root = Path(__file__).parent.parent
        seeds_path = project_root / 'output' / 'domain_seeds.json'

        if seeds_path.exists():
            with open(seeds_path, 'r', encoding='utf-8') as f:
                return json.load(f)

        return {
            'dealers': [],
            'customers': [],
            'locations': [],
            'codes': [],
        }

    def _save_domain_seeds(self):
        """Save updated domain seeds."""
        project_root = Path(__file__).parent.parent
        output_dir = project_root / 'output'
        output_dir.mkdir(exist_ok=True)

        seeds_path = output_dir / 'domain_seeds.json'
        with open(seeds_path, 'w', encoding='utf-8') as f:
            json.dump(self.domain_seeds, f, indent=2, ensure_ascii=False)

    def _init_log_file(self):
        """Initialize NDJSON log file."""
        project_root = Path(__file__).parent.parent
        log_dir = project_root / 'logs'
        log_dir.mkdir(exist_ok=True)

        log_path = log_dir / 'run.ndjson'
        self.log_file = open(log_path, 'w', encoding='utf-8')

    def _log(self, entry: Dict[str, Any]):
        """Write a log entry."""
        if self.log_file:
            entry['ts'] = time.time()
            self.log_file.write(json.dumps(entry, ensure_ascii=False) + '\n')
            self.log_file.flush()

    def _should_skip_write(self, endpoint: Dict[str, Any]) -> Optional[str]:
        """
        Determine if a write operation should be skipped.

        Args:
            endpoint: Endpoint definition

        Returns:
            Skip reason if should skip, None otherwise
        """
        if not endpoint['is_write_operation']:
            return None

        # Check if endpoint has a dry_run or test mode parameter
        query_params = endpoint['parameters']['query']
        for param in query_params:
            param_name = param.get('name', '').lower()
            if param_name in ['dry_run', 'test', 'test_mode', 'dry_mode']:
                return None  # Has safe mode, proceed

        # No safe mode found
        return 'write_prohibited'

    def _extract_domain_seeds(self, endpoint: Dict[str, Any], response_data: Any):
        """
        Extract domain seeds from response data.

        Args:
            endpoint: Endpoint definition
            response_data: Response data
        """
        if not endpoint['is_lookup']:
            return

        path_lower = endpoint['path'].lower()

        # Extract dealers
        if 'dealer' in path_lower:
            if isinstance(response_data, list):
                for item in response_data[:5]:  # Limit to first 5
                    if isinstance(item, dict):
                        dealer = {
                            'id': item.get('id') or item.get('dealerId') or item.get('DealerId'),
                            'code': item.get('code') or item.get('dealerCode') or item.get('DealerCode'),
                            'name': item.get('name') or item.get('dealerName') or item.get('DealerName'),
                        }
                        if dealer['id'] or dealer['code']:
                            if dealer not in self.domain_seeds['dealers']:
                                self.domain_seeds['dealers'].append(dealer)

        # Extract customers
        if 'customer' in path_lower:
            if isinstance(response_data, list):
                for item in response_data[:5]:
                    if isinstance(item, dict):
                        customer = {
                            'id': item.get('id') or item.get('customerId') or item.get('CustomerId'),
                            'code': item.get('code') or item.get('customerCode') or item.get('CustomerCode'),
                            'dealer_id': item.get('dealerId') or item.get('DealerId'),
                        }
                        if customer['id'] or customer['code']:
                            if customer not in self.domain_seeds['customers']:
                                self.domain_seeds['customers'].append(customer)

        # Extract locations
        if 'location' in path_lower:
            if isinstance(response_data, list):
                for item in response_data[:5]:
                    if isinstance(item, dict):
                        location = {
                            'id': item.get('id') or item.get('locationId') or item.get('LocationId'),
                            'customer_id': item.get('customerId') or item.get('CustomerId'),
                            'name': item.get('name') or item.get('locationName') or item.get('LocationName'),
                        }
                        if location['id']:
                            if location not in self.domain_seeds['locations']:
                                self.domain_seeds['locations'].append(location)

        # Update payload generator with new seeds
        self.payload_gen.update_seeds(self.domain_seeds)
        self._save_domain_seeds()

    def probe_endpoint(self, endpoint: Dict[str, Any]) -> Dict[str, Any]:
        """
        Probe a single endpoint to discover working payload.

        Args:
            endpoint: Endpoint definition

        Returns:
            Result dictionary
        """
        result = {
            'path': endpoint['path'],
            'method': endpoint['method'],
            'operation_id': endpoint['operation_id'],
            'status': 'unknown',
            'attempts': 0,
            'skip_reason': None,
            'success': None,
            'errors': [],
        }

        # Check if should skip
        skip_reason = self._should_skip_write(endpoint)
        if skip_reason:
            result['status'] = 'skipped'
            result['skip_reason'] = skip_reason
            self._log({
                'method': endpoint['method'],
                'url': endpoint['path'],
                'outcome': 'skipped',
                'skip_reason': skip_reason,
            })
            return result

        # Generate path
        path = endpoint['path']
        path_params = endpoint['parameters']['path']
        if path_params:
            filled_path, _ = self.payload_gen.generate_path_params(path, path_params)
            path = filled_path

        # Try to discover working payload
        last_query_params = None
        last_request_body = None

        for attempt in range(self.max_attempts):
            result['attempts'] = attempt + 1

            # Generate query params (fresh on first attempt, or use adjusted ones)
            if attempt == 0 or last_query_params is None:
                query_params = self.payload_gen.generate_query_params(
                    endpoint['parameters']['query']
                )
            else:
                # Use adjusted params from previous attempt
                query_params = last_query_params

            # Generate request body
            if attempt == 0 or last_request_body is None:
                request_body = None
                if endpoint['request_body']:
                    request_body = self.payload_gen.generate_request_body(
                        endpoint['request_body']
                    )
            else:
                # Use adjusted body from previous attempt
                request_body = last_request_body

            # Make request
            start_time = time.time()
            status_code, response_data, response_text = self.http_client.request(
                method=endpoint['method'],
                path=path,
                query_params=query_params,
                body=request_body,
                require_auth=endpoint['requires_auth'],
            )
            latency_ms = int((time.time() - start_time) * 1000)

            # Log attempt
            log_entry = {
                'method': endpoint['method'],
                'url': redact_url(f"{self.config['mps_base_url']}/{path}"),
                'status': status_code,
                'attempt': attempt + 1,
                'latency_ms': latency_ms,
                'query_params': redact(query_params),
                'request_body': redact(request_body) if request_body else None,
            }

            # Check result
            if 200 <= status_code < 300:
                # Success!
                result['status'] = 'discovered'
                result['success'] = {
                    'status_code': status_code,
                    'query_params': query_params,
                    'request_body': request_body,
                    'response_sample': response_data,
                    'latency_ms': latency_ms,
                }

                log_entry['outcome'] = 'success'
                self._log(log_entry)

                # Extract domain seeds if applicable
                self._extract_domain_seeds(endpoint, response_data)

                break

            elif 400 <= status_code < 500:
                # Client error - analyze and adjust payload
                error_msg = response_data.get('error') or response_data.get('message') or response_text[:200]
                result['errors'].append({
                    'attempt': attempt + 1,
                    'status': status_code,
                    'error': error_msg,
                })

                log_entry['outcome'] = 'client_error'
                log_entry['hint'] = error_msg[:100]
                self._log(log_entry)

                # Analyze error and get suggestions
                suggestions = self.error_analyzer.analyze_error(
                    status_code, response_data, response_text,
                    query_params, request_body
                )

                if suggestions:
                    # Apply suggestions for next attempt
                    adjusted_qp, adjusted_body = apply_suggestions(
                        suggestions, query_params, request_body,
                        self.config, self.domain_seeds, attempt
                    )
                    last_query_params = adjusted_qp
                    last_request_body = adjusted_body
                    log_entry['suggestions_applied'] = len(suggestions)
                else:
                    # No suggestions, keep current for retry
                    last_query_params = query_params
                    last_request_body = request_body

            elif status_code >= 500:
                # Server error
                result['errors'].append({
                    'attempt': attempt + 1,
                    'status': status_code,
                    'error': 'server_error',
                })

                log_entry['outcome'] = 'server_error'
                self._log(log_entry)

            else:
                # Network or other error
                result['errors'].append({
                    'attempt': attempt + 1,
                    'status': status_code,
                    'error': response_data.get('error', 'unknown_error'),
                })

                log_entry['outcome'] = 'network_error'
                self._log(log_entry)

            # Small delay between attempts
            time.sleep(0.5)

        # If we didn't succeed, mark as error
        if result['status'] == 'unknown':
            if result['errors'] and result['errors'][-1]['status'] >= 500:
                result['status'] = 'server_error'
            else:
                result['status'] = 'error'

        return result

    def probe_all(self, endpoints: List[Dict[str, Any]]):
        """
        Probe all endpoints.

        Args:
            endpoints: List of endpoint definitions
        """
        self._init_log_file()

        total = len(endpoints)
        print(f"\nProbing {total} endpoints...")
        print("=" * 60)

        for i, endpoint in enumerate(endpoints, 1):
            print(f"\n[{i}/{total}] {endpoint['method']} {endpoint['path']}")

            result = self.probe_endpoint(endpoint)
            self.results.append(result)

            # Print result
            if result['status'] == 'discovered':
                print(f"  [OK] SUCCESS (status {result['success']['status_code']}, "
                      f"{result['success']['latency_ms']}ms)")
            elif result['status'] == 'skipped':
                print(f"  [-] SKIPPED ({result['skip_reason']})")
            else:
                print(f"  [X] FAILED (status {result['status']}, "
                      f"{result['attempts']} attempts)")

            # Save results incrementally
            if i % 10 == 0:
                self._save_results()

        # Final save
        self._save_results()

        if self.log_file:
            self.log_file.close()

        # Print summary
        self._print_summary()

    def _save_results(self):
        """Save probe results."""
        project_root = Path(__file__).parent.parent
        output_dir = project_root / 'output'
        output_dir.mkdir(exist_ok=True)

        results_path = output_dir / 'probe_results.json'
        with open(results_path, 'w', encoding='utf-8') as f:
            json.dump(self.results, f, indent=2, ensure_ascii=False)

    def _print_summary(self):
        """Print summary statistics."""
        total = len(self.results)
        discovered = sum(1 for r in self.results if r['status'] == 'discovered')
        skipped = sum(1 for r in self.results if r['status'] == 'skipped')
        errors = total - discovered - skipped

        print("\n" + "=" * 60)
        print("SUMMARY")
        print("=" * 60)
        print(f"Total endpoints:    {total}")
        print(f"Discovered:         {discovered} ({discovered*100//total if total else 0}%)")
        print(f"Skipped:            {skipped} ({skipped*100//total if total else 0}%)")
        print(f"Failed:             {errors} ({errors*100//total if total else 0}%)")

        # Domain seeds
        print(f"\nDomain seeds discovered:")
        print(f"  Dealers:    {len(self.domain_seeds['dealers'])}")
        print(f"  Customers:  {len(self.domain_seeds['customers'])}")
        print(f"  Locations:  {len(self.domain_seeds['locations'])}")


def main():
    """Main entry point."""
    import argparse

    parser = argparse.ArgumentParser(description='Probe API endpoints')
    parser.add_argument('--input', default='output/endpoints.json',
                        help='Input endpoints file')
    parser.add_argument('--max-attempts', type=int, default=12,
                        help='Maximum attempts per endpoint')
    parser.add_argument('--limit', type=int, default=None,
                        help='Limit number of endpoints to probe')

    args = parser.parse_args()

    # Load config
    config = load_env()

    # Load endpoints
    project_root = Path(__file__).parent.parent
    endpoints_path = project_root / args.input

    if not endpoints_path.exists():
        print(f"Error: Endpoints file not found: {endpoints_path}", file=sys.stderr)
        print("Run discover_endpoints.py first.", file=sys.stderr)
        sys.exit(1)

    with open(endpoints_path, 'r', encoding='utf-8') as f:
        endpoints = json.load(f)

    # Limit if requested
    if args.limit:
        endpoints = endpoints[:args.limit]

    # Probe
    prober = EndpointProber(config, max_attempts=args.max_attempts)
    prober.probe_all(endpoints)


if __name__ == '__main__':
    main()
