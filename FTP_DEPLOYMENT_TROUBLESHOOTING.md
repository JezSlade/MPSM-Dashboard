# FTP Deployment Troubleshooting Guide

## Error 553: Can't open that file: No such file or directory

This error typically occurs when:
1. The FTP action tries to upload a file that doesn't exist locally
2. The FTP action tries to create a file in a directory that doesn't exist on the server
3. The sync state file is corrupted or causing conflicts
4. Exclusion patterns are too broad and blocking necessary files

---

## Fixes Applied

### 1. ✅ Updated Exclusion Patterns

**Changed from**: Loose patterns like `*.md`, `logs/`
**Changed to**: Specific glob patterns with `**/` prefix

```yaml
exclude: |
  **/.git/**          # Git directory
  **/.github/**       # GitHub Actions
  **/.vscode/**       # VS Code settings
  **/*.log            # All log files
  .claude/**          # Claude Code directory
  # ... etc
```

**Why**: This prevents accidentally excluding files the FTP action needs.

### 2. ✅ Added Sync State Configuration

```yaml
state-name: .ftp-deploy-sync-state.json
dangerous-clean-slate: false
```

**Why**: Explicitly names the sync state file and ensures it's managed correctly.

### 3. ✅ Exclude Sync State from Upload

```yaml
exclude: |
  .ftp-deploy-sync-state.json
```

**Why**: Prevents the sync state file from being uploaded to the server (it should only exist locally in GitHub Actions).

### 4. ✅ Added Debug Step

Shows which files will be deployed and checks for problematic files.

---

## If Error Persists - Try These Steps

### Step 1: Check GitHub Actions Logs

Look for the exact file causing the error:
```
FTPError: 553 Can't open that file: /path/to/file.ext
```

The path will tell you which file is problematic.

### Step 2: Verify File Exists Locally

In your GitHub Actions logs, check the "Debug — List Files" section:
- Does the file exist in the checkout?
- Is it being excluded by accident?

### Step 3: Clean Slate Deployment (Nuclear Option)

If the sync state is corrupted, try a one-time clean slate:

```yaml
dangerous-clean-slate: true  # ⚠️ WARNING: Deletes all files on server first!
```

**IMPORTANT**:
- This will DELETE all files on the server before uploading
- Only use once to fix the issue
- Change back to `false` after successful deployment
- Make sure you have a backup!

### Step 4: Check Server Directory Structure

The error might occur if the server directory doesn't exist. Verify:

1. Log into FTP manually
2. Navigate to the deployment path (`/`)
3. Ensure the directory structure matches what you expect

### Step 5: Check FTP Permissions

The FTP user needs permissions to:
- Create directories
- Upload files
- Delete files (for sync to work)
- Write to the root directory

Test by manually uploading a file via FTP client.

### Step 6: Simplify Exclusions Temporarily

Try with minimal exclusions to isolate the issue:

```yaml
exclude: |
  **/.git/**
  **/.github/**
```

If this works, add exclusions back one at a time to find the culprit.

### Step 7: Check for Special Characters

Some FTP servers have issues with:
- Files with spaces in names
- Special characters (é, ñ, etc.)
- Very long file names
- Unicode characters

Rename any problematic files.

---

## Debugging Checklist

- [ ] Check GitHub Actions logs for exact file path in error
- [ ] Verify file exists in repository
- [ ] Verify file is not in `.gitignore`
- [ ] Check exclusion patterns aren't too broad
- [ ] Verify FTP credentials are correct
- [ ] Verify server directory exists
- [ ] Check FTP user has write permissions
- [ ] Look for files with special characters
- [ ] Check if sync state is corrupted (try clean-slate once)

---

## Common Causes & Solutions

### Cause: Excluded `.md` Files
**Problem**: Using `*.md` excludes ALL markdown files
**Solution**: Use specific paths or remove markdown exclusion
```yaml
# Don't do this:
*.md

# Do this instead (if you want to exclude docs):
documentation/**/*.md
```

### Cause: Logs Directory Pattern
**Problem**: Pattern like `**/logs/*.log` might cause issues if `logs/` doesn't exist
**Solution**: Use just `**/*.log` to match log files anywhere
```yaml
# This is better:
**/*.log
```

### Cause: Sync State Corruption
**Problem**: The `.ftp-deploy-sync-state.json` file is corrupted
**Solution**: Use clean-slate ONCE
```yaml
dangerous-clean-slate: true  # Set to true ONCE, then back to false
```

### Cause: Missing Parent Directory on Server
**Problem**: Trying to upload `mps-api/file.php` but `mps-api/` doesn't exist on server
**Solution**: FTP action should create directories automatically, but verify permissions allow it

### Cause: Server Path Mismatch
**Problem**: `server-dir` points to wrong location
**Solution**: Verify the actual server path
```yaml
server-dir: /          # Root of FTP account
# OR
server-dir: /public_html/  # If that's where files go
```

---

## Current Configuration

Your `deploy.yml` is now configured with:

✅ Specific exclusion patterns (`**/` prefixes)
✅ Explicit sync state configuration
✅ Sync state excluded from upload
✅ Debug output for troubleshooting
✅ Verbose logging enabled

---

## Testing the Fix

1. **Commit the updated `deploy.yml`**:
   ```bash
   git add .github/workflows/deploy.yml
   git commit -m "Fix FTP deployment error 553 with updated exclusions"
   git push origin main
   ```

2. **Monitor the deployment**:
   - Go to GitHub → Actions
   - Watch the "Debug — List Files" output
   - Check for any files that shouldn't be there
   - Look for missing files that should be there

3. **If it fails again**:
   - Note the exact file path in the error
   - Check if that file is in the debug output
   - Check if it's being excluded
   - Check the troubleshooting steps above

---

## Emergency Fix: Manual Upload

If automated deployment keeps failing:

1. **Use an FTP client** (FileZilla, Cyberduck, etc.)
2. **Connect with your credentials**:
   - Host: `ftp.resolutionsbydesign.us`
   - User: `mpsm@mpsm.resolutionsbydesign.us`
   - Pass: `Deploy123!`
   - Port: `21`
3. **Upload files manually**:
   - Upload all `.php` files
   - Upload `.htaccess` files
   - Upload `.canonical/Swagger.json`
   - Upload `.env` file
   - Skip `.git`, `.github`, logs, etc.

---

## Prevention

To avoid this in the future:

1. **Test exclusions locally** before committing
2. **Use specific patterns** instead of wildcards
3. **Keep sync state clean** (don't upload it to server)
4. **Monitor GitHub Actions** for warnings
5. **Have a backup** before major deployments

---

## Support

If the issue persists after trying all the above:

1. Check the FTP-Deploy-Action GitHub issues: https://github.com/SamKirkland/FTP-Deploy-Action/issues
2. Try upgrading the action version
3. Try a different FTP action
4. Contact your hosting provider about FTP server logs

---

## Quick Reference

| Issue | Quick Fix |
|-------|-----------|
| Error 553 | Check file exists, check permissions |
| Sync state error | Try `dangerous-clean-slate: true` once |
| Exclusion too broad | Use `**/` prefix and specific paths |
| Missing directory | Verify server structure manually |
| Permission denied | Check FTP user permissions |
| File not found | Check file is in repo and not excluded |

---

**Last Updated**: 2025-10-16
**Status**: deploy.yml updated with fixes
**Next Step**: Commit and push to test
