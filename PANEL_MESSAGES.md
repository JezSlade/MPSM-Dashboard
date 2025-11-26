# Panel Message Callback System

**Status**: ✅ LIVE AND OPERATIONAL
**Messages Captured**: 9+ (and growing)
**Deployment Date**: November 4-5, 2025

---

## Overview

The Panel Message Callback System is a real-time monitoring solution that captures device panel messages sent from the MPS Monitor system. When printers/devices send maintenance alerts, panel messages, or diagnostic events, they're instantly captured, stored, and made available for monitoring.

**This is MASSIVE** because it provides:
- Real-time device health monitoring
- Proactive maintenance alerts
- Complete audit trail of all device events
- Foundation for automated response workflows

---

## Architecture

### 1. Callback Receiver
**File**: [`mps-api/callbacks/panel-message.php`](mps-api/callbacks/panel-message.php)

**Purpose**: Webhook endpoint that receives POST requests from MPS Monitor

**Key Features**:
- Validates shared secret (`mpsm-panel-message-v1`)
- Auto-creates database table on first run
- Stores complete JSON payload for analysis
- Logs events to daily rotating log files
- Returns acknowledgement to sender

**Database Schema**:
```sql
CREATE TABLE mpsm_panel_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    customer_code VARCHAR(100),
    customer_description VARCHAR(255),
    device_serial VARCHAR(150),
    maintenance_alert_code VARCHAR(150),
    maintenance_alert_id VARCHAR(150),
    panel_configuration VARCHAR(255),
    source_ip VARCHAR(45),
    payload JSON NOT NULL,
    processed TINYINT(1) DEFAULT 0,
    INDEX idx_received_at (received_at),
    INDEX idx_customer_code (customer_code),
    INDEX idx_device_serial (device_serial),
    INDEX idx_processed (processed)
)
```

**Security**:
- Shared secret validation
- IP logging for audit trail
- JSON-only content type enforcement
- POST-only method restriction

### 2. Monitoring UI
**Page**: [`cms/command-center.php?tab=panel`](cms/command-center.php)

**Purpose**: Real-time dashboard for viewing incoming panel messages (Panel Stream tab)

**Features**:
- Live table view with auto-refresh (30 seconds)
- Time window filtering (1h, 6h, 12h, 24h, 48h, 72h, 7 days)
- Limit control (50, 100, 200, 300, 500 messages)
- Payload viewer modal (formatted JSON) via `api/get-panel-message.php`
- Clean, professional UI matching dashboard theme

**Access**:
- Direct link: `https://mpsm.resolutionsbydesign.us/cms/command-center.php?tab=panel`
- Dashboard header: Satellite dish icon (📡)
- Requires authentication

### 3. Retrieval API
**File**: [`cms/api/get-panel-messages.php`](cms/api/get-panel-messages.php)

**Purpose**: Fetch panel messages with filtering

**Parameters**:
- `limit` (1-500, default: 200)
- `hours` (1-168 for time window filtering)

**Response Format**:
```json
{
  "success": true,
  "messages": [
    {
      "id": 9,
      "received_at": "2025-11-05 11:30:58",
      "customer_code": "TEST123",
      "customer_description": "Test Customer",
      "device_serial": "TESTSERIAL123",
      "maintenance_alert_code": "TEST_ALERT",
      "maintenance_alert_id": "TEST_ID_001",
      "panel_configuration": "Test Panel Config",
      "processed": false,
      "payload": {
        "callbackSecret": "mpsm-panel-message-v1",
        "customer": {
          "code": "TEST123",
          "description": "Test Customer"
        },
        "installedProduct": {
          "serialNumber": "TESTSERIAL123"
        },
        "maintenanceAlert": {
          "code": "TEST_ALERT",
          "id": "TEST_ID_001",
          "panelConfiguration": "Test Panel Config"
        }
      }
    }
  ]
}
```

### 4. Frontend JavaScript
**File**: [`cms/assets/panel-messages.js`](cms/assets/panel-messages.js)

**Purpose**: Powers the monitoring UI

**Capabilities**:
- Auto-refresh every 30 seconds
- Manual refresh on demand
- Time window and limit controls
- Payload modal viewer
- Responsive table rendering
- Error handling with user feedback

### 5. Diagnostics Script
**File**: [`scripts/panel-message-diagnostics.ps1`](scripts/panel-message-diagnostics.ps1)

**Purpose**: Command-line tool for testing and monitoring

