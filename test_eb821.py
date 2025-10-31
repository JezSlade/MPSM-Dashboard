import sys
import json

data = json.load(sys.stdin)
devices = data.get('devices', [])

print(f'Total devices returned: {len(devices)}')
print(f'Total in meta: {data.get("total")}')

# Search for EB821
eb821 = [d for d in devices if 'EB821' in (d.get('ExternalIdentifier') or '').upper()]

print(f'\n{"="*50}')
print(f'EB821 FOUND: {len(eb821) > 0}')
print(f'{"="*50}')

if eb821:
    for device in eb821:
        print(f'  ExternalIdentifier: {device.get("ExternalIdentifier")}')
        print(f'  SerialNumber: {device.get("SerialNumber")}')
        print(f'  IsOffline: {device.get("IsOffline")}')
        print(f'  Customer: {device.get("CustomerDescription")}')
else:
    print('  Device EB821 not found in response')

# Also check for offline devices
offline = [d for d in devices if d.get('IsOffline')]
print(f'\nOffline devices: {len(offline)}')
