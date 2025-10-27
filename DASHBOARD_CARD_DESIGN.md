# MPS Monitor Dashboard Card Design
**Version:** 1.0
**Created:** 2025-10-27
**Purpose:** Define dashboard card layouts for the MPS Monitor CMS based on verified API endpoints

---

## Executive Summary

This document outlines the recommended dashboard cards for an MPS Monitor fleet management system. Based on analysis of 188 API endpoints (82 verified working), we've identified the **TOP 15 MOST VALUABLE ENDPOINTS** for fleet monitoring and management.

**Key Statistics from API Analysis:**
- Total Endpoints Tested: 188
- Working Endpoints: 82 (43.62% success rate)
- Average Response Time: ~3,000ms
- Data Categories: Device Management, Supplies, Alerts, Customer Management, Integrations

---

## Dashboard Card Priority Matrix

### Tier 1: Critical Dashboard Cards (Always Visible)
Cards that provide immediate fleet health status and actionable insights.

### Tier 2: Secondary Dashboard Cards (Collapsible/Tabs)
Important but less urgent information that can be accessed on-demand.

### Tier 3: Drill-Down Views (Click to Expand)
Detailed information accessed from main cards.

### Tier 4: Reports & Admin (Separate Section)
Exportable data and configuration settings.

---

## TOP 15 PRIORITY ENDPOINTS FOR DASHBOARD

### TIER 1: CRITICAL DASHBOARD CARDS

---

## 1. CUSTOMER DASHBOARD OVERVIEW CARD

**Endpoint:** `CustomerDashboard`
**Priority:** TIER 1 - Main Dashboard Card
**Status:** Verified Working (Success: true, Response: 3181ms)

### Endpoint Details
```json
{
  "action": "CustomerDashboard",
  "method": "GET",
  "requires_auth": true,
  "payload": {
    "customerCode": "REQUIRED"
  }
}
```

### Data Returned
- **Type:** Dictionary (dict)
- **Contains:** Comprehensive customer statistics and metrics
- **Key Metrics:** Device counts, alert summaries, volume statistics

### Card Design Recommendation

**Layout:** Hero Card (Full Width, Top of Dashboard)

```
┌─────────────────────────────────────────────────────────────────┐
│  CUSTOMER DASHBOARD                          [Last Updated: X]  │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐       │
│  │  DEVICES │  │  ALERTS  │  │ SUPPLIES │  │  VOLUME  │       │
│  │    142   │  │    23    │  │   LOW: 8 │  │  245.3K  │       │
│  │  Active  │  │ Critical │  │  OK: 134 │  │  Pages   │       │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘       │
│                                                                   │
│  Customer: [CUSTOMER NAME]                                       │
│  Contract Expires: [DATE] | Monthly Mono: X | Monthly Color: Y  │
│                                                                   │
│  [View Full Details] [Export Report]                            │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

### Required API Payload
```php
$payload = [
    'customerCode' => 'W9OPXL0YDK' // From session/dropdown
];
```

### Data Transformation
```php
// Extract key metrics from API response
$deviceCount = count($response['devices']);
$activeAlerts = count(array_filter($response['alerts'], fn($a) => !$a['IsHidden']));
$lowSupplies = count(array_filter($response['supplies'], fn($s) => $s['level'] < 20));
$monthlyVolume = $response['monthlyVolumeMono'] + $response['monthlyVolumeColor'];
```

### Refresh Interval
- **Default:** 5 minutes
- **User Configurable:** 1-60 minutes
- **Real-time on Alert:** Critical alerts trigger immediate refresh

---

## 2. DEVICE LIST & STATUS CARD

**Endpoint:** `Device/List`
**Priority:** TIER 1 - Main Dashboard Card
**Status:** Verified Working (Success: true, Returns: 50 devices per page)

### Endpoint Details
```json
{
  "action": "Device/List",
  "method": "POST",
  "requires_auth": true,
  "payload": {
    "FilterCustomerCodes": ["REQUIRED"],
    "Status": 1,
    "PageNumber": 1,
    "PageRows": 50,
    "SortColumn": "Id",
    "SortOrder": 0
  }
}
```

### Data Returned
- **Type:** Paged List (PagedResultResponse)
- **Items Per Page:** 50 (configurable)
- **Contains:** Device details, toner levels, counters, alerts

### Card Design Recommendation

**Layout:** Data Table with Status Indicators

```
┌─────────────────────────────────────────────────────────────────────┐
│  FLEET DEVICES                        [Filter ▼] [Search]  [Export] │
├──────────┬──────────────┬────────┬─────────┬─────────┬─────────────┤
│ STATUS   │ DEVICE       │ MODEL  │ TONER   │ COUNTER │ LAST UPDATE │
├──────────┼──────────────┼────────┼─────────┼─────────┼─────────────┤
│ 🟢 ONLINE│ HPC16790     │ HP M404│ █████ 87│  92,467 │ 2h ago      │
│ 🟡 LOW   │ KM363-33     │ KM 363 │ ██░░░ 23│ 718,480 │ 5h ago      │
│ 🔴 ALERT │ HP-E78330    │ HP E783│ ░░░░░  4│ 109,549 │ 12h ago     │
│ 🟢 ONLINE│ MX-5141      │ SHARP  │ ████░ 76│  45,223 │ 1h ago      │
│ ⚫ OFFLINE│ BIZ-454E     │ KM 454 │ ░░░░░  0│ 231,445 │ 2 days ago  │
├──────────┴──────────────┴────────┴─────────┴─────────┴─────────────┤
│ Showing 1-5 of 142 devices           [1][2][3]...[29] [Next >]    │
└─────────────────────────────────────────────────────────────────────┘
```

### Required API Payload
```php
$payload = [
    'FilterDealerId' => $_SESSION['dealer_id'],
    'FilterCustomerCodes' => [$customerCode],
    'Status' => 1, // 1=Active, 2=Inactive, 3=All
    'PageNumber' => 1,
    'PageRows' => 50,
    'SortColumn' => 'LastUpdate',
    'SortOrder' => 1 // 0=Asc, 1=Desc
];
```

### Data Transformation
```php
// Transform device status
$statusMap = [
    'online' => ['color' => 'green', 'icon' => '🟢', 'text' => 'ONLINE'],
    'low_toner' => ['color' => 'yellow', 'icon' => '🟡', 'text' => 'LOW SUPPLY'],
    'alert' => ['color' => 'red', 'icon' => '🔴', 'text' => 'ALERT'],
    'offline' => ['color' => 'gray', 'icon' => '⚫', 'text' => 'OFFLINE']
];

