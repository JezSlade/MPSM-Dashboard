#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Verify that the canonical swagger.json is properly structured
and can be parsed by the SwaggerActionRegistry logic.
"""

import json
import sys
import io
from pathlib import Path
from collections import defaultdict

# Set UTF-8 encoding for stdout
if sys.platform == 'win32':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

def main():
    print("=" * 80)
    print("MPS API Engine - Canonical Swagger Integration Verification")
    print("=" * 80)
    print()

    # Load the canonical swagger
    canonical_path = Path(__file__).parent.parent / '.canonical' / 'Swagger.json'

    if not canonical_path.exists():
        print(f"❌ ERROR: Canonical swagger not found at {canonical_path}")
        return 1

    print(f"✓ Found canonical swagger at: {canonical_path}")
    print(f"  File size: {canonical_path.stat().st_size:,} bytes")
    print()

    try:
        with open(canonical_path, 'r', encoding='utf-8') as f:
            spec = json.load(f)
    except json.JSONDecodeError as e:
        print(f"❌ ERROR: Failed to parse swagger JSON: {e}")
        return 1

    print("✓ Swagger JSON parsed successfully")
    print()

    # Analyze the spec
    print("Swagger Specification Details:")
    print("-" * 80)
    print(f"  Version: {spec.get('swagger', spec.get('openapi', 'unknown'))}")
    print(f"  Title: {spec.get('info', {}).get('title', 'N/A')}")
    print(f"  API Version: {spec.get('info', {}).get('version', 'N/A')}")
    print(f"  Host: {spec.get('host', 'N/A')}")
    print(f"  Base Path: {spec.get('basePath', '/')}")
    print(f"  Schemes: {', '.join(spec.get('schemes', []))}")
    print()

    # Count paths and operations
    paths = spec.get('paths', {})
    print(f"Total Paths: {len(paths)}")
    print()

    # Parse operations
    operations = []
    operations_by_tag = defaultdict(list)
    methods_count = defaultdict(int)

    for path, methods in paths.items():
        if not isinstance(methods, dict):
            continue

        for method, definition in methods.items():
            if method == 'parameters' or not isinstance(definition, dict):
                continue

            operation_id = definition.get('operationId', f'{method.upper()} {path}')
            tags = definition.get('tags', ['untagged'])
            summary = definition.get('summary', '')

            operation = {
                'path': path,
                'method': method.upper(),
                'operationId': operation_id,
                'tags': tags,
                'summary': summary,
                'parameters': definition.get('parameters', []),
                'consumes': definition.get('consumes', []),
                'produces': definition.get('produces', []),
            }

            operations.append(operation)
            operations_by_tag[tags[0] if tags else 'untagged'].append(operation)
            methods_count[method.upper()] += 1

    print(f"Total Operations: {len(operations)}")
    print()

    # HTTP Methods breakdown
    print("HTTP Methods:")
    print("-" * 80)
    for method, count in sorted(methods_count.items()):
        bar = '█' * (count // 10)
        print(f"  {method:8s} {count:4d} {bar}")
    print()

    # Tags/Groups
    print(f"Operation Groups (by tag): {len(operations_by_tag)}")
    print("-" * 80)
    for tag, ops in sorted(operations_by_tag.items(), key=lambda x: len(x[1]), reverse=True)[:15]:
        print(f"  {tag:40s} {len(ops):4d} operations")
    if len(operations_by_tag) > 15:
        print(f"  ... and {len(operations_by_tag) - 15} more groups")
    print()

    # Parameter types analysis
    param_locations = defaultdict(int)
    for op in operations:
        for param in op['parameters']:
            location = param.get('in', 'unknown')
            param_locations[location] += 1

    print("Parameter Locations:")
    print("-" * 80)
    for location, count in sorted(param_locations.items()):
        print(f"  {location:15s} {count:5d} parameters")
    print()

    # Sample operations
    print("Sample Operations (first 20):")
    print("-" * 80)
    for i, op in enumerate(operations[:20], 1):
        summary = op['summary'][:40] + '...' if len(op['summary']) > 40 else op['summary']
        print(f"{i:2d}. {op['method']:6s} {op['path']:45s}")
        print(f"    ID: {op['operationId']}")
        if summary:
            print(f"    → {summary}")
    print()

    # Verify key operations exist
    print("Verifying Key Operations:")
    print("-" * 80)

    key_operations = [
        'Account/GetProfile',
        'Account/Login',
        'Dealer/List',
        'Dealer/Get',
        'Customer/List',
        'Customer/Get',
        'Device/List',
        'Device/Get',
        'InstalledProduct/List',
    ]

    found_count = 0
    for key_op in key_operations:
        found = any(op['operationId'] == key_op for op in operations)
        status = '✓' if found else '✗'
        print(f"  {status} {key_op}")
        if found:
            found_count += 1

    print()
    print(f"Found {found_count}/{len(key_operations)} key operations")
    print()

    # Verify SwaggerActionRegistry compatibility
    print("SwaggerActionRegistry Compatibility Check:")
    print("-" * 80)

    checks = []

    # Check 1: Has paths
    checks.append(('Has "paths" section', 'paths' in spec and len(spec['paths']) > 0))

    # Check 2: Operations have IDs
    ops_with_ids = sum(1 for op in operations if op['operationId'])
    checks.append(('Operations have IDs', ops_with_ids == len(operations)))

    # Check 3: Has definitions (for schema references)
    checks.append(('Has "definitions" section', 'definitions' in spec))

    # Check 4: Parameters properly structured
    all_params_valid = all(
        isinstance(param, dict) and 'name' in param and 'in' in param
        for op in operations
        for param in op['parameters']
    )
    checks.append(('Parameters properly structured', all_params_valid))

    # Check 5: Paths use standard format
    all_paths_valid = all(path.startswith('/') for path in paths.keys())
    checks.append(('Paths use standard format', all_paths_valid))

    for check_name, passed in checks:
        status = '✓' if passed else '✗'
        print(f"  {status} {check_name}")

    all_passed = all(passed for _, passed in checks)
    print()

    if all_passed:
        print("=" * 80)
        print("✓ SUCCESS: Canonical swagger is fully compatible!")
        print(f"  All {len(operations)} operations are ready to use with the API engine.")
        print("=" * 80)
        return 0
    else:
        print("=" * 80)
        print("⚠ WARNING: Some compatibility issues detected")
        print("=" * 80)
        return 1

if __name__ == '__main__':
    sys.exit(main())
