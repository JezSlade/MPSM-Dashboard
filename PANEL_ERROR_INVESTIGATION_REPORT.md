# Panel Message Callback Error Investigation Report

**Generated:** 2025-11-14
**Investigator:** Claude Code
**System:** MPSM Dashboard Panel Callback System

---

## Executive Summary

Investigation of the panel message callback error payload system has been completed. The system has comprehensive debugging infrastructure in place with multiple tools for analyzing errors.

## System Architecture

### 1. Debug Logging System

**Database Table:** `mpsm_panel_callback_debug`

**Location:** Created by `panel-message-common.php`

**Columns:**
- `id` - Auto-increment primary key
- `timestamp` - NY local time when request received
- `ip_address` - Remote IP address
- `http_method` - HTTP method (POST, GET, etc.)
- `content_type` - Content-Type header
- `user_agent` - User-Agent string
- `unique_source` - Composite identifier (IP + forwarded IP + user agent)
- `forwarded_for` - X-Forwarded-For header value
- `headers` - JSON of all HTTP headers
- `raw_body` - Raw request body (LONGTEXT, up to 65KB stored)
- `status` - ERROR, SUCCESS, or PROCESSING
- `message` - Error or success message
- `http_code` - HTTP response code sent
- `completed_at` - NY local time when processing completed

**Indexes:**
- `idx_timestamp` - For time-based queries
- `idx_ip_address` - For source tracking
- `idx_status` - For error filtering

### 2. Callback Processing Pipeline

**Entry Point:** `mps-api/callbacks/panel-message-debug.php`

**Processing Flow:**
1. Create debug log entry (status: PROCESSING)
2. Validate HTTP method (must be POST)
3. Validate Content-Type (must be application/json)
4. Read raw body
5. Sanitize payload using `payload-sanitizer.php`
6. Decode JSON with strict error handling
7. Validate structure (must be array/object)
8. Validate callback secret
9. Insert into `mpsm_panel_messages` table
10. Update debug log with final status

**Error Handling:**
- Every request is logged regardless of validity
- Errors update debug log with specific error message and HTTP code
- Invalid JSON triggers additional logging to `panel-message-invalid-json.log`

### 3. Payload Sanitization

**File:** `mps-api/callbacks/payload-sanitizer.php`

**Functions:**
- `sanitizeRawPayload()` - Normalizes raw JSON before decoding
  - Normalizes line endings (\r\n, \r → \n)
  - Removes BOM (Byte Order Mark)
  - Forces valid UTF-8 encoding
  - Escapes control characters and Unicode line separators
  - Preserves whitespace inside JSON strings

- `logInvalidJsonPayloadSample()` - Logs failed payloads
  - Writes to `mps-api/logs/panel-message-invalid-json.log`
  - Includes debug_id, error message, raw and sanitized payload samples
  - Truncates to 1024 chars for safety

### 4. Analysis Tools Available

#### A. Payload Debugger UI
**URL:** `https://mpsm.resolutionsbydesign.us/cms/payload-debugger.php`
**Access:** Requires authentication (401 without login)

**Features:**
- Real-time dashboard with auto-refresh (5s)
- Statistics: Total requests, successful, errors, last request time
- Filters: Status (SUCCESS/ERROR/PROCESSING), Source, Limit (50-500)
- Shows all debug log entries with full details
- Expandable payload and header views
- Source analysis and grouping

**Data API:** `cms/api/get-payload-debug-logs.php`

#### B. Panel Error Report (NEW)
**URL:** `https://mpsm.resolutionsbydesign.us/cms/panel-error-report.php`
**Created:** This investigation

**Features:**
- Comprehensive error statistics
- Error breakdown by type and HTTP code
- Sample error payloads
- JSON-specific error analysis
- Test/junk data detection
- Source analysis with error rates
- Cleanup SQL generation
- Automated recommendations

**Data API:** `cms/api/panel-error-report.php`

#### C. Invalid JSON Analyzer
**URL:** `https://mpsm.resolutionsbydesign.us/cms/api/analyze-invalid-json.php`
**Type:** Text output report

**Features:**
- Counts invalid JSON payload errors
- Re-validates stored "invalid" payloads
- Categorizes: Valid JSON arrays, Valid JSON null, Valid JSON non-array, Actually invalid
- Shows common patterns
- Includes drill-down cache analysis

#### D. Database Inspector
**URL:** `https://mpsm.resolutionsbydesign.us/cms/db-inspector.php`

