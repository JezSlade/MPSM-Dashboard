# Alert System Setup Instructions

**Date**: 2025-11-28
**Status**: Ready for execution

## Overview

The alert system has been audited and a CRITICAL pattern matching bug has been fixed. Now we need to:
1. Insert 5 common-sense alert rules into the database
2. Reprocess existing panel messages to populate notifications

## Critical Fix Applied

**File**: [mps-api/callbacks/command-center-engine.php](../mps-api/callbacks/command-center-engine.php:246-270)
**Commit**: e2a9149e
**Issue**: Pattern matching was completely broken - wildcards (%, _) never matched
**Impact**: ALL wildcard-based rules have been non-functional since system inception
**Fix**: Manually escape regex special chars while preserving % and _ for wildcard conversion
**Test Results**: 2/8 tests passed → 8/8 tests passed ✅

## Setup URLs

### Step 1: Check Current Status
```
https://mpsm.resolutionsbydesign.us/cms/setup-alerts.php?action=status
```

### Step 2: Insert Alert Rules
```
https://mpsm.resolutionsbydesign.us/cms/setup-alerts.php?action=insert_rules
```

**This will insert 5 rules:**
1. **Repeated JAM Alerts** (JAM%, 3 in 24h, same device, high)
2. **Emergency Stop Pattern** (E-%, 2 in 12h, same customer, critical)
3. **Persistent Sensor Failures** (SENS%, 5 in 48h, same device, high)
4. **Widespread Communication Loss** (COMM%, 10 in 1h, same alert, critical)
5. **Motor Overload Pattern** (MOT%, 4 in 24h, same device, high)

### Step 3: Reprocess Panel Messages
```
https://mpsm.resolutionsbydesign.us/cms/setup-alerts.php?action=reprocess
```

**Optional**: Limit number of messages to process (default 500):
```
https://mpsm.resolutionsbydesign.us/cms/setup-alerts.php?action=reprocess&limit=1000
```

### Step 4: View Results
```
https://mpsm.resolutionsbydesign.us/cms/command-center.php?tab=notifications
```

## Alternative: SQL Direct Execution

If web access isn't available, execute the SQL file directly:

**File**: [insert-alert-rules.sql](../insert-alert-rules.sql)

```sql
-- Execute via MySQL CLI or phpMyAdmin:
SOURCE /path/to/insert-alert-rules.sql;
```

## Expected Outcome

After executing steps 2 and 3:
- 5 notification rules will be active in the system
- Historical panel messages will be processed against these rules
- Notifications will appear for devices that meet the frequency thresholds
- Dashboard hero notifications will display top 6 priority alerts
- Command Center Notifications tab will show full list

## Verification Checklist

- [ ] Access Step 1 URL - verify 0 active rules initially
- [ ] Access Step 2 URL - verify "Inserted: 5" message
- [ ] Access Step 1 URL again - verify 5 active rules listed
- [ ] Access Step 3 URL - observe reprocessing progress
- [ ] Note "Notifications created: X" count
- [ ] Access Step 4 URL - verify notifications appear
- [ ] Check dashboard hero notifications section
- [ ] Verify notification severity colors (critical=red, high=orange)
- [ ] Test device modal click (should open device details)

## Troubleshooting

### No notifications created after reprocessing
**Cause**: No devices meet the frequency thresholds in recent history
**Solution**: This is normal - rules only trigger when thresholds are exceeded

### "No active notification rules found" error
**Cause**: Step 2 (insert rules) wasn't executed or failed
**Solution**: Re-run Step 2 URL and verify "Inserted: 5" message

### Permission denied
**Cause**: Not authenticated
**Solution**: Log in to the MPSM dashboard first, then access URLs

## Files Created/Modified

### Created
- [cms/setup-alerts.php](../cms/setup-alerts.php) - Web interface for setup
- [insert-alert-rules.sql](../insert-alert-rules.sql) - SQL version
- [context/alert-setup-instructions.md](alert-setup-instructions.md) - This file

### Modified
- [mps-api/callbacks/command-center-engine.php](../mps-api/callbacks/command-center-engine.php) - Fixed matchesPattern()

### Existing (for reference)
- [reprocess-panel-messages.php](../reprocess-panel-messages.php) - Original CLI script
- [insert-alert-rules.php](../insert-alert-rules.php) - Original CLI script (requires PHP MySQL extension)

## Next Steps After Setup

1. Monitor Command Center → Notifications tab for 24-48 hours
2. Adjust frequency thresholds if too noisy or too quiet
3. Add additional rules for customer-specific alert codes
4. Configure email notifications (requires SMTP setup)
5. Implement mobile notification display (currently missing)
6. Enable notification badge in desktop header (currently hidden)

## Performance Notes

- Reprocessing 500 messages takes ~10-30 seconds (depends on rules complexity)
- Use `limit` parameter to process more or fewer messages
- Default limit of 500 is safe for web execution without timeout
- For bulk reprocessing (10k+ messages), use CLI script with set_time_limit(0)

## Security

- All URLs require authentication (redirects to login if not authenticated)
- Uses prepared statements (SQL injection protected)
- Pattern matching escapes regex special chars (ReDoS protected)
- created_by tracks which user inserted rules

---

**Ready to execute**: Visit Step 1 URL first to verify system status, then proceed with Steps 2-4.