// Calculate toner level visual
function renderTonerBar($level) {
    $filled = round($level / 20); // 5 blocks = 100%
    return str_repeat('█', $filled) . str_repeat('░', 5 - $filled) . ' ' . $level;
}
```

### Click Actions
- **Row Click:** Navigate to Device Detail View (Drill-Down)
- **Device Name:** Quick Info Popup (Drill-Down)
- **Toner Bar:** Show Supply Details (Drill-Down)
- **Export Button:** Generate CSV/PDF Report

---

## 3. ACTIVE ALERTS & WARNINGS CARD

**Endpoint:** `SupplyAlert/List`
**Priority:** TIER 1 - Main Dashboard Card
**Status:** Verified Working (Returns: 50 alerts per page)

### Endpoint Details
```json
{
  "action": "SupplyAlert/List",
  "method": "POST",
  "requires_auth": true,
  "payload": {
    "DealerCode": "REQUIRED",
    "CustomerCode": "OPTIONAL",
    "SupplyType": null,
    "ManageOption": null,
    "PageNumber": 1,
    "PageRows": 50,
    "SortColumn": "InitialDate",
    "SortOrder": 0
  }
}
```

### Data Returned
- **Type:** Paged List
- **Contains:** Supply alerts, toner warnings, maintenance kit notifications
- **Key Fields:** Device info, alert type, dates, supply levels

### Card Design Recommendation

**Layout:** Prioritized Alert List with Action Buttons

```
┌─────────────────────────────────────────────────────────────────────┐
│  SUPPLY ALERTS & WARNINGS               [Filter: All ▼] [Critical ▼]│
├─────────┬──────────────────────┬──────────────┬──────────┬─────────┤
│ URGENCY │ DEVICE               │ ALERT        │ DATE     │ ACTION  │
├─────────┼──────────────────────┼──────────────┼──────────┼─────────┤
│ 🔴 HIGH │ HP E78330            │ Black Toner  │ 2h ago   │ [Ship]  │
│         │ CNB3P1C8GW          │ 4% Remaining │          │ [Hide]  │
├─────────┼──────────────────────┼──────────────┼──────────┼─────────┤
│ 🟡 MED  │ KM BIZHUB 363       │ Developer    │ 5h ago   │ [Ship]  │
│         │ A1UE011023015       │ 0% Black     │          │ [Hide]  │
├─────────┼──────────────────────┼──────────────┼──────────┼─────────┤
│ 🟢 LOW  │ HP M404DN           │ Black Toner  │ 1d ago   │ [Ship]  │
│         │ JPBDM26300          │ 15% Remaining│          │ [Hide]  │
├─────────┴──────────────────────┴──────────────┴──────────┴─────────┤
│ 23 Total Alerts | 8 Critical | 12 Shipped | 3 Hidden               │
│ [View All Alerts] [Mass Ship] [Export Report]                      │
└─────────────────────────────────────────────────────────────────────┘
```

### Required API Payload
```php
$payload = [
    'DealerCode' => $_SESSION['dealer_code'],
    'CustomerCode' => $customerCode,
    'SupplyType' => null, // 1=MaintKit, 2=PhotoConductor, 3=Toner
    'ManageOption' => null, // 0=ToManage, 1=Managed, 2=All
    'HiddenOption' => false,
    'PageNumber' => 1,
    'PageRows' => 50,
    'SortColumn' => 'InitialDate',
    'SortOrder' => 1 // Most recent first
];
```

### Data Transformation
```php
// Calculate urgency based on supply level and age
function calculateUrgency($alert) {
    $level = $alert['ActualResidualPercentage'];
    $daysOpen = (strtotime('now') - strtotime($alert['InitialDate'])) / 86400;

    if ($level <= 5 || $daysOpen > 7) return 'HIGH';
    if ($level <= 15 || $daysOpen > 3) return 'MEDIUM';
    return 'LOW';
}

