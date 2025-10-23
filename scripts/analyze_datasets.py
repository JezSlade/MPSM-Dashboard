#!/usr/bin/env python3
"""
Analyze collected GET endpoint datasets and create comprehensive catalog
with exact data structures and example values
"""

import json
import os
from datetime import datetime
from collections import Counter

def analyze_structure(obj, max_depth=3, current_depth=0):
    """Recursively analyze the structure of an object"""
    if current_depth > max_depth:
        return "..."

    if obj is None:
        return "null"
    elif isinstance(obj, bool):
        return f"boolean (example: {obj})"
    elif isinstance(obj, int):
        return f"integer (example: {obj})"
    elif isinstance(obj, float):
        return f"float (example: {obj})"
    elif isinstance(obj, str):
        if len(obj) > 50:
            return f"string (example: \"{obj[:47]}...\")"
        return f"string (example: \"{obj}\")"
    elif isinstance(obj, list):
        if len(obj) == 0:
            return "array (empty)"
        # Analyze first item as representative
        return f"array of {analyze_structure(obj[0], max_depth, current_depth+1)} ({len(obj)} items)"
    elif isinstance(obj, dict):
        structure = {}
        for key, value in obj.items():
            structure[key] = analyze_structure(value, max_depth, current_depth+1)
        return structure
    else:
        return str(type(obj).__name__)

def main():
    script_dir = os.path.dirname(os.path.abspath(__file__))
    input_file = os.path.join(script_dir, '..', 'output', 'get_endpoint_data.json')
    output_file = os.path.join(script_dir, '..', 'output', 'payload_templates.json')
    catalog_file = os.path.join(script_dir, '..', 'output', 'COMPLETE_ENDPOINT_CATALOG.md')

    print("Loading collected endpoint data...")
    with open(input_file, 'r', encoding='utf-8') as f:
        data = json.load(f)

    total = data['endpoint_count']
    successful = [r for r in data['results'] if r['success']]
    failed = [r for r in data['results'] if not r['success']]

    print(f"Total endpoints: {total}")
    print(f"Successful: {len(successful)} ({len(successful)/total*100:.1f}%)")
    print(f"Failed: {len(failed)} ({len(failed)/total*100:.1f}%)")
    print()

    # Build templates from successful endpoints
    templates = {}
    catalog_entries = []

    for result in successful:
        action = result['action']
        response_data = result['response'].get('data') if result['response'] else None

        if response_data is not None:
            # Analyze structure
            structure = analyze_structure(response_data)

            # Build catalog entry
            entry = {
                'action': action,
                'success': True,
                'data_type': type(response_data).__name__,
                'item_count': len(response_data) if isinstance(response_data, (list, dict)) else None,
                'structure': structure,
                'sample_data': response_data if isinstance(response_data, str) else (
                    response_data[0] if isinstance(response_data, list) and response_data else (
                        response_data if isinstance(response_data, dict) else None
                    )
                )
            }

            catalog_entries.append(entry)

            # Create payload template
            templates[action] = {
                'query': {},
                'response_type': type(response_data).__name__,
                'response_structure': structure
            }

    # Save templates JSON
    print(f"Saving {len(templates)} payload templates...")
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(templates, f, indent=2, ensure_ascii=False)

    # Create markdown catalog
    print(f"Creating comprehensive catalog...")

    with open(catalog_file, 'w', encoding='utf-8') as f:
        f.write("# Complete MPSM API GET Endpoint Catalog\n\n")
        f.write(f"**Generated**: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n\n")
        f.write(f"**Total Endpoints Tested**: {total}\n")
        f.write(f"**Successful**: {len(successful)} ({len(successful)/total*100:.1f}%)\n")
        f.write(f"**Failed**: {len(failed)} ({len(failed)/total*100:.1f}%)\n\n")

        f.write("---\n\n")

        f.write("## Table of Contents\n\n")
        f.write("- [Successful Endpoints](#successful-endpoints)\n")
        f.write("- [Failed Endpoints](#failed-endpoints)\n")
        f.write("- [Dataset Structures](#dataset-structures)\n\n")

        f.write("---\n\n")

        # Successful endpoints summary
        f.write("## Successful Endpoints\n\n")

        # Group by data type
        by_type = {'list': [], 'dict': [], 'str': []}
        for entry in catalog_entries:
            by_type[entry['data_type']].append(entry)

        f.write(f"### List Responses ({len(by_type['list'])} endpoints)\n\n")
        f.write("| Endpoint | Items | Structure |\n")
        f.write("|----------|-------|------|-----|\n")

        for entry in sorted(by_type['list'], key=lambda x: x['item_count'] or 0, reverse=True):
            count = entry['item_count'] or 0
            f.write(f"| {entry['action']} | {count} | Array |\n")

        f.write(f"\n### Object Responses ({len(by_type['dict'])} endpoints)\n\n")
        f.write("| Endpoint | Fields |\n")
        f.write("|----------|--------|\n")

        for entry in sorted(by_type['dict'], key=lambda x: x['action']):
            field_count = len(entry['structure']) if isinstance(entry['structure'], dict) else 0
            f.write(f"| {entry['action']} | {field_count} |\n")

        f.write(f"\n### String Responses ({len(by_type['str'])} endpoints)\n\n")
        for entry in by_type['str']:
            f.write(f"- **{entry['action']}**: Returns string data\n")

        # Dataset structures
        f.write("\n---\n\n")
        f.write("## Dataset Structures\n\n")
        f.write("Detailed structure and example values for each successful endpoint.\n\n")

        for entry in sorted(catalog_entries, key=lambda x: x['action']):
            f.write(f"### {entry['action']}\n\n")
            f.write(f"**Type**: `{entry['data_type']}`\n\n")

            if entry['item_count'] is not None:
                f.write(f"**Count**: {entry['item_count']} items\n\n")

            f.write("**Structure**:\n\n")
            f.write("```json\n")
            f.write(json.dumps(entry['structure'], indent=2, ensure_ascii=False))
            f.write("\n```\n\n")

            if entry['sample_data']:
                f.write("**Sample Data**:\n\n")
                f.write("```json\n")
                sample_json = json.dumps(entry['sample_data'], indent=2, ensure_ascii=False)
                f.write(sample_json[:2000])  # Limit to 2000 chars
                if len(sample_json) > 2000:
                    f.write("\n... (truncated)")
                f.write("\n```\n\n")

            f.write("---\n\n")

        # Failed endpoints
        f.write("## Failed Endpoints\n\n")
        f.write(f"The following {len(failed)} endpoints returned errors:\n\n")

        # Group by error
        errors = Counter()
        for r in failed:
            error = r['error'] or 'Unknown error'
            errors[error] += 1

        f.write("### Error Summary\n\n")
        for error, count in errors.most_common():
            f.write(f"- **{error}**: {count} endpoints\n")

        f.write("\n### Failed Endpoint List\n\n")
        f.write("| Endpoint | Error |\n")
        f.write("|----------|-------|\n")

        for r in sorted(failed, key=lambda x: x['action']):
            error = r['error'] or 'Unknown'
            if len(error) > 60:
                error = error[:57] + "..."
            f.write(f"| {r['action']} | {error} |\n")

    print(f"\nCatalog saved to: {catalog_file}")
    print(f"Templates saved to: {output_file}")

    print(f"\nSummary:")
    print(f"  - {len(by_type['list'])} endpoints return lists")
    print(f"  - {len(by_type['dict'])} endpoints return objects")
    print(f"  - {len(by_type['str'])} endpoints return strings")

if __name__ == '__main__':
    main()
