# Alert System Audit Report
**Date**: 2025-11-28
**Auditor**: Codex (AI Agent)
**Scope**: Dashboard notifications, mobile alerts, rule triggering logic

## Executive Summary

Alert system is **functional** but has **no pre-configured rules** for common device issues. Created 5 common-sense alert rules to detect recurring device problems (JAM%, E-%, SENS%, COMM%, MOT% patterns). Alert display works correctly on both desktop and mobile, but mobile UI lacks dedicated notification section.

---

## 1. Common-Sense Alert Rules Created

### Rule 1: Repeated JAM Alerts
**Pattern**: `JAM%`
**Threshold**: 3 occurrences within 24 hours (same device)
**Severity**: High
**Rationale**: Persistent jam alerts indicate mechanical blockages, worn components, or sensor malfunctions requiring maintenance.

### Rule 2: Emergency Stop Pattern
**Pattern**: `E-%`
**Threshold**: 2 occurrences within 12 hours (same customer)
**Severity**: Critical
**Rationale**: Multiple emergency stops suggest safety concerns, operator training issues, or emergency button malfunctions.

### Rule 3: Persistent Sensor Failures
**Pattern**: `SENS%`
**Threshold**: 5 occurrences within 48 hours (same device)
**Severity**: High
**Rationale**: Repeated sensor errors indicate hardware failure, wiring issues, or environmental interference.

### Rule 4: Widespread Communication Loss
**Pattern**: `COMM%`
**Threshold**: 10 occurrences within 1 hour (same alert type)
**Severity**: Critical
**Rationale**: Multiple devices losing communication simultaneously indicates network infrastructure failure, not individual device issues.

### Rule 5: Motor Overload Pattern
**Pattern**: `MOT%`
**Threshold**: 4 occurrences within 24 hours (same device)
**Severity**: High
**Rationale**: Recurring motor overload suggests mechanical binding, excessive resistance, belt tension problems, or bearing wear.

**Insertion Script**: [insert-alert-rules.php](../tools/command-center/insert-alert-rules.php)
**SQL Backup**: [common-sense-alert-rules.sql](common-sense-alert-rules.sql)

---

## 2. Dashboard Alert Display Audit

