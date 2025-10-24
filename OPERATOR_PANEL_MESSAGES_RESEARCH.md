# Operator Panel Message Tracing - Research Findings

## Overview
Research into MPS Monitor API endpoints for tracking real-time printer operator panel messages (jams, errors, cover open, etc.)

**Date**: 2025-10-24
**Customer**: Cape Fear Valley Med Ctr (W9OPXL0YDK)
**Dealer**: NY06AGDWUQ

---

## Key Findings

### ✅ Trace Configurations Found

You have **4 active Panel Message Alert configurations**:
1. **Trace Cover Open** - Tracks when covers are opened
2. **Trace Jam Codes** - Tracks specific jam error codes
3. **Trace Jams** - Tracks all jam events
4. **Trace Subunit Life Almost Over Code** - Tracks maintenance warnings

Retrieved via:
```javascript
PanelMessageAlert/List
  Params: { DealerCode, SortColumn, pageNumber, pageRows }
  Returns: Array of alert configurations
```

---

## API Endpoints Discovered

### 1. **TraceVolume/List** (PRIMARY ENDPOINT)
**Purpose**: Returns trace volume events for a specific device
**Summary**: "Returns a list of TraceVolume by device"
**Method**: GET (via POST to /query)
**Parameters**:
- `id` (required) - Device ID

**Usage Pattern**:
```javascript
// For EACH device in customer
const traces = await MPSApi.makeRequest('TraceVolume/List', {
  id: deviceId
});
```

**Challenge**: Requires individual device ID, cannot query all customer devices at once

---

### 2. **Device/ListErrorsMessagesDataHistory**
**Purpose**: Returns error message history for a device
**Parameters** (all required):
- `DeviceId` - Specific device
- `FromDate` - Start date (YYYY-MM-DD)
- `ToDate` - End date (YYYY-MM-DD)
- `SortColumn` - Column to sort by (e.g., "Date")
- `pageNumber`, `pageRows` - Pagination

**Returns**:
```json
{
  "DeviceId": 2529369,
  "CustomerCode": "BG0XWRS21F",
  "DateUTC": "2025-10-20T07:16:01.227Z",
  "AlertCode": "3",  // Cover Open
  "AlertDescription": "Cover Open"
}
```

---

### 3. **Device/ListMaintenanceKitMessagesDataHistory**
**Purpose**: Maintenance kit message history
**Same parameters as ErrorsMessagesDataHistory**

---

### 4. **PanelMessageAlert/GetErrorCodes**
**Purpose**: Gets available panel message codes (3, 8, 10, etc.)
**Use**: Reference list for decoding error codes

---

## Data Architecture

### How MPSM Stores Traced Messages

Based on SDK analysis and API testing:

1. **Configuration Level** (PanelMessageAlert)
   - Stores WHAT to trace (jam codes, cover open, etc.)
   - Applies to brands/models
   - Retrieved via `PanelMessageAlert/List`

2. **Device Level** (Device object)
   - Each device has `PanelMessage` field (stores current message)
   - Each device has `TraceVolume` field (link to trace events)
   - Retrieved via `Device/List` or `Device/Get`

3. **Event Level** (TraceVolume)
   - Individual trace events stored per device
   - Retrieved via `TraceVolume/List` (by device ID)
   - Contains timestamps, codes, descriptions

---

## Implementation Strategy

### Option A: Composite Aggregation (RECOMMENDED)
Since there's no single customer-wide endpoint, we must aggregate device-by-device:

```javascript
async function getOperatorPanelMessages(customerCode, days = 7) {
    // 1. Get all devices for customer
    const devices = await MPSApi.getDevicesByCustomer(customerCode);

    // 2. Calculate date range
    const toDate = new Date();
    const fromDate = new Date(toDate - (days * 24 * 60 * 60 * 1000));

    // 3. For each device, get error messages
    const allMessages = [];
    for (const device of devices) {
        const messages = await MPSApi.makeRequest('Device/ListErrorsMessagesDataHistory', {
            DeviceId: device.Id,
            FromDate: fromDate.toISOString().split('T')[0],
            ToDate: toDate.toISOString().split('T')[0],
            SortColumn: 'Date',
            pageNumber: 1,
            pageRows: 100
        });

        if (messages.success && messages.data) {
            messages.data.forEach(msg => {
                if (msg.AlertCode && msg.AlertDescription) {
                    allMessages.push({
                        deviceId: device.Id,
                        deviceName: device.ExternalIdentifier || device.AssetNumber,
                        code: msg.AlertCode,
                        description: msg.AlertDescription,
                        timestamp: new Date(msg.DateUTC),
                        customerCode: msg.CustomerCode
                    });
                }
            });
        }
    }

    // 4. Sort by timestamp (most recent first)
    allMessages.sort((a, b) => b.timestamp - a.timestamp);

    return allMessages;
}
```

**Pros**:
- Gets actual historical event data
- Shows codes, descriptions, timestamps
- Can filter/aggregate by code type

**Cons**:
- Requires one API call per device (766 devices = 766 calls!)
- May be slow on initial load
- Need to implement caching/batching

---

### Option B: Webhook/Push Notifications
**Status**: NOT FOUND in API documentation

Searched for:
- Webhook endpoints
- Notification endpoints
- Push/subscribe patterns
- Event streaming

**Result**: No push-based APIs found. All data retrieval is pull-based.

---

### Option C: Analytics/Report Export
**Endpoint**: `Analytics/GetReportResult`

**Possibility**: User could create a custom report in MPSM Portal showing traced messages, then call the API to retrieve it.

**Requires**:
- Pre-configured report in MPSM Portal
- Report ID
- May not update in real-time

---

## Recommended Implementation

### Phase 1: Proof of Concept
1. Select top 10 devices with most activity
2. Query error messages for last 7 days
3. Display in timeline card
4. Test performance and data quality

### Phase 2: Optimized Production
1. Implement parallel API calls (batches of 10 devices)
2. Cache results (5-minute TTL)
3. Add filters by message type (jams only, errors only, etc.)
4. Show frequency/repeat counters
5. Add device drill-down

### Phase 3: Advanced Features
1. Real-time alerts for critical messages
2. Pattern detection (same device jamming repeatedly)
3. Trend analysis (increasing jam frequency)
4. Export to CSV/Excel

---

## Message Code Reference

Based on your screenshot and MPSM portal:

| Code | Description |
|------|-------------|
| 3 | Cover Open |
| 8 | Jam |
| 10 | Subunit Life Almost Over |
| ... | (Need to call GetErrorCodes for full list) |

---

## Next Steps

1. ✅ **Create API wrapper function** for `getOperatorPanelMessages()`
2. ✅ **Test with Cape Fear** (766 devices)
3. ✅ **Implement timeline card UI**
4. ✅ **Add pagination/filtering**
5. ✅ **Deploy to CMS dashboard**

---

## Alternative: Ask MPSM Support

Since the portal shows a unified trace view but the API requires per-device queries, there may be an undocumented endpoint. Consider:

**Email**: help@mpsmonitor.com
**Question**: "Is there an API endpoint to retrieve all traced operator panel messages for a customer (not device-by-device)? Similar to the 'Message tracing from operator panel' view in the portal?"

---

## Files Referenced

- `mps-api/swagger.json` - API schema
- `MPSM DOCS/SDK_SOURCE_CODE/` - .NET SDK source code
- `MPSM DOCS/MPS_Monitor_SDK.pdf` - Official SDK documentation
- SDK Enums: `JamConfigItemEnum.cs`, `JamOperationResultEnum.cs`

---

**Last Updated**: 2025-10-24
**Researcher**: Claude (via Claude Code)