// Format supply type display
$supplyTypeMap = [
    1 => 'Maint Kit',
    2 => 'Photo Conductor',
    3 => 'Toner'
];
```

### Action Buttons
- **[Ship]:** Create shipping order for supply
- **[Hide]:** Hide alert from list (sets IsHidden = true)
- **[Mass Ship]:** Bulk ship multiple selected alerts
- **[Export Report]:** PDF/CSV of all alerts

---

## 4. FLEET STATISTICS & METRICS CARD

**Endpoint:** `CustomerDashboard/Pages`
**Priority:** TIER 1 - Main Dashboard Card
**Status:** Verified Working (Success: true, Response: 2749ms)

### Endpoint Details
```json
{
  "action": "CustomerDashboard/Pages",
  "method": "GET",
  "requires_auth": true,
  "payload": {
    "customerCode": "REQUIRED"
  }
}
```

### Data Returned
- **Type:** Dictionary
- **Contains:** Page volume statistics, print trends, usage analytics

### Card Design Recommendation

**Layout:** Multi-Metric Dashboard with Charts

```
┌─────────────────────────────────────────────────────────────────────┐
│  FLEET STATISTICS                              [Period: Month ▼]    │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌──────────────────────────┐  ┌──────────────────────────┐        │
│  │ PRINT VOLUME TREND       │  │ SUPPLY FORECAST          │        │
│  │                          │  │                          │        │
│  │    📊 [Chart Area]       │  │    📊 [Chart Area]       │        │
│  │                          │  │                          │        │
│  │  Mono: 2.8M pages        │  │  Low in 7 Days: 8        │        │
│  │  Color: 248K pages       │  │  Low in 30 Days: 23      │        │
│  └──────────────────────────┘  └──────────────────────────┘        │
│                                                                       │
│  ┌───────────────┬───────────────┬───────────────┬───────────────┐ │
│  │ AVG DAILY VOL │ COST PER PAGE │  DEVICES/USER │ AVG AGE       │ │
│  │    14,521     │    $0.0042    │      1.8      │   2.3 Years   │ │
│  └───────────────┴───────────────┴───────────────┴───────────────┘ │
│                                                                       │
│  [View Detailed Analytics] [Export Report]                          │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

### Required API Payload
```php
$payload = [
    'customerCode' => $customerCode
];
```

### Data Transformation
```php
// Calculate metrics from response data
$metrics = [
    'avgDailyVolume' => ($response['MonthlyVolumeMono'] + $response['MonthlyVolumeColor']) / 30,
    'costPerPage' => 0.0042, // From contract data
    'devicesPerUser' => $totalDevices / $totalUsers,
    'avgDeviceAge' => calculateAverageAge($devices)
];

// Generate chart data for trends
$chartData = generateVolumeChart($response['history'], $period);
```

---

## 5. HP SDS SERVICE REQUESTS CARD

**Endpoint:** `SdsAction/GetDeviceActions`
**Priority:** TIER 1 - Main Dashboard Card (for HP fleets)
**Status:** Verified Working (Returns: 15 items, Response: 13597ms)

### Endpoint Details
```json
{
  "action": "SdsAction/GetDeviceActions",
  "method": "GET",
  "requires_auth": true,
  "payload": {
    "DealerCode": "REQUIRED",
    "CustomerCode": "OPTIONAL",
    "State": null,
    "Severity": null,
    "ActionType": null,
    "PageNumber": 1,
    "PageRows": 50
  }
}
```

### Data Returned
- **Type:** Paged List
- **Count:** 15 items typical
- **Contains:** HP-specific service requests, predictive maintenance, firmware alerts

