# How to Access Panel Callback Error Data

## Quick Start

The panel callback system has comprehensive error logging. Here are the fastest ways to access error data:

## Option 1: Web UI (Recommended)

### Payload Debugger
**Best for:** Real-time monitoring and viewing all requests

**URL:** https://mpsm.resolutionsbydesign.us/cms/payload-debugger.php

**Steps:**
1. Login to CMS at https://mpsm.resolutionsbydesign.us/cms/
2. Navigate to the Payload Debugger URL above
3. Use the "Status" filter and select "Error"
4. Click "Refresh Now" or wait for auto-refresh (5 seconds)
5. Click "View Payload" on any entry to see the full request body
6. Click "View Headers" to see HTTP headers

**Features:**
- Shows total requests, successful, errors, last request time
- Filter by status, source, and limit
- Expandable payload and header views
- Auto-refresh every 5 seconds
- Source grouping and analysis

### Panel Error Report (NEW)
**Best for:** Error analysis, trends, and identifying patterns

**URL:** https://mpsm.resolutionsbydesign.us/cms/panel-error-report.php

**Steps:**
1. Login to CMS
2. Navigate to the Panel Error Report URL above
3. Review the comprehensive error breakdown
4. Check recommendations section
5. Use cleanup SQL if test data is found

**Features:**
- Error statistics and rates
- Error breakdown by type and HTTP code
- Sample error payloads
- Test data detection
- Source analysis with success/error rates
- Automated recommendations
- Cleanup SQL generation

### Invalid JSON Analyzer
**Best for:** Deep-dive into JSON parsing issues

**URL:** https://mpsm.resolutionsbydesign.us/cms/api/analyze-invalid-json.php

**Steps:**
1. Login to CMS
2. Navigate to the analyzer URL above
3. Review text output showing:
   - Valid JSON incorrectly rejected
   - Valid JSON null/non-array (correctly rejected)
   - Actually invalid JSON with error messages
   - Common patterns

## Option 2: Direct Database Query

**Database Details:**
- Host: localhost
- Database: resolut7_mpsm
- Table: mpsm_panel_callback_debug

### Quick Stats Query
```sql
SELECT
    COUNT(*) as total,
    SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as errors,
    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success,
    ROUND(SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as error_rate
FROM mpsm_panel_callback_debug;
```

### Recent Errors Query
```sql
SELECT
    id,
    timestamp,
    message,
    http_code,
    unique_source,
    SUBSTRING(raw_body, 1, 200) as payload_preview
FROM mpsm_panel_callback_debug
WHERE status = 'ERROR'
ORDER BY timestamp DESC
LIMIT 10;
```

### Full Query Collection
See file: `panel_error_queries.sql` for comprehensive SQL queries

## Option 3: Invalid JSON Log File

**Path:** `mps-api/logs/panel-message-invalid-json.log`

**Status:** File only exists if JSON parsing errors have occurred

**Format:** Each line is a JSON object:
```json
{
  "timestamp": "2025-11-14 12:34:56",
  "debug_id": 123,
  "error": "Syntax error",
  "raw": "first 1024 chars of raw payload",
  "sanitized": "first 1024 chars of sanitized payload"
}
```

**How to Check:**
```bash
# Check if file exists
ls -la mps-api/logs/panel-message-invalid-json.log

# View recent entries
tail -20 mps-api/logs/panel-message-invalid-json.log
```

## What You'll Find

### Error Types

1. **Method Not Allowed (405)**
   - Non-POST requests
   - Fix: Use POST method

2. **Invalid Content-Type (415)**
   - Missing or wrong Content-Type header
   - Fix: Set `Content-Type: application/json`

3. **Empty request body (400)**
   - No payload sent
   - Fix: Send JSON payload

4. **Invalid JSON (400)**
   - Malformed JSON, encoding issues, control characters
   - Fix: Validate JSON syntax and encoding

