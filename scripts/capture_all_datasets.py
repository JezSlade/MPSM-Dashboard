#!/usr/bin/env python3
"""
Capture Complete Dataset Structures from ALL GET Endpoints

This script systematically tests every GET endpoint in the MPSM API,
retrying with prerequisite data when needed, until we get real datasets
from every endpoint.

Success Criteria:
- Every GET endpoint must return actual data (not errors)
- Full response structure must be captured
- Example values must be documented
"""

import requests
import json
import time
import os
from datetime import datetime

# API Configuration
API_URL = "https://mpsm.resolutionsbydesign.us/mps-api/query"
DEALER_CODE = "NY06AGDWUQ"

# Storage for collected data that can be used as prerequisites
SEED_DATA = {
    'customerCodes': [],
    'deviceIds': [],
    'apiClientIds': [],
    'roleIds': [],
    'brandNames': [],
    'modelNames': [],
    'productIds': [],
    'supplyIds': [],
    'customFieldIds': [],
    'explorerIds': [],
    'integrationIds': [],
    'officeIds': [],
    'projectIds': [],
    'standardProductIds': [],
    'operationIds': [],
    'notificationIds': [],
}

def load_actions_list():
    """Load the list of all GET actions"""
    script_dir = os.path.dirname(os.path.abspath(__file__))
    actions_file = os.path.join(script_dir, '..', 'output', 'working_actions_list.txt')

    # If working list exists, start with those
    if os.path.exists(actions_file):
        with open(actions_file, 'r') as f:
            return [line.strip() for line in f if line.strip()]

    # Otherwise load from swagger
    swagger_file = os.path.join(script_dir, '..', 'mps-api', 'swagger.json')
    with open(swagger_file, 'r') as f:
        swagger = json.load(f)

    actions = []
    for path, methods in swagger.get('paths', {}).items():
        if 'get' in methods:
            op = methods['get']
            if 'x-action-name' in op:
                actions.append(op['x-action-name'])

    return actions

def collect_seeds():
    """Collect prerequisite data from seed endpoints"""
    print("\n=== COLLECTING SEED DATA ===\n")

    seed_endpoints = [
        'Device/Deleted/ListByDealer',
        'ApiClient/List',
        'Role/List',
        'Product/GetBrands',
        'Product/GetModels',
        'CustomField/List',
        'StandardProduct/ListStandardProducts',
        'DealerSupplySet/List',
        'Integrations/List',
        'Explorer/Configuration/List',
    ]

    for action in seed_endpoints:
        print(f"Collecting from {action}...")
        result = test_endpoint(action, {})

        if result['success'] and result['data']:
            extract_seed_data(action, result['data'])
            time.sleep(0.3)

    print(f"\nSeed data collected:")
    for key, values in SEED_DATA.items():
        if values:
            print(f"  {key}: {len(values)} items")

def extract_seed_data(action, data):
    """Extract useful IDs and codes from response data"""

    if not data:
        return

    # Handle list responses
    if isinstance(data, list):
        for item in data:
            if isinstance(item, dict):
                extract_from_dict(item)

    # Handle dict responses
    elif isinstance(data, dict):
        extract_from_dict(data)

def extract_from_dict(item):
    """Extract IDs from a dictionary"""

    # Customer codes
    if 'CustomerCode' in item and item['CustomerCode']:
        if item['CustomerCode'] not in SEED_DATA['customerCodes']:
            SEED_DATA['customerCodes'].append(item['CustomerCode'])

    # Device IDs
    if 'DeviceId' in item and item['DeviceId']:
        if item['DeviceId'] not in SEED_DATA['deviceIds']:
            SEED_DATA['deviceIds'].append(item['DeviceId'])

    if 'Id' in item and item['Id']:
        # Try to determine what type of ID this is based on other fields
        if 'ClientId' in item:
            if item['Id'] not in SEED_DATA['apiClientIds']:
                SEED_DATA['apiClientIds'].append(item['Id'])
        elif 'RoleName' in item or 'Name' in item:
            if 'Capabilities' in item:  # It's a role
                if item['Id'] not in SEED_DATA['roleIds']:
                    SEED_DATA['roleIds'].append(item['Id'])
        elif 'FieldName' in item:
            if item['Id'] not in SEED_DATA['customFieldIds']:
                SEED_DATA['customFieldIds'].append(item['Id'])
        elif 'SupplyName' in item or 'Supply' in str(item):
            if item['Id'] not in SEED_DATA['supplyIds']:
                SEED_DATA['supplyIds'].append(item['Id'])
        elif 'StandardProductId' in item:
            if item['Id'] not in SEED_DATA['productIds']:
                SEED_DATA['productIds'].append(item['Id'])

    # Brand names
    if 'BrandName' in item and item['BrandName']:
        if item['BrandName'] not in SEED_DATA['brandNames']:
            SEED_DATA['brandNames'].append(item['BrandName'])

    # Model names
    if 'ModelName' in item and item['ModelName']:
        if item['ModelName'] not in SEED_DATA['modelNames']:
            SEED_DATA['modelNames'].append(item['ModelName'])

    # Standard Product IDs
    if 'StandardProductId' in item and item['StandardProductId']:
        if item['StandardProductId'] not in SEED_DATA['standardProductIds']:
            SEED_DATA['standardProductIds'].append(item['StandardProductId'])

    # Operation IDs
    if 'OperationId' in item and item['OperationId']:
        if item['OperationId'] not in SEED_DATA['operationIds']:
            SEED_DATA['operationIds'].append(item['OperationId'])

    # Integration IDs
    if 'IntegrationId' in item and item['IntegrationId']:
        if item['IntegrationId'] not in SEED_DATA['integrationIds']:
            SEED_DATA['integrationIds'].append(item['IntegrationId'])

    # Explorer IDs
    if 'ExplorerId' in item and item['ExplorerId']:
        if item['ExplorerId'] not in SEED_DATA['explorerIds']:
            SEED_DATA['explorerIds'].append(item['ExplorerId'])

    # Office IDs
    if 'OfficeId' in item and item['OfficeId']:
        if item['OfficeId'] not in SEED_DATA['officeIds']:
            SEED_DATA['officeIds'].append(item['OfficeId'])

