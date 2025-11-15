# Deployment Instructions

## Critical Fix Ready for Deployment

**Commit:** 5501c3c - CRITICAL FIX: Replace non-existent callMpsGetDeviceList() with callMPSMAPI()

This fix resolves the bug that has been preventing ANY devices from being cached for the past 2 weeks.

---

## Option 1: Manual Deployment via SSH (Recommended)

```bash
ssh resolut7@mpsm.resolutionsbydesign.us
cd public_html
git pull origin main
```

---

## Option 2: Automated Deployment (One-time setup required)

### Step 1: Add deployment secret to config.php

Edit `cms/config.php` on the server and add this line after the "Security" section:

```php
// Deployment Configuration
define('DEPLOY_SECRET', 'mpsm_deploy_4089ad30f8ef64274f6d015e1aa15fad');
```

### Step 2: Pull code manually once to get deploy.php

```bash
cd public_html
git pull origin main
```

### Step 3: Future deployments via HTTP

```bash
curl "https://mpsm.resolutionsbydesign.us/deploy.php?secret=mpsm_deploy_4089ad30f8ef64274f6d015e1aa15fad"
```

---

## Option 3: Wait for automatic pull (Slowest)

The CRON job may have auto-pull logic, or you can wait until the next deployment cycle.

**Not recommended** - The bug is critical and should be deployed ASAP.

---

## Verification After Deployment

Once deployed, verify the fix is working:

### 1. Reset cache state
```bash
curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php?action=start"
```

### 2. Check status every 30 seconds
```bash
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php?action=status" | grep current_page
```

You should see `current_page` incrementing (1, 2, 3, 4...) instead of being stuck at 1.

### 3. Check device count growing
```bash
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/cache-status-report.php" | head -30
```

You should see "Total Devices Cached" growing from 200 → 300 → 500 → 1000+ within the first few minutes.

---

## Expected Results

- **Device pages:** 528 pages @ 100 devices/page = 52,800 devices
- **Processing time:** ~30-60 minutes (CRON runs every minute)
- **Drill-downs:** ~5,000 devices will get full drill-down data
- **Total duration:** ~60-90 minutes for complete refresh

---

## Current State (Before Fix)

- **Status:** Stuck on page 1 since 2025-11-14 19:00:35
- **Devices cached:** 200 (unchanged for 2 weeks)
- **Error:** Fatal error - function callMpsGetDeviceList() does not exist
- **Impact:** ZERO devices being cached, CRON failing silently

---

## After Fix

- **Status:** Should progress through pages 1→528
- **Devices cached:** Should grow from 200 → 52,800+
- **Errors:** None (function now uses correct callMPSMAPI())
- **Impact:** Full device cache with drill-down data
