# Deployment Guide

> Complete deployment procedures for MPSM Dashboard including automated CI/CD, manual deployment, and performance refactor deployment.

---

## Automatic Deployment (Standard Process)

**The MPSM Dashboard uses GitHub Actions for automatic deployment.**

### How It Works

1. **Trigger:** Push to `main` branch
2. **Workflow:** `.github/workflows/deploy.yml`
3. **Method:** FTP deployment to GreenGeeks hosting
4. **Duration:** 2-5 minutes
5. **Result:** Code automatically live on production

### Workflow Details

**GitHub Actions Workflow:**
- **File:** `.github/workflows/deploy.yml`
- **Runs on:** Every push to `main` branch
- **Timeout:** 15 minutes max
- **FTP Server:** ftp.resolutionsbydesign.us
- **FTP User:** mpsm@mpsm.resolutionsbydesign.us
- **Target Directory:** `/` (webroot)

**What Gets Deployed:**
- All PHP files (`cms/`, `mps-api/`)
- All JavaScript/CSS assets
- Configuration files (`.env`, `config.php`)
- `.htaccess` files
- Database schema (not data)

**What Gets Excluded:**
- `.git/`, `.github/` (version control)
- `documentation/`, `scripts/` (development docs)
- `tests/` (test files)
- `*.log` (log files)
- `node_modules/` (if present)
- `.ftp-deploy-sync-state.json` (deployment state)

### Standard Deployment Process

```bash
# Step 1: Make changes locally
# Edit files as needed

# Step 2: Test locally
# Verify changes work

# Step 3: Commit changes
git add .
git commit -m "Descriptive commit message

🤖 Generated with Claude Code

Co-Authored-By: Claude <noreply@anthropic.com>"

# Step 4: Push to trigger deployment
git push origin main

# Step 5: Monitor deployment
# Go to: https://github.com/JezSlade/MPSM-Dashboard/actions
# Wait for green checkmark (success)

# Step 6: Verify live site
# Visit: https://mpsm.resolutionsbydesign.us/cms/
# Hard refresh: Ctrl+Shift+R
# Test: Login, dashboard, search, device modal
```

### Monitoring Deployment

**View deployment status:**
1. Go to: https://github.com/JezSlade/MPSM-Dashboard/actions
2. Click on latest workflow run
3. View logs in real-time
4. Check for errors or warnings

**Deployment States:**
- 🟡 **Yellow dot:** In progress
- ✅ **Green checkmark:** Success
- ❌ **Red X:** Failed

**If deployment fails:**
1. Click on failed workflow
2. Expand failed step to see error
3. Fix issue locally
4. Push again to retry

---

## Manual Deployment (Alternative)

**Use when GitHub Actions unavailable or for urgent hotfixes.**

### Using PowerShell Deployment Scripts

**Available scripts:**
- `deploy-critical-fix.ps1` - Critical hotfix deployment
- `deploy-performance-refactor.ps1` - Performance refactor deployment
- `deploy-monitoring-updates.ps1` - Monitoring system updates
- `deploy-all.ps1` - Full deployment

**Example usage:**
```powershell
# Run deployment script
.\deploy-critical-fix.ps1

# Follow prompts:
# - Enter FTP credentials
# - Confirm files to deploy
# - Wait for upload completion
# - Verify deployment
```

### Manual FTP Upload

**If scripts unavailable:**

1. **Connect via FTP client** (FileZilla, WinSCP, etc.)
   - Host: `ftp.resolutionsbydesign.us`
   - Port: 21
   - User: `mpsm@mpsm.resolutionsbydesign.us`
   - Protocol: FTP

2. **Upload files:**
   - Navigate to target directory
   - Upload modified files
   - Preserve directory structure

3. **Verify permissions:**
   - Directories: 755
   - PHP files: 644
   - `.htaccess`: 644

---

## Performance Refactor Deployment (v2.1.0)

**Special deployment procedure for performance optimizations.**

### Quick Deployment (15 minutes)

**All files are committed and ready. GitHub Actions will deploy automatically:**

```bash
# The refactor is already committed to main
# Simply push to deploy:
git push origin main

# Monitor at: https://github.com/JezSlade/MPSM-Dashboard/actions
```

### Post-Deployment Steps (Required)

**After automatic deployment completes:**

#### 1. Apply Database Indexes (5 minutes)

**Option A: phpMyAdmin (Recommended)**
1. Login to cPanel: https://mpsm.resolutionsbydesign.us:2083
2. Click "phpMyAdmin"
3. Select database: `resolut7_mpsm`
4. Click "SQL" tab
5. Copy contents of: `database_optimizations.sql`
6. Paste into query box
7. Click "Go"
8. Verify: "10 indexes added successfully"

**Option B: MySQL Command Line**
```bash
mysql -u resolut7_mpsm -p resolut7_mpsm < database_optimizations.sql
```

**Option C: PowerShell Script**
```powershell
.\execute-database-optimizations.ps1
```

#### 2. Rename get-cached-devices.php (1 minute)

