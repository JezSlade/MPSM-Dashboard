# SortColumn Fix - Required Parameter Added

## Issue
**Error**: `"SortColumn cannot be null or empty"`

## Root Cause
The MPS Monitor API requires **both `SortColumn` and `SortOrder`** for most list/paged operations, but the examples were:
1. Using wrong parameter names (`SortField` instead of `SortColumn`)
2. Missing the parameters entirely in some examples

## Schema Requirements

Most paged operations use `FilteredPagedRequest` which requires:
```json
{
  "PageNumber": 1,      // Required
  "PageRows": 10,       // Required
  "SortColumn": "Name", // Required (was missing!)
  "SortOrder": "Asc"    // Required (was missing!)
}
```

## Fixed Examples

### 1. ✅ Dealer/GetDealers
**Before**:
```json
{"request": {"PageNumber": 1, "PageRows": 10, "SortField": "Name", "SortDirection": "Asc"}}
```

**After**:
```json
{"request": {"PageNumber": 1, "PageRows": 10, "SortColumn": "Name", "SortOrder": "Asc"}}
```

**Changes**: `SortField` → `SortColumn`, `SortDirection` → `SortOrder`

---

### 2. ✅ Customer/GetCustomers
**Before**:
```json
{"request": {"FilterDealerId": 1, "PageNumber": 1, "PageRows": 10}}
```

**After**:
```json
{"request": {"FilterDealerId": 1, "PageNumber": 1, "PageRows": 10, "SortColumn": "Name", "SortOrder": "Asc"}}
```

**Changes**: Added `SortColumn` and `SortOrder`

---

### 3. ✅ InstalledProduct/GetInstalledProducts
**Before**:
```json
{"request": {"PageNumber": 1, "PageRows": 10}}
```

**After**:
```json
{"request": {"PageNumber": 1, "PageRows": 10, "SortColumn": "ProductName", "SortOrder": "Asc"}}
```

**Changes**: Added `SortColumn` and `SortOrder`

---

### 4. ✅ Supply/GetSupplies
**Before**:
```json
{"request": {"PageNumber": 1, "PageRows": 10}}
```

**After**:
```json
{"request": {"PageNumber": 1, "PageRows": 10, "SortColumn": "Name", "SortOrder": "Asc"}}
```

**Changes**: Added `SortColumn` and `SortOrder`

---

### 5. ✅ Counter/Device/List
**Before**:
```json
{"request": {"FilterDeviceId": 1, "PageNumber": 1, "PageRows": 10}}
```

**After**:
```json
{"request": {"FilterDeviceId": 1, "PageNumber": 1, "PageRows": 10, "SortColumn": "DateRead", "SortOrder": "Desc"}}
```

**Changes**: Added `SortColumn` and `SortOrder`

---

## Parameter Name Reference

### MPS API Parameter Conventions

| Wrong ❌ | Correct ✅ | Used In |
|---------|-----------|---------|
| `SortField` | `SortColumn` | Most list operations |
| `SortDirection` | `SortOrder` | Most list operations |
| `pageSize` | `PageRows` | All paged operations |
| `pageNumber` | `PageNumber` | All paged operations |

### Valid SortColumn Values (Examples)

| Operation | Common SortColumn Values |
|-----------|-------------------------|
| Dealers | `Name`, `Code`, `CreatedDate` |
| Customers | `Name`, `Code`, `CreatedDate` |
| Devices | `SerialNumber`, `Model`, `LastSeen` |
| Products | `ProductName`, `Manufacturer` |
| Counters | `DateRead`, `TotalPages` |
| Supplies | `Name`, `PartNumber` |

### Valid SortOrder Values

- `Asc` - Ascending (A-Z, 0-9, oldest-newest)
- `Desc` - Descending (Z-A, 9-0, newest-oldest)

## Summary of All Examples

| Example | Has SortColumn/SortOrder | Status |
|---------|--------------------------|--------|
| Health Check | N/A (no params) | ✅ OK |
| Get Account Profile | N/A (no params) | ✅ OK |
| Get Dealer | N/A (single item) | ✅ OK |
| **List Dealers** | ✅ Yes | ✅ Fixed |
| **List Customers** | ✅ Yes | ✅ Fixed |
| Get Device | N/A (single item) | ✅ OK |
| **List Products** | ✅ Yes | ✅ Fixed |
| Explorer Data | Different schema | ✅ OK |
| **Device Counters** | ✅ Yes | ✅ Fixed |
| **List Supplies** | ✅ Yes | ✅ Fixed |

## Testing

All examples should now work without the "SortColumn cannot be null or empty" error.

### Test Steps:
1. Visit monitoring interface
2. Click "List Dealers" button
3. Submit the auto-filled form
4. Should return list of dealers (no error)

Repeat for other list operations.

## Files Modified

1. ✅ [index.php](index.php)
   - Fixed Dealer/GetDealers
   - Fixed Customer/GetCustomers
   - Fixed InstalledProduct/GetInstalledProducts
   - Fixed Supply/GetSupplies
   - Fixed Counter/Device/List

---

**Issue**: "SortColumn cannot be null or empty"
**Status**: ✅ Fixed
**Examples Updated**: 5
**Date**: 2025-10-16
