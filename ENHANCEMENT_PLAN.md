# CMS Enhancement Plan

## Completed ✅
1. Persistent localStorage caching (5-minute TTL)
2. Sortable printer table with 7 columns
3. Customer field structure fixes (CustomerCode vs Customer.Code)
4. Fixed display logic (removed broken filter)

## In Progress 🔄

### 1. Pagination for Printer Table
- Add "Show 25/50/100/All" selector
- Add Previous/Next buttons
- Add page number display "Showing 1-50 of 766"
- Maintain sort state across pagination

### 2. Enhanced Customer Dashboard Header
**Current**: Shows basic SDS metrics
**New**: Comprehensive snapshot including:
- Total Devices (with online/offline breakdown)
- Total Pages (Mono + Color with monthly volumes)
- Alert Summary (Critical/Warning/Info counts)
- Supply Status (Low toner count, anomalies)
- Top 5 highest usage devices
- Last update timestamp

### 3. Fix & Enhance All Cards

#### Errors & Alerts Card
**Current**: Empty/placeholder
**New**:
- Snapshot: Total count, breakdown by severity
- Expand: Sortable table (Device, Alert Type, Severity, Date)
- Drill-down: Click alert → device modal

#### Toner Levels Card
**Current**: Placeholder with top 5
**New**:
- Snapshot: Devices with low toner (<20%), anomalies count
- Expand: Sortable table (Device, Black%, Cyan%, Magenta%, Yellow%, Status)
- Color-coded levels (Red <20%, Yellow <40%, Green >=40%)
- Drill-down: Click device → full device modal

#### Meter Reads Card
**Current**: Empty state
**New**:
- Snapshot: Total pages this month (Mono/Color), top 3 devices
- Expand: Sortable table (Device, Mono Counter, Color Counter, Monthly Mono, Monthly Color)
- Drill-down: Click device → counter history modal

#### Recent Activity Card
**Current**: Shows SDS actions
**New**:
- Snapshot: Last 5 activities
- Expand: Full activity log (sortable by date/type/device)
- Activity types: Device added, Counter updated, Alert triggered, Supply ordered
- Drill-down: Click activity → related device/alert details

### 4. Card Collapse/Expand Pattern
All cards implement:
```
[Card Header with Icon] [Count Badge] [▼ Expand]
├─ Snapshot (always visible)
│  └─ Key metrics, totals, top items
└─ Expanded View (toggle)
   ├─ Sortable table with all data
   ├─ Search/filter controls
   └─ Drill-down links
```

### 5. Admin Panel Enhancements
- Verify customer dropdown uses persistent cache
- Add "Refresh Data" button per dropdown
- Show cache status/age
- Add "Clear All Cache" button

### 6. Battle Testing Checklist
- [ ] Fresh page load (no cache)
- [ ] Cached page load (instant)
- [ ] Sort all table columns
- [ ] Pagination controls
- [ ] Card expand/collapse
- [ ] Device drill-down modals
- [ ] Customer switching
- [ ] Theme toggle
- [ ] Admin panel dropdowns
- [ ] Cache clearing
- [ ] Auto-refresh toggle
- [ ] All cards with real data
- [ ] Error handling (network failure)
- [ ] Large datasets (766 devices)
- [ ] Mobile responsiveness

## API Endpoints Needed

### Already Using ✅
- `Device/List` - Main device data
- `CustomerDashboard` - SDS metrics
- `CustomerDashboard/Pages` - Page volumes

### Need to Integrate 🔄
- `SdsAction/GetDeviceActions` - Recent activities
- `AlertLimit2/Customer/GetDefault` - Alert limits
- `Device/GetSuppliesDetails` - Individual device toner
- `Counter/Device/List` - Device counter history

## File Changes Required

### cms/assets/js/app.js
- Add pagination state and controls
- Enhance loadCustomerDashboard()
- Rewrite loadErrorsAlerts()
- Rewrite loadTonerLevels()
- Rewrite loadMeterReads()
- Enhance loadRecentActivity()
- Add card collapse/expand handlers
- Add search/filter functions

### cms/assets/css/styles.css
- Pagination controls styling
- Card expand/collapse animations
- Enhanced customer dashboard layout
- Color-coded toner levels
- Activity timeline styles
- Mobile responsive breakpoints

### cms/index.php
- Add pagination controls HTML
- Add expand/collapse buttons to cards
- Enhance customer dashboard structure

## Performance Targets
- Initial load: < 15s (fetching 766 devices)
- Cached load: < 1s (from localStorage)
- Card expand: < 100ms (DOM manipulation)
- Table sort: < 200ms (766 rows)
- Pagination: < 50ms (slice array)
- Device modal: < 500ms (fetch additional data)

## Next Steps
1. Add pagination to printer table (30 min)
2. Enhance customer dashboard header (45 min)
3. Fix & enhance all 4 cards (2 hours)
4. Add collapse/expand to all cards (30 min)
5. Verify admin dropdowns (15 min)
6. Battle test all functionality (1 hour)

**Total Estimated Time**: ~5 hours of focused development
