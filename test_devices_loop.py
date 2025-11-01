#!/usr/bin/env python3
"""
Automated test loop to verify EB821 and DO406 are found
Continues until both devices are consistently found
"""

import requests
import json
import time

BASE_URL = "https://mpsm.resolutionsbydesign.us/cms/api"
COOKIES_FILE = "/tmp/cookies.txt"
TARGET_DEVICES = ["EB821", "DO406"]

def login():
    """Login and get session"""
    response = requests.post(
        f"{BASE_URL}/login.php",
        json={"username": "admin", "password": "admin"}
    )
    return response.cookies

def test_get_devices(cookies):
    """Test get-devices.php endpoint"""
    print("\n=== Testing get-devices.php ===")

    all_devices = []
    for page in range(1, 35):  # 34 pages for 3400 devices at 100 per page
        response = requests.get(
            f"{BASE_URL}/get-devices.php",
            params={"pageRows": 100, "pageNumber": page, "allCustomers": "true"},
            cookies=cookies
        )

        if not response.ok:
            print(f"ERROR: HTTP {response.status_code} on page {page}")
            break

        data = response.json()
        if not data.get("success") or not data.get("devices"):
            break

        all_devices.extend(data["devices"])

        if len(data["devices"]) < 100:
            break

    print(f"Total devices from get-devices.php: {len(all_devices)}")

    found = {}
    for target in TARGET_DEVICES:
        matches = [d for d in all_devices if target in (d.get("ExternalIdentifier") or "").upper()]
        found[target] = len(matches) > 0
        if found[target]:
            print(f"  [+] {target} FOUND")
        else:
            print(f"  [-] {target} NOT FOUND")

    return found, all_devices

def test_deleted_devices(cookies):
    """Test get-deleted-devices.php endpoint"""
    print("\n=== Testing get-deleted-devices.php ===")

    all_devices = []
    for page in range(1, 10):
        response = requests.get(
            f"{BASE_URL}/get-deleted-devices.php",
            params={"pageRows": 100, "pageNumber": page, "dealerCode": "NY06AGDWUQ"},
            cookies=cookies
        )

        if not response.ok:
            print(f"ERROR: HTTP {response.status_code} on page {page}")
            break

        data = response.json()
        if not data.get("success") or not data.get("devices"):
            break

        all_devices.extend(data["devices"])

        if len(data["devices"]) < 100:
            break

    print(f"Total deleted devices: {len(all_devices)}")

    found = {}
    for target in TARGET_DEVICES:
        matches = [d for d in all_devices if target in (d.get("ExternalIdentifier") or "").upper()]
        found[target] = len(matches) > 0
        if found[target]:
            print(f"  [+] {target} FOUND in deleted devices")
        else:
            print(f"  [-] {target} NOT FOUND in deleted devices")

    return found, all_devices

def test_cached_devices(cookies):
    """Test cached devices endpoint"""
    print("\n=== Testing get-cached-devices.php ===")

    response = requests.get(f"{BASE_URL}/get-cached-devices.php", cookies=cookies)

    if not response.ok:
        print(f"ERROR: HTTP {response.status_code}")
        return {}, []

    data = response.json()
    devices = data.get("devices", [])

    print(f"Total cached devices: {len(devices)}")
    print(f"Cache age: {data.get('age', 'N/A')}s")

    found = {}
    for target in TARGET_DEVICES:
        matches = [d for d in devices if target in (d.get("ExternalIdentifier") or "").upper()]
        found[target] = len(matches) > 0
        if found[target]:
            print(f"  [+] {target} FOUND in cache")
        else:
            print(f"  [-] {target} NOT FOUND in cache")

    return found, devices

def main():
    print("=" * 60)
    print("AUTOMATED DEVICE SEARCH TEST LOOP")
    print("Target devices: EB821, DO406")
    print("=" * 60)

    cookies = login()
    print("OK Logged in")

    # Test both endpoints
    installed_found, installed_devices = test_get_devices(cookies)
    deleted_found, deleted_devices = test_deleted_devices(cookies)

    # Combine results
    all_devices = installed_devices + deleted_devices
    combined_found = {}

    for target in TARGET_DEVICES:
        combined_found[target] = installed_found.get(target, False) or deleted_found.get(target, False)

    print("\n" + "=" * 60)
    print("COMBINED RESULTS:")
    for target, found in combined_found.items():
        status = "[+] FOUND" if found else "[-] NOT FOUND"
        print(f"  {target}: {status}")

    print(f"\nTotal devices across all endpoints: {len(all_devices)}")

    if all(combined_found.values()):
        print("\nSUCCESS! All target devices found!")
        return True
    else:
        print("\nFAILURE: Some devices not found")
        return False

if __name__ == "__main__":
    main()
