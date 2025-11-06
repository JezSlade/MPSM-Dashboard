# 🚀 DEPLOY NOW - Step-by-Step Guide

**CRITICAL: Follow these steps in order**

---

## ⚡ Quick Deploy (15 minutes)

### Step 1: Apply Database Indexes (5 minutes)

**Option A: Using phpMyAdmin (Recommended)**

1. Go to: https://mpsm.resolutionsbydesign.us:2083 (cPanel)
2. Login with your hosting credentials
3. Click "phpMyAdmin"
4. Select database: `resolut7_mpsm`
5. Click "SQL" tab
6. Open file: `database_optimizations.sql`
7. Copy entire contents
8. Paste into SQL query box
9. Click "Go"
10. ✅ Verify: You should see "10 indexes added successfully"

**Option B: Using MySQL Command Line**

```bash
mysql -u resolut7_mpsm -p resolut7_mpsm < database_optimizations.sql
```

**✅ Checkpoint:** Database indexes added (queries will now be 10x faster)

---

### Step 2: Deploy Updated Code (5 minutes)

**Upload these 2 files via FTP or cPanel File Manager:**

1. **cms/functions.php** → `/public_html/cms/functions.php`
   - Location: `c:\Users\jez.slade\Desktop\Projects\MPSM-Dashboard\cms\functions.php`
   - Contains: New shared utility functions

2. **cms/api/get-cached-devices.php.NEW** → `/public_html/cms/api/get-cached-devices.php`
   - Location: `c:\Users\jez.slade\Desktop\Projects\MPSM-Dashboard\cms\api\get-cached-devices.php.NEW`
   - **IMPORTANT:** Rename from `.NEW` to `.php` when uploading
   - Contains: Optimized MySQL cache version

**Using cPanel File Manager:**
1. Login to cPanel
2. Click "File Manager"
3. Navigate to `/public_html/cms/`
4. Click "Upload"
5. Select `functions.php` from your local machine
6. Confirm overwrite
7. Navigate to `/public_html/cms/api/`
8. Upload `get-cached-devices.php.NEW` as `get-cached-devices.php`
9. Confirm overwrite

**✅ Checkpoint:** Code deployed (cache system now uses MySQL)

---

### Step 3: Populate Cache (5 minutes)

**Trigger the background cache refresh:**

Open in browser:
```
https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php
```

**Expected:**
- Takes 5-10 minutes for first run
- Shows progress: "Fetched X devices total"
- Returns JSON with stats

**Or use curl:**
```bash
curl https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php
```

**✅ Checkpoint:** Cache populated (dashboard will now load instantly)

---

### Step 4: Schedule Cron Job (2 minutes)

**To keep cache fresh, schedule automatic refresh every 5 minutes:**

1. Login to cPanel
2. Click "Cron Jobs"
3. Add new cron job:
   - **Minute:** `*/5`
   - **Hour:** `*`
   - **Day:** `*`
   - **Month:** `*`
   - **Weekday:** `*`
   - **Command:**
     ```bash
     curl -s https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php > /dev/null 2>&1
     ```
4. Click "Add New Cron Job"

**✅ Checkpoint:** Cron job scheduled (cache will auto-refresh every 5 minutes)

---

### Step 5: Test Live Site (5 minutes)

**🧪 Quick Smoke Test:**

1. **Open:** https://mpsm.resolutionsbydesign.us/cms/
2. **Login:** admin / admin
3. **✓ Test 1:** Dashboard loads in < 3 seconds?
4. **✓ Test 2:** Cards show data (not loading spinners)?
5. **✓ Test 3:** Search for a device (e.g., type serial number)
6. **✓ Test 4:** Click search result → Device modal opens instantly?
7. **✓ Test 5:** Modal shows all data (counters, supplies, panel history)?
8. **✓ Test 6:** Open browser console (F12) → No red errors?
9. **✓ Test 7:** Click "Admin" tab → System health shows green?
10. **✓ Test 8:** Panel message monitor loads quickly?

**If ALL tests pass:** ✅ Deployment successful!

**If any test fails:** See troubleshooting below

---

## 🎯 Performance Verification

**Check actual performance improvements:**

1. Open browser DevTools (F12)
2. Go to "Network" tab
3. Hard refresh (Ctrl+Shift+R)
4. Check timings:

