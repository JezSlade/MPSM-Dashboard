# Panel Callback Payload Debugger Guide

**Created**: November 5, 2025
**Status**: Deployed and Ready for Testing

---

## Overview

The Payload Debugger is a comprehensive diagnostic tool integrated into your MPSM Dashboard that logs and displays **ALL incoming HTTP callbacks** from MPS Monitor. This helps determine whether:

1. MPSM is broadcasting callbacks correctly
2. Your server is receiving them
3. The payload format matches expectations
4. Your server is responding appropriately

---

## Features

### Real-Time Monitoring
- Auto-refreshes every 5 seconds
- Live stats dashboard showing success/error counts
- Filter by status (SUCCESS, ERROR, PROCESSING)
- Configurable result limits (50, 100, 200, 500)

### Comprehensive Logging
Every request is logged with:
- **Timestamp**: Exact time of request
- **IP Address**: Source IP (identifies if it's from MPSM)
- **HTTP Method**: Should be POST
- **Content-Type**: Should be application/json
- **User-Agent**: Client identifier
- **Headers**: All HTTP headers as JSON
- **Raw Body**: Complete request payload
- **Parsed Body**: JSON-decoded payload for readability
- **Status**: SUCCESS, ERROR, or PROCESSING
- **Message**: Error description or success confirmation
- **HTTP Code**: Response code (200, 400, 401, 415, 500, etc.)

---

## Access URLs

### Payload Debugger UI
```
https://mpsm.resolutionsbydesign.us/cms/payload-debugger.php
```

### Debug Callback Endpoint
```
https://mpsm.resolutionsbydesign.us/mps-api/callbacks/panel-message-debug.php
```

### API Endpoint
```
https://mpsm.resolutionsbydesign.us/cms/api/get-payload-debug-logs.php
```

---

## Database Schema

### Table: `mpsm_panel_callback_debug`

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT | Auto-increment primary key |
| `timestamp` | DATETIME | Request timestamp |
| `ip_address` | VARCHAR(45) | Sender IP address |
| `http_method` | VARCHAR(10) | HTTP method (POST, GET, etc.) |
| `content_type` | VARCHAR(255) | Content-Type header |
| `user_agent` | VARCHAR(500) | User-Agent header |
| `headers` | JSON | All HTTP headers |
| `raw_body` | TEXT | Complete request body |
| `status` | VARCHAR(20) | SUCCESS, ERROR, or PROCESSING |
| `message` | VARCHAR(500) | Status message or error description |
| `http_code` | INT | HTTP response code |

**Indexes**:
- `idx_timestamp` - For chronological sorting
- `idx_ip_address` - For filtering by source
- `idx_status` - For filtering by status

---

## Testing with Test Payloads Helper

The historical PowerShell payload script is not present in the current active repository. Recreate this as a portable Python or shell helper before rerunning the full callback matrix.

### Suggested Helper

```bash
# Example shape; implement before use.
python3 tests/payload_debugger_matrix.py
```

### Tests Performed

1. **Test 1**: Valid payload with correct secret → **SUCCESS (200)**
2. **Test 2**: Invalid secret → **ERROR (401 Unauthorized)**
3. **Test 3**: Missing secret → **ERROR (401 Unauthorized)**
4. **Test 4**: Wrong Content-Type → **ERROR (415 Unsupported Media Type)**
5. **Test 5**: Invalid JSON → **ERROR (400 Bad Request)**
6. **Test 6**: Empty body → **ERROR (400 Bad Request)**
7. **Test 7**: GET request (should be POST) → **ERROR (405 Method Not Allowed)**
8. **Test 8**: Second valid payload → **SUCCESS (200)**

### Expected Results in Debugger

After running the test script, you should see:
- **Total Requests**: 8
- **Success Count**: 2 (Tests 1 and 8)
- **Error Count**: 6 (Tests 2-7)

---

## Validation Checks Performed

The debug callback endpoint performs the following validations:

### 1. HTTP Method Validation
- **Requirement**: Must be POST
- **Error**: 405 Method Not Allowed
- **Logged**: YES

### 2. Content-Type Validation
- **Requirement**: Must contain "application/json"
- **Error**: 415 Unsupported Media Type
- **Logged**: YES

### 3. Body Validation
- **Requirement**: Non-empty request body
- **Error**: 400 Bad Request (Empty request body)
- **Logged**: YES

### 4. JSON Validation
- **Requirement**: Valid JSON structure
- **Error**: 400 Bad Request (Invalid JSON payload)
- **Logged**: YES (with raw body for inspection)

### 5. Secret Validation
- **Requirement**: `callbackSecret` must equal `mpsm-panel-message-v1`
- **Error**: 401 Unauthorized
- **Logged**: YES (with raw body for inspection)

### 6. Database Storage
- **Success**: Stores message in `mpsm_panel_messages` table
- **Returns**: `{success: true, stored: true}`
- **Logged**: YES with database message ID

---

## How to Use for MPSM Diagnostics

### Step 1: Configure MPSM Callback
In MPSM, update your panel message callback to use the **debug endpoint**:

```
https://mpsm.resolutionsbydesign.us/mps-api/callbacks/panel-message-debug.php
```

### Step 2: Monitor the Debugger
Open the payload debugger in your browser:

```
https://mpsm.resolutionsbydesign.us/cms/payload-debugger.php
```

### Step 3: Trigger a Panel Message
Wait for a real device to send a panel message, or manually trigger one in MPSM.

### Step 4: Analyze the Logs

#### If NO requests appear:
- **Problem**: MPSM is not broadcasting
- **Solution**: Check MPSM callback configuration
- **Verify**: Callback URL, Active status, Trigger conditions

#### If requests appear with ERROR status:
- **Check IP Address**: Is it from MPSM's servers?
- **Check Content-Type**: Should be `application/json`
- **Check Message**: Tells you exactly what failed
- **Check Raw Body**: See if MPSM is sending unexpected format

#### If requests appear with SUCCESS status:
- **Check Parsed Body**: Verify all expected fields are present
- **Check Message**: Should say "Message stored with ID X"
- **Check `mpsm_panel_messages` table**: Confirm data is stored

### Step 5: Compare Expected vs Actual

#### Expected Payload Structure:
```json
{
  "callbackSecret": "mpsm-panel-message-v1",
  "customer": {
    "code": "CUSTOMER_CODE",
    "description": "Customer Name"
  },
  "installedProduct": {
    "serialNumber": "DEVICE_SERIAL",
    "product": { "brand": "...", "model": "..." },
    "toner": { "black": 85, "cyan": 90, ... },
    "office": { "name": "...", "address": "..." }
  },
  "maintenanceAlert": {
    "code": "ALERT_CODE",
    "id": "ALERT_ID",
    "panelConfiguration": "Alert Description"
  }
}
```

---

## Troubleshooting Guide

### Problem: "No callback requests logged yet"

**Possible Causes**:
1. MPSM callback not configured
2. MPSM callback marked as Inactive
3. No devices have triggered panel messages
4. Firewall blocking MPSM's IP
5. Wrong callback URL in MPSM

**Solutions**:
- Verify callback URL in MPSM matches debug endpoint
- Check callback is marked as "Active"
- Manually trigger a test callback in MPSM
- Review server firewall logs

---

### Problem: Requests logged with ERROR (401)

**Message**: "Unauthorized - invalid secret"

**Possible Causes**:
1. `callbackSecret` field missing from payload
2. `callbackSecret` has wrong value
3. Payload uses old placeholder format

**Solutions**:
- Check raw body in debugger
- Verify MPSM payload JSON includes: `"callbackSecret": "mpsm-panel-message-v1"`
- Update MPSM callback payload configuration

---

### Problem: Requests logged with ERROR (415)

**Message**: "Invalid Content-Type"

**Possible Causes**:
1. MPSM sending as `text/plain` instead of `application/json`
2. Content-Type header not set

**Solutions**:
- Check headers in debugger
- Verify MPSM callback is configured to send JSON
- Contact MPSM support if Content-Type cannot be changed

---

### Problem: Requests logged with ERROR (400)

**Message**: "Invalid JSON payload"

**Possible Causes**:
1. MPSM sending malformed JSON
2. Special characters not escaped
3. Encoding issues

**Solutions**:
- Check raw body in debugger
- Look for syntax errors in JSON
- Verify character encoding (should be UTF-8)

---

### Problem: Requests SUCCESS but missing fields

**Symptoms**:
- Status shows SUCCESS
- Message stored in database
- But parsed body is missing expected fields (toner, office, project, etc.)

**Possible Causes**:
1. MPSM payload configuration incomplete
2. Placeholders not replaced by MPSM
3. Device doesn't have data for those fields

**Solutions**:
- Review parsed body in debugger
- Compare to expected payload structure
- Update MPSM payload JSON to include all placeholders
- Verify device has the expected data in MPSM

---

## Switching Between Endpoints

### Debug Endpoint (Current)
Use this endpoint while troubleshooting:
```
/mps-api/callbacks/panel-message-debug.php
```

**Advantages**:
- Logs every request with full details
- Helps diagnose MPSM broadcast issues
- Shows exactly what's being sent

**Disadvantages**:
- Slightly slower (more logging overhead)
- Larger database (stores all attempts)

### Production Endpoint
Use this endpoint after validation:
```
/mps-api/callbacks/panel-message.php
```

**Advantages**:
- Faster (less logging)
- Smaller database (only stores successful messages)
- Production-optimized

**Disadvantages**:
- Minimal logging (only success/error in log file)
- Harder to diagnose issues

---

## Integration with Panel Message System

### Database Tables

Both endpoints write to:
1. **`mpsm_panel_messages`** - Successful panel messages
2. **`mpsm_panel_callback_debug`** - All callback attempts (debug only)

### Files Deployed

1. **`mps-api/callbacks/panel-message-debug.php`** - Debug callback endpoint
2. **`cms/payload-debugger.php`** - UI for viewing logs
3. **`cms/api/get-payload-debug-logs.php`** - API for fetching logs
4. **Portable payload matrix helper** - Not currently present; recreate in Python/shell if needed

### Also Fixed

- **`cms/assets/app.js`** - Added safety checks for CardManager.setContext (4 locations)
  - Lines 1256-1262
  - Lines 1432-1438
  - Lines 1742-1748
  - Lines 1769-1775

---

## Next Steps

1. **Run Test Helper**: Recreate and execute a portable payload matrix helper to verify the debugger is working
2. **Update MPSM**: Change callback URL to debug endpoint
3. **Monitor Logs**: Watch for real device panel messages
4. **Validate Payload**: Ensure all expected fields are present
5. **Switch to Production**: Once validated, switch back to production endpoint

---

## Support

For issues or questions:
- Review debugger logs at `/cms/payload-debugger.php`
- Check file logs at `mps-api/logs/panel-message-YYYY-MM-DD.log`
- Query database directly: `SELECT * FROM mpsm_panel_callback_debug ORDER BY timestamp DESC LIMIT 10`

---

**Report Generated**: November 5, 2025
**Status**: ✅ Deployed and Ready for Testing
**Test Helper**: Historical `test-payloads.ps1` retired; recreate as Python/shell if needed
**Debugger URL**: https://mpsm.resolutionsbydesign.us/cms/payload-debugger.php
