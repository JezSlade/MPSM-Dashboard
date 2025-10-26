# MPS Monitor API - Verified Truths & Working Knowledge

**Last Updated**: 2025-10-24
**Session**: CMS Dashboard Development
**Status**: Production Verified ✅

---

## 🎯 Core API Architecture

### API Endpoint Structure
- **URL**: `https://mpsm.resolutionsbydesign.us/mps-api/query`
- **Method**: POST (always)
- **All Operations**: Single `/query` endpoint handles ALL 544 actions
- **Request Format**:
```json
{
  "action": "Device/List",
  "params": {
    "FilterDealerId": "SZ13qRwU5GtFLj0i_CbEgQ2",
    "pageNumber": 1,
    "pageRows": 100
  }
}
```
- **Response Format**:
```json
{
  "success": true,
  "data": [...],
  "error": null
}
```

### Authentication
- **Type**: OAuth 2.0 Password Grant (handled server-side by mps-api/engine.php)
- **Client Side**: No auth headers needed (proxied through /mps-api/query)

---

## 🔑 Critical IDs & Codes (VERIFIED)

### Dealer Information
```
Dealer Code: NY06AGDWUQ
Dealer ID:   SZ13qRwU5GtFLj0i_CbEgQ2
Dealer Name: SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE
```

### Default Customer (Cape Fear Medical Center)
```
Customer Code: W9OPXL0YDK
Customer ID:   0xUi5WEYLzOCrZ8ILowOvA2
Customer Name: CAPE FEAR VALLEY MED CTR.
Total Devices: 766 active devices
```

### Other Verified Customers (Sample)
```
INK1P3UAZR: 452 devices
5X86FEPU4R: 143 devices (CITY OF FAYETTEVILLE)
IU9NP77182: 125 devices (RICHMOND COUNTY SCHOOLS)
BG0XWRS21F: 3 devices (MANNA CHURCH)
```

**Total Across Dealer**: 2,500+ devices across 47 customers

---

## 📊 Device/List Endpoint (PRIMARY ENDPOINT)

### ✅ VERIFIED WORKING

**Action**: `Device/List`

**Parameters**:
```javascript
{
  "FilterDealerId": "SZ13qRwU5GtFLj0i_CbEgQ2",  // Required
  "pageNumber": 1,                               // Required (starts at 1)
  "pageRows": 100                                // Required (max 100)
}
```

**Optional Parameters**:
- `FilterCustomerId`: **DO NOT USE** - Returns devices from multiple customers (parent account ID)

### Response Structure (CRITICAL!)

**TRUTH**: Device data is at **ROOT LEVEL**, not nested in a `Customer` object!

```javascript
{
  "Id": "R7JAUfIi-V9k5-7SXcn2HA2",

  // Customer fields (ROOT LEVEL - NOT device.Customer.Code!)
  "CustomerCode": "BG0XWRS21F",                    // ✅ Use this
  "CustomerId": "TWK-x1Zd0o7yxYRRA7Il-w2",
  "CustomerDescription": "MANNA CHURCH",

  // Dealer fields
  "DealerId": "SZ13qRwU5GtFLj0i_CbEgQ2",
  "DealerCode": "NY06AGDWUQ",
  "DealerDescription": "SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE",

  // Device identification
  "ExternalIdentifier": "FN769",                   // Asset tag
  "AssetNumber": "FN769",
  "SerialNumber": "3090R300134",
  "MacAddress": "58-38-79-3F-8E-F6",
  "IpAddress": "192.168.2.200",

  // Location
  "OfficeId": "fVO2P-PH7ropFit0PKQdyg2",
  "OfficeCode": "DEFAULT",
  "OfficeDescription": "DEFAULT",
  "Note": "Executive Place",                       // Location note

  // Product info
  "Product": {
    "Model": "RICOH IM C2500",
    "Brand": "RICOH",
    "Id": "1XYJZJI4DFPwaqCD5hlRtQ2",
    "Color": 3,
    "FormatType": 2
  },

  // Toner levels (null if not available)
  "BlackToner": 40,                                // Percentage (0-100)
  "CyanToner": 50,
  "MagentaToner": 50,
  "YellowToner": 40,

  // Counters
  "CounterMono": 52937,                            // Total mono pages
  "CounterColor": 25512,                           // Total color pages
  "MonthlyMonoVolume": 163,                        // Pages this month
  "MonthlyColorVolume": 276,

  // Coverage
  "MonoCoverage": 4.58,                            // Percentage
  "ColorCoverage": 4.33,

  // Install info
  "Install": "2023-03-16T00:00:00Z",
  "InstallCounterMono": 47759,
  "InstallCounterColor": 16730,

  // Status
  "IsOffline": false,
  "LastUpdate": "2025-10-23T18:16:00Z",
  "IsManageSupplies": false,
  "IsAlertGenerator": true,
  "IsClassified": true
}
```

