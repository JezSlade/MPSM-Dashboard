# MPS Monitors API Engine - Deployment Guide

Complete step-by-step instructions for deploying to GreenGeeks shared hosting.

## Prerequisites

✅ GreenGeeks hosting account with cPanel access  
✅ MPS Monitors API credentials (base URL and API key)  
✅ FTP/SFTP client (FileZilla recommended) or cPanel File Manager  
✅ Text editor for .env configuration  

## Deployment Methods

Choose one:
- **Method 1**: cPanel File Manager (Easiest)
- **Method 2**: FTP/SFTP Upload (Recommended)
- **Method 3**: SSH/Terminal (Advanced)

---

## Method 1: cPanel File Manager (Easiest)

### Step 1: Access cPanel

1. Log into your GreenGeeks account
2. Go to cPanel dashboard
3. Navigate to **File Manager**

### Step 2: Create Directory

1. Navigate to `public_html/`
2. Click **+ Folder** button
3. Name it `mps-api` (or your preferred name)
4. Enter the folder

### Step 3: Upload Files

1. Click **Upload** button
2. Select all files from the `mps-api` folder:
   - `index.php`
   - `engine.php`
   - `config.php`
   - `.env.example`
   - `.htaccess`
   - `swagger.json`
   - `SDK_Examples_Verified_Working.md`
   - `README.md`
   - `DEPLOYMENT.md`
3. Wait for upload to complete
4. Return to File Manager

### Step 4: Configure Environment

1. Right-click `.env.example`
2. Select **Copy**
3. Name the copy `.env`
4. Right-click `.env` → **Edit**
5. Update with your credentials:
```env
MPS_BASE_URL=https://api.mpsmonitors.com/v1
MPS_API_KEY=your_actual_api_key_here
MPS_TIMEOUT=30
MPS_DEBUG=false
```
6. **Save Changes**

### Step 5: Set Permissions

1. Select `.env` file
2. Click **Permissions** button
3. Set to `644` (Owner: Read+Write, Group: Read, World: Read)
4. Click **Change Permissions**

### Step 6: Create Logs Directory

1. Click **+ Folder**
2. Name it `logs`
3. Set permissions to `755`

### Step 7: Verify .htaccess

1. Right-click `.htaccess` → **Edit**
2. Find line 9: `RewriteBase /mps-api/`
3. If your subdirectory is different, update it
4. Example: If using `/api/`, change to `RewriteBase /api/`
5. **Save Changes**

### Step 8: Test Installation

1. Open browser
2. Navigate to: `https://yourdomain.com/mps-api/health`
3. Expected response:
```json
{
  "status": "healthy",
  "api_connection": true,
  "response_time": "123ms",
  "timestamp": "2024-10-15T12:00:00+00:00"
}
```

✅ **Success!** Your API is deployed.

---

## Method 2: FTP/SFTP Upload (Recommended)

### Step 1: Get FTP Credentials

1. Log into GreenGeeks cPanel
2. Go to **FTP Accounts**
3. Note your FTP hostname, username, and password
4. Or create new FTP account for this project

### Step 2: Connect via FTP Client

Using FileZilla:
1. Host: `ftp.yourdomain.com` (or provided hostname)
2. Username: Your FTP username
3. Password: Your FTP password
4. Port: 21 (or 22 for SFTP)
5. Click **Quickconnect**

### Step 3: Navigate to Public Directory

1. In remote site (right panel), navigate to:
   - `public_html/` or `/home/username/public_html/`

### Step 4: Create Subdirectory

1. Right-click in remote panel
2. Select **Create directory**
3. Name: `mps-api`
4. Enter the directory

### Step 5: Upload All Files

1. In local site (left panel), navigate to your `mps-api` folder
2. Select all files:
   - `index.php`
   - `engine.php`
   - `config.php`
   - `.env.example`
   - `.htaccess`
   - `swagger.json`
   - All `.md` files
3. Drag to remote panel or right-click → **Upload**
4. Wait for transfer to complete

### Step 6: Configure .env File

**Option A: Edit Locally Then Upload**
1. On your computer, copy `.env.example` to `.env`
2. Edit `.env` with your credentials
3. Upload `.env` to server
4. Delete or don't upload `.env.example`