**Expected Results:**
- **Dashboard load:** < 3 seconds total
- **get-cached-devices.php:** < 100ms (was 2-5s)
- **get-device-deep-dive.php:** < 200ms (was 5-10s)
- **get-panel-messages.php:** < 100ms (was 500ms)

**Before vs After:**
| Endpoint | Before | After | Improvement |
|----------|--------|-------|-------------|
| get-cached-devices.php | 2-5s | <100ms | **20-50x** |
| get-device-deep-dive.php | 5-10s | <200ms | **25-50x** |
| get-panel-messages.php | 500ms | 50ms | **10x** |

---

## 🐛 Troubleshooting

### Dashboard still loads slowly

**Cause:** Cache not populated yet

**Fix:**
```bash
# Manually trigger cache refresh
curl https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php

# Check cache age
curl https://mpsm.resolutionsbydesign.us/cms/api/system-health.php
```

---

### Device modal doesn't open

**Cause:** JavaScript error or API failure

**Fix:**
1. Open browser console (F12)
2. Look for red errors
3. Check Network tab for failed requests
4. Verify `get-device-deep-dive.php` returns 200 OK

---

### "Cache not initialized" error

**Cause:** Database tables not created yet

**Fix:**
```bash
# Run cache refresh to create tables
curl https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php
```

---

### Database error after applying indexes

**Cause:** Syntax error or permission issue

**Rollback:**
1. Open phpMyAdmin
2. Go to SQL tab
3. Run contents of: `rollback_indexes.sql`
4. This removes all indexes (system still works, just slower)

---

## 🔄 Rollback Procedures

**If you need to undo changes:**

### Rollback Database Indexes
```sql
-- In phpMyAdmin, run:
-- Copy contents of rollback_indexes.sql
```

### Rollback Code Changes

**Via FTP or cPanel:**
1. Download previous versions from Git
2. Upload old versions to server
3. System will work as before (just slower)

**Via Git:**
```bash
git checkout HEAD~1 cms/functions.php
git checkout HEAD~1 cms/api/get-cached-devices.php
# Upload these to server
```

---

## 📊 Monitor Performance (Next 24 Hours)

**Check these logs:**

1. **PHP Errors:** `/public_html/cms/logs/php_errors.log`
2. **Cache Refresh:** `/public_html/cms/logs/cache-refresh-YYYY-MM-DD.log`
3. **Panel Messages:** `/public_html/mps-api/logs/panel-message-YYYY-MM-DD.log`

**Watch for:**
- No errors in PHP error log
- Cache refresh completes successfully every 5 min
- No 404 errors in browser console

---

## ✨ Success Indicators

**You'll know deployment succeeded when:**

✅ Dashboard loads in under 3 seconds
✅ Device modals open instantly (<500ms)
✅ Search returns results in under 1 second
✅ No JavaScript errors in console
✅ System health shows all green
✅ Cache refreshes automatically every 5 minutes
✅ Error logs remain clean

---

## 🎉 After Successful Testing

**Once you've confirmed everything works (24-48 hours):**

### Optional: Remove Dead Code

Run this script to permanently remove obsolete files:
```powershell
.\finalize-dead-code-removal.ps1
```

This will delete:
- `refresh-cache.php.bak`
- `refresh-cache-v2.php.bak`
- `refresh-cache-cron.php.bak`
- `get-all-devices-all-customers.php.bak`
- `get-all-customers-devices.php.bak`

**These files are no longer needed and were marked obsolete during deployment.**

---

## 📞 Need Help?

**If deployment fails or you see unexpected behavior:**

1. **Check error logs** (see Monitor Performance section)
2. **Run rollback scripts** (see Rollback Procedures section)
3. **Review troubleshooting** (see Troubleshooting section)
4. **Contact support** with deployment log

**Deployment is designed to be safe and reversible!**

---

## 🚀 Ready to Deploy?

**Checklist:**
- [ ] Step 1: Apply database indexes
- [ ] Step 2: Deploy updated code
- [ ] Step 3: Populate cache
- [ ] Step 4: Schedule cron job
- [ ] Step 5: Test live site

**Estimated Time:** 15 minutes
**Risk Level:** LOW (all changes are reversible)
**Expected Result:** 7-10x performance improvement

**Let's go! 🎉**