**Usage**:
```powershell
# Default (20 messages)
.\scripts\panel-message-diagnostics.ps1

# Custom limit
.\scripts\panel-message-diagnostics.ps1 -Limit 50

# Custom credentials
.\scripts\panel-message-diagnostics.ps1 -Username admin -Password admin
```

**Output**:
- Latest message details
- Recent message summary table
- Processed/unprocessed statistics

---

## Testing & Validation

### Live Testing Results ✅

**Test 1: Diagnostics Script**
```
ID: 9
Received: 2025-11-05 11:30:58
Customer: TEST123
Device: TESTSERIAL123
Alert: TEST_ALERT

Messages in DB: 9
Processed: 0
Unprocessed: 9
```

**Test 2: Callback Endpoint**
```bash
curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/callbacks/panel-message.php \
  -H "Content-Type: application/json" \
  -d '{
    "callbackSecret":"mpsm-panel-message-v1",
    "customer":{"code":"TEST123","description":"Test Customer"},
    "installedProduct":{"serialNumber":"TESTSERIAL123"},
    "maintenanceAlert":{"code":"TEST_ALERT","id":"TEST_ID_001","panelConfiguration":"Test Panel Config"}
  }'

Response: {"success":true,"stored":true}
```

**Test 3: Security Validation**
```bash
# Test with wrong secret
curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/callbacks/panel-message.php \
  -H "Content-Type: application/json" \
  -d '{"callbackSecret":"wrong-secret"}'

Response: {"success":false,"error":"Unauthorized"} (HTTP 401)
```

### Battle Test Integration

Added 5 new tests to [`battle_test.html`](battle_test.html):

1. **Panel monitor page loads** - Verifies UI accessibility
2. **Panel callback endpoint exists** - Tests security validation
3. **Panel messages have valid structure** - Validates data format
4. **Panel messages can be retrieved** - Tests API functionality
5. **Panel messages JS loads** - Verifies frontend assets

**Run Tests**: Open `battle_test.html` and click "📡 Panel Tests Only"

---

## Message Flow

```
┌─────────────────┐
│   MPS Device    │
│   (Printer)     │
└────────┬────────┘
         │ Panel Message Event
         │ (Toner low, jam, etc.)
         ↓
┌─────────────────┐
│  MPS Monitor    │
│   Callback      │
└────────┬────────┘
         │ HTTP POST (JSON)
         │ + Shared Secret
         ↓
┌─────────────────┐
│ panel-message   │
│     .php        │
└────────┬────────┘
         │ Validate Secret
         │ Parse JSON
         │ Extract Fields
         ↓
┌─────────────────┐
│    MySQL DB     │
│ panel_messages  │
└────────┬────────┘
         │
         ↓
┌─────────────────┐     ┌──────────────────┐
│  Monitor UI     │────→│  Diagnostics     │
│  (Live View)    │     │  Script (CLI)    │
└─────────────────┘     └──────────────────┘
```

---

## Current State

### Captured Messages (Sample)

| ID | Received | Customer | Serial | Alert Code |
|----|----------|----------|--------|------------|
| 9 | 2025-11-05 11:30:58 | TEST123 | TESTSERIAL123 | TEST_ALERT |
| 8 | 2025-11-04 16:53:14 | BATCH5 | BATCHSN5 | BATCHCODE5 |
| 7 | 2025-11-04 16:53:13 | BATCH4 | BATCHSN4 | BATCHCODE4 |
| 6 | 2025-11-04 16:53:12 | BATCH3 | BATCHSN3 | BATCHCODE3 |
| 5 | 2025-11-04 16:53:12 | BATCH2 | BATCHSN2 | BATCHCODE2 |

### Processing Status
- **Total Messages**: 9
- **Processed**: 0 (ready for workflow implementation)
- **Unprocessed**: 9

---

## Integration with Dashboard

### Navigation
Added satellite dish icon (📡) to main dashboard header:

