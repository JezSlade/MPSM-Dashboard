#!/usr/bin/env python3
"""
Retry all 107 failed endpoints with proper prerequisites extracted from successful responses
"""

import json
import requests
import time
import os
from datetime import datetime

API_URL = 'https://mpsm.resolutionsbydesign.us/mps-api/query'

def load_data():
    """Load existing test results and extract prerequisites"""
    script_dir = os.path.dirname(os.path.abspath(__file__))
    data_file = os.path.join(script_dir, '..', 'output', 'get_endpoint_data.json')

    with open(data_file, 'r', encoding='utf-8') as f:
        return json.load(f)

def extract_prerequisites(data):
    """Extract all prerequisite data from successful responses"""
    prereqs = {
        'dealerCode': 'NY06AGDWUQ',
        'customerCodes': set(),
        'deviceIds': set(),
        'brands': set(),
        'models': set(),
        'standardProductIds': set(),
        'operationIds': set(),
        'apiClientIds': set(),
        'roleIds': set(),
        'customFieldIds': set(),
        'integrationIds': set(),
        'explorerIds': set(),
        'notificationIds': set(),
        'officeIds': set(),
        'projectIds': set(),
    }

    for result in data['results']:
        if not result['success'] or not result['response']:
            continue

        rdata = result['response'].get('data')
        if not rdata:
            continue

        # Extract from lists
        if isinstance(rdata, list):
            for item in rdata:
                if not isinstance(item, dict):
                    continue

                # Customer codes
                if 'CustomerCode' in item and item['CustomerCode']:
                    prereqs['customerCodes'].add(item['CustomerCode'])

                # Customer from nested object
                if 'Customer' in item and isinstance(item['Customer'], dict):
                    if 'Code' in item['Customer']:
                        prereqs['customerCodes'].add(item['Customer']['Code'])

                # Device IDs
                if 'Id' in item and item['Id']:
                    # Determine type by other fields
                    if 'DeviceId' in str(result['action']) or 'SerialNumber' in item:
                        prereqs['deviceIds'].add(item['Id'])
                    elif 'ClientId' in item or 'ApiClient' in result['action']:
                        prereqs['apiClientIds'].add(item['Id'])
                    elif 'Role' in result['action'] or 'Capabilities' in item:
                        prereqs['roleIds'].add(item['Id'])
                    elif 'CustomField' in result['action']:
                        prereqs['customFieldIds'].add(item['Id'])
                    elif 'Integration' in result['action']:
                        prereqs['integrationIds'].add(item['Id'])
                    elif 'Explorer' in result['action']:
                        prereqs['explorerIds'].add(item['Id'])
                    elif 'Notification' in result['action']:
                        prereqs['notificationIds'].add(item['Id'])
                    elif 'Office' in result['action']:
                        prereqs['officeIds'].add(item['Id'])
                    elif 'Project' in result['action']:
                        prereqs['projectIds'].add(item['Id'])

                # Product info
                if 'Product' in item and isinstance(item['Product'], dict):
                    if 'Model' in item['Product']:
                        prereqs['models'].add(item['Product']['Model'])
                    if 'Brand' in item['Product']:
                        prereqs['brands'].add(item['Product']['Brand'])
                    if 'Id' in item['Product']:
                        prereqs['standardProductIds'].add(item['Product']['Id'])

                # Direct brand/model
                if 'BrandName' in item and item['BrandName'] and item['BrandName'] != '#VALUE!':
                    prereqs['brands'].add(item['BrandName'])
                if 'ModelName' in item and item['ModelName'] and '\\u0000' not in str(item['ModelName']):
                    prereqs['models'].add(item['ModelName'])

                # Standard Product ID
                if 'StandardProductId' in item and item['StandardProductId']:
                    prereqs['standardProductIds'].add(item['StandardProductId'])

                # Operation ID
                if 'OperationId' in item and item['OperationId']:
                    prereqs['operationIds'].add(item['OperationId'])

        # Extract from dicts
        elif isinstance(rdata, dict):
            if 'CustomerCode' in rdata:
                prereqs['customerCodes'].add(rdata['CustomerCode'])

    # Convert to sorted lists
    for key in prereqs:
        if isinstance(prereqs[key], set):
            prereqs[key] = sorted(list(prereqs[key]))

    return prereqs

