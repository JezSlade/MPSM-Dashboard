# Dealer Dashboard - Complete Summary

**Last Updated:** 2025-12-03 14:17 UTC (Eastern)
**Status:** ✅ FULLY OPERATIONAL (Chart.js visualizations deployed)
**URL:** [https://mpsm.resolutionsbydesign.us/cms/dealer.php](https://mpsm.resolutionsbydesign.us/cms/dealer.php)

## Overview

The Dealer Dashboard provides a **20,000ft view** of the entire dealership, aggregating metrics across ALL customers with active devices. This is the executive-level view for dealer decision-making.

## Key Features

### 1. Dealership Overview Banner
- Shows total customer count
- Displays total managed devices across entire dealership
- Makes it clear this is an aggregated view, not individual customer data

### 2. Dealer Scorecard (9 Metrics)
All metrics are **aggregated totals** across the entire dealership:

1. **Active Customers**: Count of customers with non-zero devices
2. **Managed Devices**: Total devices across all customers
3. **Ghost Devices**: Devices not contacted in 7+ days (dealership-wide)
4. **Active Alerts**: Total alerts requiring attention
5. **Connector Health**: Average connector health score
6. **Duplicate IPs**: Mission-critical IP conflict detection
7. **Fleet Age (5yr+)**: Percentage of devices aging out
8. **Panel Errors (24h)**: Recent panel error count
9. **Uninstalled Devices**: Devices removed from service

### 3. Visual Analytics Dashboard (NEW)
**4 Animated Chart.js Visualizations** showing at-a-glance insights:

1. **Fleet Age Distribution** (Doughnut Chart)
   - Under 1 Year, 1-3 Years, 3-5 Years, Over 5 Years, Unknown
   - Color-coded: Green (new) → Red (aging)
   - Shows count and percentage on hover

2. **Device Health Status** (Doughnut Chart)
   - Healthy, Ghost (7d), Offline segments
   - Real-time health visualization
   - Percentage-based distribution

3. **Data Quality Metrics** (Horizontal Bar Chart)
   - Duplicate IPs, Ghost Devices, Panel Errors, Uninstalled
   - Dynamic color coding based on severity
   - Instant issue identification

4. **Connector Health** (Bar Chart)
   - Active, Offline, Health Score
   - Color-coded by threshold (95%+, 85-95%, <85%)
   - Connector status at a glance

**Technical Details:**
- Chart.js 4.4.0 via CDN
- Smooth animations (1s, easeInOutQuart)
- Memory-safe (destroys instances on refresh)
- Responsive grid layout (2x2 desktop, stacked mobile)
- Hover effects and tooltips

### 4. Customer Portfolio Table
- Lists **up to 20 customers** with active devices (configurable to 50 via `?limit=`)
- Sortable by any column (health, devices, alerts, etc.)
- Searchable by customer name or code
- Filterable by health status (healthy, attention, critical)
- Drill-down to individual customer view

### 5. Visual Enhancements
- Color-coded health indicators
- Status badges (success, warning, danger)
- Animated chart transitions

## Data Sources

### Current Mode: Live API (Direct MPS API Queries)

**Dealer Summary API** (`get-dealer-summary.php`):
- **Customer/GetCustomers**: Fetches all 82 customers
- **CustomerDashboard/Get**: Processes ALL 82 customers (not sampled)
- **Device/List**: Paginates through all devices (50 pages max, 1000/page)
- **Warnings Field**: Extracts duplicate IPs and panel errors from pre-calculated metrics
- **ContactedDevices**: Calculates offline and ghost device counts

**Customer Portfolio API** (`get-customer-portfolio.php`):
- Fetches up to 20 customers by default (configurable to 50 via `?limit=`)
- Aggregates metrics per customer
- Filters to only customers with active devices

### Cache Architecture (In Progress)

**Staging Tables** (currently populating):
- `mpsm_cache_devices_staging`: 3,400 devices cached (100% complete)
- `mpsm_cache_device_drilldown_staging`: 310 drill-downs cached (9% complete)

**Production Tables** (awaiting cutover):
- `mpsm_cache_devices`: 0 devices (will be populated on cutover)
- `mpsm_cache_device_drilldown`: 0 drill-downs (will be populated on cutover)

**Cutover Process:**
- Atomic swap from staging to production when all drill-downs complete
- Prevents partial data from being served
- Dashboard automatically switches to cache mode when production tables populated

**Cache Refresh Status:**
- State: `fetching_drilldowns` (actively running)
- Progress: 310/3,400 drill-downs (~9%)
- ETA: ~5-6 hours at current pace
- Errors: Legacy OAuth timeouts (non-blocking, already resolved)

### Performance Implications

**Current (Live API)**:
- **Scorecard Load**: 90-120 seconds (processes all 82 customers)
- **Portfolio Load**: 10-15 seconds (limited to 20 customers)
- **Force Refresh**: May timeout if processing all customers

**After Cache Cutover** (estimated):
- **Scorecard Load**: <3 seconds (read from cache)
- **Portfolio Load**: <2 seconds (read from cache)
- **Force Refresh**: Triggers background cache refresh, serves cached data

## Recent Changes (2025-12-03)

### Commit: 39b13e9 (FEATURE - 14:40 UTC)
**"Add Chart.js visualizations to dealer dashboard"**

**Features Added:**
- 4 animated Chart.js charts for at-a-glance insights
- Fleet Age Distribution (doughnut chart)
- Device Health Status (doughnut chart)
- Data Quality Metrics (horizontal bar chart)
- Connector Health (bar chart)
- Responsive grid layout with hover effects
- Memory-safe chart instance management

**Backend Enhancement:**
- Changed `get-dealer-summary.php` to process ALL 82 customers (not sampled)
- Removed extrapolation logic - uses actual totals
- Fixed undefined variable error
- Accurate dealer-wide metrics across entire customer base

**Files Modified:**
- [cms/dealer.php](cms/dealer.php) - Added Visual Analytics section
- [cms/assets/dealer.css](cms/assets/dealer.css) - Added chart styling
- [cms/assets/dealer.js](cms/assets/dealer.js#L237-L521) - Added renderCharts() function (291 lines)
- [cms/api/get-dealer-summary.php](cms/api/get-dealer-summary.php) - Process all customers

### Commit: b25abcf (FIX - 12:15 UTC)
**"Extract duplicate IPs and panel errors from Warnings field"**

**Issue**: Dealer metrics showing 0 for duplicate IPs when customer dashboards showed non-zero
**Root Cause**: Different data sources between customer and dealer dashboards
**Fix**:
- Extract from `CustomerDashboard/Get` Warnings field (same as customer dashboard)
- Count where `Key='IP_Duplicated'` for duplicate IPs
- Count where `Key='PanelErrorCount'` for panel errors
**Result**: Metrics now match customer dashboard calculation method

### Commit: f0364ba (FIX - 11:30 UTC)
**"Add pagination loop for Device/List to fetch all devices"**

**Issue**: Only 100 devices returned from Device/List API
**Root Cause**: Single API call doesn't fetch all pages
**Fix**: Added do-while pagination loop (max 50 pages, 1000/page)
**Result**: All devices now fetched for accurate fleet age and duplicate IP detection

### Commit: 26f7db7 (CRITICAL FIX - 12:20 UTC)
**"Remove orphaned data-quality-container references"**

**Issue**: Site unresponsive after overnight updates
**Root Cause**: HTML/JavaScript mismatch - removed DOM element still referenced
**Fix**: Removed all orphaned references (91 lines)
**Result**: Site responsiveness restored

### Commit: 9974928a (HOTFIX - 05:30 UTC)
**"Add 20-customer limit to portfolio to prevent timeout"**

**Issue**: Portfolio API timing out processing all 82 customers
**Fix**: Added configurable limit (default 20, max 50 via `?limit=`)
**Result**: Site loads in <15 seconds

## API Endpoints

### Dealer Summary
```
GET /cms/api/get-dealer-summary.php?secret=DEALER_API_2025&force=1
```
Returns aggregated metrics across entire dealership.
- Processes ALL 82 customers
- Paginates through all devices
- Extracts metrics from Warnings field
- Cache parameter: `&force=1` bypasses cache

### Customer Portfolio
```
GET /cms/api/get-customer-portfolio.php?secret=DEALER_API_2025&limit=20&force=1
```
Returns customers with active devices (totalDevices > 0).
- Default limit: 20 customers
- Max limit: 50 (`&limit=50`)
- Filters to active devices only
- Cache parameter: `&force=1` bypasses cache

### Cache Progress
```
GET /cms/api/check-cache-progress.php
```
Returns current cache refresh status (state file + production table counts).

### Cache Refresh Status
```
GET /cms/api/refresh-cache-chunked.php?action=status
```
Returns detailed chunked refresh state (staging table progress, errors, queue).

## Files

### Frontend
- [cms/dealer.php](../cms/dealer.php) - Main dashboard page
- [cms/assets/dealer.js](../cms/assets/dealer.js) - Dashboard logic and rendering
- [cms/assets/dealer.css](../cms/assets/dealer.css) - Dashboard styling

### Backend APIs
- [cms/api/get-dealer-summary.php](../cms/api/get-dealer-summary.php) - Aggregate metrics
- [cms/api/get-dealer-summary-hybrid.php](../cms/api/get-dealer-summary-hybrid.php) - Cache-first hybrid
- [cms/api/get-customer-portfolio.php](../cms/api/get-customer-portfolio.php) - Customer list with metrics

## User Flow

1. User navigates to [dealer.php](https://mpsm.resolutionsbydesign.us/cms/dealer.php)
2. Dashboard loads scorecard showing dealership-wide aggregate metrics
3. Portfolio table loads showing all customers with active devices
4. User can:
   - Search/filter customers
   - Sort by any metric
   - Click "View" to drill down to individual customer dashboard
   - Force refresh to get latest data

## Future Enhancements

### Planned
- **Pie Charts**: Device status distribution (online/offline/ghost)
- **Trend Charts**: Historical metrics over time
- **Fleet Age Visualization**: Bar chart showing age distribution
- **Alert Breakdown**: Chart showing alert types
- **Performance Optimization**: Implement async loading for portfolio

### Optional
- **Export to CSV/PDF**: Download portfolio data
- **Email Reports**: Scheduled executive summaries
- **Custom Dashboards**: User-defined metric arrangements
- **Mobile Optimization**: Responsive design improvements

## Troubleshooting

### Slow Dashboard Load (90-120 seconds)
- **Cause**: Processing all 82 customers from live MPS API
- **Status**: Normal during cache population
- **Solution**: Wait for cache cutover (ETA: ~5-6 hours from 14:17 UTC)
- **Verification**: Check cache progress with `check-cache-progress.php`

### Charts Not Rendering
- **Possible Causes**:
  1. Browser cache serving old JavaScript
  2. Chart.js CDN not loaded
  3. JavaScript errors in console
- **Solutions**:
  1. Hard refresh: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)
  2. Check browser console (F12) for errors
  3. Verify Chart.js loads: Look for `Chart is not defined` error

### Portfolio Shows Limited Customers
- **Expected**: Default 20 customers (configurable)
- **Reason**: Performance optimization for live API mode
- **Workaround**: Add `?limit=50` to URL for more customers
- **Permanent Fix**: Cache cutover will enable full customer list

## Success Criteria

✅ **Complete**: All requirements met
- ✅ Dealer dashboard displays aggregated dealership-wide metrics
- ✅ Customer portfolio shows customers with active devices (limit: 20-50)
- ✅ Metrics are accurate (zeros reflect real data, not bugs)
- ✅ User can drill down to individual customers
- ✅ Data refreshes on demand
- ✅ Visual indicators clearly show health status
- ✅ **NEW**: Animated Chart.js visualizations for at-a-glance insights
- ✅ **NEW**: 4 charts show fleet age, device health, data quality, connector status
- 🔄 **In Progress**: Cache population for faster load times

---

**Dashboard is production-ready.** All features functional with live MPS API data. Cache cutover will improve performance.
