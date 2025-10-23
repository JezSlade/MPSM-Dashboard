#!/usr/bin/env python3
"""
Comprehensive MPSM API Endpoint Testing
Tests all 188 GET endpoints systematically and documents results
"""

import requests
import json
import time
from datetime import datetime

import os

API_URL = "https://mpsm.resolutionsbydesign.us/mps-api/query"
script_dir = os.path.dirname(os.path.abspath(__file__))
RESULTS_FILE = os.path.join(script_dir, '..', 'output', 'endpoint_test_results.json')

def test_endpoint(action):
    """Test a single endpoint"""
    payload = {"action": action, "params": {}}

    try:
        start_time = time.time()
        response = requests.post(API_URL, json=payload, timeout=30)
        duration = (time.time() - start_time) * 1000

        data = response.json()

        return {
            'action': action,
            'success': data.get('success', False),
            'error': data.get('error'),
            'error_code': data.get('error_code'),
            'http_code': data.get('http_code', response.status_code),
            'duration_ms': round(duration, 2),
            'has_data': 'data' in data,
            'data_type': type(data.get('data')).__name__ if 'data' in data else None,
            'data_count': len(data.get('data', [])) if isinstance(data.get('data'), list) else None,
            'timestamp': datetime.now().isoformat()
        }
    except requests.Timeout:
        return {
            'action': action,
            'success': False,
            'error': 'Request timeout (30s)',
            'timestamp': datetime.now().isoformat()
        }
    except Exception as e:
        return {
            'action': action,
            'success': False,
            'error': f'Exception: {str(e)}',
            'timestamp': datetime.now().isoformat()
        }

# Load the 188 working actions
actions_file = os.path.join(script_dir, '..', 'output', 'working_actions_list.txt')
with open(actions_file, 'r') as f:
    actions = [line.strip() for line in f if line.strip()]

print(f"Testing {len(actions)} endpoints...")
print(f"Started: {datetime.now().isoformat()}")
print("="*80)

results = []
successful = 0
failed = 0
errors_by_type = {}

for i, action in enumerate(actions, 1):
    print(f"\n[{i}/{len(actions)}] Testing: {action}")

    result = test_endpoint(action)
    results.append(result)

    if result['success']:
        successful += 1
        data_info = f" ({result['data_type']}"
        if result['data_count'] is not None:
            data_info += f", {result['data_count']} items"
        data_info += ")"
        print(f"  [OK] SUCCESS{data_info} - {result['duration_ms']}ms")
    else:
        failed += 1
        error = result.get('error', 'Unknown')
        error_key = error[:50]  # First 50 chars
        errors_by_type[error_key] = errors_by_type.get(error_key, 0) + 1
        print(f"  [FAIL] FAILED: {error}")

    # Small delay to avoid overwhelming server
    time.sleep(0.5)

    # Progress update every 20 endpoints
    if i % 20 == 0:
        print(f"\n--- Progress: {i}/{len(actions)} ({successful} success, {failed} failed) ---\n")

# Save results
with open(RESULTS_FILE, 'w') as f:
    json.dump({
        'test_date': datetime.now().isoformat(),
        'total_endpoints': len(actions),
        'successful': successful,
        'failed': failed,
        'success_rate': round((successful / len(actions)) * 100, 2),
        'errors_by_type': errors_by_type,
        'results': results
    }, f, indent=2)

print("\n" + "="*80)
print(f"TEST COMPLETE")
print(f"="*80)
print(f"Total Endpoints: {len(actions)}")
print(f"Successful: {successful} ({round((successful/len(actions))*100, 1)}%)")
print(f"Failed: {failed} ({round((failed/len(actions))*100, 1)}%)")
print(f"\nCommon Errors:")
for error, count in sorted(errors_by_type.items(), key=lambda x: x[1], reverse=True)[:10]:
    print(f"  {count:3d}x {error}")
print(f"\nResults saved to: {RESULTS_FILE}")
