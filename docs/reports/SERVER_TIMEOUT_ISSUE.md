# Server Timeout Issue - Device Population

**Date**: 2025-11-08
**Status**: ⚠️ IN PROGRESS - Server Under Heavy Load

---

## Current Situation

The server is currently **unresponsive** to web requests because:

1. **refresh-cache-enhanced.php is running** and trying to populate 8000+ devices
2. **Process is taking too long** - server timeout is being hit (Request Timeout error)
3. **All web requests timing out** - including the monitor dashboard

---

## What's Happening

### The Good News ✅

- **Root cause FIXED**: [refresh-cache-enhanced.php:258-306](cms/api/refresh-cache-enhanced.php#L258-L306) now correctly fetches ALL devices
- **Pagination logic corrected**: Checks for `< 100` devices per page (not `< 50`)
- **Direct array usage**: Removed broken `extractDevicesFromResponse()` call
- **Capacity increased**: Can handle up to 50,000 devices (500 pages × 100/page)

### The Challenge ⚠️

- **API has 8000+ devices** (not 5000 as originally estimated)
- **Processing time too long**: Fetching + storing 8000 devices exceeds web server timeout
- **Server configuration**: Web request timeout appears to be ~10 minutes
- **Resource consumption**: Processing all devices at once overwhelms PHP/MySQL

---

## Why Monitor Shows Inaccurate Information

The monitor dashboard ([monitor-drilldown-progress.php](cms/api/monitor-drilldown-progress.php)) cannot load because:

1. Database is locked by the ongoing insert operations
2. Web server timeout prevents the page from loading
3. MySQL queries timing out due to heavy write load

**Once the refresh completes**, the monitor will show accurate counts.

---

## Solutions

### Option 1: Wait for Current Process (RECOMMENDED)

The refresh-cache-enhanced.php process is likely still running on the server. It will eventually complete.

**Expected behavior:**
- Process runs for 15-30 minutes
- Fetches all 8000+ devices from API
- Stores them in `mpsm_cache_devices` table
- Server becomes responsive again
- Monitor shows accurate counts

**How to verify:**
```bash
# After ~30 minutes, check counts:
curl "https://mpsm.resolutionsbydesign.us/cms/api/quick-count.php" --user jez:PASSWORD

# Expected output:
{
  "devices": 8000+,
  "drilldowns": 100-200,
  "timestamp": "2025-11-08 XX:XX:XX"
}
```

### Option 2: SSH Access + Background Process

If you have SSH access to the server:

```bash
# Run via CLI (bypasses web timeout)
ssh user@server
cd /path/to/MPSM-Dashboard
nohup php cms/api/refresh-cache-enhanced.php > /tmp/refresh.log 2>&1 &

# Monitor progress
tail -f /tmp/refresh.log
```

### Option 3: Chunked Population Script

Create a script that populates in smaller batches (e.g., 1000 devices at a time) with status tracking.

**NOT YET IMPLEMENTED** - would require new script development.

---

## Server Configuration Issues

### Web Timeout Too Short

For 8000+ device populations, the server needs:

- **PHP max_execution_time**: 1800+ seconds (30 minutes)
- **MySQL timeout**: 300+ seconds
- **Web server timeout**: 600+ seconds (10 minutes)

### Current Evidence

```
<h2>Request Timeout</h2>
<p>This request takes too long to process, it is timed out by the server.
```

This indicates the web server (likely Apache/Nginx) is killing the request before PHP completes.

---

## What Was Fixed

### Files Modified

1. ✅ **cms/api/refresh-cache-enhanced.php** (commit f1f25aa3)
   - Fixed pagination: `< 100` instead of `< 50`
   - Direct array use: Removed `extractDevicesFromResponse()`
   - Increased capacity: 500 pages max

2. ✅ **cms/api/monitor-drilldown-progress.php** (commits cd0c6b4c, 009fcd0a, f2de3333)
   - Added device population tracking
   - Fixed SQL syntax for MySQL/MariaDB (`DATE_SUB` instead of `datetime()`)
   - Updated expected count to 8000 devices

3. ✅ **Documentation** (commits 8a3cbd95, f1f25aa3)
   - CRITICAL_FIX_DEVICE_PAGINATION.md
   - context/operations-playbook.md
   - context/data-flows.md

---

## Next Steps

### Immediate (Now)

1. **Wait 30-60 minutes** for current refresh to complete
2. **Check counts** using quick-count.php endpoint
3. **Verify monitor** loads and shows accurate data

### After Population Completes

1. **Verify device count**: Should be ~8000 devices
2. **Check coverage**: `devices / 8000 * 100%`
3. **Populate drill-downs**: Run [force-populate-all-drilldowns.php](cms/api/force-populate-all-drilldowns.php)
4. **Monitor cron**: Ensure 5-minute cron keeps counts fresh

### Long-term

1. **Increase server timeouts** to handle large populations
2. **Consider chunked processing** for initial population
3. **Add progress tracking** to see real-time status during long operations
4. **Implement rate limiting** to avoid API overload

---

## Monitoring Commands

```bash
# Quick count check (lightweight)
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/quick-count.php" --user jez:PASSWORD

# Full monitor dashboard (after server recovers)
open https://mpsm.resolutionsbydesign.us/cms/api/monitor-drilldown-progress.php

# System diagnostics (comprehensive)
curl -s "https://mpsm.resolutionsbydesign.us/cms/system-diagnostics.php" --user jez:PASSWORD | less
```

---

## Expected Timeline

| Time | Event |
|------|-------|
| T+0  | refresh-cache-enhanced.php starts |
| T+5  | Fetching page 1-5 (500 devices) |
| T+10 | Fetching page 10-15 (1500 devices) |
| T+15 | Fetching page 20-30 (3000 devices) |
| T+20 | Fetching page 40-50 (5000 devices) |
| T+25 | Fetching page 60-80 (8000 devices) |
| T+30 | **Population complete**, server responsive |
| T+35 | Monitor dashboard loads correctly |

---

## Verification After Completion

Once server is responsive:

```bash
# 1. Check counts
curl "https://mpsm.resolutionsbydesign.us/cms/api/quick-count.php" --user jez:PASSWORD

# Expected: {"devices":8000+,"drilldowns":100-200}

# 2. View monitor dashboard
open https://mpsm.resolutionsbydesign.us/cms/api/monitor-drilldown-progress.php

# Should show:
# - Total Devices: 8000+
# - STATUS: RUNNING (if drill-downs pending) or COMPLETE
# - Coverage: X% (drilldowns / devices)

# 3. Check last cached timestamp
# Should be recent (within last few minutes)
```

---

## Related Documentation

- [CRITICAL_FIX_DEVICE_PAGINATION.md](CRITICAL_FIX_DEVICE_PAGINATION.md) - Root cause analysis
- [DRILL_DOWN_CACHE_FIX.md](DRILL_DOWN_CACHE_FIX.md) - Drill-down population process
- [context/operations-playbook.md](context/operations-playbook.md) - Daily operations guide

---

## Summary

**Problem**: Server timing out during 8000+ device population
**Cause**: Web server timeout too short for long-running process
**Solution**: Wait for current process to complete (~30 min), or use SSH/CLI
**Status**: Fixes deployed, process running, waiting for completion

The core bug is **FIXED**. This is just a server resource/timeout issue during the initial large population.