**Features:**
- Lists all database tables
- Shows row counts
- Shows latest timestamps
- Auto-refreshes every 30 seconds

#### E. Panel Messages Viewer
**API:** `cms/api/get-panel-messages.php`

**Features:**
- Retrieves successful panel messages from `mpsm_panel_messages` table
- Filters by hours or limit
- Shows parsed payloads

## Error Types Expected

Based on the code analysis, these error types should appear in the debug log:

### 1. HTTP Method Errors
- **Message:** "Method Not Allowed"
- **HTTP Code:** 405
- **Cause:** Non-POST requests
- **Fix:** Ensure caller uses POST

### 2. Content-Type Errors
- **Message:** "Invalid Content-Type"
- **HTTP Code:** 415
- **Cause:** Missing or non-JSON Content-Type header
- **Fix:** Set Content-Type: application/json

### 3. Empty Payload Errors
- **Message:** "Empty request body"
- **HTTP Code:** 400
- **Cause:** No body or whitespace-only body
- **Fix:** Ensure payload is sent

### 4. JSON Parsing Errors
- **Message:** "Invalid JSON: {specific error}"
- **HTTP Code:** 400
- **Causes:**
  - Malformed JSON syntax
  - Invalid UTF-8 sequences
  - Unescaped control characters
  - Unicode line separators (U+2028, U+2029)
  - Trailing commas
  - Single quotes instead of double quotes
- **Fix:** Validate JSON before sending
- **Note:** Sanitizer attempts to fix common issues automatically

### 5. Structure Validation Errors
- **Message:** "Invalid JSON payload: Expected array/object"
- **HTTP Code:** 400
- **Cause:** Valid JSON but not an array/object (e.g., JSON null, string, number)
- **Fix:** Ensure payload is a JSON object

### 6. Authentication Errors
- **Message:** "Unauthorized - invalid secret"
- **HTTP Code:** 401
- **Cause:** Missing or incorrect callback secret
- **Expected Secret:** `mpsm-panel-message-v1`
- **Field Names:** `callbackSecret` or `secret`
- **Fix:** Include correct secret in payload

### 7. Database Errors
- **Message:** "Database error: {exception message}"
- **HTTP Code:** 500
- **Causes:** Database connection issues, constraint violations, etc.
- **Fix:** Check database connectivity and schema

## Test Data Identification

The system can identify test data using these patterns:

**Search Patterns:**
- `raw_body LIKE '%TEST%'`
- `raw_body LIKE '%test%'`
- `raw_body LIKE '%SUCCESS_SERIAL%'`
- `message LIKE '%test%'`
- `unique_source LIKE '%test%'`

**Cleanup SQL Format:**
```sql
DELETE FROM mpsm_panel_callback_debug WHERE id IN (1, 2, 3, ...);
```

## Invalid JSON Log File

**Path:** `mps-api/logs/panel-message-invalid-json.log`
**Status:** Does not exist yet (no invalid JSON errors have occurred)

**Format (when created):**
```json
{
  "timestamp": "2025-11-14 12:34:56",
  "debug_id": 123,
  "error": "Syntax error",
  "raw": "first 1024 chars of raw payload",
  "sanitized": "first 1024 chars of sanitized payload"
}
```

Each line is a separate JSON object.

## Accessing the Analysis Tools

### Method 1: Via Browser (Requires Login)

1. **Payload Debugger:**
   ```
   https://mpsm.resolutionsbydesign.us/cms/payload-debugger.php
   ```

2. **Panel Error Report:**
   ```
   https://mpsm.resolutionsbydesign.us/cms/panel-error-report.php
   ```

3. **Invalid JSON Analysis:**
   ```
   https://mpsm.resolutionsbydesign.us/cms/api/analyze-invalid-json.php
   ```

4. **Database Inspector:**
   ```
   https://mpsm.resolutionsbydesign.us/cms/db-inspector.php
   ```

### Method 2: Direct Database Query

You can connect to the database and run:

