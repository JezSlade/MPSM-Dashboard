#!/usr/bin/env python3
"""
Battle Test - Live Site Search
Tests edge cases and verifies search works correctly
"""

import requests
import time
import json

print("="*80)
print("BATTLE TEST - LIVE SITE GLOBAL SEARCH")
print("="*80)

s = requests.Session()
r = s.post('https://mpsm.resolutionsbydesign.us/cms/api/login.php', json={'username':'admin','password':'admin'})
print(f"\n[LOGIN] {r.json().get('message')}")

# Test 1: Verify all customers are accessible
print(f"\n{'='*80}")
print("[TEST 1] Verify Customer List Endpoint")
print('='*80)
r = s.get('https://mpsm.resolutionsbydesign.us/cms/api/get-customers.php')
data = r.json()
customers = data.get('customers', [])
print(f"Total customers: {len(customers)}")
print(f"Success: {'PASS' if len(customers) > 50 else 'FAIL - Expected 82 customers'}")

cape_fear = [c for c in customers if 'CAPE FEAR' in c.get('Description', '').upper()]
if cape_fear:
    print(f"Cape Fear found: {cape_fear[0]['Description']} ({cape_fear[0]['Code']})")
else:
    print("Cape Fear NOT FOUND - FAIL")

# Test 2: Search for known device (control test)
print(f"\n{'='*80}")
print("[TEST 2] Control Test - Search for EB045 (known device)")
print('='*80)

# Search using API directly (simulating frontend)
found_eb045 = False
for page in range(1, 3):
    r = s.get(f'https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php?pageRows=100&pageNumber={page}&allCustomers=true')
    data = r.json()
    for d in data.get('devices', []):
        if d.get('ExternalIdentifier') == 'EB045':
            print(f"EB045 FOUND via allCustomers=true")
            print(f"  Customer: {d.get('CustomerDescription')}")
            found_eb045 = True
            break
    if found_eb045:
        break

print(f"Result: {'PASS' if found_eb045 else 'FAIL'}")

# Test 3: Search EN413 in Cape Fear specifically
print(f"\n{'='*80}")
print("[TEST 3] Search EN413 in Cape Fear (W9OPXL0YDK)")
print('='*80)

start = time.time()
found_en413 = False
cape_devices = []

for page in range(1, 11):  # Search first 10 pages (1000 devices)
    r = s.get(f'https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php?pageRows=100&pageNumber={page}&customerCode=W9OPXL0YDK')
    data = r.json()
    devices = data.get('devices', [])

    if not devices:
        break

    cape_devices.extend(devices)

    for d in devices:
        asset = str(d.get('AssetNumber', '')).upper()
        ext = str(d.get('ExternalIdentifier', '')).upper()
        sn = str(d.get('SerialNumber', '')).upper()

        if 'EN413' in asset or 'EN413' in ext or 'EN413' in sn:
            print(f"EN413 FOUND!")
            print(f"  AssetNumber: {d.get('AssetNumber')}")
            print(f"  ExternalIdentifier: {d.get('ExternalIdentifier')}")
            print(f"  SerialNumber: {d.get('SerialNumber')}")
            print(f"  Customer: {d.get('CustomerDescription')}")
            found_en413 = True
            break

    if found_en413:
        break

elapsed = time.time() - start
print(f"Searched {len(cape_devices)} Cape Fear devices in {elapsed:.1f}s")
print(f"Result: {'FOUND' if found_en413 else 'NOT FOUND'}")

# Test 4: Edge cases
print(f"\n{'='*80}")
print("[TEST 4] Edge Case Testing")
print('='*80)

edge_cases = [
    ('Partial match', 'EB0'),  # Partial identifier
    ('Numeric only', '045'),   # Just numbers
    ('Case insensitive', 'eb045'),  # Lowercase
    ('Serial search', 'MXDCF9L1HN'),  # Known serial
]

for test_name, query in edge_cases:
    found = False
    for page in range(1, 3):
        r = s.get(f'https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php?pageRows=100&pageNumber={page}&allCustomers=true')
        data = r.json()
        for d in data.get('devices', []):
            haystack = f"{d.get('AssetNumber')} {d.get('ExternalIdentifier')} {d.get('SerialNumber')}".upper()
            if query.upper() in haystack:
                print(f"  {test_name} ('{query}'): FOUND - {d.get('ExternalIdentifier') or d.get('SerialNumber')}")
                found = True
                break
        if found:
            break

    if not found:
        print(f"  {test_name} ('{query}'): NOT FOUND")

# Test 5: Deleted devices search
print(f"\n{'='*80}")
print("[TEST 5] Deleted Devices Search")
print('='*80)

r = s.get('https://mpsm.resolutionsbydesign.us/cms/api/get-deleted-devices.php?pageRows=100&pageNumber=1')
data = r.json()
deleted_total = data.get('total', 0)
deleted_page1 = len(data.get('devices', []))

print(f"Total deleted devices: {deleted_total}")
print(f"Page 1 devices: {deleted_page1}")
print(f"IsUninstalled flag present: {data['devices'][0].get('IsUninstalled') if deleted_page1 > 0 else 'N/A'}")
print(f"Result: {'PASS' if deleted_total > 0 else 'FAIL'}")

# Sample deleted device
if deleted_page1 > 0:
    sample = data['devices'][0]
    print(f"Sample deleted device: {sample.get('AssetNumber')} (Customer: {sample.get('Customer', {}).get('Description')})")

# Test 6: Multi-customer comprehensive search simulation
print(f"\n{'='*80}")
print("[TEST 6] Multi-Customer Search Simulation")
print('='*80)

print("Testing first 10 customers to verify query works...")
test_customers = customers[:10]
total_devices = 0
errors = 0

for cust in test_customers:
    try:
        r = s.get(f'https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php?pageRows=100&pageNumber=1&customerCode={cust["Code"]}')
        data = r.json()
        device_count = len(data.get('devices', []))
        total_devices += device_count
        print(f"  {cust['Description'][:40]:40} {device_count:3} devices")
    except Exception as e:
        errors += 1
        print(f"  {cust['Description'][:40]:40} ERROR: {e}")

print(f"\nTotal devices from 10 customers: {total_devices}")
print(f"Errors: {errors}")
print(f"Result: {'PASS' if errors == 0 else 'FAIL'}")

# Summary
print(f"\n{'='*80}")
print("BATTLE TEST SUMMARY")
print('='*80)

results = {
    'Customer list (82 expected)': len(customers) >= 80,
    'Cape Fear accessible': len(cape_fear) > 0,
    'Control test (EB045)': found_eb045,
    'EN413 search': found_en413,
    'Deleted devices': deleted_total > 0,
    'Multi-customer query': errors == 0
}

for test, passed in results.items():
    status = 'PASS' if passed else 'FAIL'
    print(f"  {test:35} [{status}]")

all_pass = all(results.values())
print(f"\n{'='*80}")
print(f"FINAL VERDICT: {'ALL TESTS PASSED' if all_pass else 'SOME TESTS FAILED'}")
print('='*80)

if not found_en413:
    print("\nEN413 NOT FOUND - Possible reasons:")
    print("  1. Device doesn't exist in Asset Management API")
    print("  2. Beyond first 1000 Cape Fear devices")
    print("  3. Different identifier field (not Asset/External/Serial)")
    print("  4. May be in deleted devices - check those separately")