**Option B: Edit on Server**
1. Use cPanel File Manager to copy `.env.example` to `.env`
2. Edit `.env` through File Manager

### Step 7: Set File Permissions via FTP

1. Right-click `.env` → **File Permissions**
2. Set to `644` (Read+Write, Read, Read)
3. Click **OK**

### Step 8: Create Logs Directory

1. Right-click in remote panel
2. **Create directory** → name: `logs`
3. Right-click `logs` → **File Permissions** → `755`

### Step 9: Verify Deployment

1. Browser: `https://yourdomain.com/mps-api/health`
2. Should return healthy status JSON

---

## Method 3: SSH/Terminal (Advanced)

### Step 1: Connect via SSH

```bash
ssh username@yourdomain.com
# Enter password when prompted
```

### Step 2: Navigate to Web Root

```bash
cd public_html
# or
cd ~/public_html
```

### Step 3: Create Directory

```bash
mkdir mps-api
cd mps-api
```

### Step 4: Upload Files

**Option A: SCP from Local Machine**
```bash
# From your local machine (new terminal)
scp -r /path/to/local/mps-api/* username@yourdomain.com:~/public_html/mps-api/
```

**Option B: Download via wget/curl** (if files are in a repo)
```bash
# Example if you have files in a git repo
git clone https://github.com/yourrepo/mps-api.git .
```

### Step 5: Configure Environment

```bash
cp .env.example .env
nano .env
# or
vi .env
```

Update credentials:
```env
MPS_BASE_URL=https://api.mpsmonitors.com/v1
MPS_API_KEY=your_key_here
MPS_TIMEOUT=30
MPS_DEBUG=false
```

Save and exit (`Ctrl+X`, `Y`, `Enter` for nano)

### Step 6: Set Permissions

```bash
chmod 644 .env
chmod 755 .
mkdir logs
chmod 755 logs
```

### Step 7: Verify .htaccess

```bash
nano .htaccess
```

Check `RewriteBase` line matches your subdirectory.

### Step 8: Test

```bash
curl https://yourdomain.com/mps-api/health
```

Should return JSON health status.

---

## Post-Deployment Configuration

### Update Subdirectory Path (If Needed)

If you used a different subdirectory name:

1. Edit `.htaccess`, line 9:
```apache
RewriteBase /your-subdirectory/
```

2. Test with: `https://yourdomain.com/your-subdirectory/health`

### Configure CORS for Production

Default allows all origins. To restrict:

**In `index.php` (line 30):**
```php
header('Access-Control-Allow-Origin: https://your-dashboard-domain.com');
```

**In `.htaccess` (line 26):**
```apache
Header set Access-Control-Allow-Origin "https://your-dashboard-domain.com"
```

### Test All Endpoints

```bash
# Base info
curl https://yourdomain.com/mps-api/

# Health check
curl https://yourdomain.com/mps-api/health

# Available endpoints
curl https://yourdomain.com/mps-api/endpoints

# Swagger docs
curl https://yourdomain.com/mps-api/swagger.json

# Query endpoint test
curl -X POST https://yourdomain.com/mps-api/query \
  -H "Content-Type: application/json" \
  -d '{"action":"healthCheck","params":{}}'
```

---

## Verification Checklist

After deployment, verify:

- [ ] Base URL loads (shows API info)
- [ ] `/health` returns healthy status
- [ ] `/endpoints` lists all endpoints
- [ ] `/swagger.json` returns OpenAPI spec
- [ ] `/query` accepts POST requests
- [ ] Logs directory exists and is writable
- [ ] `.env` file is not accessible via browser
- [ ] CORS headers present in responses

### Testing .env Security

Try accessing: `https://yourdomain.com/mps-api/.env`

Should return **403 Forbidden** (blocked by `.htaccess`)

---

## Troubleshooting

### Issue: 500 Internal Server Error

**Causes:**
- `.env` file missing or unreadable
- PHP version incompatibility
- Incorrect file permissions

**Solutions:**
1. Check `.env` exists: `ls -la` (should show `.env`)
2. Check PHP version: Create `phpinfo.php`:
```php
<?php phpinfo();
```
Upload, access via browser, check version (need 7.4+)
3. Set permissions: `chmod 644 .env`
4. Check error logs: `logs/php_errors_*.log`