**Via cPanel File Manager:**
1. Navigate to: `/public_html/cms/api/`
2. Find: `get-cached-devices.php.NEW`
3. Rename to: `get-cached-devices.php`
4. Confirm overwrite

**Via FTP:**
1. Connect to FTP
2. Navigate to: `/cms/api/`
3. Rename `get-cached-devices.php.NEW` → `get-cached-devices.php`

#### 3. Populate Cache (5 minutes)

**Trigger background cache refresh:**
```bash
curl https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php
```

**Or visit in browser:**
https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php

**Expected:**
- Takes 5-10 minutes
- Returns JSON with stats
- Populates `mpsm_cache_devices` and `mpsm_cache_device_drilldown` tables

#### 4. Cron Schedule (Updated 2025-11-09)

These jobs are configured in cPanel and must remain active unless the owner updates them:

```bash
# UPDATED 2025-11-09: Changed from */5 to hourly (0 * * * *) to prevent infinite loop
# Previous: 5-minute cron was truncating cache before 30-minute refresh could complete
0 * * * * /usr/bin/timeout 240 /usr/bin/curl -s "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php?skipDrilldown=1" >/dev/null 2>&1
0 0 * * * /usr/bin/timeout 1800 /usr/bin/curl -s "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php?force=1" >/dev/null 2>&1
0,30 * * * * /usr/bin/curl -s "https://mpsm.resolutionsbydesign.us/mps-api/health" >> /home/youruser/logs/mps-api-health.log
0 0 * * * /usr/bin/curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-database-monitor.php" >> /home/youruser/logs/database-monitor.log
0 0 * * 0 /usr/bin/php /home/youruser/public_html/cms/api/cleanup-payload-debug.php >/dev/null 2>&1
```

> **Note:** First cron job changed from every 5 minutes to hourly (2025-11-09) to allow full 30,000+ device cache to complete without truncation. This fixes the infinite loop where new cron runs were wiping cache before previous runs could finish. May restore more frequent updates after verifying cache stability.

#### 5. Test Live Site (5 minutes)

**Quick smoke test:**
1. Visit: https://mpsm.resolutionsbydesign.us/cms/
2. Login: admin / admin
3. ✓ Dashboard loads in < 3 seconds
4. ✓ Search for device
5. ✓ Device modal opens instantly (<500ms)
6. ✓ No console errors (F12 → Console)
7. ✓ Panel message monitor loads fast
8. ✓ System health shows green

**If all tests pass:** ✅ Deployment successful!

**See `TEST_LIVE_SITE.md` for comprehensive testing.**

---

## Rollback Procedures

### Automatic Deployment Rollback

**If deployed commit has issues:**

```bash
# Revert to previous commit
git revert HEAD
git push origin main

# Automatic deployment will restore previous version
# Wait 2-5 minutes for deployment to complete
```

### Manual Rollback

**If immediate rollback needed:**

1. **Revert local changes:**
   ```bash
   git reset --hard HEAD~1
   ```

2. **Force push (use with caution):**
   ```bash
   git push -f origin main
   ```

3. **Or upload previous version via FTP**

### Database Rollback

**If database indexes cause issues:**

```powershell
# Run rollback script
.\rollback-database-optimizations.ps1

# Or via phpMyAdmin:
# Copy contents of rollback_indexes.sql
# Run in SQL tab
```

### Code Rollback

**If refactor code causes issues:**

```powershell
# Restore dead code
.\restore-dead-code.ps1

# Restore previous get-cached-devices.php
# Upload from Git history or backup
```

---

## Post-Deployment Verification

### Standard Verification

**After any deployment:**

1. **Site Loads:**
   - Visit: https://mpsm.resolutionsbydesign.us/cms/
   - Should load without errors
   - Should not show PHP warnings

2. **Login Works:**
   - Enter: admin / admin
   - Should redirect to dashboard
   - Session should persist

3. **Dashboard Functions:**
   - All cards load with data
   - No stuck loading spinners
   - No JavaScript errors in console

4. **Core Features:**
   - Device search works
   - Device modal opens
   - Panel messages display
   - Admin tab accessible

5. **Check Logs:**
   - PHP errors: `cms/logs/php_errors.log`
   - Cache logs: `cms/logs/cache-refresh-*.log`
   - No new errors after deployment

### Performance Verification (After Refactor)

**Measure actual improvements:**

1. **Dashboard Load Time:**
   - Open DevTools (F12) → Network tab
   - Hard refresh (Ctrl+Shift+R)
   - Measure total load time
   - Target: < 3 seconds
   - Before refactor: 10-15 seconds

2. **Device Modal Time:**
   - Search for device
   - Click result
   - Measure time to full modal load
   - Target: < 500ms
   - Before refactor: 5-10 seconds

3. **API Response Times:**
   - Check Network tab for these endpoints:
   - `get-cached-devices.php` → Target: <100ms
   - `get-device-deep-dive.php` → Target: <200ms
   - `get-panel-messages.php` → Target: <100ms

4. **Cache Status:**
   - Check system health
   - Cache age should be < 10 minutes
   - Cache entries should match device count