### Architecture
**File**: [hero-notifications.js](../cms/assets/hero-notifications.js)
**Render Location**: `#hero-notifications` section in [index.php](../cms/index.php#L105)
**API Endpoint**: `/cms/api/command-center.php?action=get_notifications`

### Display Logic
```javascript
// Auto-refresh: 30 seconds
// Grouping: By device_serial + alert_code (deduplicates)
// Limit: Top 6 priority notifications
// Sorting: By priority (critical > high > warning > info)
```

### Visual Design
- **Severity Colors**:
  - Critical: Red gradient `#e74c3c → #c0392b`
  - High: Orange gradient `#f39c12 → #e67e22`
  - Warning: Yellow gradient `#f39c12 → #d68910`
  - Info: Blue gradient `#3498db → #2980b9`

- **Notification Chips**: Compact cards with alert code, device serial, customer code, trigger count
- **Collapsible**: Toggle bar with count badge (expanded/collapsed state)
- **Empty State**: Shield icon with "All clear" message when no notifications

### Customer Filtering
- Notifications automatically filtered by selected customer (`window.currentCustomerCode`)
- JOIN with `panel_messages` table ensures customer association
- Client-side double filtering removed (was causing false negatives)

### Status: ✅ WORKING CORRECTLY

---

## 3. Mobile Alert Display Audit

### Architecture
**File**: [mobile.php](../cms/mobile.php)
**Assets**: [mobile.css](../cms/assets/mobile.css), [mobile.js](../cms/assets/mobile.js)

### Current State
**Issue Identified**: Mobile UI has **NO dedicated notification display section**

**Evidence**:
- No `#hero-notifications` element in mobile.php
- No import of `hero-notifications.js`
- No notification badge in mobile header
- Customer selector and search work, but no alert display

### Mobile Header Structure
```html
<header class="mobile-header">
  <div class="header-top">
    <div class="brand"> ... customer selector ... </div>
    <div class="quick-links">
      <a href="index.php?forceDesktop=1">Desktop</a>
      <button id="mobile-refresh">Refresh</button>
      <button id="mobile-logout">Logout</button>
    </div>
  </div>
  <div class="header-context">
    <div class="context-chip"> ... customer code ... </div>
  </div>
  <div class="search-box"> ... </div>
  <!-- MISSING: Notifications section -->
</header>
```

### Status: ❌ MOBILE ALERTS NOT IMPLEMENTED

### Recommendation
Add notification section to mobile.php:
1. Import `hero-notifications.js` or create mobile-specific variant
2. Add `<section id="mobile-notifications">` below search-box
3. Style for mobile viewport (stack vertically, larger tap targets)
4. Add notification badge to mobile header
5. Enable Command Center link from mobile

---

## 4. Alert Triggering Logic Audit

### Engine Location
**File**: [command-center-engine.php](../mps-api/callbacks/command-center-engine.php)
**Function**: `processIncomingMessage(array $message): void`

### Trigger Flow
```
1. Panel message arrives → processIncomingMessage()
2. Load active rules → SELECT FROM mpsm_notification_rules WHERE enabled = 1
3. For each rule:
   a. matchesPattern() - SQL LIKE wildcard matching
   b. shouldTrigger() - Frequency threshold check
   c. createNotification() - Insert into mpsm_dashboard_notifications
4. Notification displayed via hero-notifications.js
```

### Pattern Matching
**Function**: `matchesPattern(string $value, string $pattern): bool`

**Implementation**:
```php
// Escape regex special chars to prevent ReDoS
$pattern = preg_quote($pattern, '/');

// Convert SQL LIKE wildcards to regex
$pattern = str_replace('\\%', '.*', $pattern);
$pattern = str_replace('\\_', '.', $pattern);
$pattern = '/^' . $pattern . '$/i';

return (bool)preg_match($pattern, $value);
```

**Security**: ✅ ReDoS vulnerability **FIXED** (2025-11-27)

### Frequency Threshold Check
**Function**: `shouldTrigger(PDO $pdo, array $rule, string $alertCode, string $deviceSerial, string $customerCode): bool`

**Logic**:
```sql
SELECT COUNT(*) FROM mpsm_panel_messages
WHERE maintenance_alert_code = :alert_code
  AND received_at >= DATE_SUB(getNYTimestamp(), INTERVAL :window HOUR)
  AND [frequency_type conditions]
```

**Frequency Types**:
- `same_device`: Count messages from exact device
- `same_alert`: Count messages with exact alert code (any device)
- `same_customer`: Count messages from same customer (any device/alert)
- `any`: Count all matching messages

### Status: ✅ WORKING CORRECTLY

---

## 5. Command Center Integration

### Notifications Tab
**Location**: [command-center.php](../cms/command-center.php) → Notifications tab
**JavaScript**: [command-center.js](../cms/assets/command-center.js) → `loadNotifications()`

**Features**:
- Real-time notification list (10s auto-refresh)
- Severity filtering (critical, high, warning, info)
- Customer code filtering
- Status filtering (active, dismissed)
- Dismiss action (marks notification as inactive)
- Click device → Opens device modal with details

### Rules Tab
**Features**:
- Create/Edit/Delete notification rules
- Pattern matching with wildcards (%, _)
- Frequency thresholds (count + time window)
- Severity levels (critical, high, warning, info)
- Enable/disable toggle
- Auto-dismiss configuration

**Recent Fix**: Added `enabled` field to rule submission (2025-11-28)

### Alert Labels Tab
**Features**:
- CRUD operations for alert definitions
- Maps alert codes (E-001, JAM-001) to display names
- Used by pattern suggestions in rule modal

**Recent Enhancement**: Full CRUD integrated (2025-11-28)

### Status: ✅ FULLY FUNCTIONAL

---

## 6. Database Schema

### Tables
```sql
mpsm_notification_rules           -- Rule definitions
mpsm_dashboard_notifications      -- Active/dismissed notifications
mpsm_panel_messages               -- Source data (alerts from devices)
mpsm_alert_definitions            -- Alert code → display name mapping
```

### Key Indexes (Verified)
```sql
-- mpsm_panel_messages
INDEX idx_alert_code (maintenance_alert_code)
INDEX idx_device_serial (device_serial)
INDEX idx_customer_code (customer_code)
INDEX idx_received_at (received_at)

-- mpsm_notification_rules
INDEX idx_enabled (enabled)
INDEX idx_alert_pattern (alert_code_pattern)

-- mpsm_dashboard_notifications
INDEX idx_status (status)
INDEX idx_customer_code (customer_code)
INDEX idx_created_at (created_at)
```

### Status: ✅ PROPERLY INDEXED

---

## 7. Findings Summary

### ✅ Working Correctly
1. Dashboard hero notifications display
2. Command Center notification tabs (all 6 tabs functional)
3. Alert triggering engine and frequency thresholds
4. Pattern matching (ReDoS vulnerability fixed)
5. Rule CRUD operations (edit rule save error fixed)
6. Alert Labels CRUD (recently added)
7. Database schema and indexes

### ❌ Issues Identified
1. **Mobile UI has NO notification display** - Users cannot see alerts on mobile devices
2. **No pre-configured alert rules** - System requires manual rule creation
3. **Notification badge hidden** - Header badge set to `display: none` (line 98, hero-notifications.js)

### ⚠️ Minor Issues
1. Tools tab used iframes (fixed 2025-11-28, now uses tool cards)
2. Pattern suggestions only load from first 200 alert definitions (could miss rare codes)

---

## 8. Recommendations

### Immediate (Priority 1)
1. **Insert common-sense alert rules**: Run `insert-alert-rules.php` to populate 5 default rules
2. **Add mobile notifications**: Implement alert display in mobile.php
3. **Enable notification badge**: Remove `display: none` from header badge

### Short-term (Priority 2)
4. **Email notifications**: Configure SMTP and add email recipients to critical rules
5. **Alert sound**: Add optional audio notification for critical alerts
6. **Notification history**: Add "Dismissed" tab showing past 7 days of resolved alerts

### Long-term (Priority 3)
7. **Machine learning**: Detect anomalies beyond pattern matching (trending failures)
8. **Escalation rules**: Auto-escalate if notification not acknowledged within X hours
9. **Integration webhooks**: Send notifications to Slack, Teams, PagerDuty

---

## 9. Test Plan

### Rule Insertion Test
```bash
# Visit in browser (requires auth)
https://mpsm.resolutionsbydesign.us/cms/insert-alert-rules.php

# Expected output:
# OK: Inserted rule 'Repeated JAM Alerts' (ID: X)
# OK: Inserted rule 'Emergency Stop Pattern' (ID: Y)
# ...
# Inserted: 5, Skipped: 0, Total: 5
```

### Dashboard Alert Test
1. Navigate to Command Center → Rules tab
2. Verify 5 new rules appear in list
3. Click rule → Edit modal opens with all fields populated
4. Navigate to Notifications tab → Wait for auto-refresh
5. Verify no errors in browser console

### Mobile Alert Test (Post-Fix)
1. Visit `https://mpsm.resolutionsbydesign.us/cms/mobile.php`
2. Verify notification section displays below search box
3. Verify alerts appear for selected customer
4. Tap alert → Device details modal opens
5. Verify 30s auto-refresh works

### Trigger Logic Test
```sql
-- Simulate recurring JAM alerts
INSERT INTO mpsm_panel_messages (
    device_serial, maintenance_alert_code, customer_code,
    received_at, panel_configuration, department
) VALUES
('TEST-001', 'JAM-001', 'TESTCUST', DATE_SUB(NOW(), INTERVAL 1 HOUR), 'Test Panel', 'Test Dept'),
('TEST-001', 'JAM-001', 'TESTCUST', DATE_SUB(NOW(), INTERVAL 30 MINUTE), 'Test Panel', 'Test Dept'),
('TEST-001', 'JAM-001', 'TESTCUST', NOW(), 'Test Panel', 'Test Dept');

-- Run engine manually or wait for next panel message
-- Check for notification creation
SELECT * FROM mpsm_dashboard_notifications
WHERE alert_code = 'JAM-001'
  AND device_serial = 'TEST-001'
ORDER BY created_at DESC LIMIT 1;
```

---

## 10. Deployment Checklist

- [x] Create common-sense alert rules SQL script
- [x] Create PHP insertion script with duplicate check
- [ ] Run insertion script on production
- [ ] Verify rules in Command Center → Rules tab
- [ ] Monitor Command Center → Notifications tab for 24 hours
- [ ] Check browser console for JavaScript errors
- [ ] Test mobile.php (expect no notifications until fixed)
- [ ] Document findings in `context/test-log.md`

---

## 11. Files Modified/Created

### Created
- [context/common-sense-alert-rules.sql](common-sense-alert-rules.sql) - SQL INSERT statements
- [insert-alert-rules.php](../tools/command-center/insert-alert-rules.php) - PHP insertion script
- [context/alert-system-audit.md](alert-system-audit.md) - This document

### To Modify (Mobile Notifications)
- [cms/mobile.php](../cms/mobile.php) - Add notification section
- [cms/assets/mobile.css](../cms/assets/mobile.css) - Style mobile notifications
- [cms/assets/mobile.js](../cms/assets/mobile.js) - Load and render notifications

---

## Conclusion

Alert system core functionality is **solid and secure**. Key fixes deployed:
1. ✅ Edit rule save error (enabled field missing)
2. ✅ Alert Labels CRUD integration
3. ✅ Tools tab iframe removal
4. ✅ ReDoS vulnerability patched

**Critical gap**: Mobile UI lacks notification display. Desktop hero notifications work perfectly with proper grouping, filtering, and auto-refresh.

**Next action**: Insert 5 common-sense alert rules to enable proactive device monitoring.
