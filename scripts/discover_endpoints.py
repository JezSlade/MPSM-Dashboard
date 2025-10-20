#!/usr/bin/env python3
"""
Discover endpoints from swagger.json and emit normalized endpoint matrix.
"""
import json
import sys
from pathlib import Path
from typing import Dict, Any, List


def parse_swagger(swagger_path: str) -> Dict[str, Any]:
    """
    Load and parse swagger.json file.

    Args:
        swagger_path: Path to swagger.json

    Returns:
        Parsed swagger document
    """
    with open(swagger_path, 'r', encoding='utf-8') as f:
        return json.load(f)


def normalize_endpoint(
    path: str,
    method: str,
    operation: Dict[str, Any],
    swagger: Dict[str, Any]
) -> Dict[str, Any]:
    """
    Normalize an endpoint operation into a standard format.

    Args:
        path: API path
        method: HTTP method
        operation: OpenAPI operation object
        swagger: Full swagger document (for resolving refs)

    Returns:
        Normalized endpoint dict
    """
    # Extract parameters
    parameters = operation.get('parameters', [])

    # Categorize parameters by location
    path_params = [p for p in parameters if p.get('in') == 'path']
    query_params = [p for p in parameters if p.get('in') == 'query']
    header_params = [p for p in parameters if p.get('in') == 'header']

    # Extract request body
    request_body = operation.get('requestBody')

    # Extract responses
    responses = operation.get('responses', {})
    success_responses = {
        code: resp for code, resp in responses.items()
        if code.startswith('2')
    }

    # Determine auth requirements
    security = operation.get('security', swagger.get('security', []))
    # IMPORTANT: This Swagger.json doesn't document security, but the API requires auth
    # Default to requiring auth for all endpoints unless explicitly public
    requires_auth = True if len(security) == 0 else len(security) > 0

    # Determine if this is a destructive operation
    is_write_operation = method.upper() in ['POST', 'PUT', 'PATCH', 'DELETE']

    # Check if it's a list/lookup endpoint (for domain seed discovery)
    is_lookup = any([
        'list' in path.lower(),
        'getall' in path.lower(),
        'dealer' in path.lower() and method.upper() == 'GET',
        'customer' in path.lower() and method.upper() == 'GET',
        'location' in path.lower() and method.upper() == 'GET',
    ])

    # Determine priority for discovery (lower = higher priority)
    priority = 999
    if is_lookup and not is_write_operation:
        priority = 1  # Highest priority - domain seeds
    elif not is_write_operation and not path_params:
        priority = 10  # High priority - simple GETs
    elif not is_write_operation:
        priority = 50  # Medium priority - GETs with params
    elif is_write_operation:
        priority = 100  # Low priority - writes

    return {
        'path': path,
        'method': method.upper(),
        'operation_id': operation.get('operationId', ''),
        'summary': operation.get('summary', ''),
        'description': operation.get('description', ''),
        'tags': operation.get('tags', []),
        'parameters': {
            'path': path_params,
            'query': query_params,
            'header': header_params,
        },
        'request_body': request_body,
        'responses': {
            'success': success_responses,
            'all': responses,
        },
        'security': security,
        'requires_auth': requires_auth,
        'is_write_operation': is_write_operation,
        'is_lookup': is_lookup,
        'priority': priority,
        'deprecated': operation.get('deprecated', False),
    }


def discover_endpoints(swagger_path: str) -> List[Dict[str, Any]]:
    """
    Discover all endpoints from swagger.json.

    Args:
        swagger_path: Path to swagger.json

    Returns:
        List of normalized endpoints
    """
    swagger = parse_swagger(swagger_path)

    endpoints = []
    paths = swagger.get('paths', {})

    for path, path_item in paths.items():
        # OpenAPI allows these HTTP methods
        http_methods = ['get', 'post', 'put', 'patch', 'delete', 'head', 'options']

        for method in http_methods:
            if method in path_item:
                operation = path_item[method]
                endpoint = normalize_endpoint(path, method, operation, swagger)
                endpoints.append(endpoint)

    # Sort by priority (domain seeds first, then safe reads, then writes)
    endpoints.sort(key=lambda e: (e['priority'], e['path'], e['method']))

    return endpoints


def main():
    """Main entry point."""
    # Find swagger.json in project root
    project_root = Path(__file__).parent.parent
    swagger_path = project_root / 'Swagger.json'

    if not swagger_path.exists():
        print(f"Error: Swagger.json not found at {swagger_path}", file=sys.stderr)
        sys.exit(1)

    print(f"Discovering endpoints from {swagger_path}...")

    endpoints = discover_endpoints(str(swagger_path))

    # Statistics
    total = len(endpoints)
    lookup_count = sum(1 for e in endpoints if e['is_lookup'])
    write_count = sum(1 for e in endpoints if e['is_write_operation'])
    read_count = total - write_count

    print(f"Discovered {total} endpoints:")
    print(f"  - {lookup_count} lookup/list endpoints (domain seeds)")
    print(f"  - {read_count} read operations")
    print(f"  - {write_count} write operations")

    # Save to output
    output_dir = project_root / 'output'
    output_dir.mkdir(exist_ok=True)

    output_path = output_dir / 'endpoints.json'
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(endpoints, f, indent=2, ensure_ascii=False)

    print(f"\nEndpoint matrix saved to {output_path}")

    # Save summary by tag
    by_tag = {}
    for endpoint in endpoints:
        tags = endpoint.get('tags', ['untagged'])
        for tag in tags:
            if tag not in by_tag:
                by_tag[tag] = []
            by_tag[tag].append(endpoint)

    summary_path = output_dir / 'endpoints_by_tag.json'
    with open(summary_path, 'w', encoding='utf-8') as f:
        summary = {
            tag: {
                'count': len(eps),
                'endpoints': [{'path': e['path'], 'method': e['method']} for e in eps]
            }
            for tag, eps in sorted(by_tag.items())
        }
        json.dump(summary, f, indent=2, ensure_ascii=False)

    print(f"Summary by tag saved to {summary_path}")
    print("\nTop tags:")
    for tag, eps in sorted(by_tag.items(), key=lambda x: -len(x[1]))[:10]:
        print(f"  - {tag}: {len(eps)} endpoints")


if __name__ == '__main__':
    main()