---

## Deployment Troubleshooting

### Common Issues

#### Deployment Fails in GitHub Actions

**Symptoms:** Red X on workflow, FTP connection fails

**Solutions:**
1. Check FTP credentials in workflow file
2. Verify FTP server is accessible
3. Check repository secrets (if using secrets)
4. Try manual deployment as fallback

#### Site Shows Old Version After Deployment

**Symptoms:** Changes not visible, old code running

**Solutions:**
1. Hard refresh browser: `Ctrl+Shift+R`
2. Clear browser cache completely
3. Check deployment actually completed (green checkmark)
4. Verify files on server via FTP
5. Check `.htaccess` isn't caching aggressively

#### Database Indexes Fail to Apply

**Symptoms:** SQL errors when running optimization script

**Solutions:**
1. Check database credentials
2. Verify table names match (prefix: `mpsm_`)
3. Check for existing indexes with same name
4. Try dropping existing indexes first
5. Run via phpMyAdmin instead of command line

#### Cache Not Populating

**Symptoms:** `get-cached-devices.php` returns 0 devices

**Solutions:**
1. Run `refresh-cache-enhanced.php` manually
2. Check dealer code in `cms/config.php`
3. Verify `mps-api/query` endpoint works
4. Check cache tables exist in database
5. Review cache refresh logs for errors

#### Performance Not Improved

**Symptoms:** Dashboard still slow after refactor

**Solutions:**
1. Verify database indexes applied
2. Check cache is populated (run refresh)
3. Verify cron job is running (every 5 min)
4. Check cache age (should be < 10 min)
5. Clear browser cache completely
6. Test in incognito mode

---

## Deployment Checklist

### Pre-Deployment

- [ ] All changes tested locally
- [ ] Code committed with descriptive message
- [ ] No uncommitted files
- [ ] Documentation updated
- [ ] Breaking changes documented

### During Deployment

- [ ] Monitor GitHub Actions workflow
- [ ] Watch for errors in logs
- [ ] Verify green checkmark
- [ ] Check deployment time reasonable

### Post-Deployment

- [ ] Hard refresh browser
- [ ] Test login
- [ ] Test dashboard load
- [ ] Test core features
- [ ] Check error logs
- [ ] Verify no console errors

### Performance Refactor Additional Steps

- [ ] Apply database indexes
- [ ] Rename get-cached-devices.php.NEW
- [ ] Populate cache (run refresh)
- [ ] Schedule cron job
- [ ] Verify performance improvements
- [ ] Monitor for 24-48 hours

---

## Emergency Procedures

### Site Down After Deployment

**Immediate actions:**

1. **Revert via Git:**
   ```bash
   git revert HEAD
   git push origin main
   ```

2. **Or restore via FTP:**
   - Upload previous version from backup
   - Or download from Git history

3. **Check error logs:**
   - PHP errors: `cms/logs/php_errors.log`
   - Server errors: Check cPanel error log

4. **Notify team:**
   - Document issue
   - Share error messages
   - Coordinate response

### Database Corruption

**If database indexes cause issues:**

1. **Immediate rollback:**
   ```sql
   # Copy contents of rollback_indexes.sql
   # Run in phpMyAdmin
   ```

2. **Verify database:**
   ```sql
   SELECT COUNT(*) FROM mpsm_cache_devices;
   SHOW INDEX FROM mpsm_panel_messages;
   ```

3. **Restore from backup if needed:**
   - Contact hosting support
   - Restore from most recent backup
   - Reapply legitimate changes

---

## Reference

### Key Files

**Deployment Configuration:**
- `.github/workflows/deploy.yml` - GitHub Actions workflow
- `deploy-*.ps1` - Manual deployment scripts
- `.env` - Environment configuration (deployed)

**Performance Refactor:**
- `database_optimizations.sql` - Database indexes
- `rollback_indexes.sql` - Rollback script
- `cms/functions.php` - Shared utilities
- `cms/api/get-cached-devices.php.NEW` - Optimized cache

**Documentation:**
- `START_HERE.md` - Quick start
- `DEPLOY_NOW.md` - Deployment guide
- `TEST_LIVE_SITE.md` - Testing checklist
- `REFACTOR_COMPLETE.md` - Technical details

### Key URLs

**Production:**
- Dashboard: https://mpsm.resolutionsbydesign.us/cms/
- API: https://mpsm.resolutionsbydesign.us/mps-api/
- Panel Monitor: https://mpsm.resolutionsbydesign.us/cms/panel-message-monitor.php

**Deployment:**
- GitHub Actions: https://github.com/JezSlade/MPSM-Dashboard/actions
- cPanel: https://mpsm.resolutionsbydesign.us:2083

**Monitoring:**
- System Health: `/cms/api/system-health.php`
- Cache Refresh: `/cms/api/refresh-cache-enhanced.php`
- Error Logs: `/cms/logs/php_errors.log`

---

**Last Updated:** 2025-11-06
**Version:** 2.1.0 (Performance Optimized)