### Card Design Recommendation

**Layout:** Service Request Queue with Priority

```
┌─────────────────────────────────────────────────────────────────────┐
│  HP SDS SERVICE REQUESTS                    [Filter: Open ▼]        │
├──────────┬──────────────────┬─────────────────┬──────────┬─────────┤
│ PRIORITY │ DEVICE           │ REQUEST         │ OPENED   │ ACTION  │
├──────────┼──────────────────┼─────────────────┼──────────┼─────────┤
│ ⚠️ HIGH  │ HP E78330        │ TriageFuser     │ 4d ago   │ [View]  │
│          │ CNB3P1C8GW      │ Event: 50.*     │          │ [Close] │
│          │                  │ MTTR: 2.5h      │          │         │
├──────────┼──────────────────┼─────────────────┼──────────┼─────────┤
│ 🔧 MED   │ HP M404DN       │ FirmwareUpdate  │ 7d ago   │ [View]  │
│          │ JPBDM26300      │ Available       │          │ [Apply] │
├──────────┴──────────────────┴─────────────────┴──────────┴─────────┤
│ 14 Open Requests | 3 High Priority | 6 Resolved This Week          │
│ [View All] [Generate Work Orders]                                   │
└─────────────────────────────────────────────────────────────────────┘
```

### Required API Payload
```php
$payload = [
    'DealerCode' => $_SESSION['dealer_code'],
    'CustomerCode' => $customerCode,
    'State' => null, // 0=New, 1=InProgress, 2=Closed
    'Severity' => null, // 1=High, 2=Medium, 3=Low
    'ActionType' => null, // Filter by type
    'PageNumber' => 1,
    'PageRows' => 50,
    'SortColumn' => 'ActionDateUtc',
    'SortOrder' => 1
];
```

### Data Transformation
```php
// Map severity to display priority
$severityMap = [
    1 => ['icon' => '⚠️', 'text' => 'HIGH', 'class' => 'priority-high'],
    2 => ['icon' => '🔧', 'text' => 'MED', 'class' => 'priority-medium'],
    3 => ['icon' => '📋', 'text' => 'LOW', 'class' => 'priority-low']
];

// Calculate service request age
function formatRequestAge($dateUtc) {
    $diff = time() - strtotime($dateUtc);
    $days = floor($diff / 86400);
    return $days > 0 ? "{$days}d ago" : "Today";
}
```

---

### TIER 2: SECONDARY DASHBOARD CARDS

---

## 6. EXPLORER CONNECTORS STATUS CARD

**Endpoint:** `Explorer/GetExplorerDatas`
**Priority:** TIER 2 - Secondary Dashboard Card
**Status:** Verified Working (Returns: 50 items, Response: 4637ms)

### Endpoint Details
```json
{
  "action": "Explorer/GetExplorerDatas",
  "method": "GET",
  "requires_auth": true,
  "payload": {
    "dealerCode": "OPTIONAL",
    "customerCode": "OPTIONAL"
  }
}
```

### Card Design Recommendation

**Layout:** Connector Health Dashboard (Collapsible)

```
┌─────────────────────────────────────────────────────────────────────┐
│  EXPLORER CONNECTORS                      [▼ Collapse] [Refresh]    │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ Connected: 47  │  Offline: 3  │  Last Sync: 15m ago          │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                       │
│  ┌─────────┬──────────────────┬──────────────┬──────────────────┐  │
│  │ STATUS  │ CONNECTOR        │ LOCATION     │ LAST CONTACT     │  │
│  ├─────────┼──────────────────┼──────────────┼──────────────────┤  │
│  │ 🟢 OK   │ EXP-001          │ Building A   │ 15 min ago       │  │
│  │ 🟢 OK   │ EXP-002          │ Building B   │ 23 min ago       │  │
│  │ 🔴 DOWN │ EXP-003          │ Building C   │ 4 hours ago      │  │
│  └─────────┴──────────────────┴──────────────┴──────────────────┘  │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 7. CUSTOMER LIST & SELECTOR CARD

**Endpoint:** `Customer/GetCustomers`
**Priority:** TIER 2 - Secondary Dashboard Card
**Status:** Verified Working (Returns: Customer list)

### Endpoint Details
```json
{
  "action": "Customer/GetCustomers",
  "method": "POST",
  "requires_auth": true,
  "payload": {
    "DealerCode": "REQUIRED",
    "PageNumber": 1,
    "PageRows": 2147483647,
    "SortColumn": "Description",
    "SortOrder": 0
  }
}
```

### Card Design Recommendation

**Layout:** Customer Selector Dropdown with Quick Stats

```
┌─────────────────────────────────────────────────────────────────────┐
│  SELECT CUSTOMER                                                     │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ [Search Customers...]                                        ▼│ │
│  │                                                                 │ │
│  │ ☐ CAPE FEAR VALLEY MED CTR. (142 devices, 23 alerts)         │ │
│  │ ☐ MOORE COUNTY SCHOOLS (87 devices, 5 alerts)                │ │
│  │ ☐ REED LALLIER CHEVROLET (34 devices, 2 alerts)              │ │
│  │ ...                                                            │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                       │
│  76 Total Customers | 3,428 Total Devices                           │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 8. PRODUCT BRANDS & MODELS CARD

