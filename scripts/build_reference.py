#!/usr/bin/env python3
"""
Build reference files from probe results.
"""
import json
import sys
from pathlib import Path
from typing import Dict, Any, List
import yaml

# Add parent directory to path for imports
sys.path.insert(0, str(Path(__file__).parent))

from utils.redact import redact, redact_url


def build_endpoint_reference(
    endpoints: List[Dict[str, Any]],
    probe_results: List[Dict[str, Any]]
) -> List[Dict[str, Any]]:
    """
    Build endpoint reference from probe results.

    Args:
        endpoints: Original endpoint definitions
        probe_results: Probe results

    Returns:
        List of reference entries
    """
    reference = []

    # Create lookup map
    results_map = {
        (r['path'], r['method']): r
        for r in probe_results
    }

    for endpoint in endpoints:
        key = (endpoint['path'], endpoint['method'])
        result = results_map.get(key)

        if not result:
            continue

        entry = {
            'path': endpoint['path'],
            'method': endpoint['method'],
            'operation_id': endpoint['operation_id'],
            'summary': endpoint['summary'],
            'tags': endpoint['tags'],
        }

        # Add auth info
        if endpoint['requires_auth']:
            entry['auth'] = {
                'scheme': 'bearer',
                'header': 'Authorization',
            }

        # Add result based on status
        if result['status'] == 'discovered':
            success = result['success']
            entry['status'] = 'discovered'
            entry['params'] = {
                'query': success.get('query_params', {}),
            }
            entry['payload_template'] = success.get('request_body')
            entry['success'] = {
                'status': success['status_code'],
                'latency_ms': success['latency_ms'],
            }

            # Check for pagination in response
            response_sample = success.get('response_sample', {})
            if isinstance(response_sample, dict):
                # Common pagination indicators
                pagination = None
                if 'nextPageToken' in response_sample:
                    pagination = {
                        'type': 'cursor',
                        'next_key': 'nextPageToken',
                    }
                elif 'next' in response_sample:
                    pagination = {
                        'type': 'cursor',
                        'next_key': 'next',
                    }
                elif 'page' in response_sample and 'totalPages' in response_sample:
                    pagination = {
                        'type': 'page_number',
                        'page_key': 'page',
                        'total_key': 'totalPages',
                    }

                if pagination:
                    entry['success']['pagination'] = pagination

        elif result['status'] == 'skipped':
            entry['status'] = 'skipped'
            entry['skip_reason'] = result['skip_reason']

        else:
            entry['status'] = 'error'
            entry['attempts'] = result['attempts']
            if result['errors']:
                # Include last error
                last_error = result['errors'][-1]
                entry['last_error'] = {
                    'status': last_error.get('status'),
                    'message': str(last_error.get('error', ''))[:200],
                }

        reference.append(entry)

    return reference


def build_curl_recipes(
    reference: List[Dict[str, Any]],
    base_url: str
) -> str:
    """
    Build cURL recipes markdown.

    Args:
        reference: Endpoint reference
        base_url: API base URL

    Returns:
        Markdown content
    """
    lines = [
        '# cURL Recipes',
        '',
        'Copy-pasteable cURL commands for each endpoint.',
        '',
        '**Note**: Replace `<REDACTED>` with actual values from your `.env` file.',
        '',
    ]

    # Group by tag
    by_tag = {}
    for entry in reference:
        tags = entry.get('tags', ['Untagged'])
        for tag in tags:
            if tag not in by_tag:
                by_tag[tag] = []
            by_tag[tag].append(entry)

    # Generate recipes by tag
    for tag in sorted(by_tag.keys()):
        lines.append(f'## {tag}')
        lines.append('')

        for entry in by_tag[tag]:
            if entry['status'] != 'discovered':
                continue

            # Build URL
            path = entry['path']
            query_params = entry.get('params', {}).get('query', {})
            url = f'{base_url}/{path.lstrip("/")}'

            if query_params:
                query_str = '&'.join(f'{k}={v}' for k, v in query_params.items())
                url += f'?{query_str}'

            # Redact URL
            url = redact_url(url)

            # Build curl command
            curl_parts = ['curl']

            # Method
            if entry['method'] != 'GET':
                curl_parts.append(f'-X {entry["method"]}')

            # Headers
            if entry.get('auth'):
                curl_parts.append('-H "Authorization: Bearer <REDACTED>"')

            # Body
            payload = entry.get('payload_template')
            if payload:
                curl_parts.append('-H "Content-Type: application/json"')
                # Redact payload
                redacted_payload = redact(payload)
                payload_json = json.dumps(redacted_payload, ensure_ascii=False)
                curl_parts.append(f"-d '{payload_json}'")

            # URL
            curl_parts.append(f'"{url}"')

            # Write to markdown
            operation_id = entry.get('operation_id', '')
            summary = entry.get('summary', '')

            lines.append(f'### {entry["method"]} {entry["path"]}')
            if summary:
                lines.append(f'_{summary}_')
                lines.append('')
            if operation_id:
                lines.append(f'**Operation ID**: `{operation_id}`')
                lines.append('')

            lines.append('```bash')
            lines.append(' '.join(curl_parts))
            lines.append('```')
            lines.append('')

    return '\n'.join(lines)


