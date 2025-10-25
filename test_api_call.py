#!/usr/bin/env python3
"""Test MPS API call via engine"""
import requests
import json

response = requests.post(
    'https://mpsm.resolutionsbydesign.us/mps-api/query',
    json={
        'action': 'Customer/List',
        'params': {
            'dealerCode': 'NY06AGDWUQ',
            'pageNumber': 1,
            'pageRows': 5
        }
    }
)

print(f"Status: {response.status_code}")
print(f"Response: {json.dumps(response.json(), indent=2)}")
