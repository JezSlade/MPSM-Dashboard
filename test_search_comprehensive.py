#!/usr/bin/env python3
"""
Comprehensive Search Test
Tests the live site search functionality for known devices including uninstalled devices
"""

import requests
import json

print("=== Comprehensive Search Test ===\n")

# Login
s = requests.Session()
r = s.post('https://mpsm.resolutionsbydesign.us/cms/api/login.php', json={'username':'admin','password':'admin'})
print(f"Login: {r.json()}")

# Test 1: Fetch installed devices (should include 3400+ devices)
print("\n--- Test 1: Installed Devices ---")
r = s.get('https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php?pageRows=100&pageNumber=1&allCustomers=true')
data = r.json()
print(f"Page 1 devices: {len(data.get('devices', []))}")
print(f"Total installed: {data.get('total', 0)}")

# Test 2: Fetch deleted devices (should include 632 devices)
print("\n--- Test 2: Deleted Devices ---")
r = s.get('https://mpsm.resolutionsbydesign.us/cms/api/get-deleted-devices.php?pageRows=100&pageNumber=1')
data = r.json()
print(f"Page 1 deleted devices: {len(data.get('devices', []))}")
print(f"Total deleted: {data.get('total', 0)}")

# Test 3: Search for EB045 (known to exist in installed devices)
print("\n--- Test 3: Search for EB045 (should exist) ---")
found_eb045 = False
for page in range(1, 35):
    r = s.get(f'https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php?pageRows=100&pageNumber={page}&allCustomers=true')
    data = r.json()
    devices = data.get('devices', [])

    for d in devices:
        if d.get('ExternalIdentifier') == 'EB045':
            found_eb045 = True
            print(f"[OK] Found EB045: Asset={d.get('AssetNumber')}, Serial={d.get('SerialNumber')}, Customer={d.get('Customer', {}).get('Code')}")
            break

    if found_eb045:
        break

if not found_eb045:
    print("[FAIL] EB045 NOT FOUND in installed devices")

# Test 4: Search for EB821 in both installed and deleted
print("\n--- Test 4: Search for EB821 (target device) ---")
print("Searching installed devices...")
found_eb821_installed = False
for page in range(1, 35):
    r = s.get(f'https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php?pageRows=100&pageNumber={page}&allCustomers=true')
    data = r.json()
    devices = data.get('devices', [])

    for d in devices:
        if d.get('AssetNumber') == 'EB821' or d.get('ExternalIdentifier') == 'EB821':
            found_eb821_installed = True
            print(f"[OK] Found EB821 in INSTALLED: Asset={d.get('AssetNumber')}, Ext={d.get('ExternalIdentifier')}")
            break

    if found_eb821_installed:
        break

if not found_eb821_installed:
    print("[INFO] EB821 NOT in installed devices")

print("Searching deleted devices...")
found_eb821_deleted = False
for page in range(1, 8):
    r = s.get(f'https://mpsm.resolutionsbydesign.us/cms/api/get-deleted-devices.php?pageRows=100&pageNumber={page}')
    data = r.json()
    devices = data.get('devices', [])

    for d in devices:
        if d.get('AssetNumber') == 'EB821' or d.get('ExternalIdentifier') == 'EB821':
            found_eb821_deleted = True
            print(f"[OK] Found EB821 in DELETED: Asset={d.get('AssetNumber')}, Ext={d.get('ExternalIdentifier')}")
            break

    if found_eb821_deleted:
        break

if not found_eb821_deleted:
    print("[INFO] EB821 NOT in deleted devices")

# Test 5: Search for DO406
print("\n--- Test 5: Search for DO406 (target device) ---")
print("Searching by serial A4FK011003124...")
found_do406 = False
for page in range(1, 35):
    r = s.get(f'https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php?pageRows=100&pageNumber={page}&allCustomers=true')
    data = r.json()
    devices = data.get('devices', [])

    for d in devices:
        if d.get('SerialNumber') == 'A4FK011003124' or d.get('ExternalIdentifier') == 'DO406' or d.get('AssetNumber') == 'DO406':
            found_do406 = True
            print(f"[OK] Found DO406: Asset={d.get('AssetNumber')}, Serial={d.get('SerialNumber')}, Ext={d.get('ExternalIdentifier')}")
            break

    if found_do406:
        break

if not found_do406:
    print("[INFO] DO406 NOT in installed devices")

print("\n=== Summary ===")
print(f"EB045 (control): {'PASS' if found_eb045 else 'FAIL'}")
print(f"EB821: {'FOUND' if (found_eb821_installed or found_eb821_deleted) else 'NOT FOUND'}")
print(f"DO406: {'FOUND' if found_do406 else 'NOT FOUND'}")

print("\n=== CRITICAL FINDINGS ===")
if not found_eb821_installed and not found_eb821_deleted:
    print("[WARNING] EB821 does NOT exist in the Asset Management API")
    print("  This means it may have been:")
    print("  1. Deleted from the system entirely")
    print("  2. Under a different dealer code")
    print("  3. Using a different identifier")

if not found_do406:
    print("[WARNING] DO406 does NOT exist in the Asset Management API")
    print("  The user reported seeing it on 'MPSM website' which likely means")
    print("  the official Asset Management portal, not our dashboard")