def test_endpoint(action, params, max_retries=3):
    """Test a single endpoint with given params"""

    payload = {
        "action": action,
        "params": params
    }

    for attempt in range(max_retries):
        try:
            response = requests.post(API_URL, json=payload, timeout=30)

            # Handle non-JSON responses
            try:
                data = response.json()
            except:
                return {
                    'action': action,
                    'success': False,
                    'error': 'Invalid JSON response',
                    'http_code': response.status_code,
                    'params_used': params,
                    'data': None
                }

            # Extract actual data
            success = data.get('success', False)
            error = data.get('error', data.get('message'))
            result_data = data.get('data')

            return {
                'action': action,
                'success': success,
                'error': error,
                'error_code': data.get('errorCode'),
                'http_code': response.status_code,
                'params_used': params.copy(),
                'data': result_data,
                'data_type': type(result_data).__name__ if result_data is not None else None,
                'data_count': len(result_data) if isinstance(result_data, (list, dict)) else None,
            }

        except requests.Timeout:
            if attempt < max_retries - 1:
                time.sleep(1)
                continue
            return {
                'action': action,
                'success': False,
                'error': 'Request timeout',
                'params_used': params,
                'data': None
            }
        except Exception as e:
            return {
                'action': action,
                'success': False,
                'error': str(e),
                'params_used': params,
                'data': None
            }

    return {
        'action': action,
        'success': False,
        'error': 'Max retries exceeded',
        'params_used': params,
        'data': None
    }

def smart_retry_endpoint(action, initial_result):
    """Intelligently retry an endpoint with prerequisite data"""

    error = initial_result.get('error', '')

    # Build params based on error message
    retry_params = {}

    # Customer code required
    if 'customerCode' in error.lower() or 'customer not found' in error.lower():
        if SEED_DATA['customerCodes']:
            retry_params['customerCode'] = SEED_DATA['customerCodes'][0]
        else:
            return None  # Can't retry without customer code

    # Device ID required
    if 'deviceid' in error.lower() or 'device not found' in error.lower():
        if SEED_DATA['deviceIds']:
            retry_params['deviceId'] = SEED_DATA['deviceIds'][0]
        else:
            return None

    # Brand required
    if 'brand' in error.lower():
        if SEED_DATA['brandNames']:
            retry_params['brandName'] = SEED_DATA['brandNames'][0]
        else:
            return None

    # Model required
    if 'model' in error.lower():
        if SEED_DATA['modelNames']:
            retry_params['modelName'] = SEED_DATA['modelNames'][0]
        else:
            return None

    # Operation ID required
    if 'operationid' in error.lower():
        if SEED_DATA['operationIds']:
            retry_params['operationId'] = SEED_DATA['operationIds'][0]
        else:
            return None

    # Standard Product ID required
    if 'standardproductid' in error.lower():
        if SEED_DATA['standardProductIds']:
            retry_params['standardProductId'] = SEED_DATA['standardProductIds'][0]
        else:
            return None

    # Integration ID required
    if 'integrationid' in error.lower() or 'integration not found' in error.lower():
        if SEED_DATA['integrationIds']:
            retry_params['integrationId'] = SEED_DATA['integrationIds'][0]
        else:
            return None

    # Office ID required
    if 'officeid' in error.lower():
        if SEED_DATA['officeIds']:
            retry_params['officeId'] = SEED_DATA['officeIds'][0]
        else:
            return None

    # API Client ID required
    if 'clientid' in error.lower():
        if SEED_DATA['apiClientIds']:
            retry_params['id'] = SEED_DATA['apiClientIds'][0]
        else:
            return None

    # Role ID required
    if 'roleid' in error.lower() or 'role not found' in error.lower():
        if SEED_DATA['roleIds']:
            retry_params['idRole'] = SEED_DATA['roleIds'][0]
        else:
            return None

    # Custom Field ID required
    if 'customfieldid' in error.lower() or 'customfield not found' in error.lower():
        if SEED_DATA['customFieldIds']:
            retry_params['id'] = SEED_DATA['customFieldIds'][0]
        else:
            return None

    # If we have retry params, try again
    if retry_params:
        print(f"  Retrying with params: {retry_params}")
        return test_endpoint(action, retry_params)

    return None