def build_retry_params(action, error, prereqs):
    """Build parameters for retrying based on error message"""
    params = {}
    error_lower = (error or '').lower()

    # Customer code required
    if 'customercode' in error_lower or 'customer not found' in error_lower or 'customer' in error_lower:
        if prereqs['customerCodes']:
            params['customerCode'] = prereqs['customerCodes'][0]

    # Device ID required
    if 'deviceid' in error_lower or 'device not found' in error_lower:
        if prereqs['deviceIds']:
            params['deviceId'] = prereqs['deviceIds'][0]

    # Brand required
    if 'brand' in error_lower and 'required' in error_lower:
        if prereqs['brands']:
            params['brandName'] = prereqs['brands'][0]

    # Model required
    if 'model' in error_lower and prereqs['models']:
        params['modelName'] = prereqs['models'][0]

    # Operation ID
    if 'operationid' in error_lower and prereqs['operationIds']:
        params['operationId'] = prereqs['operationIds'][0]

    # Standard Product ID
    if 'standardproductid' in error_lower and prereqs['standardProductIds']:
        params['standardProductId'] = prereqs['standardProductIds'][0]

    # Integration ID
    if 'integrationid' in error_lower or 'integration not found' in error_lower:
        if prereqs['integrationIds']:
            params['integrationId'] = prereqs['integrationIds'][0]

    # Explorer ID
    if 'explorerid' in error_lower and prereqs['explorerIds']:
        params['explorerId'] = prereqs['explorerIds'][0]

    # Office ID
    if 'officeid' in error_lower and prereqs['officeIds']:
        params['officeId'] = prereqs['officeIds'][0]

    # Project ID
    if 'projectid' in error_lower or 'project not found' in error_lower:
        if prereqs['projectIds']:
            params['projectId'] = prereqs['projectIds'][0]

    # API Client ID
    if 'clientid' in error_lower or 'id not found' in error_lower:
        if prereqs['apiClientIds']:
            params['id'] = prereqs['apiClientIds'][0]

    # Role ID
    if 'roleid' in error_lower or 'idrole' in error_lower:
        if prereqs['roleIds']:
            params['idRole'] = prereqs['roleIds'][0]

    # Custom Field ID
    if 'customfieldid' in error_lower and prereqs['customFieldIds']:
        params['id'] = prereqs['customFieldIds'][0]

    # Report ID
    if 'idreport' in error_lower:
        params['idReport'] = 1  # Try a default

    # Platform
    if 'platform' in error_lower:
        params['platform'] = 'windows'  # Try default

    # Dealer code (always include for dealer-specific endpoints)
    if 'Dealer' in action or 'dealer' in error_lower:
        params['dealerCode'] = prereqs['dealerCode']

    return params

def test_endpoint(action, params):
    """Test endpoint with given params"""
    try:
        response = requests.post(API_URL, json={'action': action, 'params': params}, timeout=30)
        data = response.json()

        return {
            'action': action,
            'success': data.get('success', False),
            'error': data.get('error'),
            'data': data.get('data'),
            'data_type': type(data.get('data')).__name__ if data.get('data') is not None else None,
            'count': len(data.get('data')) if isinstance(data.get('data'), (list, dict)) else None,
            'params_used': params,
            'http_status': response.status_code,
        }
    except Exception as e:
        return {
            'action': action,
            'success': False,
            'error': str(e),
            'data': None,
            'params_used': params,
        }

def main():
    print("="*70)
    print("Retrying 107 Failed Endpoints with Prerequisites")
    print("="*70)
    print()

    # Load original data
    data = load_data()

    # Extract prerequisites
    print("Extracting prerequisites from successful responses...")
    prereqs = extract_prerequisites(data)

    print(f"Found:")
    print(f"  Customer Codes: {len(prereqs['customerCodes'])}")
    print(f"  Device IDs: {len(prereqs['deviceIds'])}")
    print(f"  Brands: {len(prereqs['brands'])}")
    print(f"  Models: {len(prereqs['models'])}")
    print(f"  Explorer IDs: {len(prereqs['explorerIds'])}")
    print(f"  Role IDs: {len(prereqs['roleIds'])}")
    print()

    # Get failed endpoints
    failed = [r for r in data['results'] if not r['success']]
    print(f"Retrying {len(failed)} failed endpoints...")
    print()

    results = []
    newly_successful = 0
    still_failed = 0

    for i, original in enumerate(failed, 1):
        action = original['action']
        error = original['error']

        print(f"[{i}/{len(failed)}] {action:<60}", end=" ", flush=True)

        # Build retry params
        params = build_retry_params(action, error, prereqs)

        # Test
        result = test_endpoint(action, params)

        if result['success']:
            print(f"[OK] {result['data_type']:<8} {result['count'] or 0:>4} items")
            newly_successful += 1
        else:
            error_msg = result['error'] or 'Unknown'
            if len(error_msg) > 40:
                error_msg = error_msg[:37] + "..."
            print(f"[FAIL] {error_msg}")
            still_failed += 1

        results.append(result)
        time.sleep(0.3)

    print()
    print("="*70)
    print(f"Retry Complete:")
    print(f"  Newly Successful: {newly_successful}")
    print(f"  Still Failed: {still_failed}")
    print(f"  Success Rate: {newly_successful/len(failed)*100:.1f}%")
    print("="*70)

    # Save results
    script_dir = os.path.dirname(os.path.abspath(__file__))
    output_file = os.path.join(script_dir, '..', 'output', 'retry_results.json')

    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump({
            'generated_at': datetime.now().isoformat(),
            'total_retried': len(results),
            'newly_successful': newly_successful,
            'still_failed': still_failed,
            'prerequisites_used': {k: len(v) if isinstance(v, list) else v for k, v in prereqs.items()},
            'results': results
        }, f, indent=2, ensure_ascii=False)

    print(f"\nResults saved to: {output_file}")

if __name__ == '__main__':
    main()
