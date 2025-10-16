# FTP Error 553 - Specific Fix for Missing Directory

## The Exact Problem

```
> STOR documentation/Endpoints/SDK_Examples_Verified_Working.MD
< 553 Can't open that file: No such file or directory
```

**Cause**: The `documentation/Endpoints/` directory doesn't exist on the server, and the FTP server won't create it automatically.

---

## Solution Applied

I've made **TWO changes** to your `deploy.yml`:

### 1. ✅ Enabled Clean Slate (One-Time)
```yaml
dangerous-clean-slate: true  # ⚠️ TEMPORARY!
```

**What this does**:
- Deletes ALL files on the server
- Recreates the entire directory structure
- Uploads everything fresh

**⚠️ CRITICAL**: After ONE successful deployment, change this back to:
```yaml
dangerous-clean-slate: false
```

### 2. ✅ Excluded Documentation Folder
```yaml
exclude: |
  documentation/**
```

**What this does**:
- Prevents the `documentation/` folder from being deployed
- Avoids the directory creation issue
- You don't need it since you have the canonical swagger

---

## Next Steps - IMPORTANT!

### Step 1: Deploy with Current Settings
```bash
git add .github/workflows/deploy.yml
git commit -m "Fix FTP 553 - clean slate + exclude docs"
git push origin main
```

### Step 2: Watch the Deployment
- Go to GitHub Actions
- The deployment should succeed this time
- It will delete old files and upload fresh ones

### Step 3: IMMEDIATELY Change Back
After the deployment succeeds, **immediately** change this line in `deploy.yml`:

**Change FROM**:
```yaml
dangerous-clean-slate: true
```

**Change TO**:
```yaml
dangerous-clean-slate: false
```

Then commit and push again:
```bash
git add .github/workflows/deploy.yml
git commit -m "Disable clean-slate after successful deployment"
git push origin main
```

---

## Why This Happened

1. The server had an old/corrupted directory structure
2. The `documentation/Endpoints/` directory was missing
3. FTP server permissions don't allow automatic directory creation
4. The FTP action tried to upload to a non-existent path

---

## What Clean Slate Does

```
Before:
Server: /some-old-file.php
        /old-directory/

Clean Slate:
Server: (empty)

After Upload:
Server: /index.php
        /.htaccess
        /mps-api/
        /.canonical/Swagger.json
        (everything fresh!)
```

---

## Alternative: Keep Documentation

If you DO want the documentation folder deployed:

### Option A: Remove the exclusion
Remove this line from `deploy.yml`:
```yaml
documentation/**  # Remove this
```

### Option B: Manually create directory via FTP
1. Connect with FTP client
2. Create `documentation/` folder
3. Create `documentation/Endpoints/` folder
4. Then deploy normally

---

## Current Configuration

Your `deploy.yml` now has:
- ✅ `dangerous-clean-slate: true` (ONE TIME ONLY!)
- ✅ `documentation/**` excluded
- ✅ Better exclusion patterns
- ✅ Enhanced debugging

---

## Expected Result

After this deployment:
1. ✅ Server will be clean
2. ✅ All files uploaded fresh
3. ✅ No directory structure issues
4. ✅ No 553 errors
5. ✅ Deployment succeeds

Then after changing back to `dangerous-clean-slate: false`:
6. ✅ Future deployments only update changed files
7. ✅ Much faster deployments
8. ✅ No risk of deleting everything each time

---

## Verification Checklist

After deployment succeeds:

- [ ] Visit your site - should work
- [ ] Check `/mps-api/health` - should return JSON
- [ ] Check files exist on server via FTP
- [ ] Confirm `dangerous-clean-slate` changed back to `false`
- [ ] Test next deployment (should be incremental, not clean slate)

---

## If It STILL Fails

If clean-slate doesn't work, try:

1. **Check FTP user permissions**:
   - Can create directories?
   - Can delete files?
   - Has write access to root?

2. **Try different server-dir**:
   ```yaml
   server-dir: /public_html/  # Instead of /
   ```

3. **Manual FTP upload**:
   - Use FileZilla or similar
   - Upload files manually once
   - Then use GitHub Actions for updates

---

## Summary

| Setting | Current Value | After First Deploy |
|---------|---------------|-------------------|
| `dangerous-clean-slate` | `true` | Change to `false` |
| `documentation/**` | Excluded | Keep excluded (or remove if needed) |
| Expected Result | Full upload | Only changed files |

---

**REMEMBER**: Change `dangerous-clean-slate` back to `false` after ONE successful deployment!

**Last Updated**: 2025-10-16
**Status**: Ready to deploy with clean slate
**Action Required**: Change back to `false` after success