def test_all_endpoints():
    """Test all GET endpoints systematically"""

    print("\n=== TESTING ALL GET ENDPOINTS ===\n")

    actions = load_actions_list()
    print(f"Total GET endpoints to test: {len(actions)}\n")

    results = []
    successful = 0
    failed = 0

    for i, action in enumerate(actions, 1):
        print(f"[{i}/{len(actions)}] Testing: {action}")

        # Initial test with empty params
        result = test_endpoint(action, {})

        # If failed, try intelligent retry
        if not result['success'] and result['error']:
            retry_result = smart_retry_endpoint(action, result)

            if retry_result and retry_result['success']:
                print(f"  [OK] Success after retry - got {retry_result['data_type']}")
                result = retry_result

                # Extract more seed data from this success
                if retry_result['data']:
                    extract_seed_data(action, retry_result['data'])
            else:
                print(f"  [FAIL] {result['error']}")
        else:
            if result['success']:
                print(f"  [OK] {result['data_type']} - {result['data_count']} items")

                # Extract seed data from success
                if result['data']:
                    extract_seed_data(action, result['data'])
            else:
                print(f"  [FAIL] {result['error']}")

        results.append(result)

        if result['success']:
            successful += 1
        else:
            failed += 1

        # Rate limiting
        time.sleep(0.5)

    print(f"\n=== TEST COMPLETE ===")
    print(f"Successful: {successful}/{len(actions)} ({successful/len(actions)*100:.1f}%)")
    print(f"Failed: {failed}/{len(actions)}")

    return results

def save_results(results):
    """Save complete dataset structures to file"""

    script_dir = os.path.dirname(os.path.abspath(__file__))
    output_dir = os.path.join(script_dir, '..', 'output')

    # Save full results with data structures
    output_file = os.path.join(output_dir, 'complete_dataset_catalog.json')

    output = {
        'generated_at': datetime.now().isoformat(),
        'total_endpoints': len(results),
        'successful': sum(1 for r in results if r['success']),
        'failed': sum(1 for r in results if not r['success']),
        'seed_data_collected': {k: len(v) for k, v in SEED_DATA.items() if v},
        'endpoints': results
    }

    with open(output_file, 'w') as f:
        json.dump(output, f, indent=2)

    print(f"\nResults saved to: {output_file}")

    # Create a summary report
    report_file = os.path.join(output_dir, 'DATASET_CATALOG.md')

    with open(report_file, 'w') as f:
        f.write("# Complete MPSM API Dataset Catalog\n\n")
        f.write(f"Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n\n")

        f.write("## Summary\n\n")
        f.write(f"- **Total Endpoints**: {len(results)}\n")
        f.write(f"- **Successful**: {output['successful']} ({output['successful']/len(results)*100:.1f}%)\n")
        f.write(f"- **Failed**: {output['failed']} ({output['failed']/len(results)*100:.1f}%)\n\n")

        f.write("## Seed Data Collected\n\n")
        for key, values in SEED_DATA.items():
            if values:
                f.write(f"- **{key}**: {len(values)} items\n")
        f.write("\n")

        # Successful endpoints
        f.write("## Successful Endpoints\n\n")
        for result in results:
            if result['success']:
                f.write(f"### {result['action']}\n\n")
                f.write(f"- **Data Type**: {result['data_type']}\n")
                f.write(f"- **Item Count**: {result['data_count']}\n")

                if result['params_used']:
                    f.write(f"- **Params Used**: `{json.dumps(result['params_used'])}`\n")

                f.write(f"- **Sample Data**:\n```json\n")

                # Show sample of data
                if isinstance(result['data'], list) and result['data']:
                    f.write(json.dumps(result['data'][0], indent=2))
                elif isinstance(result['data'], dict):
                    f.write(json.dumps(result['data'], indent=2))
                else:
                    f.write(json.dumps(result['data'], indent=2))

                f.write("\n```\n\n")

        # Failed endpoints
        f.write("## Failed Endpoints\n\n")
        for result in results:
            if not result['success']:
                f.write(f"### {result['action']}\n\n")
                f.write(f"- **Error**: {result['error']}\n")
                if result['params_used']:
                    f.write(f"- **Params Tried**: `{json.dumps(result['params_used'])}`\n")
                f.write("\n")

    print(f"Report saved to: {report_file}")

def main():
    """Main execution"""

    print("="*60)
    print("MPSM API - Complete Dataset Capture")
    print("="*60)

    # Step 1: Collect seed data
    collect_seeds()

    # Step 2: Test all endpoints
    results = test_all_endpoints()

    # Step 3: Save results
    save_results(results)

    print("\n" + "="*60)
    print("COMPLETE")
    print("="*60)

if __name__ == '__main__':
    main()