def save_samples(
    reference: List[Dict[str, Any]],
    output_dir: Path
):
    """
    Save sample request/response files.

    Args:
        reference: Endpoint reference
        output_dir: Output directory
    """
    samples_dir = output_dir / 'samples'
    samples_dir.mkdir(exist_ok=True)

    for entry in reference:
        if entry['status'] != 'discovered':
            continue

        # Create safe filename
        operation_id = entry.get('operation_id', '')
        if not operation_id:
            # Generate from path and method
            path_safe = entry['path'].replace('/', '_').replace('{', '').replace('}', '')
            operation_id = f"{entry['method']}_{path_safe}"

        # Save request
        request_file = samples_dir / f'{operation_id}_request.json'
        request_data = {
            'method': entry['method'],
            'path': entry['path'],
            'query_params': entry.get('params', {}).get('query', {}),
            'body': entry.get('payload_template'),
        }

        with open(request_file, 'w', encoding='utf-8') as f:
            json.dump(redact(request_data), f, indent=2, ensure_ascii=False)


def generate_coverage_report(reference: List[Dict[str, Any]]) -> str:
    """
    Generate coverage report.

    Args:
        reference: Endpoint reference

    Returns:
        Report text
    """
    total = len(reference)
    discovered = sum(1 for e in reference if e['status'] == 'discovered')
    skipped = sum(1 for e in reference if e['status'] == 'skipped')
    errors = sum(1 for e in reference if e['status'] == 'error')

    lines = [
        '# API Discovery Coverage Report',
        '',
        f'**Total Endpoints**: {total}',
        f'**Discovered**: {discovered} ({discovered*100//total if total else 0}%)',
        f'**Skipped**: {skipped} ({skipped*100//total if total else 0}%)',
        f'**Errors**: {errors} ({errors*100//total if total else 0}%)',
        '',
        '## Breakdown by Status',
        '',
    ]

    # Discovered
    lines.append(f'### Discovered ({discovered})')
    lines.append('')
    for entry in reference:
        if entry['status'] == 'discovered':
            lines.append(f'- `{entry["method"]} {entry["path"]}` - {entry.get("summary", "")}')
    lines.append('')

    # Skipped
    if skipped > 0:
        lines.append(f'### Skipped ({skipped})')
        lines.append('')
        skip_reasons = {}
        for entry in reference:
            if entry['status'] == 'skipped':
                reason = entry.get('skip_reason', 'unknown')
                if reason not in skip_reasons:
                    skip_reasons[reason] = []
                skip_reasons[reason].append(entry)

        for reason, entries in skip_reasons.items():
            lines.append(f'#### {reason} ({len(entries)})')
            lines.append('')
            for entry in entries[:10]:  # Limit to 10
                lines.append(f'- `{entry["method"]} {entry["path"]}`')
            if len(entries) > 10:
                lines.append(f'- ... and {len(entries) - 10} more')
            lines.append('')

    # Errors
    if errors > 0:
        lines.append(f'### Errors ({errors})')
        lines.append('')
        for entry in reference[:20]:  # Limit to 20
            if entry['status'] == 'error':
                last_error = entry.get('last_error', {})
                lines.append(f'- `{entry["method"]} {entry["path"]}` - '
                             f'Status {last_error.get("status")}: '
                             f'{last_error.get("message", "")[:50]}')
        if errors > 20:
            lines.append(f'- ... and {errors - 20} more')
        lines.append('')

    return '\n'.join(lines)


def main():
    """Main entry point."""
    project_root = Path(__file__).parent.parent
    output_dir = project_root / 'output'

    # Load endpoints
    endpoints_path = output_dir / 'endpoints.json'
    if not endpoints_path.exists():
        print(f"Error: {endpoints_path} not found", file=sys.stderr)
        sys.exit(1)

    with open(endpoints_path, 'r', encoding='utf-8') as f:
        endpoints = json.load(f)

    # Load probe results
    results_path = output_dir / 'probe_results.json'
    if not results_path.exists():
        print(f"Error: {results_path} not found", file=sys.stderr)
        print("Run probe_endpoint.py first.", file=sys.stderr)
        sys.exit(1)

    with open(results_path, 'r', encoding='utf-8') as f:
        probe_results = json.load(f)

    # Load config for base URL
    from utils.env_loader import load_env
    config = load_env()
    base_url = config['mps_base_url']

    print("Building reference files...")

    # Build endpoint reference
    reference = build_endpoint_reference(endpoints, probe_results)

    # Save as YAML
    reference_path = output_dir / 'endpoint_reference.yaml'
    with open(reference_path, 'w', encoding='utf-8') as f:
        yaml.dump(reference, f, default_flow_style=False, sort_keys=False, allow_unicode=True)
    print(f"[OK] Saved endpoint reference to {reference_path}")

    # Build cURL recipes
    curl_recipes = build_curl_recipes(reference, base_url)
    curl_path = output_dir / 'curl_recipes.md'
    with open(curl_path, 'w', encoding='utf-8') as f:
        f.write(curl_recipes)
    print(f"[OK] Saved cURL recipes to {curl_path}")

    # Save samples
    save_samples(reference, output_dir)
    print(f"[OK] Saved samples to {output_dir / 'samples'}")

    # Generate coverage report
    coverage_report = generate_coverage_report(reference)
    coverage_path = output_dir / 'coverage_report.md'
    with open(coverage_path, 'w', encoding='utf-8') as f:
        f.write(coverage_report)
    print(f"[OK] Saved coverage report to {coverage_path}")

    print("\nDone! Reference files are ready for API engine implementation.")


if __name__ == '__main__':
    main()
