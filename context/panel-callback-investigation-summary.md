# Panel Callback Investigation Summary

**Date:** 2025-11-14
**Status:** Investigation tools deployed, awaiting user review of live data

---

## Investigation Request

User requested investigation of panel message callbacks that are received in "error" state:
1. Examine callback payloads with errors
2. Delete first 10 test payloads
3. Identify how to reduce errors (data cleaning or acceptance changes)
4. Clean existing error payloads
5. Verify Command Center filtering and alert system wiring

---

## System Architecture

### Callback Endpoint
**URL:** `https://mpsm.resolutionsbydesign.us/mps-api/callbacks/panel-message.php`
**Authentication:** Shared secret `mpsm-panel-message-v1` in JSON payload

### Database Tables
1. **`mpsm_panel_messages`** - Successfully stored panel messages (2824 total)
2. **`mpsm_panel_callback_debug`** - ALL callback attempts including errors
3. **`mpsm_notification_rules`** - Command Center alert rules (12 active, 3 inactive)
4. **`mpsm_dashboard_notifications`** - Active notifications in hero header
5. **`mpsm_alert_aggregations`** - Frequency tracking for threshold alerts
6. **`mpsm_rule_match_history`** - Audit trail of rule triggers

### Error Detection Flow
1. HTTP Method validation (must be POST)
2. Content-Type validation (must be application/json)
3. Payload sanitization via `payload-sanitizer.php`
4. JSON parsing with UTF-8 substitution
5. Structure validation (must be object/array)
6. Secret validation
7. Database storage

---

## Error Types Detected by System

| Error Type | HTTP Code | Description |
|------------|-----------|-------------|
| Method Not Allowed | 405 | Non-POST requests |
| Invalid Content-Type | 415 | Missing/wrong Content-Type header |
| Empty Payload | 400 | No request body |
| JSON Parsing Error | 400 | Malformed JSON, encoding issues |
| Structure Validation | 400 | Valid JSON but not object/array |
| Unauthorized | 401 | Invalid callback secret |
| Database Error | 500 | DB connection/constraint failures |

---

## Investigation Tools Deployed

### 1. Panel Error Report (NEW)
**URL:** https://mpsm.resolutionsbydesign.us/cms/panel-error-report.php
**API:** https://mpsm.resolutionsbydesign.us/cms/api/panel-error-report.php

**Features:**
- Total entries vs error count
- Error rate percentage
- Error type breakdown
- Sample error payloads (first 5)
- Test data detection
- Source analysis
- Automated cleanup SQL generation
- Recommendations

**Files Created:**
- `cms/panel-error-report.php` (UI)
- `cms/api/panel-error-report.php` (API backend)

### 2. Payload Debugger (Existing, Enhanced Documentation)
**URL:** https://mpsm.resolutionsbydesign.us/cms/payload-debugger.php

**Features:**
- Real-time auto-refresh (5 seconds)
- Filter by status (SUCCESS, ERROR, PROCESSING)
- View full payloads and headers
- Source tracking
- Request/response inspection

### 3. SQL Query Collection (NEW)
**File:** `panel_error_queries.sql`

**Contains 20+ queries for:**
- Error statistics and summaries
- Error type breakdown
- Sample error payloads
- Test data detection
- Cleanup operations
- Validation queries
- Time-based analysis

### 4. Documentation (NEW)
**Files Created:**
- `PANEL_ERROR_INVESTIGATION_REPORT.md` - Complete technical documentation
- `HOW_TO_ACCESS_ERROR_DATA.md` - Quick access guide

---

## Expected Error Patterns to Investigate

### Test Data Indicators
- `raw_body LIKE '%TEST%'`
- `raw_body LIKE '%SUCCESS_SERIAL%'`
- `raw_body LIKE '%ACME_CORP%'`
- `message LIKE '%test%'`
- User agent: `PostmanRuntime`

### Production Error Patterns
- JSON encoding issues (UTF-8 problems)
- Malformed JSON structure
- Missing required fields
- Invalid secret
- Database constraint violations

---

## Recommended Investigation Steps

1. **Login to CMS:** https://mpsm.resolutionsbydesign.us/cms/

2. **Access Panel Error Report:**
   - View comprehensive error analysis
   - Identify most common error types
   - Review sample error payloads
   - Check for test data

3. **Access Payload Debugger:**
   - Filter by ERROR status
   - Examine individual error entries
   - Check headers and request details
   - Identify patterns

4. **Review Error Statistics:**
   - Total entries vs errors
   - Error rate percentage
   - Time distribution
   - Source analysis