### Issue: 404 Not Found

**Causes:**
- Incorrect subdirectory path
- `.htaccess` not loaded
- Mod_rewrite disabled

**Solutions:**
1. Check `.htaccess` RewriteBase matches your subdirectory
2. Verify `.htaccess` uploaded and readable
3. Contact GreenGeeks to enable mod_rewrite (usually enabled by default)

### Issue: CORS Errors

**Causes:**
- Browser blocking cross-origin requests
- CORS headers not set

**Solutions:**
1. Check `index.php` CORS headers (lines 25-28)
2. Check `.htaccess` CORS section (lines 25-29)
3. Update allowed origin from `*` to specific domain

### Issue: API Connection Failed

**Causes:**
- Incorrect `MPS_BASE_URL`
- Invalid `MPS_API_KEY`
- Firewall blocking outbound requests

**Solutions:**
1. Verify credentials in `.env`
2. Test MPS API directly:
```bash
curl -H "Authorization: Bearer YOUR_KEY" https://api.mpsmonitors.com/v1/health
```
3. Check `logs/error_*.log` for details
4. Contact GreenGeeks if outbound HTTPS blocked

### Issue: Logs Not Created

**Causes:**
- Logs directory missing
- Incorrect permissions

**Solutions:**
```bash
mkdir logs
chmod 755 logs
```

---

## Security Hardening (Optional)

### 1. Restrict .env Access (Already Done)

`.htaccess` blocks `.env` by default.

### 2. Add HTTP Authentication

Edit `.htaccess`, add before `RewriteEngine`:
```apache
AuthType Basic
AuthName "MPS API Access"
AuthUserFile /path/to/.htpasswd
Require valid-user
```

Create `.htpasswd`:
```bash
htpasswd -c .htpasswd apiuser
# Enter password when prompted
```

### 3. IP Whitelist

Edit `.htaccess`, add after `RewriteBase`:
```apache
# Allow only specific IPs
Order Deny,Allow
Deny from all
Allow from 123.456.789.0
Allow from 98.765.432.0
```

### 4. Rate Limiting

Add to `.htaccess`:
```apache
<IfModule mod_ratelimit.c>
    SetOutputFilter RATE_LIMIT
    SetEnv rate-limit 500
</IfModule>
```

---

## ChatGPT Actions Setup

After deployment:

1. **Get Swagger URL**: `https://yourdomain.com/mps-api/swagger.json`

2. **In ChatGPT**:
   - Go to GPT settings
   - Actions → Create new action
   - Import from URL: Use Swagger URL above
   - Authentication: None
   - Save

3. **Test with prompts**:
   - "Check MPS API health"
   - "List all monitors"
   - "Create a monitor for example.com"

---

## Maintenance

### View Logs

Via cPanel File Manager or SSH:
```bash
cd mps-api/logs
tail -f error_$(date +%Y-%m-%d).log
```

### Clear Old Logs

```bash
cd logs
rm error_2024-*.log
rm php_errors_2024-*.log
```

### Update API

1. Backup current installation
2. Upload new files
3. Keep existing `.env`
4. Test `/health` endpoint

---

## Support Contacts

- **GreenGeeks Support**: For hosting, PHP, permissions issues
- **MPS Monitors Support**: For API credentials, endpoint issues
- **Documentation**: README.md, SDK_Examples_Verified_Working.md

---

## Quick Reference

| Task | Command/URL |
|------|-------------|
| Health Check | `https://yourdomain.com/mps-api/health` |
| View Logs | cPanel File Manager → `mps-api/logs/` |
| Test API | `curl https://yourdomain.com/mps-api/endpoints` |
| Edit Config | cPanel File Manager → Edit `.env` |
| Check Permissions | cPanel → File Manager → Select file → Permissions |

---

**Deployment Complete!** 🎉

Your MPS Monitors API Engine is now live at:
`https://mpsm.resolutionsbydesign.us/mps-api/`

Next steps:
1. Integrate with ChatGPT Actions
2. Build dashboard UI
3. Monitor logs for issues

---

**Version:** 1.0.0  
**Last Updated:** October 2024
