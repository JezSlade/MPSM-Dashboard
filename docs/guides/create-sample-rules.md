# Command Center - Create Sample Notification Rules

The Command Center is **fully functional** and processing all 913+ incoming panel messages. However, **no notification rules have been created yet**, so no notifications are being displayed.

## Current Status

✅ Backend Command Center Engine - WORKING (processing all callbacks)
✅ Hero Notifications Widget - READY (waiting for notifications to display)
✅ Command Center UI - DEPLOYED (accessible via shield icon)
✅ API Endpoints - FUNCTIONAL (all CRUD operations working)
❌ **Notification Rules - NONE CREATED YET** ← This is why you see no alerts

## How to Activate Notifications (2 Simple Steps)

### Step 1: Open Command Center
Navigate to: https://mpsm.resolutionsbydesign.us/cms/command-center.php

### Step 2: Create Your First Rule

Click **"Create New Rule"** and use these example configurations:

---

### Example Rule 1: Monitor All Incoming Alerts

**Basic Information:**
- Name: `All Panel Messages Monitor`
- Severity: `Critical`
- Description: `Catch all incoming panel messages for real-time monitoring`

**Pattern Matching:**
- Alert Code Pattern: `%` (% = wildcard, matches everything)
- Device Serial Pattern: _(leave blank)_
- Customer Code Pattern: _(leave blank)_

**Notification Template:**
- Title: `{severity} Alert - {device} has {alert}`
- Message: `Device {device} triggered {alert}. Customer: {customer}`

**Actions:**
- ✅ Show in Dashboard Hero Header
- Auto-dismiss: `24` hours

Click **Save Rule**

---

### Example Rule 2: Detect Frequent Alerts (High Priority)

**Basic Information:**
- Name: `High Frequency Device Alerts`
- Severity: `High`
- Description: `Alert when same device triggers multiple times quickly`

**Pattern Matching:**
- _(Leave all blank to match any alert)_

**Frequency Threshold:**
- Occurrence Count: `3`
- Time Window (hours): `1`
- Frequency Type: `Same Device`

**Notification Template:**
- Title: `Frequent Alerts - {device}`
- Message: `{device} has triggered {alert} {count} times in the past {window}`

**Actions:**
- ✅ Show in Dashboard Hero Header
- Auto-dismiss: `12` hours

Click **Save Rule**

---

### Example Rule 3: Specific Alert Code (if you know the codes)

If you know specific alert codes from the payload debugger (e.g., JAM, ERROR, FAULT):

**Pattern Matching:**
- Alert Code Pattern: `JAM%` (matches JAM-001, JAM-002, etc.)
- Or: `E-%` (matches E-001, E-002, etc.)
- Or exact match: `CRITICAL_FAULT`

---

## What Happens Next

1. **Immediately:** Next incoming panel message that matches a rule → notification created
2. **Dashboard Hero Header:** Top 5 priority notifications appear as colored banners
3. **Notification Badge:** Header shows count of active notifications
4. **Command Center:** All notifications visible in "Active Notifications" tab
5. **Auto-Refresh:** Both dashboard and Command Center update every 10-30 seconds

## Verify It's Working

After creating a rule, wait 30-60 seconds for the next panel message callback:

1. **Check Command Center** → "Active Notifications" tab should show new notifications
2. **Check Dashboard** → Hero header should display colored notification banners
3. **Check Header Badge** → Shield icon should show notification count

## Why No Alerts Are Showing Yet

The system is designed to be **user-controlled**. It will NOT create notifications until YOU define rules that specify:
- What alerts to monitor (pattern matching)
- What severity level to assign
- What triggers a notification (single event vs. frequency threshold)
- What message to display

This prevents notification spam and ensures only relevant alerts appear on your dashboard.

## Next Action

**CREATE AT LEAST ONE RULE** using the examples above, then watch the dashboard hero header populate with live notifications as panel messages arrive!

---

**Quick Start:** Use Example Rule 1 (catch-all with `%` wildcard) to immediately see ALL incoming panel messages as notifications.
