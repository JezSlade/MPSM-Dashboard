# Remaining Tasks - November 24, 2025

## Immediate Actions Required

### 1. Populate Alert Definitions ⚠️ HIGH PRIORITY
**Status:** Script created but needs manual execution
**Action Required:**
1. Log into MPSM Dashboard at https://mpsm.resolutionsbydesign.us
2. Navigate to: `https://mpsm.resolutionsbydesign.us/cms/api/populate-alert-definitions.php`
3. Verify success message shows `inserted: 7` or similar
4. Refresh dashboard to see "Paper Jam" instead of "Alert 808"

**What it does:**
- Inserts human-readable descriptions for common alert codes:
  - 808 → Paper Jam
  - 024 → Toner Low
  - 025 → Toner Empty
  - 026 → Waste Toner Full
  - 116 → Service Required
  - 200 → Cover Open
  - 400 → Offline

**File:** cms/api/populate-alert-definitions.php (already deployed)

---

### 2. Panel Message Monitor Customer Filtering ⚠️ MEDIUM PRIORITY
**User Requirement:** "the red pill on the top of the dashboard still links to display the system alerts for ALL customers. This pill should only show the number of alerts for the customer currently displayed on the dashboard and link to show every alert for only that customer."

**Current State:**
- Panel Message Monitor shows messages from ALL customers
- No customer filter in UI
- Table has Customer column but no way to filter by it

**Required Changes:**
1. **Add Badge to index.php:**
   - Locate panel-message-monitor link (line 50-52 in cms/index.php)
   - Add `<span id="panel-alert-badge">` with count
   - Make badge visible only when count > 0

2. **Update app.js:**
   - Function `loadPanelAlertBadge()` exists (lines 4184-4210)
   - Already filters by customer: `api/get-panel-alert-count.php?customerCode=${state.customerCode}`
   - Need to make badge visible and link properly

3. **Add Customer Filter to panel-message-monitor.php:**
   - Add customer dropdown to monitor-controls section (after line 219)
   - Update panel-messages.js to respect customer filter
   - Pre-select customer from URL parameter if provided

4. **Update Badge Click Behavior:**
   - Make badge link to panel-message-monitor.php with customer filter applied
   - Example: `panel-message-monitor.php?customerCode=ABC123&hours=24`

**Files to Modify:**
- cms/index.php - Add badge HTML
- cms/assets/app.js - Show badge and add customer param to link
- cms/panel-message-monitor.php - Add customer filter dropdown
- cms/assets/panel-messages.js - Implement customer filtering

---

### 3. Handle 429 Rate Limit Errors ⚠️ MEDIUM PRIORITY
**User Report:** Devices card displays error:
```
Failed to fetch devices: mps-api returned HTTP 429: { "success": false, "error": "Rate limit exceeded", "retry_after": 60 }
```

**Required Changes:**
1. **Update app.js device fetch logic:**
   - Detect 429 responses
   - Show user-friendly message: "Too many requests, retrying in 60s..."
   - Implement automatic retry after `retry_after` seconds
   - Add exponential backoff for subsequent 429s

2. **Add rate limit handling to other API calls:**
   - Search devices
   - Fetch all devices
   - Panel messages

**Files to Modify:**
- cms/assets/app.js - Add 429 detection and retry logic
- cms/assets/hero-notifications.js - Handle 429 on notification load
- cms/assets/panel-messages.js - Handle 429 on message load

**Example Implementation:**
```javascript
async function fetchWithRetry(url, options = {}, maxRetries = 3) {
    for (let attempt = 0; attempt < maxRetries; attempt++) {
        const response = await fetch(url, options);

        if (response.status === 429) {
            const data = await response.json();
            const retryAfter = (data.retry_after || 60) * 1000;
            console.warn(`Rate limited, retrying in ${retryAfter/1000}s...`);
            await new Promise(resolve => setTimeout(resolve, retryAfter));
            continue;
        }

        return response;
    }

    throw new Error('Max retries exceeded');
}
```

---

### 4. Mobile Version Sync ⚠️ LOW PRIORITY
**User Request:** "make sure the mobile version of the site is in sync with these updates"

**Files to Check:**
- cms/mobile.php - May have separate notification rendering
- Check if mobile uses same hero-notifications.js or has separate implementation
- Verify mobile dashboard shows correct alert display

**Action:**
1. Test mobile.php on actual mobile device or responsive mode
2. Verify System Alerts display correctly (no serial numbers)
3. Ensure duplicate IP cards work
4. Test panel message monitor on mobile

---

## Technical Debt & Improvements

### Device Enrichment Data
**Issue:** cache_devices table doesn't exist, causing device metadata (Equipment ID, Model, Department) to be unavailable

**Options:**
1. **Create cache_devices table** and populate it from MPS API
2. **Store enrichment data** when notifications are created
3. **Fetch enrichment on-demand** when displaying notifications (adds latency)

**Recommended:** Option 2 - Store equipment_id, model, department in dashboard_notifications table when notifications are created

**Files to Modify:**
- mps-api/callbacks/command-center-engine.php (createDashboardNotification function)
- Add equipment_id, model, department columns to dashboard_notifications table
- Extract these fields from panel message payload during notification creation

---

## Deployment Checklist

Before marking as complete, verify:
- [ ] Alert definitions populated (808 shows as "Paper Jam")
- [ ] System Alerts cards show human-readable names, not serial numbers
- [ ] Duplicate IP cards display all 37 duplicates
- [ ] Panel Message Monitor has customer filter
- [ ] Red pill badge filters by current customer
- [ ] 429 rate limit errors handled gracefully
- [ ] Mobile version tested and working
- [ ] All changes committed to git
- [ ] Session.md updated with final status

---

## Contact & Context

**Session Date:** November 24, 2025
**Primary Issues Fixed:**
- System Alerts displaying serial numbers → Now shows customer codes or "Device Alert"
- Alert codes showing raw numbers → Now shows "Alert 808" (until definitions populated)

**Key Files Changed:**
- cms/assets/hero-notifications.js ✅ Deployed
- cms/api/populate-alert-definitions.php ✅ Deployed (needs manual execution)

**Pending:**
- Panel Message Monitor customer filtering
- 429 rate limit handling
- Mobile verification
