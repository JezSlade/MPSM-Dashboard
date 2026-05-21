# EXECUTE NOW: Alert System Setup

> Historical runbook. This document records a previous alert setup task and is not the current deployment procedure. For current production deployment use `docs/operations/DEPLOY-INSTRUCTIONS.md`, `scripts/ftp_backup.py`, `scripts/ftp_deploy.py`, and `scripts/live_smoke.py`.

**Status**: Ready for immediate execution
**Estimated Time**: 5 minutes
**Prerequisites**: Logged into MPSM Dashboard

---

## What Was Done

### Critical Bug Fixed ✅
**Commit**: e2a9149e
**File**: [mps-api/callbacks/command-center-engine.php](../../mps-api/callbacks/command-center-engine.php) (lines 246-270 in the original report)
**Issue**: Pattern matching completely broken - ALL wildcard rules (JAM%, E-%, etc.) never matched
**Impact**: Notification system non-functional for wildcard patterns since inception
**Fix**: Rewrote matchesPattern() to properly handle SQL LIKE wildcards (% and _)
**Tests**: 2/8 passed → 8/8 passed ✅

### Setup Tools Created ✅
**Commit**: 2e19c1c0

1. **Web Interface**: [cms/setup-alerts.php](../../cms/setup-alerts.php)
   - Insert 5 common-sense alert rules
   - Reprocess panel messages to populate notifications
   - Check system status

2. **SQL Files**: Backup and direct execution options
   - [insert-alert-rules.sql](../../database/migrations/insert-alert-rules.sql) - Pure SQL
   - [context/common-sense-alert-rules.sql](../../context/common-sense-alert-rules.sql) - Documented backup

3. **Documentation**: [context/alert-setup-instructions.md](../../context/alert-setup-instructions.md)

---

## EXECUTE THESE URLS (in order)

### Step 1: Check Status
```
https://mpsm.resolutionsbydesign.us/cms/setup-alerts.php?action=status
```
**Expected**: Shows 0 active rules, X total panel messages

### Step 2: Insert Alert Rules
```
https://mpsm.resolutionsbydesign.us/cms/setup-alerts.php?action=insert_rules
```
**Expected**: "Inserted: 5" message with rule IDs

### Step 3: Reprocess Panel Messages
```
https://mpsm.resolutionsbydesign.us/cms/setup-alerts.php?action=reprocess
```
**Expected**: Processing log with "Notifications created: X" summary

### Step 4: View Notifications
```
https://mpsm.resolutionsbydesign.us/cms/command-center.php?tab=notifications
```
**Expected**: Active notifications displayed by severity (if thresholds met)

---

## What These Rules Do

### 1. Repeated JAM Alerts (HIGH)
- **Pattern**: `JAM%` matches JAM-001, JAM-002, JAMMING, etc.
- **Trigger**: 3 occurrences in 24 hours from same device
- **Why**: Indicates mechanical blockages, worn components, sensor malfunctions

### 2. Emergency Stop Pattern (CRITICAL)
- **Pattern**: `E-%` matches E-001, E-STOP, E-EMERGENCY, etc.
- **Trigger**: 2 occurrences in 12 hours at same customer location
- **Why**: Multiple e-stops suggest safety concerns or operator training issues

### 3. Persistent Sensor Failures (HIGH)
- **Pattern**: `SENS%` matches SENS-001, SENSOR-FAIL, etc.
- **Trigger**: 5 occurrences in 48 hours from same device
- **Why**: Repeated sensor errors indicate hardware failure or wiring issues

### 4. Widespread Communication Loss (CRITICAL)
- **Pattern**: `COMM%` matches COMM-LOSS, COMM-ERROR, etc.
- **Trigger**: 10 occurrences in 1 hour across all devices (same alert type)
- **Why**: Multiple devices losing communication indicates network infrastructure failure

### 5. Motor Overload Pattern (HIGH)
- **Pattern**: `MOT%` matches MOT-001, MOTOR-OVERLOAD, etc.
- **Trigger**: 4 occurrences in 24 hours from same device
- **Why**: Recurring overload suggests mechanical binding or motor bearing wear

---

## Commits Ready to Deploy

You have 3 commits ready to push to production:

```bash
git log --oneline -3
```

1. **2e19c1c0** - Add alert system setup tools and common-sense notification rules
2. **e2a9149e** - CRITICAL: Fix pattern matching for SQL LIKE wildcards (%, _)
3. **16a88e1b** - Command Center Phase 2&3: JS consolidation + single message endpoint + paging

**Deploy these commits:**
```bash
git push origin main
```

---

## What Happens Next

### Immediate (after executing URLs above)
1. 5 alert rules will be active in the database
2. Last 500 panel messages will be reprocessed
3. Notifications will be created for devices meeting frequency thresholds
4. Dashboard hero notifications will display top 6 priority alerts
5. Command Center Notifications tab will show full list

### Ongoing Monitoring
- Notifications auto-refresh every 30 seconds (dashboard) / 10 seconds (Command Center)
- New panel messages automatically trigger rule evaluation
- Auto-dismiss after configured hours (24-72h depending on rule)
- Deduplication prevents duplicate notifications for same device+alert+rule

### If No Notifications Appear
**This is NORMAL** - it means no devices have exceeded the frequency thresholds recently.

The system is working correctly if:
- Step 2 shows "Inserted: 5"
- Step 3 shows "Messages processed: 500"
- Rules appear in Command Center → Rules tab

Notifications will only appear when a device triggers the threshold (e.g., 3 JAM alerts in 24h).

---

## Verification Checklist

After executing all 4 URLs:

- [ ] Step 1 initially shows "Active notification rules: 0"
- [ ] Step 2 shows "Inserted: 5" with rule names
- [ ] Step 1 again shows "Active notification rules: 5" with list
- [ ] Step 3 shows processing output and "Notifications created: X"
- [ ] Step 4 Command Center opens without errors
- [ ] Dashboard (https://mpsm.resolutionsbydesign.us/cms/index.php) shows hero notifications section
- [ ] No JavaScript errors in browser console (F12)

---

## Troubleshooting

### "No active notification rules found" in Step 3
**Fix**: Go back to Step 2, re-run insert_rules

### "could not find driver" error
**Fix**: Use the web URLs above (don't run PHP CLI scripts)

### Permission denied / Login required
**Fix**: Log into MPSM Dashboard first, then access URLs in same browser

### Reprocessing timeout (500 error)
**Fix**: Reduce message limit:
```
?action=reprocess&limit=100
```

---

## Support Files

- **Full audit report**: [context/forensic-audit-findings.md](../../context/forensic-audit-findings.md)
- **Alert system audit**: [context/alert-system-audit.md](../../context/alert-system-audit.md)
- **Setup instructions**: [context/alert-setup-instructions.md](../../context/alert-setup-instructions.md)
- **Test scripts**: [test-pattern-matching.php](../../tests/php/test-pattern-matching.php), [test-pattern-fix.php](../../tests/php/test-pattern-fix.php)

---

**START NOW**: Copy Step 1 URL and paste into browser (while logged into MPSM Dashboard)