5. **Invalid JSON payload: Expected array/object (400)**
   - Valid JSON but wrong type (e.g., null, string, number)
   - Fix: Send JSON object

6. **Unauthorized - invalid secret (401)**
   - Missing or wrong callback secret
   - Fix: Include `"callbackSecret": "mpsm-panel-message-v1"`

7. **Database error (500)**
   - Database connection or constraint issues
   - Fix: Check database connectivity

### Data Fields

Each debug entry contains:
- **timestamp** - NY local time when request received
- **ip_address** - Remote IP
- **unique_source** - Composite identifier (IP + user agent + forwarded IP)
- **http_method** - POST, GET, etc.
- **content_type** - Content-Type header value
- **user_agent** - User-Agent string
- **headers** - Full HTTP headers (JSON)
- **raw_body** - Complete request payload (up to 65KB)
- **status** - ERROR, SUCCESS, or PROCESSING
- **message** - Error or success description
- **http_code** - Response code sent (400, 401, 415, 500, etc.)
- **completed_at** - NY local time when processing finished

## Common Use Cases

### "I need to see ALL errors"
1. Go to Payload Debugger
2. Filter: Status = "Error"
3. Limit: 500
4. Review list

### "What's the most common error?"
1. Go to Panel Error Report
2. Check "Error Breakdown by Type" section
3. Top entry shows most common

### "Show me actual error payloads"
1. Go to Payload Debugger
2. Filter: Status = "Error"
3. Click "View Payload" on any entry
4. Or go to Panel Error Report and check "Recent Error Samples"

### "Are there test entries I should delete?"
1. Go to Panel Error Report
2. Check "Test/Junk Data Detected" section
3. Copy cleanup SQL if test data found
4. Run SQL in database (after reviewing IDs)

### "Which sources are causing errors?"
1. Go to Panel Error Report
2. Check "Source Analysis" section
3. Shows error count and rate per source

### "I need to debug a specific JSON parsing error"
1. Go to Invalid JSON Analyzer
2. Review actually invalid vs incorrectly rejected
3. Check for common patterns
4. Or check `mps-api/logs/panel-message-invalid-json.log`

## Testing the System

Send a test request:

```bash
curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/callbacks/panel-message-debug.php \
  -H "Content-Type: application/json" \
  -d '{
    "callbackSecret": "mpsm-panel-message-v1",
    "customer": {
      "code": "TEST001",
      "description": "Test Customer"
    },
    "installedProduct": {
      "serialNumber": "TEST_SERIAL_001"
    },
    "maintenanceAlert": {
      "code": "TEST_ALERT",
      "id": "TEST_ID_001",
      "panelConfiguration": "Test Config"
    }
  }'
```

Expected response:
```json
{
  "success": true,
  "stored": true
}
```

Then check Payload Debugger to see the entry.

## Files Reference

**Analysis Tools:**
- `cms/payload-debugger.php` - Main UI for viewing all requests
- `cms/panel-error-report.php` - Comprehensive error analysis UI
- `cms/api/get-payload-debug-logs.php` - API for payload debugger
- `cms/api/panel-error-report.php` - API for error report
- `cms/api/analyze-invalid-json.php` - Text-based JSON error analysis
- `cms/db-inspector.php` - Database table inspector

**Core System:**
- `mps-api/callbacks/panel-message-debug.php` - Callback endpoint
- `mps-api/callbacks/panel-message-common.php` - Shared functions and table creation
- `mps-api/callbacks/payload-sanitizer.php` - JSON payload sanitization

**Documentation:**
- `PANEL_ERROR_INVESTIGATION_REPORT.md` - Full technical investigation report
- `panel_error_queries.sql` - Collection of useful SQL queries
- `HOW_TO_ACCESS_ERROR_DATA.md` - This file

## Support

For questions or issues:
1. Review error in Payload Debugger
2. Check Panel Error Report recommendations
3. Run relevant SQL query from panel_error_queries.sql
4. Review full investigation report in PANEL_ERROR_INVESTIGATION_REPORT.md
