# System Diagnostics Improvements

**Date**: 2025-11-07
**Status**: ✅ COMPLETE

## Overview

Comprehensive improvements to panel callback validation, error reporting, and system diagnostics to address the 388 invalid JSON payload errors and provide detailed drill-down cache statistics.

---

## Issues Addressed

### 1. Invalid JSON Payload Errors (388 total)

**Problem**: Generic "Invalid JSON payload" error message didn't distinguish between:
- Actually malformed JSON
- Valid JSON but wrong type (e.g., `null`, strings, numbers instead of objects/arrays)
- Empty request bodies

**Impact**: Difficult to diagnose whether errors were legitimate rejections or system issues.

---

## Changes Made

### 1. Enhanced JSON Validation ([mps-api/callbacks/panel-message.php](mps-api/callbacks/panel-message.php#L39-L54))

**Before**:
```php
$decoded = json_decode($rawBody, true);
if (!is_array($decoded)) {
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', 'Invalid JSON payload', 400, $rawBody);
    respondError('Invalid JSON payload');
}
```

**After**:
```php
$decoded = json_decode($rawBody, true);
$jsonError = json_last_error();

// Provide detailed error messages for debugging
if ($jsonError !== JSON_ERROR_NONE) {
    $errorMsg = 'Invalid JSON: ' . json_last_error_msg();
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', $errorMsg, 400, $rawBody);
    respondError($errorMsg);
}

if (!is_array($decoded)) {
    $actualType = gettype($decoded);
    $errorMsg = "Invalid JSON payload: Expected object/array, received {$actualType}";
    updatePanelCallbackDebugLog($debugLogId, 'ERROR', $errorMsg, 400, $rawBody);
    respondError($errorMsg);
}
```

**Improvements**:
- ✅ Distinguishes between malformed JSON and valid JSON of wrong type
- ✅ Provides specific JSON error messages (e.g., "Syntax error", "Unexpected character")
- ✅ Reports the actual type received (e.g., "null", "string", "integer")
- ✅ Better debugging information in logs

---

### 2. System Diagnostics API ([cms/api/get-system-diagnostics.php](cms/api/get-system-diagnostics.php))

**New API endpoint** that provides comprehensive system analysis:

#### Panel Callback Statistics
- Total callbacks received
- Success/error counts
- Error breakdown by type
- Last callback timestamp

#### Invalid JSON Analysis
- Total invalid JSON errors
- Breakdown by category:
  - Actually invalid JSON (malformed syntax)
  - Valid JSON but not array/object
  - Valid null values
  - Empty request bodies
- Sample payloads for each category

#### Panel Message Statistics
- Total messages stored successfully
- Unique devices sending messages
- Unique customers
- First/last message timestamps

#### Drill-Down Cache Statistics
- **Total devices in cache**
- **Devices with full drill-down data** (answers your question!)
- **Coverage percentage**
- Devices with alerts
- Devices with supply information
- Cache freshness metrics (last hour, 24h, 7d)
- Recent cache entries sample

#### System Health Assessment
- Overall health status (EXCELLENT/WARNING/CRITICAL)
- Identified issues
- Actionable recommendations

**Example Response**:
```json
{
  "success": true,
  "data": {
    "panel_callbacks": {
      "total": 1301,
      "success": 913,
      "errors": 388,
      "error_breakdown": [...]
    },
    "panel_messages": {
      "total_messages": 913,
      "unique_devices": 247,
      "unique_customers": 45
    },
    "cache": {
      "total_devices": 1024,
      "devices_with_drilldown": 856,
      "coverage_percent": 83.59
    },
    "health": {
      "status": "EXCELLENT",
      "issues": [],
      "recommendations": [...]
    }
  }
}
```

---

### 3. System Diagnostics Dashboard ([cms/system-diagnostics.php](cms/system-diagnostics.php))

**New visual interface** for monitoring system health:

#### Features
- **Real-time statistics** - Auto-refreshable diagnostics
- **Health status indicator** - Color-coded (green/yellow/red)
- **Detailed metrics** - All key system metrics in one view
- **Error analysis** - Breakdown of all error types
- **Cache insights** - Drill-down cache coverage and freshness
- **Invalid JSON analysis** - Categorized error samples

#### Access
- **URL**: `https://mpsm.resolutionsbydesign.us/cms/system-diagnostics.php`
- **Authentication**: Required (existing session)

#### Visual Design
- Clean, modern interface
- Color-coded statistics (green = good, red = error, yellow = warning)
- Gradient health status cards
- Responsive grid layout
- Sample error payloads with syntax highlighting

---

## How to Use

### Quick Check
1. Navigate to: `https://mpsm.resolutionsbydesign.us/cms/system-diagnostics.php`
2. Review the health status card
3. Check drill-down cache coverage
4. Review any recommendations

### Detailed Analysis
1. Scroll to "Panel Callbacks" section for success/error rates
2. Check "Error Breakdown" to see types of errors
3. Review "Invalid JSON Analysis" to understand the 388 errors:
   - How many are actually invalid vs. wrong type
   - Sample payloads for each category
4. Check "Drill-Down Cache" for exact device counts