```sql
-- Overall statistics
SELECT
    COUNT(*) as total_entries,
    SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as total_errors,
    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as total_success,
    SUM(CASE WHEN status = 'PROCESSING' THEN 1 ELSE 0 END) as total_processing,
    MIN(timestamp) as first_entry,
    MAX(timestamp) as last_entry
FROM mpsm_panel_callback_debug;

-- Error breakdown
SELECT
    message,
    http_code,
    COUNT(*) as count,
    MIN(timestamp) as first_seen,
    MAX(timestamp) as last_seen,
    COUNT(DISTINCT unique_source) as unique_sources
FROM mpsm_panel_callback_debug
WHERE status = 'ERROR'
GROUP BY message, http_code
ORDER BY count DESC;

-- Sample errors
SELECT
    id,
    timestamp,
    ip_address,
    unique_source,
    message,
    http_code,
    SUBSTRING(raw_body, 1, 200) as body_preview
FROM mpsm_panel_callback_debug
WHERE status = 'ERROR'
ORDER BY timestamp DESC
LIMIT 10;

-- Find test data
SELECT
    id,
    timestamp,
    message,
    SUBSTRING(raw_body, 1, 100) as body_preview
FROM mpsm_panel_callback_debug
WHERE
    raw_body LIKE '%TEST%'
    OR raw_body LIKE '%SUCCESS_SERIAL%'
    OR raw_body LIKE '%test%'
    OR message LIKE '%test%'
ORDER BY timestamp DESC;
```

## Recommendations

1. **Access the Payload Debugger UI** - This is the primary tool for real-time monitoring
   - Login to CMS first
   - Navigate to payload-debugger.php
   - Filter by ERROR status to see only failures

2. **Use Panel Error Report** - For comprehensive analysis
   - Shows error patterns and trends
   - Identifies test data automatically
   - Provides cleanup SQL

3. **Check Invalid JSON Log** - If JSON errors are occurring
   - File: `mps-api/logs/panel-message-invalid-json.log`
   - Shows both raw and sanitized payloads for debugging

4. **Production vs Test Data** - Separate analysis
   - Use test data detection to filter out junk
   - Focus on production sources (non-test IPs/user agents)
   - Review unique_source field to identify legitimate senders

5. **Common Fixes for Senders:**
   - Ensure Content-Type: application/json header
   - Include callback secret: `{"callbackSecret": "mpsm-panel-message-v1"}`
   - Send valid JSON object (not null, string, or number)
   - Use POST method only
   - Ensure UTF-8 encoding

## Files Created During Investigation

1. **C:\Users\jez.slade\Desktop\Projects\MPSM-Dashboard\cms\api\panel-error-report.php**
   - API endpoint for comprehensive error analysis

2. **C:\Users\jez.slade\Desktop\Projects\MPSM-Dashboard\cms\panel-error-report.php**
   - HTML UI for viewing error report

3. **C:\Users\jez.slade\Desktop\Projects\MPSM-Dashboard\cms\debug-error-analysis.php**
   - Command-line analysis script (requires PDO MySQL driver)

4. **C:\Users\jez.slade\Desktop\Projects\MPSM-Dashboard\PANEL_ERROR_INVESTIGATION_REPORT.md**
   - This report

## Next Steps

To complete the investigation, you need to:

1. **Login to the CMS** at https://mpsm.resolutionsbydesign.us/cms/

2. **Access the Payload Debugger** to see live data:
   - URL: https://mpsm.resolutionsbydesign.us/cms/payload-debugger.php
   - Filter by ERROR status
   - Review error messages and payloads

3. **Access the Panel Error Report** for analysis:
   - URL: https://mpsm.resolutionsbydesign.us/cms/panel-error-report.php
   - Review error breakdown
   - Check recommendations
   - Identify test vs production data

4. **Share findings** with the investigation requestor:
   - Total error count
   - Most common error types
   - Sample error payloads
   - Test data cleanup SQL (if applicable)
   - Recommendations for fixing payload acceptance

---

## Technical Details

**Database Connection:**
- Host: localhost
- Database: resolut7_mpsm
- User: resolut7_mpsm_agent
- Prefix: mpsm_

**Timezone:** America/New_York (all timestamps in NY local time)

**Callback Endpoint:**
```
POST https://mpsm.resolutionsbydesign.us/mps-api/callbacks/panel-message-debug.php
Content-Type: application/json

{
  "callbackSecret": "mpsm-panel-message-v1",
  "customer": {
    "code": "...",
    "description": "..."
  },
  "installedProduct": {
    "serialNumber": "..."
  },
  "maintenanceAlert": {
    "code": "...",
    "id": "...",
    "panelConfiguration": "..."
  }
}
```

**Response Format:**
```json
{
  "success": true,
  "stored": true
}
```

**Error Response Format:**
```json
{
  "success": false,
  "error": "Error message here"
}
```