**Endpoints:** `Product/GetBrands` + `Product/GetModels`
**Priority:** TIER 2 - Secondary Dashboard Card
**Status:** Both Verified Working (50 items each)

### Card Design Recommendation

**Layout:** Fleet Breakdown by Manufacturer

```
┌─────────────────────────────────────────────────────────────────────┐
│  FLEET BY MANUFACTURER                        [▼ Collapse]          │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌────────────┬─────────┬──────────┬──────────┐                    │
│  │ HP         │   68    │ ████████ │   47.9%  │                    │
│  │ KONICA     │   43    │ █████░░░ │   30.3%  │                    │
│  │ SHARP      │   18    │ ██░░░░░░ │   12.7%  │                    │
│  │ LEXMARK    │   13    │ █░░░░░░░ │    9.1%  │                    │
│  └────────────┴─────────┴──────────┴──────────┘                    │
│                                                                       │
│  [View Model Breakdown] [Export Report]                             │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 9. DEALER SUPPLIES INVENTORY CARD

**Endpoint:** `DealerSupply/List`
**Priority:** TIER 2 - Secondary Dashboard Card
**Status:** Verified Working (Returns: 50 supplies per page)

### Card Design Recommendation

**Layout:** Supply Inventory Manager

```
┌─────────────────────────────────────────────────────────────────────┐
│  SUPPLY INVENTORY                       [Search] [Add Supply]       │
├──────────────────┬──────────────────┬──────────┬──────────┬────────┤
│ PART NUMBER      │ DESCRIPTION      │ TYPE     │ QTY      │ ACTION │
├──────────────────┼──────────────────┼──────────┼──────────┼────────┤
│ 841925RV         │ Black Toner      │ Toner    │ 12,500   │ [Edit] │
│ CE285A           │ HP 85A Black     │ Toner    │  8,200   │ [Edit] │
│ TN-321C          │ Cyan Toner       │ Toner    │  3,400   │ [Edit] │
├──────────────────┴──────────────────┴──────────┴──────────┴────────┤
│ Showing 1-50 of 423 supplies                    [1][2][3]...[Next] │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 10. EXPLORER CLUSTERS CARD

**Endpoint:** `Explorer/Cluster/List`
**Priority:** TIER 2 - Secondary Dashboard Card
**Status:** Verified Working (Returns: 50 clusters, Response: 4093ms)

### Card Design Recommendation

**Layout:** Cluster Overview Map

```
┌─────────────────────────────────────────────────────────────────────┐
│  EXPLORER CLUSTERS                            [▼ Collapse]          │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  50 Active Clusters | 34 Auto-Discovered                            │
│                                                                       │
│  ┌─────────┬───────────────────┬──────────┬──────────────────────┐ │
│  │ CLUSTER │ LOCATION          │ DEVICES  │ STATUS               │ │
│  ├─────────┼───────────────────┼──────────┼──────────────────────┤ │
│  │ CL-001  │ Main Office       │   24     │ 🟢 All Connected     │ │
│  │ CL-002  │ Warehouse         │   18     │ 🟢 All Connected     │ │
│  │ CL-003  │ Remote Site       │   12     │ 🟡 2 Offline         │ │
│  └─────────┴───────────────────┴──────────┴──────────────────────┘ │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

### TIER 3: DRILL-DOWN VIEWS (CLICK TO EXPAND)

---

## 11. DEVICE DETAIL VIEW

**Endpoint:** `Device/GetDetailedInformations`
**Priority:** TIER 3 - Drill-Down View
**Status:** Available (Requires deviceId)

### Trigger
- Click on device row in Device List Card
- Click on device name in alerts

### Card Design Recommendation

**Layout:** Modal/Sidebar Detail Panel

```
┌─────────────────────────────────────────────────────────────────────┐
│  DEVICE DETAILS                                             [✕ Close]│
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  HP LASERJET PRO M404DN                                              │
│  Serial: JPBDM26300 | Asset: FQ966 | IP: 10.5.1.31                 │
│                                                                       │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ STATUS: 🟢 Online | Last Update: 2 hours ago                  │  │
│  │ Counter: 92,467 pages | Monthly Avg: 1,736 pages             │  │
│  │ Location: Building A, Floor 2, Room 201                       │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                       │
│  SUPPLY LEVELS:                                                      │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ Black Toner:    ████████████████████░░  87%  (Est: 45 days)  │  │
│  │ Maintenance:    ████████████░░░░░░░░░░  54%  (Est: 120 days) │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                       │
│  [View History] [Schedule Service] [Export Report]                  │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 12. DEVICE SUPPLY DETAILS