### API Integration
```javascript
// Fetch diagnostics programmatically
fetch('/cms/api/get-system-diagnostics.php')
  .then(r => r.json())
  .then(result => {
    console.log('Devices with drill-down:', result.data.cache.devices_with_drilldown);
    console.log('Coverage:', result.data.cache.coverage_percent + '%');
  });
```

---

## Expected Results

### Invalid JSON Errors Breakdown

Based on code analysis, the 388 errors likely consist of:

1. **Actually Invalid JSON** (~10-20%)
   - Malformed syntax from external source
   - Test payloads during setup
   - Network corruption

2. **Valid JSON, Wrong Type** (~70-80%)
   - JSON `"null"` values (legitimate rejection)
   - Primitive values like strings, numbers (legitimate rejection)
   - These are correctly rejected since we need objects/arrays

3. **Empty Bodies** (~5-10%)
   - Network issues
   - Incomplete requests

### Drill-Down Cache Count

The diagnostics API queries:
```sql
SELECT COUNT(*) FROM mpsm_cache_device_drilldown
```

This provides the **exact number** of devices with full drill-down data cached.

Expected: **80-95% coverage** based on the cache refresh system.

---

## Validation & Testing

### Test Cases

1. **Valid JSON Array** → Should succeed
   ```bash
   curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/callbacks/panel-message.php \
     -H "Content-Type: application/json" \
     -d '{"callbackSecret":"mpsm-panel-message-v1","device":{}}'
   ```

2. **Invalid JSON** → Should fail with specific error
   ```bash
   curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/callbacks/panel-message.php \
     -H "Content-Type: application/json" \
     -d '{invalid json}'
   ```
   Expected: `{"success":false,"error":"Invalid JSON: Syntax error"}`

3. **Valid JSON Null** → Should fail with type error
   ```bash
   curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/callbacks/panel-message.php \
     -H "Content-Type: application/json" \
     -d 'null'
   ```
   Expected: `{"success":false,"error":"Invalid JSON payload: Expected object/array, received NULL"}`

4. **Valid JSON String** → Should fail with type error
   ```bash
   curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/callbacks/panel-message.php \
     -H "Content-Type: application/json" \
     -d '"test"'
   ```
   Expected: `{"success":false,"error":"Invalid JSON payload: Expected object/array, received string"}`

---

## Benefits

### For Operators
- ✅ **Clear error categorization** - Know immediately if errors need action
- ✅ **Exact cache counts** - No guessing about drill-down coverage
- ✅ **Health at a glance** - Single page shows system status
- ✅ **Actionable recommendations** - System suggests next steps

### For Developers
- ✅ **Better debugging** - Specific error messages in logs
- ✅ **API access** - Programmatic diagnostics retrieval
- ✅ **Sample payloads** - See actual error cases for analysis

### For System Reliability
- ✅ **Proactive monitoring** - Detect issues before they impact users
- ✅ **Metrics tracking** - Historical error patterns
- ✅ **Performance insights** - Cache freshness and coverage

---

## Future Enhancements

### Potential Improvements
1. **Historical tracking** - Store diagnostics snapshots daily
2. **Alerting** - Email/SMS when health status degrades
3. **Comparison view** - Compare current vs. previous diagnostics
4. **Export capability** - Download diagnostics as JSON/CSV
5. **Webhook retry** - Auto-retry failed callbacks with exponential backoff

### Progressive Caching (Already Documented)
From [CACHE_SYSTEM_AUDIT.md](CACHE_SYSTEM_AUDIT.md):
- Phase 1: Quick device list refresh
- Phase 2: Priority drill-down for devices with alerts
- Phase 3: Background incremental refresh

---

## Files Modified

| File | Changes | Impact |
|------|---------|--------|
| [mps-api/callbacks/panel-message.php](mps-api/callbacks/panel-message.php) | Enhanced JSON validation (lines 39-54) | Better error messages |

## Files Created

| File | Purpose | Access |
|------|---------|--------|
| [cms/api/get-system-diagnostics.php](cms/api/get-system-diagnostics.php) | API endpoint for comprehensive diagnostics | API |
| [cms/system-diagnostics.php](cms/system-diagnostics.php) | Visual dashboard for system health | Web UI |
| [cms/api/analyze-invalid-json.php](cms/api/analyze-invalid-json.php) | Detailed JSON error analysis | API/Web |
| [analyze_invalid_json.php](analyze_invalid_json.php) | Command-line analysis tool | CLI |
| [query_invalid_json.php](query_invalid_json.php) | Direct database query script | CLI |

---

## Summary

### Issues Resolved
- ✅ Invalid JSON errors now categorized and explained
- ✅ Drill-down cache count available via API and UI
- ✅ System health monitoring in place
- ✅ Actionable recommendations provided

### Key Metrics Available
- ✅ Total panel callbacks (success/error breakdown)
- ✅ Exact drill-down cache device count
- ✅ Cache coverage percentage
- ✅ Error categorization (invalid vs. wrong type)
- ✅ System health status

### Action Required
1. **Visit**: https://mpsm.resolutionsbydesign.us/cms/system-diagnostics.php
2. **Review**: Health status and drill-down cache coverage
3. **Verify**: Invalid JSON analysis shows expected error types
4. **Monitor**: Periodically check for new issues

---

**Status**: All improvements deployed and ready for use.

**Next Steps**: Review diagnostics dashboard to confirm system health and drill-down cache statistics.
