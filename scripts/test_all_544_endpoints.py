#!/usr/bin/env python3
"""
Test ALL 544 MPSM API Endpoints (GET and POST methods via /query)
Document complete response structures with real data
"""

import json
import requests
import time
import os
from datetime import datetime

API_URL = 'https://mpsm.resolutionsbydesign.us/mps-api/query'

# Prerequisites from previous discovery
DEALER_CODE = 'NY06AGDWUQ'
DEALER_ID = 'SZ13qRwU5GtFLj0i_CbEgQ2'
CUSTOMER_CODE = 'W9OPXL0YDK'
CUSTOMER_ID = '0xUi5WEYLzOCrZ8ILowOvA2'

# Device IDs to try (will collect more as we go)
DEVICE_IDS = []
PRODUCT_IDS = []
ROLE_IDS = []

def load_all_actions():
    """Load all 544 actions from swagger"""
    script_dir = os.path.dirname(os.path.abspath(__file__))
    swagger_file = os.path.join(script_dir, '..', 'mps-api', 'swagger.json')

    with open(swagger_file, 'r') as f:
        swagger = json.load(f)

    # Extract all action names from paths
    actions = []
    for path in swagger['paths'].keys():
        action = path.lstrip('/')
        actions.append(action)

    return sorted(actions)

def test_endpoint(action, params):
    """Test a single endpoint"""
    try:
        response = requests.post(API_URL, json={'action': action, 'params': params}, timeout=30)
        data = response.json()

        success = data.get('success', False)
        result_data = data.get('data')

        return {
            'action': action,
            'success': success,
            'data': result_data,
            'data_type': type(result_data).__name__ if result_data is not None else None,
            'count': len(result_data) if isinstance(result_data, (list, dict)) else None,
            'error': data.get('error'),
            'params_used': params,
            'http_code': response.status_code
        }
    except Exception as e:
        return {
            'action': action,
            'success': False,
            'data': None,
            'error': str(e),
            'params_used': params
        }

def smart_params_for_action(action):
    """Build smart parameter sets for an action"""
    action_lower = action.lower()

    # Common parameter combinations to try
    param_sets = [
        {},  # Empty first
    ]

    # Dealer parameters
    if 'dealer' in action_lower:
        param_sets.extend([
            {'dealerCode': DEALER_CODE},
            {'dealerCode': DEALER_CODE, 'pageNumber': 1, 'pageRows': 100},
            {'FilterDealerId': DEALER_ID},
            {'FilterDealerId': DEALER_ID, 'pageNumber': 1, 'pageRows': 100},
        ])

    # Customer parameters
    if 'customer' in action_lower:
        param_sets.extend([
            {'customerCode': CUSTOMER_CODE},
            {'FilterCustomerId': CUSTOMER_ID},
            {'customerCode': CUSTOMER_CODE, 'pageNumber': 1, 'pageRows': 100},
        ])

    # Device parameters
    if 'device' in action_lower:
        param_sets.extend([
            {'FilterDealerId': DEALER_ID, 'pageNumber': 1, 'pageRows': 100},
            {'FilterDealerId': DEALER_ID, 'FilterCustomerId': CUSTOMER_ID, 'pageNumber': 1, 'pageRows': 100},
        ])

        if DEVICE_IDS:
            param_sets.append({'deviceId': DEVICE_IDS[0]})

    # List endpoints usually need pagination
    if 'list' in action_lower or 'search' in action_lower:
        for ps in param_sets[:]:
            if 'pageNumber' not in ps:
                param_sets.append({**ps, 'pageNumber': 1, 'pageRows': 100})

    return param_sets

def test_action_with_variants(action):
    """Test an action with multiple parameter combinations"""
    param_sets = smart_params_for_action(action)

    for params in param_sets:
        result = test_endpoint(action, params)

        if result['success']:
            # Extract useful IDs for future tests
            if result['data'] and isinstance(result['data'], list):
                for item in result['data'][:5]:  # First 5 items
                    if isinstance(item, dict):
                        if 'Id' in item and 'Device' in action and item['Id'] not in DEVICE_IDS:
                            DEVICE_IDS.append(item['Id'])
                        if 'Id' in item and 'Product' in action and item['Id'] not in PRODUCT_IDS:
                            PRODUCT_IDS.append(item['Id'])
                        if 'Id' in item and 'Role' in action and item['Id'] not in ROLE_IDS:
                            ROLE_IDS.append(item['Id'])

            return result  # Return first successful result

    # No success, return last attempt
    return result

def main():
    print("="*70)
    print("Testing ALL 544 MPSM API Endpoints")
    print("="*70)
    print()

    actions = load_all_actions()
    print(f"Total actions to test: {len(actions)}")
    print()

    results = []
    successful = 0
    failed = 0

    for i, action in enumerate(actions, 1):
        print(f"[{i}/{len(actions)}] {action:<60}", end=" ", flush=True)

        result = test_action_with_variants(action)

        if result['success']:
            print(f"[OK] {result['data_type']:<8} {result['count'] or 0:>4} items")
            successful += 1
        else:
            error_msg = result['error'] or 'Unknown'
            if len(error_msg) > 40:
                error_msg = error_msg[:37] + "..."
            print(f"[FAIL] {error_msg}")
            failed += 1

        results.append(result)
        time.sleep(0.3)  # Rate limiting

    print()
    print("="*70)
    print(f"Complete: {successful} successful, {failed} failed")
    print(f"Success Rate: {successful/len(actions)*100:.1f}%")
    print("="*70)

    # Save results
    script_dir = os.path.dirname(os.path.abspath(__file__))
    output_dir = os.path.join(script_dir, '..', 'output')
    output_file = os.path.join(output_dir, 'all_544_endpoints_tested.json')

    output = {
        'generated_at': datetime.now().isoformat(),
        'total_endpoints': len(results),
        'successful': successful,
        'failed': failed,
        'success_rate': round(successful / len(results) * 100, 2),
        'device_ids_collected': len(DEVICE_IDS),
        'endpoints': results
    }

    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(output, f, indent=2, ensure_ascii=False)

    print(f"\nResults saved to: {output_file}")
    print(f"Collected {len(DEVICE_IDS)} device IDs for future testing")

if __name__ == '__main__':
    main()