**Endpoint:** `Device/GetSuppliesDetailsSummary`
**Priority:** TIER 3 - Drill-Down View
**Status:** Available (Requires deviceId)

### Trigger
- Click on toner bar in Device List
- Click "Supply Levels" in Device Detail View

---

## 13. DEVICE COUNTER HISTORY

**Endpoint:** `Counter/Device/List`
**Priority:** TIER 3 - Drill-Down View
**Status:** Requires deviceId

### Trigger
- Click on counter value in Device List
- Click "View History" in Device Detail View

---

### TIER 4: REPORTS & ADMIN SETTINGS

---

## 14. ALERT CONFIGURATION

**Endpoints:**
- `AlertLimit/Dealer/Get` (Verified Working)
- `AlertLimit2/Dealer/GetDefault` (Verified Working, Returns: 9 items)

**Priority:** TIER 4 - Admin Setting
**Status:** Verified Working

### Card Design Recommendation

**Layout:** Settings Panel

```
┌─────────────────────────────────────────────────────────────────────┐
│  ALERT THRESHOLDS                                                    │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  Configure automatic alert triggers for supplies:                    │
│                                                                       │
│  Toner Low Alert:        [15]% remaining                             │
│  Toner Critical Alert:   [5]% remaining                              │
│  Photo Conductor Alert:  [10]% remaining                             │
│  Maintenance Kit Alert:  [20]% remaining                             │
│                                                                       │
│  ☑ Send email notifications                                         │
│  ☑ Create work orders automatically                                 │
│  ☐ Auto-ship supplies when critical                                 │
│                                                                       │
│  [Save Settings] [Reset to Defaults]                                │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 15. DEALER HIERARCHY & SETTINGS

**Endpoints:**
- `Dealer/GetDealerHierarchy` (Verified Working, Response: 2837ms)
- `Dealer/AccountingSettings/Get` (Verified Working)
- `Dealer/AlertSettings/Get` (Verified Working)

**Priority:** TIER 4 - Admin Setting
**Status:** All Verified Working

### Card Design Recommendation

**Layout:** Admin Configuration Dashboard

```
┌─────────────────────────────────────────────────────────────────────┐
│  DEALER SETTINGS                                         [Admin Only]│
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  SYSTEL BUSINESS EQUIPMENT - FAYETTEVILLE                            │
│  Code: NY06AGDWUQ                                                    │
│                                                                       │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ [Accounting Settings] [Alert Settings] [Advanced Options]      │ │
│  │                                                                 │ │
│  │ Counter Blend Settings | eXplorer Settings | Customizations    │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                       │
│  HIERARCHY:                                                          │
│  ├─ Main Dealer                                                      │
│  │  ├─ Region North (24 customers)                                  │
│  │  └─ Region South (52 customers)                                  │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## ADDITIONAL USEFUL ENDPOINTS (BONUS)

### Supporting Endpoints for Enhanced Features

#### A. Customer Detailed Information
**Endpoint:** `Customer/GetCustomerByCode`
**Use Case:** Customer profile page, contract details
**Returns:** Full customer data including contact info, contract dates, volumes

#### B. Standard Product Catalog
**Endpoint:** `StandardProduct/ListStandardProducts`
**Use Case:** Product database, device compatibility checks
**Returns:** 50+ standard product definitions

#### C. Dealer Supply Sets
**Endpoint:** `DealerSupplySet/List`
**Use Case:** Supply management, pricing, associations
**Returns:** Supply set configurations

#### D. Notification Templates
**Endpoint:** `DealerNotification/List`
**Use Case:** Email/SMS alert customization
**Returns:** 4 notification templates

#### E. Role Management
**Endpoint:** `Role/List`
**Use Case:** User access control
**Returns:** 12 user roles with capabilities

#### F. Account Profile
**Endpoint:** `Account/GetProfile`
**Use Case:** User profile, settings, preferences
**Returns:** Current user account data

#### G. Integration Status
**Endpoint:** `Integrations/GetJoinedCustomers`
**Use Case:** Third-party integration monitoring
**Returns:** 2 integration records