### Pagination TRUTH

**Verified Behavior**:
- ✅ Max `pageRows`: 100
- ✅ `pageNumber` starts at 1 (not 0)
- ✅ Returns empty array when no more pages
- ✅ Returns partial results on last page
- ✅ **Can paginate through 2,500+ devices** (tested up to 25 pages)

**Best Practice**:
```javascript
let allDevices = [];
let pageNumber = 1;
let hasMore = true;

while (hasMore && pageNumber <= 50) {  // Safety limit
    const devices = await makeRequest('Device/List', {
        FilterDealerId: dealerId,
        pageNumber: pageNumber,
        pageRows: 100
    }); // ✅ NO skipCache:true - use persistent cache!

    if (devices && devices.length > 0) {
        allDevices.push(...devices);
        hasMore = devices.length === 100;
        pageNumber++;
    } else {
        hasMore = false;
    }
}
```

### Client-Side Filtering (REQUIRED)

**TRUTH**: Must filter by `CustomerCode` on client side!

```javascript
// ❌ WRONG - FilterCustomerId returns multiple customers
const devices = await makeRequest('Device/List', {
    FilterDealerId: dealerId,
    FilterCustomerId: customerId  // Returns wrong customers!
});

// ✅ CORRECT - Filter client-side by CustomerCode
const allDevices = await fetchAllPages();
const customerDevices = allDevices.filter(d =>
    d.CustomerCode === 'W9OPXL0YDK'
);
```

---

## 📋 CustomerDashboard Endpoint

### ✅ VERIFIED WORKING

**Action**: `CustomerDashboard`

**Parameters**:
```javascript
{
  "customerCode": "W9OPXL0YDK"  // Required
}
```

**Response Structure**:
```javascript
{
  "SdsDashboard": {
    "TotalDevices": 104,                      // HP SDS devices only
    "DevicesWithErrors": 0,
    "DevicesLowToner": 0,
    "NonCommunicatingDevices": 8,
    "CommonActionsToComplete": 3
  },
  "MpsDashboardCustomer": {
    "TotalManagedDevices": 104,               // Same as SdsDashboard.TotalDevices
    "ContactedDevices": [
      {"Key": "Today", "Value": "83"},
      {"Key": "Yesterday", "Value": "13"}
    ],
    "SupplyAlerts": [
      {"Key": "ToManage", "Value": "156"},
      {"Key": "Shipped", "Value": "0"},
      {"Key": "Installed", "Value": "668"}
    ]
  }
}
```

**TRUTH**:
- ✅ Returns **HP SDS devices only** (104)
- ✅ Total devices (766) comes from Device/List, not CustomerDashboard
- ✅ Nested structure: `data.SdsDashboard.TotalDevices`

---

## 📄 CustomerDashboard/Pages Endpoint

### ✅ VERIFIED WORKING

**Action**: `CustomerDashboard/Pages`

**Parameters**:
```javascript
{
  "customerCode": "W9OPXL0YDK"
}
```

**Response**:
```javascript
{
  "MonthlyMonoManaged": 294072,
  "MonthlyMonoUnManaged": 3555,
  "MonthlyColorManaged": 66123,
  "MonthlyColorUnManaged": 0,
  "AnomalousCounters": 0
}
```

---

## 🚨 Common Mistakes & Solutions

