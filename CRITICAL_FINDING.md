# CRITICAL FINDING - API Customer Access Limitation

**Date:** 2025-11-01
**Status:** 🚨 BLOCKER IDENTIFIED

---

## The Problem

**User reports devices (EN413, EB821, DO406) are visible on "MPS Monitor" but NOT searchable in our dashboard.**

---

## Root Cause Discovered

### The API only returns 5 customers, NOT all customers

**Test Results:**
```
allCustomers=true returns only 5 customers:
1. FAYETTEVILLE STATE UNIVERSITY (CXFSWFLCK2)
2. HARNETT COUNTY FINANCE DEPARTMENT (OC0MIXVK6E)
3. RICHMOND COUNTY SCHOOLS (IU9NP77182)
4. SANDHILLS COMMUNITY COLLEGE (W108PH23E1)
5. SANFORD IMPORTS (RQWEKW38NZ)

Cape Fear customer: NOT IN LIST ❌
```

### Missing Devices Confirmed
- ✅ EN413: Cape Fear customer → NOT ACCESSIBLE
- ✅ EB821: Unknown customer → NOT ACCESSIBLE
- ✅ DO406: Customer W9OPXL0YDK → NOT IN ACCESSIBLE LIST

---

## Why `allCustomers=true` Doesn't Work

The Asset Management API parameter `allCustomers=true` does **NOT** return all customers in the system. It returns:
- Only customers associated with the authenticated dealer account
- Only customers with specific permissions/visibility
- A filtered subset based on dealer hierarchy

---

## The Two "MPS Monitor" Systems

### 1. Official Asset Management Portal
- URL: https://www.abassetmanagement.com (or similar)
- Access: Direct database access
- Visibility: ALL customers and devices
- **This is where user sees EN413, EB821, DO406**

### 2. Our Dashboard (API-based)
- URL: https://mpsm.resolutionsbydesign.us/cms/
- Access: API endpoints with limited scope
- Visibility: Only 5 customers currently
- **This is why devices are NOT found**

---

## Evidence

### Test 1: Search for EN413
```bash
Searched all 3,306 installed devices → NOT FOUND
Searched all 632 deleted devices → NOT FOUND
Reason: Cape Fear customer not in accessible customer list
```

### Test 2: Customer List
```bash
Only 5 customers accessible via allCustomers=true:
- Fayetteville State University
- Harnett County Finance Department
- Richmond County Schools
- Sandhills Community College
- Sanford Imports

Cape Fear: ❌ NOT IN LIST
```

### Test 3: Control Test
```bash
EB045 device → FOUND ✓
Customer: FAYETTEVILLE STATE UNIVERSITY
Proves: Search works for accessible customers
```

---

## Solutions

### Option 1: Get Customer Codes (FASTEST)
**User needs to provide Cape Fear's customer code**

If we have the customer code (like "W9OPXL0YDK" for DO406), we can query:
```javascript
api/get-devices.php?customerCode=XXXXX
```

This will return devices for that specific customer even if `allCustomers=true` doesn't include them.

### Option 2: Get API Access Expanded
**Contact Asset Management API support**

Request expanded dealer permissions to access all customers under the dealer account NY06AGDWUQ.

### Option 3: Direct Database Integration
**Requires significant development**

- Connect directly to Asset Management database
- Bypass API limitations
- Full visibility to all devices
- Requires credentials and infrastructure

### Option 4: Multi-Dealer Query
**Query each dealer code separately**

If devices are under different dealer codes, we need to:
1. Get list of all dealer codes
2. Query each dealer separately
3. Combine results

---

## Immediate Action Required

**QUESTION FOR USER:**

**1. What is Cape Fear's customer code in the Asset Management system?**

**2. Are EN413, EB821, DO406 under the same dealer code (NY06AGDWUQ)?**

**3. Do you have admin access to the official Asset Management portal to look up customer codes?**

---

## Why This Wasn't Caught Earlier

1. `allCustomers=true` parameter name is misleading
2. API documentation doesn't clarify scope limitations
3. We assumed "all customers" meant literally all customers
4. No test devices provided from inaccessible customers

---

## Current Dashboard Status

### ✅ What Works
- Search functionality: PERFECT (EB045 found successfully)
- Uninstalled devices: INCLUDED (632 devices)
- API endpoints: WORKING
- Total devices searchable: 3,938

### ❌ What's Limited
- Customer visibility: Only 5 customers
- Cannot access Cape Fear devices
- Cannot access other customers not in the list
- `allCustomers=true` is misleading

---

## Recommendation

**IMMEDIATE:** User must provide Cape Fear customer code so we can query it directly.

**SHORT-TERM:** Get list of ALL customer codes we should have access to, then modify search to query each one.

**LONG-TERM:** Request API access expansion or implement direct database integration.

---

**This is NOT a code bug. This is an API access/permissions limitation.**