5. **Identify Issues:**
   - Are errors from test data or production?
   - What's the most common error type?
   - Do production payloads have fixable patterns?

6. **Generate Cleanup SQL:**
   - Use Panel Error Report to auto-generate SQL
   - Or use queries from `panel_error_queries.sql`
   - Delete test data entries

7. **Fix Payload Acceptance (if needed):**
   - Enhance payload sanitization
   - Relax validation rules
   - Add field normalization
   - Improve error messages

---

## Current Statistics (from cache-status-report.php)

**Panel Messages:**
- Total Callbacks: 2824
- Devices with Messages: 822
- Unique Alert Codes: 24
- First message: 2025-11-04 16:38:54
- Latest message: 2025-11-14 12:45:36

**Top Alert Codes:**
1. 808: 1602 occurrences
2. 807: 889 occurrences
3. 1: 105 occurrences
4. 8: 73 occurrences
5. 13: 40 occurrences

---

## Command Center Wiring Status

### Notification Engine
**File:** `mps-api/callbacks/command-center-engine.php`
**Triggered:** Automatically after successful panel message storage

### Processing Flow
1. Panel message received and stored in `mpsm_panel_messages`
2. `processNotificationRules()` called with message data
3. Checks all active rules in `mpsm_notification_rules`
4. Pattern matching on:
   - Alert code pattern (supports wildcards %)
   - Device serial pattern
   - Customer code pattern
5. Frequency threshold matching:
   - Counts occurrences in time window
   - Scope: same_device, same_alert, same_customer, any
6. If rule matches:
   - Creates `mpsm_dashboard_notifications` entry
   - Updates `mpsm_alert_aggregations`
   - Records in `mpsm_rule_match_history`
   - Updates rule trigger count
7. Auto-expires notifications based on `auto_dismiss_hours`

### Notification Rules
- **Active:** 12 rules
- **Inactive:** 3 rules
- **Dashboard Notifications:** 1 active, 0 acknowledged, 0 dismissed

### Template Variables Available
- `{severity}` - Rule severity level
- `{device}` - Device serial number
- `{alert}` - Alert code
- `{customer}` - Customer code
- `{count}` - Frequency count
- `{window}` - Time window text
- `{rule_name}` - Rule name

---

## Next Actions

### Immediate (User)
1. Open Panel Error Report (already opened in browser)
2. Review error statistics and samples
3. Identify test data vs production errors
4. Share findings:
   - Total error count
   - Error rate percentage
   - Most common error type
   - Sample error payloads
   - Test data count

### After Review (Claude)
1. Generate cleanup SQL for test data
2. Analyze production error patterns
3. Recommend payload acceptance improvements
4. Fix data cleaning if needed
5. Verify Command Center filtering rules
6. Test notification system

---

## Files and URLs Reference

### Investigation Tools
- Panel Error Report UI: https://mpsm.resolutionsbydesign.us/cms/panel-error-report.php
- Panel Error Report API: https://mpsm.resolutionsbydesign.us/cms/api/panel-error-report.php
- Payload Debugger: https://mpsm.resolutionsbydesign.us/cms/payload-debugger.php
- Panel Message Monitor: https://mpsm.resolutionsbydesign.us/cms/panel-message-monitor.php

### Documentation
- `PANEL_ERROR_INVESTIGATION_REPORT.md` - Technical details
- `HOW_TO_ACCESS_ERROR_DATA.md` - Quick access guide
- `panel_error_queries.sql` - SQL query collection
- `PANEL_MESSAGES.md` - System overview
- `PAYLOAD_DEBUGGER_GUIDE.md` - Debugger usage

### Core Files
- Callback endpoint: `mps-api/callbacks/panel-message.php`
- Debug endpoint: `mps-api/callbacks/panel-message-debug.php`
- Payload sanitizer: `mps-api/callbacks/payload-sanitizer.php`
- Command Center engine: `mps-api/callbacks/command-center-engine.php`
- Panel message common: `mps-api/callbacks/panel-message-common.php`

---

## Parallel Task: Cache Refresh

**Started:** 07:47:18 EST (2025-11-14)
**Expected Completion:** 08:17 EST (approximately 30 minutes)
**Status:** Running in background (process ID: 6642fa)

**All Patches Applied:**
1. Function signature mismatch fixed
2. Batch size reduced to 1000 devices
3. Transaction wrapper removed
4. Comprehensive error logging added
5. CRON job conflict resolved

**Success Criteria:**
- >5000 devices cached
- >300 devices with drill-down data
- No errors in logs

---

**Status:** Awaiting user review of Panel Error Report and Payload Debugger