### ❌ WRONG: Using device.Customer.Code
```javascript
// This FAILS - Customer is not nested
if (device.Customer && device.Customer.Code === customerCode) {
    // Never executes
}
```

### ✅ CORRECT: Using device.CustomerCode
```javascript
// This WORKS - Fields are at root level
if (device.CustomerCode === customerCode) {
    // Works correctly
}
```

---

### ❌ WRONG: Using FilterCustomerId
```javascript
// Returns devices from MULTIPLE customers
const devices = await makeRequest('Device/List', {
    FilterDealerId: dealerId,
    FilterCustomerId: '0xUi5WEYLzOCrZ8ILowOvA2'  // Parent account ID
});
// Result: Manna Church, Caviness & Cates, Goldsboro Milling, etc.
```

### ✅ CORRECT: Filter by CustomerCode client-side
```javascript
const allDevices = await fetchAllDevicesForDealer();
const customerDevices = allDevices.filter(d =>
    d.CustomerCode === 'W9OPXL0YDK'
);
// Result: Only Cape Fear devices (766)
```

---

### ❌ WRONG: Using skipCache in pagination loops
```javascript
// This KILLS performance - bypasses cache every request
while (hasMore) {
    const devices = await makeRequest('Device/List', {
        FilterDealerId: dealerId,
        pageNumber: page,
        pageRows: 100
    }, { skipCache: true });  // ❌ NO!
}
// Result: 10-15 seconds EVERY page load
```

### ✅ CORRECT: Use persistent cache
```javascript
// This is FAST - uses localStorage cache
while (hasMore) {
    const devices = await makeRequest('Device/List', {
        FilterDealerId: dealerId,
        pageNumber: page,
        pageRows: 100
    });  // ✅ Uses cache (5-minute TTL)
}
// Result: <1 second on subsequent loads
```

---

## 🔄 Caching Strategy (VERIFIED WORKING)

### localStorage Cache Implementation

**Configuration**:
```javascript
const CACHE_DURATION = 300000; // 5 minutes
```

**Storage**:
```javascript
// Cache key format
const key = `${action}:${JSON.stringify(params)}`;
localStorage.setItem(`mps_cache_${md5(key)}`, JSON.stringify({
    data: responseData,
    timestamp: Date.now()
}));
```

**Performance**:
- ✅ First load: 10-15 seconds (8 pages × 100 devices)
- ✅ Subsequent loads: <1 second (from localStorage)
- ✅ Cache persists across page refreshes
- ✅ 5-minute TTL expires stale data

**CRITICAL**: Never use `skipCache: true` in pagination loops!

---

## 📊 Verified Endpoint List

### Working Endpoints ✅

| Endpoint | Purpose | Parameters | Returns |
|----------|---------|------------|---------|
| `Device/List` | Get all devices | FilterDealerId, pageNumber, pageRows | Array of devices |
| `CustomerDashboard` | SDS metrics | customerCode | SDS stats + managed devices |
| `CustomerDashboard/Pages` | Page volumes | customerCode | Monthly page totals |
| `Device/GetSuppliesDetails` | Toner details | deviceId | Supply levels |
| `Device/GetDeviceAdditionalInfos` | Extra device data | deviceId | Additional info |
| `Counter/Device/List` | Counter history | deviceId | Counter records |
| `SdsAction/GetDeviceActions` | Recent actions | customerCode | Action list |
| `Dealer/GetDealerHierarchy` | Dealer tree | none | Dealer structure |

### Endpoints to Avoid ⚠️

| Endpoint | Issue |
|----------|-------|
| `Device/Deleted/ListByDealer` | Returns **deleted** devices (not active) |
| Any endpoint with `FilterCustomerId` | Returns multiple customers (parent account) |

---

## 💡 Best Practices

### 1. Field Access
```javascript
✅ device.CustomerCode
✅ device.CustomerDescription
✅ device.CustomerId
✅ device.ExternalIdentifier
✅ device.Product.Model

❌ device.Customer.Code
❌ device.Customer.Description
```

