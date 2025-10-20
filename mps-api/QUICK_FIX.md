# Quick Fix Guide - 500 Error

## Your Engine Returns 500 Error - Here's How to Fix It in 2 Minutes

### Step 1: Check Diagnostics (30 seconds)

```bash
curl https://mpsm.resolutionsbydesign.us/mps-api/diagnostics
```

Look for these key indicators:

#### ❌ Problem: Missing .env
```json
"files": { ".env": false }
```
**Fix:** Create `.env` file with credentials (see below)

#### ❌ Problem: Missing Swagger.json
```json
"files": { "Swagger.json": false }
```
**Fix:** Upload `Swagger.json` to server

#### ❌ Problem: Missing OAuth credentials
```json
"env_config": {
  "has_required": {
    "CLIENT_ID": false,
    "CLIENT_SECRET": false
  }
}
```
**Fix:** Add credentials to `.env` (see below)

#### ❌ Problem: Logs not writable
```json
"filesystem": { "logs_dir_writable": false }
```
**Fix:** `chmod 755 /path/to/mps-api/logs`

#### ❌ Problem: Engine initialization failed
```json
"engine": {
  "initialization": "failed",
  "error": { "message": "..." }
}
```
**Fix:** Read the error message - it tells you exactly what's wrong

---

## Step 2: Most Common Fix - Create .env File

SSH into your server and create the `.env` file:

```bash
cd /var/www/mpsm.resolutionsbydesign.us/mps-api
nano .env
```

Paste this and fill in your credentials:

```bash
# MPSM API Configuration
API_BASE_URL=https://your-mpsm-api-url.com
TOKEN_URL=https://your-mpsm-api-url.com/oauth/token

# OAuth Credentials
CLIENT_ID=your_client_id_here
CLIENT_SECRET=your_client_secret_here
USERNAME=your_username_here
PASSWORD=your_password_here

# Dealer Information (for auto-population)
DEALER_CODE=YOUR_DEALER_CODE
DEALER_ID=12345

# Debug Mode (set to false in production)
MPS_DEBUG=true

# Timeouts
MPS_TIMEOUT=30
MPS_CONNECT_TIMEOUT=10
```

Save and set permissions:
```bash
chmod 600 .env
```

---

## Step 3: Verify Fix (10 seconds)

```bash
# Check diagnostics again
curl https://mpsm.resolutionsbydesign.us/mps-api/diagnostics | grep initialization

# Should show: "initialization": "success"

# Or check health directly
curl https://mpsm.resolutionsbydesign.us/mps-api/health
```

---

## Step 4: Test a Real Query (20 seconds)

```bash
curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/query \
  -H "Content-Type: application/json" \
  -d '{"action":"getDealerInfo","params":{}}'
```

Should return dealer information or a proper API error (not 500).

---

## Complete Deployment Checklist

If starting from scratch, verify:

- [ ] All files uploaded (index.php, engine.php, SwaggerActionRegistry.php, Swagger.json)
- [ ] Logs directory exists: `mkdir -p logs && chmod 755 logs`
- [ ] .env file created with all credentials
- [ ] .env permissions: `chmod 600 .env`
- [ ] PHP version 7.4+ (`php -v`)
- [ ] PHP extensions installed (`php -m | grep -E "curl|json|mbstring"`)
- [ ] Web server configured (Apache/Nginx virtual host)
- [ ] SSL certificate working
- [ ] Diagnostics endpoint accessible
- [ ] Health endpoint returns "ok"

---

## One-Command Server Setup

```bash
# Run this on your server
cd /var/www/mpsm.resolutionsbydesign.us/mps-api && \
mkdir -p logs && \
chmod 755 logs && \
echo "Logs directory created" && \
ls -la
```

---

## Still Having Issues?

1. **Enable debug mode** in `.env`:
   ```bash
   MPS_DEBUG=true
   ```

2. **Check diagnostics with debug enabled:**
   ```bash
   curl https://mpsm.resolutionsbydesign.us/mps-api/diagnostics | jq
   ```

3. **Look at the error details:**
   - `engine.error.message` - What went wrong
   - `engine.error.file` - Which file has the error
   - `engine.error.line` - Exact line number
   - `recent_errors` - Recent log entries

4. **Check Apache/Nginx logs:**
   ```bash
   tail -50 /var/log/apache2/error.log
   # OR
   tail -50 /var/log/nginx/error.log
   ```

---

## The Diagnostics Endpoint is Your Friend

**Remember:** The `/diagnostics` endpoint tells you EXACTLY what's wrong. You don't need to guess!

```bash
curl https://mpsm.resolutionsbydesign.us/mps-api/diagnostics | jq
```

Look for:
- ❌ `false` values in `system.files`
- ❌ `false` values in `env_config.has_required`
- ❌ `"failed"` in `engine.initialization`
- ❌ Error messages in `engine.error`

Fix those issues and your engine will work! 🚀
