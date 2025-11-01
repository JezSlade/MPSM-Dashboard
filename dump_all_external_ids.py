#!/usr/bin/env python3
"""
Dump ALL External IDs from the API to a file for manual searching
"""

import requests
import json

BASE_URL = "https://mpsm.resolutionsbydesign.us/cms/api"

def login():
    response = requests.post(
        f"{BASE_URL}/login.php",
        json={"username": "admin", "password": "admin"}
    )
    return response.cookies

def main():
    print("Fetching ALL devices...")
    cookies = login()

    all_devices = []
    for page in range(1, 35):
        response = requests.get(
            f"{BASE_URL}/get-devices.php",
            params={"pageRows": 100, "pageNumber": page, "allCustomers": "true"},
            cookies=cookies
        )

        if not response.ok:
            break

        data = response.json()
        if not data.get("success") or not data.get("devices"):
            break

        all_devices.extend(data["devices"])
        print(f"  Page {page}: {len(data['devices'])} devices (total: {len(all_devices)})")

        if len(data["devices"]) < 100:
            break

    print(f"\nTotal devices: {len(all_devices)}")

    # Extract External IDs
    external_ids = []
    for d in all_devices:
        ext_id = d.get('ExternalIdentifier', '')
        serial = d.get('SerialNumber', '')
        model = d.get('Product', {}).get('Model', '')
        customer = d.get('CustomerDescription', '')

        if ext_id:
            external_ids.append({
                'ExternalIdentifier': ext_id,
                'SerialNumber': serial,
                'Model': model,
                'Customer': customer
            })

    # Write to file
    with open('all_external_ids.json', 'w') as f:
        json.dump(external_ids, f, indent=2)

    print(f"\nWrote {len(external_ids)} external IDs to all_external_ids.json")

    # Also write simple text list
    with open('all_external_ids.txt', 'w') as f:
        for item in sorted(external_ids, key=lambda x: x['ExternalIdentifier']):
            f.write(f"{item['ExternalIdentifier']}\n")

    print(f"Wrote simple list to all_external_ids.txt")

    # Search for EB and DO patterns
    eb_devices = [x for x in external_ids if 'EB' in x['ExternalIdentifier'].upper()]
    do_devices = [x for x in external_ids if 'DO' in x['ExternalIdentifier'].upper()]

    print(f"\n EB* devices: {len(eb_devices)}")
    for d in eb_devices[:10]:
        print(f"    {d['ExternalIdentifier']}")

    print(f"\nDO* devices: {len(do_devices)}")
    for d in do_devices[:10]:
        print(f"    {d['ExternalIdentifier']}")

if __name__ == "__main__":
    main()