#### H. Explorer Configurations
**Endpoint:** `Explorer/Configuration/List`
**Use Case:** Explorer setup management
**Returns:** 1 configuration record

#### I. Custom Fields
**Endpoint:** `CustomField/List`
**Use Case:** Dynamic field management
**Returns:** Custom field definitions

#### J. Deleted Devices
**Endpoint:** `Device/Deleted/ListByDealer`
**Use Case:** Device archive, restore capability
**Returns:** 50 deleted devices per page

---

## IMPLEMENTATION PRIORITY ROADMAP

### Phase 1: Core Dashboard (Week 1-2)
1. Customer Dashboard Overview Card
2. Device List & Status Card
3. Active Alerts & Warnings Card
4. Basic customer selector

### Phase 2: Enhanced Features (Week 3-4)
5. Fleet Statistics & Metrics Card
6. HP SDS Service Requests Card
7. Device Detail Drill-Down View
8. Supply Details Drill-Down View

### Phase 3: Advanced Features (Week 5-6)
9. Explorer Connectors Status Card
10. Product Brands & Models Card
11. Dealer Supplies Inventory Card
12. Counter History Drill-Down

### Phase 4: Admin & Reports (Week 7-8)
13. Explorer Clusters Card
14. Alert Configuration Settings
15. Dealer Hierarchy & Settings
16. Export/Report functionality for all cards

---

## TECHNICAL IMPLEMENTATION NOTES

### API Call Optimization

#### Caching Strategy
```php
// Cache expensive calls
$cacheKey = "customer_dashboard_{$customerCode}";
$cacheDuration = 300; // 5 minutes

if ($cached = getCache($cacheKey)) {
    return $cached;
}

$data = callMpsApi('CustomerDashboard', $payload);
setCache($cacheKey, $data, $cacheDuration);
```

#### Batch Loading
```php
// Load multiple related endpoints in parallel
$promises = [
    'devices' => asyncCallMpsApi('Device/List', $devicePayload),
    'alerts' => asyncCallMpsApi('SupplyAlert/List', $alertPayload),
    'stats' => asyncCallMpsApi('CustomerDashboard', $statsPayload)
];

$results = Promise::all($promises)->wait();
```

### Error Handling

```php
// Graceful degradation for failed endpoints
try {
    $data = callMpsApi($endpoint, $payload);
} catch (ApiException $e) {
    // Log error
    error_log("API Error: {$endpoint} - {$e->getMessage()}");

    // Return cached data or placeholder
    return getCachedDataOrDefault($endpoint);
}
```

### Real-Time Updates

```php
// WebSocket or polling for critical alerts
if ($alert['urgency'] === 'HIGH' && !$alert['notified']) {
    // Push notification to dashboard
    pushNotification('new_critical_alert', $alert);

    // Mark as notified
    updateAlertStatus($alert['Id'], ['notified' => true]);
}
```

---

## CARD REFRESH INTERVALS

| Card Name | Default Interval | User Configurable | Critical Alert Override |
|-----------|------------------|-------------------|------------------------|
| Customer Dashboard | 5 minutes | Yes (1-60 min) | Immediate |
| Device List | 10 minutes | Yes (5-60 min) | Immediate |
| Active Alerts | 2 minutes | Yes (1-30 min) | Immediate |
| Fleet Statistics | 15 minutes | Yes (5-120 min) | No |
| HP SDS Requests | 30 minutes | Yes (10-120 min) | On new request |
| Explorer Connectors | 10 minutes | Yes (5-60 min) | On disconnect |
| Customer List | On page load | Manual refresh | No |
| Product Breakdown | On page load | Manual refresh | No |
| Supply Inventory | 60 minutes | Yes (30-240 min) | No |
| Explorer Clusters | 30 minutes | Yes (10-120 min) | No |

---

## USER EXPERIENCE CONSIDERATIONS

### Responsive Design
- All cards must be mobile-responsive
- Touch-friendly buttons (minimum 44px tap targets)
- Collapsible cards on mobile devices
- Horizontal scroll for large tables

### Loading States
```html
<div class="card-loading">
    <div class="spinner"></div>
    <p>Loading device data...</p>
</div>
```

### Empty States
```html
<div class="card-empty">
    <div class="empty-icon">📭</div>
    <h3>No Alerts Found</h3>
    <p>Your fleet is running smoothly!</p>
</div>
```

### Error States
```html
<div class="card-error">
    <div class="error-icon">⚠️</div>
    <h3>Unable to Load Data</h3>
    <p>API connection failed. <a href="#" onclick="retryLoad()">Retry</a></p>
</div>
```

---

## EXPORT & REPORT FORMATS

### Supported Export Types