### 2. Pagination
```javascript
✅ while (hasMore && page <= 50)
✅ pageNumber starts at 1
✅ pageRows max 100
✅ Check devices.length === 100 to determine hasMore

❌ Don't use skipCache: true
❌ Don't assume 50 items per page
❌ Don't start pageNumber at 0
```

### 3. Caching
```javascript
✅ Use persistent localStorage
✅ 5-minute TTL
✅ Let makeRequest handle caching
✅ Clear cache only when user requests

❌ Don't use { skipCache: true } in loops
❌ Don't cache forever (use TTL)
❌ Don't cache errors
```

### 4. Customer Filtering
```javascript
✅ Fetch all dealer devices
✅ Filter client-side by CustomerCode
✅ Use === for exact match

❌ Don't use FilterCustomerId
❌ Don't filter server-side by customer
```

---

## 🧪 Verified Data Points

### Device Counts
- **Total across dealer**: 2,500+ devices
- **Cape Fear (W9OPXL0YDK)**: 766 devices
- **Customers**: 47 unique customers
- **Pages to fetch all**: ~25 pages @ 100/page

### Toner Data
- **Format**: Integer 0-100 (percentage)
- **null**: No toner data available
- **Thresholds**:
  - Critical: <10%
  - Low: <20%
  - Warning: <40%
  - Good: >=40%

### Counters
- **MonthlyMonoVolume**: Pages printed this month (mono)
- **MonthlyColorVolume**: Pages printed this month (color)
- **CounterMono**: Total lifetime mono pages
- **CounterColor**: Total lifetime color pages
- **Coverage**: Percentage (e.g., 4.58%)

### Status Fields
- **IsOffline**: Boolean (true = offline)
- **LastUpdate**: ISO 8601 timestamp
- **IsManageSupplies**: Boolean
- **IsAlertGenerator**: Boolean

---

## 🔍 Discovery Notes

### How We Found This
1. Started with swagger.json (544 endpoints)
2. Tested Device/Deleted/ListByDealer (WRONG - deleted devices)
3. Found Device/List (CORRECT - active devices)
4. Discovered field structure via sample response
5. Tested FilterCustomerId (WRONG - multiple customers)
6. Verified client-side filtering (CORRECT)
7. Found skipCache bug killing performance
8. Measured: 766 devices for Cape Fear vs 104 SDS-only

### Testing Methodology
```python
# Test Device/List
response = requests.post(
    'https://mpsm.resolutionsbydesign.us/mps-api/query',
    json={
        'action': 'Device/List',
        'params': {
            'FilterDealerId': 'SZ13qRwU5GtFLj0i_CbEgQ2',
            'pageNumber': 1,
            'pageRows': 100
        }
    }
)
```

---

## 📚 Related Documentation

- [DEPLOYMENT_COMPLETE.md](DEPLOYMENT_COMPLETE.md) - Caching fix & deployment summary
- [CARD_FIXES_READY_TO_DEPLOY.md](CARD_FIXES_READY_TO_DEPLOY.md) - Card implementations
- [ENHANCEMENT_PLAN.md](ENHANCEMENT_PLAN.md) - Full roadmap
- [mps-api/swagger.json](mps-api/swagger.json) - All 544 endpoints

---

## ✅ Verification Checklist

Use this to verify your implementation:

- [ ] Using `device.CustomerCode` (not `device.Customer.Code`)
- [ ] Using `device.ExternalIdentifier` for display
- [ ] Filtering by `CustomerCode` client-side
- [ ] NOT using `FilterCustomerId` parameter
- [ ] NOT using `skipCache: true` in loops
- [ ] Pagination starts at pageNumber: 1
- [ ] Pagination uses pageRows: 100
- [ ] Checking `devices.length === 100` for hasMore
- [ ] Using Device/List (not Device/Deleted/ListByDealer)
- [ ] localStorage cache with 5-minute TTL
- [ ] Nested access: `data.SdsDashboard.TotalDevices`

---

**Generated**: 2025-10-24
**Verified Against**: Production API
**Customer**: Cape Fear Valley Med Ctr (766 devices)
**Dealer**: SYSTEL Business Equipment (2,500+ devices)
**Session Token Usage**: ~138K/200K
