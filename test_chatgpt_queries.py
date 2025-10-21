#!/usr/bin/env python3
"""
ChatGPT-Style Comprehensive API Testing
Simulates how ChatGPT would query the MPSM API for customer data
"""

import requests
import json
from datetime import datetime

API_URL = "https://mpsm.resolutionsbydesign.us/mps-api/query"

def query_api(action, params=None):
    """Execute an API query"""
    payload = {
        "action": action,
        "params": params or {}
    }

    try:
        response = requests.post(API_URL, json=payload, timeout=30)
        return response.json()
    except Exception as e:
        return {"success": False, "error": str(e)}

def print_result(title, result):
    """Print formatted result"""
    print(f"\n{'='*80}")
    print(f"Query: {title}")
    print(f"{'='*80}")

    if result.get('success'):
        data = result.get('data')
        if isinstance(data, list):
            print(f"[OK] SUCCESS - {len(data)} items returned")
            if len(data) > 0:
                print(f"\nFirst item preview:")
                print(json.dumps(data[0], indent=2)[:500])
                if len(data) > 1:
                    print(f"\n... and {len(data)-1} more items")
        elif isinstance(data, dict):
            print(f"[OK] SUCCESS - Object returned")
            print(json.dumps(data, indent=2)[:800])
        else:
            print(f"[OK] SUCCESS")
            print(f"Data: {data}")
    else:
        print(f"[FAIL] FAILED: {result.get('error', 'Unknown error')}")

    print(f"\nDuration: {result.get('duration_ms', 'N/A')}ms")
    print(f"Request ID: {result.get('request_id', 'N/A')}")

# === ChatGPT Query Scenarios ===

print(f"\n[AI] SIMULATING CHATGPT QUERIES")
print(f"Timestamp: {datetime.now().isoformat()}")
print(f"\n" + "="*80)

# Scenario 1: User asks "Show me my dealer information"
print("\n[1] SCENARIO 1: Show me my dealer information")
result = query_api("AlertLimit/Dealer/Get")
print_result("Get Dealer Alert Limits", result)

# Scenario 2: User asks "List all API clients"
print("\n📋 SCENARIO 2: List all API clients")
result = query_api("ApiClient/List")
print_result("List API Clients", result)

# Scenario 3: User asks "What custom fields are available?"
print("\n📋 SCENARIO 3: What custom fields are available?")
result = query_api("CustomField/List")
print_result("List Custom Fields", result)

# Scenario 4: User asks "Show me customer integration status"
print("\n📋 SCENARIO 4: Show me customer integration status")
result = query_api("Integrations/GetJoinedCustomers")
print_result("Get Joined Customers", result)

# Scenario 5: User asks "What user roles exist?"
print("\n📋 SCENARIO 5: What user roles exist?")
result = query_api("Role/List")
print_result("List Roles", result)

# Scenario 6: User asks "Show me dealer products"
print("\n📋 SCENARIO 6: Show me dealer products")
result = query_api("DealerProduct/List")
print_result("List Dealer Products", result)

# Scenario 7: User asks "What printer brands are available?"
print("\n📋 SCENARIO 7: What printer brands are available?")
result = query_api("Product/Dealer/ListBrands")
print_result("List Printer Brands", result)

# Scenario 8: User asks "Show me available printer models"
print("\n📋 SCENARIO 8: Show me available printer models")
result = query_api("Product/Dealer/ListModels")
print_result("List Printer Models", result)

# Scenario 9: User asks "What are the customer dashboards?"
print("\n📋 SCENARIO 9: What are the customer dashboards?")
result = query_api("CustomerDashboard/Pages")
print_result("Get Customer Dashboard Pages", result)

# Scenario 10: User asks "Show me dealer supply sets"
print("\n📋 SCENARIO 10: Show me dealer supply sets")
result = query_api("DealerSupplySet/List")
print_result("List Dealer Supply Sets", result)

print(f"\n\n{'='*80}")
print("🎯 TEST COMPLETE")
print(f"{'='*80}\n")