1. **PDF Reports**
   - Executive summaries
   - Device lists with photos
   - Alert reports with details

2. **CSV/Excel Exports**
   - Raw data for analysis
   - Device inventories
   - Alert histories

3. **Scheduled Reports**
   - Daily alert digest
   - Weekly fleet summary
   - Monthly volume reports

### Report Templates

```php
$reportTemplates = [
    'fleet_summary' => [
        'name' => 'Fleet Summary Report',
        'endpoints' => [
            'CustomerDashboard',
            'Device/List',
            'SupplyAlert/List'
        ],
        'format' => 'pdf',
        'schedule' => 'weekly'
    ],
    'alert_digest' => [
        'name' => 'Supply Alert Digest',
        'endpoints' => ['SupplyAlert/List'],
        'format' => 'pdf',
        'schedule' => 'daily'
    ]
];
```

---

## SECURITY & PERMISSIONS

### Role-Based Card Visibility

```php
$cardPermissions = [
    'customer_dashboard' => ['admin', 'manager', 'user'],
    'device_list' => ['admin', 'manager', 'user'],
    'active_alerts' => ['admin', 'manager', 'user'],
    'fleet_statistics' => ['admin', 'manager'],
    'sds_requests' => ['admin', 'manager', 'technician'],
    'explorer_connectors' => ['admin', 'technician'],
    'supply_inventory' => ['admin', 'manager'],
    'dealer_settings' => ['admin']
];
```

### Data Isolation

```php
// Ensure users only see their authorized customers
$authorizedCustomers = getUserAuthorizedCustomers($_SESSION['user_id']);

$payload['FilterCustomerCodes'] = array_intersect(
    $requestedCustomers,
    $authorizedCustomers
);
```

---

## PERFORMANCE BENCHMARKS

Based on API test results:

| Endpoint | Avg Response Time | Data Volume | Cache Strategy |
|----------|-------------------|-------------|----------------|
| CustomerDashboard | 3,181ms | Medium | 5 min |
| Device/List | 3,000ms | High (50 items) | 10 min |
| SupplyAlert/List | 3,000ms | High (50 items) | 2 min |
| CustomerDashboard/Pages | 2,749ms | Medium | 15 min |
| SdsAction/GetDeviceActions | 13,597ms | Medium (15 items) | 30 min |
| Explorer/GetExplorerDatas | 4,637ms | High (50 items) | 10 min |
| Explorer/Cluster/List | 4,093ms | High (50 items) | 30 min |
| Product/GetBrands | 2,057ms | High (50 items) | 24 hours |
| DealerSupply/List | 3,098ms | High (50 items) | 1 hour |

### Performance Optimization Tips

1. **Lazy Loading:** Load below-the-fold cards after initial page render
2. **Pagination:** Implement virtual scrolling for large lists
3. **Image Optimization:** Use lazy-loaded thumbnails for device images
4. **Debounced Search:** Wait 300ms after user stops typing before searching
5. **Progressive Enhancement:** Show cached data immediately, update with fresh data

---

## FUTURE ENHANCEMENTS

### Planned Features (Post-MVP)

1. **Predictive Analytics**
   - Machine learning for supply forecasting
   - Device failure prediction
   - Optimal reorder point suggestions

2. **Mobile App**
   - Native iOS/Android apps
   - Push notifications for critical alerts
   - Barcode scanning for device registration

3. **Advanced Dashboards**
   - Customizable dashboard layouts (drag-and-drop)
   - User-defined KPIs and metrics
   - Multi-customer comparison views

4. **Integration Expansion**
   - Third-party ticketing systems
   - ERP integrations (SAP, NetSuite)
   - Accounting software connections

5. **AI Assistant**
   - Natural language queries ("Show me all HP devices with low toner")
   - Automated recommendations
   - Smart alert grouping

---

## CONCLUSION

This dashboard design leverages the **TOP 15 MOST VALUABLE ENDPOINTS** from the MPS Monitor API to create a comprehensive, user-friendly fleet management interface. The tiered approach ensures critical information is always visible while providing deep drill-down capabilities for detailed analysis.

### Key Success Metrics

- **User Engagement:** Time spent on dashboard, click-through rates
- **Alert Response Time:** Reduction in time from alert to resolution
- **API Performance:** Monitor response times, implement caching
- **User Satisfaction:** Collect feedback, iterate on designs

### Next Steps

1. Review this document with stakeholders
2. Create high-fidelity mockups in Figma/Adobe XD
3. Begin Phase 1 implementation
4. User testing and feedback collection
5. Iterate and improve based on real-world usage

---

**Document Version:** 1.0
**Last Updated:** 2025-10-27
**Maintainer:** Development Team
**Status:** Ready for Implementation