**Location**: [`cms/index.php:46-48`](cms/index.php#L46-L48)

```html
<a href="panel-message-monitor.php" class="btn-icon" title="Panel Messages">
    <i class="fas fa-satellite-dish"></i>
</a>
```

### Future Dashboard Integration Ideas
1. **Live Feed Widget** - Show latest 5 messages on dashboard
2. **Alert Counter** - Badge showing unprocessed messages
3. **Device Correlation** - Link messages to device deep-dive modal
4. **Auto-Response** - Trigger actions based on alert codes

---

## Payload Examples

### Example 1: Toner Low Alert
```json
{
  "callbackSecret": "mpsm-panel-message-v1",
  "customer": {
    "code": "ACME001",
    "description": "Acme Corporation"
  },
  "installedProduct": {
    "serialNumber": "JPXYZ123456"
  },
  "maintenanceAlert": {
    "code": "TONER_LOW_BLACK",
    "id": "ALT-001",
    "panelConfiguration": "Black Toner at 10%"
  }
}
```

### Example 2: Paper Jam
```json
{
  "callbackSecret": "mpsm-panel-message-v1",
  "customer": {
    "code": "ACME001",
    "description": "Acme Corporation"
  },
  "installedProduct": {
    "serialNumber": "JPXYZ123456"
  },
  "maintenanceAlert": {
    "code": "PAPER_JAM_TRAY2",
    "id": "ALT-002",
    "panelConfiguration": "Paper jam detected in Tray 2"
  }
}
```

---

## Logging

### Application Logs
**Location**: `mps-api/logs/panel-message-YYYY-MM-DD.log`

**Format**: JSON lines
```json
{"time":"2025-11-05 11:30:58","customer":"TEST123","serial":"TESTSERIAL123","alert_code":"TEST_ALERT"}
```

**Rotation**: Daily (automatic)

### Database Audit Trail
- `received_at` timestamp for every message
- `source_ip` for request origin tracking
- `processed` flag for workflow state
- Full `payload` JSON preserved

---

## Configuration

### Shared Secret
**Current**: `mpsm-panel-message-v1`
**Location**: [`mps-api/callbacks/panel-message.php:41`](mps-api/callbacks/panel-message.php#L41)

**To Change**:
1. Update `$expectedSecret` in `panel-message.php`
2. Update MPS Monitor callback configuration
3. Update test scripts/documentation

### Database
- **Table**: `mpsm_panel_messages`
- **Prefix**: Configured via `DB_PREFIX` constant
- **Auto-Creation**: Yes (on first callback)
- **Indexes**: Optimized for time-based and device queries

---

## What's Next?

### Immediate Opportunities
1. **Device Correlation** - Link messages to device records via serial number
2. **Alert Routing** - Email/SMS notifications for critical alerts
3. **Workflow Automation** - Auto-create service tickets for specific codes
4. **Analytics Dashboard** - Trends, patterns, alert frequency by customer

### Future Enhancements
1. **Processing Pipeline** - Mark messages as processed after action taken
2. **Alert Rules Engine** - Configure responses per alert code
3. **Historical Analysis** - Reports on device reliability
4. **Predictive Maintenance** - Pattern detection for proactive service

---

## Files Summary

| File | Purpose | Status |
|------|---------|--------|
| `mps-api/callbacks/panel-message.php` | Callback receiver | ✅ Live |
| `cms/panel-message-monitor.php` | Monitoring UI | ✅ Live |
| `cms/api/get-panel-messages.php` | Retrieval API | ✅ Live |
| `cms/assets/panel-messages.js` | Frontend logic | ✅ Live |
| `scripts/panel-message-diagnostics.ps1` | CLI diagnostics | ✅ Working |
| `PANEL_MESSAGES.md` | This documentation | ✅ Complete |

---

## Testing Checklist

- ✅ Callback endpoint accepts valid POST
- ✅ Callback endpoint rejects invalid secret
- ✅ Database table auto-creates
- ✅ Messages stored with all fields
- ✅ Monitor UI loads and displays messages
- ✅ API retrieval works with filters
- ✅ Frontend auto-refresh functional
- ✅ Payload modal displays formatted JSON
- ✅ Diagnostics script retrieves messages
- ✅ Battle test suite updated
- ✅ Dashboard navigation link added

---

## Support & Maintenance

### Monitoring
- Check `mps-api/logs/panel-message-*.log` for daily activity
- Run diagnostics script to verify message capture
- Monitor database growth (consider archival after 90 days)

### Troubleshooting
**No messages appearing**:
1. Check MPS Monitor callback configuration
2. Verify shared secret matches
3. Check `mps-api/logs/` for errors
4. Test callback endpoint with curl

**UI not loading**:
1. Check authentication/session
2. Verify `panel-messages.js` loads (Network tab)
3. Check browser console for errors

**Slow queries**:
1. Database indexes are in place
2. Consider limiting default time window
3. Archive old messages to separate table

---

## Credits

Developed as part of the MPSM Dashboard real-time monitoring initiative. This system provides the foundation for proactive device management and automated maintenance workflows.

**Version**: 1.0
**Last Updated**: November 5, 2025
**Next Review**: When processing workflows are implemented

