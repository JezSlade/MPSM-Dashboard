# MPS MONITORS API ENGINE - COMPLETE HANDOFF DOCUMENTATION

**Project:** MPS Monitors API Integration  
**Version:** 1.0.0  
**Deployment:** https://mpsm.resolutionsbydesign.us/mps-api/  
**Status:** Production Ready  
**Date:** October 2024  

---

## EXECUTIVE SUMMARY

This document provides complete operational control of the MPS Monitors API Engine. After reading this, you will be able to:

✅ Deploy and configure the API engine  
✅ Integrate with ChatGPT Actions  
✅ Build dashboard integrations  
✅ Troubleshoot any issues  
✅ Maintain and update the system  
✅ Extend functionality as needed  

---

## TABLE OF CONTENTS

1. [System Overview](#system-overview)
2. [File Inventory](#file-inventory)
3. [Architecture Deep Dive](#architecture-deep-dive)
4. [Deployment Instructions](#deployment-instructions)
5. [Configuration Management](#configuration-management)
6. [API Usage Guide](#api-usage-guide)
7. [ChatGPT Actions Integration](#chatgpt-actions-integration)
8. [Dashboard Integration](#dashboard-integration)
9. [Monitoring & Maintenance](#monitoring--maintenance)
10. [Troubleshooting Guide](#troubleshooting-guide)
11. [Security Considerations](#security-considerations)
12. [Extension & Customization](#extension--customization)
13. [Testing Procedures](#testing-procedures)
14. [Emergency Procedures](#emergency-procedures)

---

## SYSTEM OVERVIEW

### What This Is

A lightweight PHP-based API gateway that:
- Interfaces with MPS Monitors API
- Provides unified endpoints for ChatGPT Actions
- Enables REST API access for dashboards
- Deploys to GreenGeeks shared hosting subdirectories

### Key Design Principles

1. **Zero Dependencies** - Pure PHP, no frameworks or external libraries
2. **Subdirectory Safe** - All paths relative, no root assumptions
3. **Single Responsibility** - Each file has one clear purpose
4. **Error Resilience** - Comprehensive error handling and logging
5. **ChatGPT Optimized** - Unified `/query` endpoint for AI assistants

### Technology Stack

- **Language**: PHP 7.4+
- **Server**: Apache with mod_rewrite
- **HTTP Client**: cURL
- **Config**: Environment variables (.env)
- **Documentation**: OpenAPI 3.0 (Swagger)
- **Hosting**: GreenGeeks Shared (Subdirectory)

---

## FILE INVENTORY

### Core Files (Required)

| File | Purpose | Size | Critical |
|------|---------|------|----------|
| `index.php` | Main router & entry point | ~8KB | ✅ YES |
| `engine.php` | Core API engine class | ~6KB | ✅ YES |
| `config.php` | Configuration loader | ~2KB | ✅ YES |
| `.env` | Environment variables | <1KB | ✅ YES |
| `.htaccess` | Apache configuration | ~1KB | ✅ YES |

### Documentation Files

| File | Purpose | Size | Required |
|------|---------|------|----------|
| `README.md` | Technical documentation | ~12KB | Recommended |
| `DEPLOYMENT.md` | Deployment guide | ~15KB | Recommended |
| `HANDOFF.md` | This file | ~20KB | Recommended |
| `SDK_Examples_Verified_Working.md` | Code examples | ~10KB | Recommended |

### API Definition Files

| File | Purpose | Size | Required |
|------|---------|------|----------|
| `swagger.json` | OpenAPI specification | ~8KB | For ChatGPT Actions |
| `.env.example` | Config template | <1KB | For setup |

### Auto-Generated

| Directory/File | Purpose | Created When |
|---------------|---------|--------------|
| `logs/` | Error logging directory | Auto-created |
| `logs/error_*.log` | Engine error logs | On error |
| `logs/php_errors_*.log` | PHP error logs | On PHP error |
| `logs/config_error_*.log` | Config errors | On config issue |

---

## ARCHITECTURE DEEP DIVE

### Request Flow

```
1. HTTP Request arrives
   ↓
2. Apache .htaccess processes
   ↓
3. index.php receives request
   ↓
4. Router determines endpoint
   ↓
5. engine.php method called
   ↓
6. config.php loads .env
   ↓
7. cURL request to MPS API
   ↓
8. Response formatted as JSON
   ↓
9. Returned to client
```

### File Responsibilities

#### `index.php` - Main Router

**Responsibilities:**
- Parse incoming HTTP requests
- Route to appropriate handler
- Manage CORS headers
- Format JSON responses
- Error handling wrapper

**Key Functions:**
- `getBasePath()` - Detect subdirectory location
- `getRequestPath()` - Parse request URL
- `sendResponse()` - Standardized JSON output
- `getRequestBody()` - Parse POST data

**Routes:**
- `/` - API information
- `/health` - Health check
- `/endpoints` - Endpoint listing
- `/query` - Unified query endpoint (primary)
- `/monitors/*` - Direct monitor access
- `/alerts` - Alert listing
- `/swagger.json` - API specification

#### `engine.php` - Core Engine

**Responsibilities:**
- Make HTTP requests to MPS API
- Handle authentication
- Process responses
- Error logging
- Provide helper methods

**Key Class: `MPSMonitorEngine`**

**Properties:**
- `$config` - Configuration array
- `$instance` - Singleton instance

**Core Methods:**
- `makeRequest()` - Universal HTTP client
- `getMonitors()` - List monitors
- `getMonitor()` - Get single monitor
- `createMonitor()` - Create new monitor
- `updateMonitor()` - Update monitor
- `deleteMonitor()` - Delete monitor
- `getAlerts()` - Get alerts
- `getStatistics()` - Get statistics
- `healthCheck()` - Health verification

**Request Structure:**
```php
$engine->makeRequest(
    $endpoint,    // e.g., 'monitors' or 'monitors/123'
    $method,      // 'GET', 'POST', 'PUT', 'DELETE'
    $data,        // Request body (array)
    $queryParams  // URL parameters (array)
);
```

#### `config.php` - Configuration Loader

**Responsibilities:**
- Load .env file
- Parse environment variables
- Validate required settings
- Provide defaults
- Error logging on config failure

**Functions:**
- `loadEnvironment()` - Parse .env file
- `validateConfig()` - Ensure required vars present

**Configuration Flow:**
```php
define('MPS_ENGINE_ACCESS', true);  // Security gate
require 'config.php';                // Loads and validates
$config = (returned array);          // Used by engine
```

#### `.htaccess` - Apache Configuration

**Responsibilities:**
- URL rewriting for clean routes
- Security rules for sensitive files
- CORS header injection (backup)
- PHP settings override

**Key Rules:**
```apache
RewriteBase /mps-api/          # Subdirectory path
RewriteRule ^(.*)$ index.php   # Route all to index
FilesMatch "^\.env"            # Block .env access
FilesMatch "\.(log)$"          # Block log access
```

### Data Flow Examples

#### Example 1: Get Monitors via Query Endpoint

```
Client Request:
POST /mps-api/query
{
  "action": "getMonitors",
  "params": {"status": "active"}
}

↓

index.php:
- Routes to /query handler
- Extracts action & params
- Calls $engine->getMonitors(['status' => 'active'])

↓

engine.php:
- makeRequest('monitors', 'GET', [], ['status' => 'active'])
- Builds URL: MPS_BASE_URL/monitors?status=active
- Adds Authorization: Bearer {API_KEY}
- cURL executes

↓

MPS API Response:
{
  "monitors": [...]
}

↓

engine.php:
- Wraps in standard format:
{
  "success": true,
  "data": {...},
  "http_code": 200
}

↓

index.php:
- sendResponse() outputs JSON
- Client receives formatted response
```

#### Example 2: Health Check

```
Client Request:
GET /mps-api/health

↓

index.php:
- Detects /health route
- Calls $engine->healthCheck()

↓

engine.php:
- Makes request to MPS API health endpoint
- Measures response time
- Returns status

↓

Client Response:
{
  "status": "healthy",
  "api_connection": true,
  "response_time": "123.45ms"
}
```

---

## DEPLOYMENT INSTRUCTIONS

### Prerequisites Checklist

- [ ] GreenGeeks hosting account with cPanel access
- [ ] MPS Monitors API credentials (base URL + API key)
- [ ] FTP/SFTP credentials or cPanel File Manager access
- [ ] Text editor for configuration

### Quick Deployment (5 Minutes)

**Step 1:** Upload all files to `public_html/mps-api/`

**Step 2:** Copy `.env.example` to `.env`, edit:
```env
MPS_BASE_URL=https://api.mpsmonitors.com/v1
MPS_API_KEY=your_actual_api_key
```

**Step 3:** Set permissions:
- `.env` → 644
- `logs/` → 755

**Step 4:** Test: `https://yourdomain.com/mps-api/health`

**Expected:** Status "healthy" with response time

See `DEPLOYMENT.md` for detailed instructions.

---

## CONFIGURATION MANAGEMENT

### Environment Variables Explained

#### `MPS_BASE_URL`

**Purpose:** Base URL for MPS Monitors API  
**Format:** Full HTTPS URL without trailing slash  
**Example:** `https://api.mpsmonitors.com/v1`  
**Required:** ✅ YES  

**Common Values:**
- Production: `https://api.mpsmonitors.com/v1`
- Staging: `https://api-staging.mpsmonitors.com/v1`
- Custom: Your MPS instance URL

#### `MPS_API_KEY`

**Purpose:** Authentication token for MPS API  
**Format:** String token/key  
**Example:** `sk_live_abc123xyz789`  
**Required:** ✅ YES  
**Security:** Never commit to git, never expose in responses

**Getting Your Key:**
1. Log into MPS Monitors dashboard
2. Navigate to Settings → API Keys
3. Generate or copy existing key
4. Paste into `.env`

#### `MPS_TIMEOUT`

**Purpose:** Maximum seconds to wait for API response  
**Format:** Integer (seconds)  
**Default:** 30  
**Required:** ❌ NO  
**Recommended:** 30-60

**Adjust if:**
- Slow network: Increase to 60
- Fast network: Decrease to 15
- Timeout errors: Increase by 15

#### `MPS_DEBUG`

**Purpose:** Enable detailed error logging  
**Format:** Boolean (true/false)  
**Default:** false  
**Required:** ❌ NO  

**When to enable:**
- Troubleshooting issues
- Development/testing
- Initial setup verification

**When to disable:**
- Production (performance)
- After issues resolved

### Changing Configuration

**Method 1: cPanel File Manager**
1. Navigate to `mps-api/.env`
2. Right-click → Edit
3. Modify values
4. Save Changes
5. No restart needed (loaded per-request)

**Method 2: FTP/SFTP**
1. Download `.env`
2. Edit locally
3. Upload back to server
4. Overwrite existing

**Method 3: SSH**
```bash
cd ~/public_html/mps-api
nano .env
# Edit, then Ctrl+X, Y, Enter
```

**Testing Changes:**
```bash
curl https://yourdomain.com/mps-api/health
```

Should reflect new configuration immediately.

---

## API USAGE GUIDE

### Endpoint Categories

#### 1. Information Endpoints

**GET /** - API Information
```bash
curl https://mpsm.resolutionsbydesign.us/mps-api/
```
Returns: Service info, version, available endpoints

**GET /health** - Health Check
```bash
curl https://mpsm.resolutionsbydesign.us/mps-api/health
```
Returns: Connection status, response time

**GET /endpoints** - Endpoint List
```bash
curl https://mpsm.resolutionsbydesign.us/mps-api/endpoints
```
Returns: All available endpoints with descriptions

**GET /swagger.json** - API Specification
```bash
curl https://mpsm.resolutionsbydesign.us/mps-api/swagger.json
```
Returns: OpenAPI 3.0 specification

#### 2. Query Endpoint (Primary)

**POST /query** - Unified Query Interface

This is the PRIMARY endpoint for ChatGPT Actions.

**Request Format:**
```json
{
  "action": "ACTION_NAME",
  "params": {
    // Action-specific parameters
  }
}
```

**Available Actions:**

| Action | Description | Required Params |
|--------|-------------|-----------------|
| `getMonitors` | List monitors | None |
| `getMonitor` | Get single monitor | `id` |
| `createMonitor` | Create monitor | `name`, `url` |
| `updateMonitor` | Update monitor | `id` + fields |
| `deleteMonitor` | Delete monitor | `id` |
| `getAlerts` | List alerts | None |
| `getStatistics` | Get stats | `id`, optional `period` |
| `healthCheck` | Health check | None |

**Examples:**

```bash
# Get all monitors
curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/query \
  -H "Content-Type: application/json" \
  -d '{"action":"getMonitors","params":{}}'

# Get specific monitor
curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/query \
  -H "Content-Type: application/json" \
  -d '{"action":"getMonitor","params":{"id":"monitor_123"}}'

# Create monitor
curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/query \
  -H "Content-Type: application/json" \
  -d '{
    "action": "createMonitor",
    "params": {
      "name": "My Website",
      "url": "https://example.com",
      "interval": 60
    }
  }'
```

#### 3. Direct REST Endpoints

For traditional REST API access:

**GET /monitors** - List Monitors
```bash
curl https://mpsm.resolutionsbydesign.us/mps-api/monitors
```

**GET /monitors/{id}** - Get Monitor
```bash
curl https://mpsm.resolutionsbydesign.us/mps-api/monitors/monitor_123
```

**POST /monitors** - Create Monitor
```bash
curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/monitors \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","url":"https://example.com"}'
```

**PUT /monitors/{id}** - Update Monitor
```bash
curl -X PUT https://mpsm.resolutionsbydesign.us/mps-api/monitors/monitor_123 \
  -H "Content-Type: application/json" \
  -d '{"name":"Updated Name"}'
```

**DELETE /monitors/{id}** - Delete Monitor
```bash
curl -X DELETE https://mpsm.resolutionsbydesign.us/mps-api/monitors/monitor_123
```

**GET /alerts** - List Alerts
```bash
curl https://mpsm.resolutionsbydesign.us/mps-api/alerts
```

**GET /monitors/{id}/statistics** - Get Statistics
```bash
curl "https://mpsm.resolutionsbydesign.us/mps-api/monitors/monitor_123/statistics?period=24h"
```

### Response Formats

**Success Response:**
```json
{
  "success": true,
  "data": {
    // Response data
  },
  "http_code": 200
}
```

**Error Response:**
```json
{
  "success": false,
  "error": "Error message here",
  "http_code": 400
}
```

### HTTP Status Codes

| Code | Meaning | When Used |
|------|---------|-----------|
| 200 | OK | Successful request |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request data |
| 403 | Forbidden | Accessing protected resource |
| 404 | Not Found | Endpoint doesn't exist |
| 500 | Server Error | Internal error occurred |

---

## CHATGPT ACTIONS INTEGRATION

### Setup Process

**Step 1: Access GPT Settings**
1. Go to ChatGPT
2. Create or edit Custom GPT
3. Navigate to "Actions" section

**Step 2: Import Schema**

**Option A: Import from URL**
```
Schema URL: https://mpsm.resolutionsbydesign.us/mps-api/swagger.json
```

**Option B: Paste JSON**
1. Open `swagger.json` file
2. Copy entire contents
3. Paste into Schema field

**Step 3: Configure Authentication**
- Authentication Type: **None**
- (API key handled by backend)

**Step 4: Test Actions**

Use these test prompts:
```
"Check the health of my MPS API"
"Show me all monitors"
"Create a monitor for https://example.com"
"What alerts do I have?"
"Get statistics for monitor_123"
```

### ChatGPT Prompt Examples

**Monitoring:**
- "Show me all my active monitors"
- "Which monitors are down right now?"
- "What's the status of monitor_123?"

**Creating:**
- "Create a monitor for https://mywebsite.com checking every 5 minutes"
- "Set up monitoring for my API at https://api.example.com"

**Updating:**
- "Change the interval for monitor_123 to 120 seconds"
- "Rename monitor_123 to 'Production API'"

**Alerts:**
- "Show me all active alerts"
- "What alerts happened in the last 24 hours?"

**Statistics:**
- "Show me statistics for monitor_123 over the last 7 days"
- "What's the uptime for all my monitors?"

### Troubleshooting ChatGPT Actions

**Issue: "Action failed"**
- Check API health: Visit `/health` endpoint
- Verify Swagger JSON loads: Visit `/swagger.json`
- Check ChatGPT Actions logs for error details

**Issue: "Schema validation failed"**
- Re-import swagger.json
- Check for JSON syntax errors
- Verify server URL in swagger.json matches deployment

**Issue: "No data returned"**
- Check MPS API credentials in `.env`
- Test endpoint directly with cURL
- Check error logs

---

## DASHBOARD INTEGRATION

### Frontend Integration Example

#### JavaScript/Fetch

```javascript
class MPSApiClient {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }
    
    async query(action, params = {}) {
        const response = await fetch(`${this.baseUrl}/query`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action, params })
        });
        return await response.json();
    }
    
    async getMonitors() {
        return this.query('getMonitors');
    }
    
    async createMonitor(name, url, interval = 60) {
        return this.query('createMonitor', { name, url, interval });
    }
    
    async getAlerts(filters = {}) {
        return this.query('getAlerts', filters);
    }
}

// Usage
const api = new MPSApiClient('https://mpsm.resolutionsbydesign.us/mps-api');

// Load monitors
const monitors = await api.getMonitors();
console.log('Monitors:', monitors.data);

// Create monitor
const result = await api.createMonitor(
    'My Website',
    'https://example.com',
    60
);
```

#### React Integration

```jsx
import { useState, useEffect } from 'react';

function MonitorsDashboard() {
    const [monitors, setMonitors] = useState([]);
    const [loading, setLoading] = useState(true);
    
    useEffect(() => {
        async function loadMonitors() {
            try {
                const response = await fetch(
                    'https://mpsm.resolutionsbydesign.us/mps-api/monitors'
                );
                const data = await response.json();
                setMonitors(data.data || []);
            } catch (error) {
                console.error('Error loading monitors:', error);
            } finally {
                setLoading(false);
            }
        }
        
        loadMonitors();
    }, []);
    
    if (loading) return <div>Loading...</div>;
    
    return (
        <div>
            <h1>Monitors</h1>
            <ul>
                {monitors.map(monitor => (
                    <li key={monitor.id}>
                        {monitor.name} - {monitor.status}
                    </li>
                ))}
            </ul>
        </div>
    );
}
```

### CORS Configuration

Default allows all origins. For production dashboard:

**Update `index.php` line 25:**
```php
header('Access-Control-Allow-Origin: https://your-dashboard.com');
```

**Update `.htaccess` line 26:**
```apache
Header set Access-Control-Allow-Origin "https://your-dashboard.com"
```

### Dashboard Features Checklist

- [ ] Monitor listing with status indicators
- [ ] Create monitor form
- [ ] Edit monitor functionality
- [ ] Delete confirmation dialog
- [ ] Real-time alerts display
- [ ] Statistics graphs/charts
- [ ] Health check indicator
- [ ] Error handling with user feedback

---

## MONITORING & MAINTENANCE

### Daily Checks

**Automated Health Check:**
```bash
# Add to cron (daily at 9am)
0 9 * * * curl -s https://mpsm.resolutionsbydesign.us/mps-api/health | grep -q "healthy" || echo "API Down!" | mail -s "MPS API Alert" admin@example.com
```

**Manual Verification:**
1. Visit `/health` endpoint
2. Check response time (<500ms ideal)
3. Verify `"status": "healthy"`

### Weekly Tasks

**Review Logs:**
```bash
cd mps-api/logs
tail -100 error_$(date +%Y-%m-%d).log
```

Look for:
- Repeated errors (indicates persistent issue)
- High frequency errors (performance problem)
- Authentication failures (credentials issue)

**Performance Check:**
```bash
# Test response time
time curl -s https://mpsm.resolutionsbydesign.us/mps-api/health
```

Expected: <1 second total time

### Monthly Tasks

**Log Cleanup:**
```bash
cd mps-api/logs
# Keep last 30 days, delete older
find . -name "*.log" -mtime +30 -delete
```

**Configuration Audit:**
1. Review `.env` file
2. Verify API key still valid
3. Check timeout settings appropriate
4. Update if needed

**Security Review:**
1. Test `.env` blocked: `curl https://yourdomain.com/mps-api/.env` → Should 403
2. Test logs blocked: `curl https://yourdomain.com/mps-api/logs/` → Should 403
3. Review access logs for suspicious activity

### Backup Procedures

**What to Backup:**
- `.env` file (CRITICAL - contains credentials)
- `engine.php` (if customized)
- `index.php` (if customized)
- `logs/` (optional, for forensics)

**Backup Script:**
```bash
#!/bin/bash
DATE=$(date +%Y-%m-%d)
BACKUP_DIR=~/backups
mkdir -p $BACKUP_DIR

cd ~/public_html/mps-api
tar -czf $BACKUP_DIR/mps-api-backup-$DATE.tar.gz \
    .env engine.php index.php config.php .htaccess

echo "Backup created: mps-api-backup-$DATE.tar.gz"
```

**Restoration:**
```bash
cd ~/public_html/mps-api
tar -xzf ~/backups/mps-api-backup-YYYY-MM-DD.tar.gz
# Test with /health endpoint
```

---

## TROUBLESHOOTING GUIDE

### Common Issues & Solutions

#### Issue 1: 500 Internal Server Error

**Symptoms:**
- All endpoints return 500
- White screen or generic error

**Diagnosis:**
```bash
# Check PHP error log
tail -50 logs/php_errors_$(date +%Y-%m-%d).log

# Check config error log
tail -50 logs/config_error_$(date +%Y-%m-%d).log

# Test .env file
cat .env
```

**Common Causes:**

**A. Missing .env file**
```bash
# Solution
cp .env.example .env
nano .env  # Add credentials
```

**B. Invalid .env syntax**
```bash
# Check for:
# - Missing values
# - Extra quotes
# - Special characters without quotes

# Valid:
MPS_API_KEY=abc123
MPS_API_KEY="abc123"

# Invalid:
MPS_API_KEY=
MPS_API_KEY=abc 123  # Space without quotes
```

**C. PHP version < 7.4**
```bash
# Check PHP version
php -v

# Solution: Contact GreenGeeks to upgrade PHP version
```

**D. File permissions**
```bash
# Fix permissions
chmod 644 .env
chmod 755 logs/
chmod 644 *.php
```

#### Issue 2: 404 Not Found

**Symptoms:**
- Endpoints return 404
- Only index.php works directly

**Diagnosis:**
```bash
# Test .htaccess loaded
curl -I https://yourdomain.com/mps-api/health
# Look for X-headers or check CORS headers
```

**Common Causes:**

**A. .htaccess not uploaded**
```bash
# Solution: Upload .htaccess file
```

**B. Wrong RewriteBase**
```bash
# Edit .htaccess line 9
# If subdirectory is /api/ instead of /mps-api/:
RewriteBase /api/
```

**C. mod_rewrite disabled**
```bash
# Contact GreenGeeks support to enable mod_rewrite
# Usually enabled by default
```

#### Issue 3: CORS Errors

**Symptoms:**
- Browser console: "CORS policy"
- Requests work in cURL but not browser

**Diagnosis:**
```bash
# Check CORS headers
curl -I https://yourdomain.com/mps-api/health
# Should see: Access-Control-Allow-Origin: *
```

**Solutions:**

**A. Update index.php (line 25)**
```php
// For specific domain
header('Access-Control-Allow-Origin: https://your-dashboard.com');

// For all (dev only)
header('Access-Control-Allow-Origin: *');
```

**B. Update .htaccess (line 26)**
```apache
Header set Access-Control-Allow-Origin "https://your-dashboard.com"
```

**C. Preflight requests**
```bash
# Test OPTIONS request
curl -X OPTIONS https://yourdomain.com/mps-api/query
# Should return 200
```

#### Issue 4: API Connection Failed

**Symptoms:**
- `/health` returns "unhealthy"
- "API Connection Failed" errors

**Diagnosis:**
```bash
# Test MPS API directly
curl -H "Authorization: Bearer YOUR_KEY" \
     https://api.mpsmonitors.com/v1/health

# Check error logs
tail -50 logs/error_$(date +%Y-%m-%d).log
```

**Common Causes:**

**A. Invalid API key**
```bash
# Solution: Get new key from MPS dashboard
# Update .env
nano .env
# Change MPS_API_KEY value
```

**B. Wrong base URL**
```bash
# Verify correct URL in .env
# Should be: https://api.mpsmonitors.com/v1
# NOT: https://api.mpsmonitors.com/v1/
```

**C. Firewall blocking**
```bash
# Test outbound HTTPS
curl -I https://www.google.com
# If fails, contact GreenGeeks about firewall

# Check if cURL enabled
php -m | grep curl
# Should show "curl"
```

**D. Network timeout**
```bash
# Increase timeout in .env
MPS_TIMEOUT=60  # Up from 30
```

#### Issue 5: Logs Not Created

**Symptoms:**
- No files in logs/ directory
- Errors not being logged

**Solutions:**

**A. Create logs directory**
```bash
mkdir logs
chmod 755 logs
```

**B. Check PHP error_log setting**
```bash
# Create phpinfo.php
<?php phpinfo(); ?>

# Visit in browser, search for "error_log"
# Should point to logs directory
```

**C. Manually test logging**
```bash
# Create test log
echo "test" > logs/test.log
# If fails, permissions issue
```

### Diagnostic Commands

**Full System Check:**
```bash
#!/bin/bash
echo "=== MPS API Diagnostics ==="
echo ""

echo "1. PHP Version:"
php -v | head -1

echo ""
echo "2. cURL Enabled:"
php -m | grep curl

echo ""
echo "3. Files Present:"
ls -la | grep -E "\.(php|env|htaccess|json)$"

echo ""
echo "4. Permissions:"
ls -l .env logs/

echo ""
echo "5. .env Contents (values hidden):"
cat .env | sed 's/=.*/=***/'

echo ""
echo "6. Health Check:"
curl -s https://yourdomain.com/mps-api/health | python -m json.tool

echo ""
echo "7. Recent Errors:"
tail -10 logs/error_$(date +%Y-%m-%d).log 2>/dev/null || echo "No errors today"

echo ""
echo "=== End Diagnostics ==="
```

---

## SECURITY CONSIDERATIONS

### Authentication

**Current Model:**
- No authentication on gateway endpoints
- MPS API key secured in backend `.env`
- API key never exposed to clients

**Adding Authentication (Optional):**

**Option 1: HTTP Basic Auth**
```apache
# Add to .htaccess before RewriteEngine
AuthType Basic
AuthName "MPS API Access"
AuthUserFile /home/username/public_html/mps-api/.htpasswd
Require valid-user
```

Create `.htpasswd`:
```bash
htpasswd -c .htpasswd apiuser
```

**Option 2: API Key Header**

Modify `index.php` after line 27:
```php
// Add API key check
$providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
$validKey = 'your_gateway_api_key_here';

if ($providedKey !== $validKey) {
    sendResponse(['error' => 'Unauthorized'], 401);
}
```

Clients must send:
```bash
curl -H "X-API-Key: your_gateway_api_key_here" \
     https://yourdomain.com/mps-api/health
```

### File Security

**Protected by .htaccess:**
- `.env` - Returns 403
- `.git/` - Returns 403  
- `*.log` - Returns 403

**Test Protection:**
```bash
# Should all return 403
curl https://yourdomain.com/mps-api/.env
curl https://yourdomain.com/mps-api/logs/error.log
```

### Input Validation

**Current Validation:**
- Required fields checked (action, params)
- Monitor IDs validated
- JSON parsing with error handling

**Enhance Validation:**

In `index.php`, add after line 90:
```php
// Validate monitor ID format
if (isset($params['id']) && !preg_match('/^[a-zA-Z0-9_-]+$/', $params['id'])) {
    sendResponse(['error' => 'Invalid monitor ID format'], 400);
}

// Validate URL format
if (isset($params['url']) && !filter_var($params['url'], FILTER_VALIDATE_URL)) {
    sendResponse(['error' => 'Invalid URL format'], 400);
}
```

### Rate Limiting

**Option 1: .htaccess (Basic)**
```apache
<IfModule mod_ratelimit.c>
    SetOutputFilter RATE_LIMIT
    SetEnv rate-limit 100
</IfModule>
```

**Option 2: PHP (Advanced)**

Add to `index.php` after line 30:
```php
function checkRateLimit($identifier) {
    $logFile = __DIR__ . '/logs/ratelimit_' . date('Y-m-d-H') . '.log';
    $requests = file_exists($logFile) ? count(file($logFile)) : 0;
    
    if ($requests > 1000) {  // 1000 requests per hour
        sendResponse(['error' => 'Rate limit exceeded'], 429);
    }
    
    file_put_contents($logFile, $identifier . "\n", FILE_APPEND);
}

// Use:
checkRateLimit($_SERVER['REMOTE_ADDR']);
```

### IP Whitelisting

**For production API, limit to known IPs:**

`.htaccess` before RewriteEngine:
```apache
Order Deny,Allow
Deny from all
Allow from 123.456.789.0       # Your dashboard server
Allow from 98.765.432.0        # Your office
Allow from 10.0.0.0/8          # Internal network
```

---

## EXTENSION & CUSTOMIZATION

### Adding New Endpoints

**Step 1: Add Method to engine.php**
```php
// Add to MPSMonitorEngine class
public function getMonitorUptime($monitorId) {
    return $this->makeRequest("monitors/{$monitorId}/uptime", 'GET');
}
```

**Step 2: Add Route to index.php**
```php
// Add to routing section (around line 140)
if (preg_match('#^/monitors/(.+)/uptime$#', $path, $matches)) {
    $monitorId = $matches[1];
    $result = $engine->getMonitorUptime($monitorId);
    sendResponse($result);
}
```

**Step 3: Update Swagger (Optional)**
```json
// Add to swagger.json paths
"/monitors/{id}/uptime": {
  "get": {
    "summary": "Get Monitor Uptime",
    "operationId": "getMonitorUptime",
    "parameters": [
      {
        "name": "id",
        "in": "path",
        "required": true,
        "schema": {"type": "string"}
      }
    ],
    "responses": {
      "200": {
        "description": "Uptime data"
      }
    }
  }
}
```

**Step 4: Add to Query Endpoint**
```php
// In index.php query handler (around line 100)
case 'getMonitorUptime':
    if (empty($params['id'])) {
        sendResponse(['error' => 'Monitor ID required'], 400);
    }
    $result = $engine->getMonitorUptime($params['id']);
    break;
```

### Adding Response Caching

**Simple File-Based Cache:**

Add to `engine.php`:
```php
private function getCachedResponse($key, $ttl = 60) {
    $cacheFile = __DIR__ . '/cache/' . md5($key) . '.cache';
    
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if (time() - $data['timestamp'] < $ttl) {
            return $data['response'];
        }
    }
    
    return null;
}

private function setCachedResponse($key, $response) {
    $cacheDir = __DIR__ . '/cache';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = $cacheDir . '/' . md5($key) . '.cache';
    file_put_contents($cacheFile, json_encode([
        'timestamp' => time(),
        'response' => $response
    ]));
}

// Usage in makeRequest():
public function makeRequest($endpoint, $method = 'GET', $data = [], $queryParams = []) {
    // Cache only GET requests
    if ($method === 'GET') {
        $cacheKey = $endpoint . json_encode($queryParams);
        $cached = $this->getCachedResponse($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
    }
    
    // ... existing request code ...
    
    // Cache the response
    if ($method === 'GET' && $result['success']) {
        $this->setCachedResponse($cacheKey, $result);
    }
    
    return $result;
}
```

### Custom Error Handling

**Add Custom Error Handler:**

In `index.php` after line 18:
```php
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $errorTypes = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_NOTICE => 'NOTICE'
    ];
    
    $type = $errorTypes[$errno] ?? 'UNKNOWN';
    $logMessage = "[{$type}] {$errstr} in {$errfile} on line {$errline}";
    
    error_log($logMessage);
    
    // Don't expose internal errors to clients
    if ($errno === E_ERROR) {
        sendResponse(['error' => 'Internal server error'], 500);
    }
});
```

### Adding Webhooks

**Webhook Endpoint:**

Add to `engine.php`:
```php
public function registerWebhook($url, $events = []) {
    return $this->makeRequest('webhooks', 'POST', [
        'url' => $url,
        'events' => $events
    ]);
}

public function getWebhooks() {
    return $this->makeRequest('webhooks', 'GET');
}

public function deleteWebhook($webhookId) {
    return $this->makeRequest("webhooks/{$webhookId}", 'DELETE');
}
```

Add routes to `index.php`:
```php
// In query handler
case 'registerWebhook':
    if (empty($params['url'])) {
        sendResponse(['error' => 'Webhook URL required'], 400);
    }
    $result = $engine->registerWebhook($params['url'], $params['events'] ?? []);
    break;

case 'getWebhooks':
    $result = $engine->getWebhooks();
    break;

case 'deleteWebhook':
    if (empty($params['id'])) {
        sendResponse(['error' => 'Webhook ID required'], 400);
    }
    $result = $engine->deleteWebhook($params['id']);
    break;
```

---

## TESTING PROCEDURES

### Pre-Deployment Testing

**1. Local Testing (if possible)**
```bash
php -S localhost:8000 -t /path/to/mps-api
```

**2. Configuration Validation**
```bash
# Test .env parsing
php -r "require 'config.php';"
# Should not error
```

**3. File Presence Check**
```bash
ls -l index.php engine.php config.php .env .htaccess swagger.json
# All should exist
```

### Post-Deployment Testing

**Automated Test Script:**

```bash
#!/bin/bash
API_BASE="https://yourdomain.com/mps-api"

echo "=== MPS API Test Suite ==="

# Test 1: Root endpoint
echo -n "1. Root endpoint... "
curl -s "$API_BASE/" | grep -q "status" && echo "✓ PASS" || echo "✗ FAIL"

# Test 2: Health check
echo -n "2. Health check... "
curl -s "$API_BASE/health" | grep -q "healthy" && echo "✓ PASS" || echo "✗ FAIL"

# Test 3: Endpoints list
echo -n "3. Endpoints list... "
curl -s "$API_BASE/endpoints" | grep -q "success" && echo "✓ PASS" || echo "✗ FAIL"

# Test 4: Swagger JSON
echo -n "4. Swagger JSON... "
curl -s "$API_BASE/swagger.json" | grep -q "openapi" && echo "✓ PASS" || echo "✗ FAIL"

# Test 5: Query endpoint
echo -n "5. Query endpoint... "
curl -s -X POST "$API_BASE/query" \
  -H "Content-Type: application/json" \
  -d '{"action":"healthCheck","params":{}}' | grep -q "success" && echo "✓ PASS" || echo "✗ FAIL"

# Test 6: CORS headers
echo -n "6. CORS headers... "
curl -s -I "$API_BASE/health" | grep -q "Access-Control-Allow-Origin" && echo "✓ PASS" || echo "✗ FAIL"

# Test 7: .env protection
echo -n "7. .env protection... "
curl -s -o /dev/null -w "%{http_code}" "$API_BASE/.env" | grep -q "403" && echo "✓ PASS" || echo "✗ FAIL"

# Test 8: Log protection
echo -n "8. Log protection... "
curl -s -o /dev/null -w "%{http_code}" "$API_BASE/logs/" | grep -q "403" && echo "✓ PASS" || echo "✗ FAIL"

echo "=== Testing Complete ==="
```

### Integration Testing

**JavaScript Test:**
```javascript
async function testAPI() {
    const base = 'https://mpsm.resolutionsbydesign.us/mps-api';
    
    // Test health
    const health = await fetch(`${base}/health`).then(r => r.json());
    console.log('Health:', health.status);
    
    // Test query endpoint
    const monitors = await fetch(`${base}/query`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'getMonitors', params: {}})
    }).then(r => r.json());
    console.log('Monitors:', monitors.success);
}

testAPI();
```

### Performance Testing

**Response Time:**
```bash
# Test 10 requests
for i in {1..10}; do
    time curl -s https://yourdomain.com/mps-api/health > /dev/null
done
```

**Load Testing (with Apache Bench):**
```bash
ab -n 100 -c 10 https://yourdomain.com/mps-api/health
```

Interpret results:
- Time per request: Should be <500ms
- Failed requests: Should be 0
- Requests per second: Higher is better

---

## EMERGENCY PROCEDURES

### API Down - Quick Recovery

**Step 1: Verify Issue**
```bash
# Test endpoint
curl https://yourdomain.com/mps-api/health

# If no response or error
```

**Step 2: Check Logs**
```bash
# Via cPanel File Manager or SSH
cd mps-api/logs
tail -100 error_$(date +%Y-%m-%d).log
tail -100 php_errors_$(date +%Y-%m-%d).log
```

**Step 3: Common Quick Fixes**

**A. Restart Apache (if you have access)**
```bash
# Via SSH (if available)
sudo service apache2 restart
# Or via cPanel → Restart Services
```

**B. Clear cache/temp files**
```bash
cd mps-api
rm -rf cache/*
rm -f logs/*.lock
```

**C. Reset .env file**
```bash
# If corrupted
cp .env.example .env
nano .env  # Re-enter credentials
```

**Step 4: Restore from Backup**
```bash
# If recent changes caused issue
cd ~/public_html/mps-api
tar -xzf ~/backups/mps-api-backup-LATEST.tar.gz
```

### Rollback Procedure

**If new deployment fails:**

**Step 1: Keep backup of current state**
```bash
mv mps-api mps-api.broken
```

**Step 2: Restore previous version**
```bash
tar -xzf ~/backups/mps-api-backup-YYYY-MM-DD.tar.gz
mv mps-api mps-api.restored
```

**Step 3: Test**
```bash
curl https://yourdomain.com/mps-api/health
```

**Step 4: Investigate issue in broken version**
```bash
cd mps-api.broken
tail -500 logs/*.log
```

### Data Loss Prevention

**The API is stateless - no local data stored.**

Critical to backup:
- `.env` - Contains credentials
- Customized `engine.php` or `index.php`

Everything else can be re-deployed from files.

### Contact Escalation

**Level 1: Logs & Documentation**
- Check error logs
- Review this HANDOFF.md
- Review DEPLOYMENT.md

**Level 2: Hosting Provider**
- GreenGeeks Support for server issues
- PHP version problems
- Permission issues

**Level 3: MPS Monitors**
- MPS Monitors Support for API issues
- Authentication problems
- Endpoint changes

---

## OPERATIONAL CHECKLIST

### Daily (Automated)

- [ ] Health check returns "healthy"
- [ ] Response time < 1 second
- [ ] No errors in logs (or errors are known/expected)

### Weekly (Manual)

- [ ] Review error logs for patterns
- [ ] Check response times trending
- [ ] Verify CORS working for dashboard
- [ ] Test query endpoint with sample data

### Monthly (Manual)

- [ ] Full API test suite
- [ ] Log cleanup (delete >30 days old)
- [ ] Configuration audit
- [ ] Security review (.env access blocked)
- [ ] Backup .env and customized files
- [ ] Check for MPS API updates
- [ ] Review and update documentation

### Quarterly (Manual)

- [ ] Performance benchmarking
- [ ] Security audit
- [ ] Dependency check (PHP version, cURL)
- [ ] Disaster recovery test
- [ ] Review and optimize caching
- [ ] Update Swagger documentation

---

## QUICK REFERENCE

### Essential URLs

- **Base**: https://mpsm.resolutionsbydesign.us/mps-api/
- **Health**: https://mpsm.resolutionsbydesign.us/mps-api/health
- **Endpoints**: https://mpsm.resolutionsbydesign.us/mps-api/endpoints
- **Swagger**: https://mpsm.resolutionsbydesign.us/mps-api/swagger.json

### Essential Commands

```bash
# Health check
curl https://yourdomain.com/mps-api/health

# View logs
tail -100 logs/error_$(date +%Y-%m-%d).log

# Edit config
nano .env

# Test query endpoint
curl -X POST https://yourdomain.com/mps-api/query \
  -H "Content-Type: application/json" \
  -d '{"action":"healthCheck","params":{}}'
```

### File Locations

- **Production**: `/home/username/public_html/mps-api/`
- **Logs**: `/home/username/public_html/mps-api/logs/`
- **Config**: `/home/username/public_html/mps-api/.env`
- **Backups**: `/home/username/backups/`

### Critical Files Priority

1. `.env` - CRITICAL (contains credentials)
2. `index.php` - HIGH (main router)
3. `engine.php` - HIGH (core logic)
4. `config.php` - HIGH (configuration)
5. `.htaccess` - MEDIUM (routing & security)
6. `swagger.json` - MEDIUM (API docs)
7. `logs/` - LOW (can regenerate)

---

## SUPPORT RESOURCES

### Documentation Files

- **README.md** - Technical overview and usage
- **DEPLOYMENT.md** - Deployment instructions
- **SDK_Examples_Verified_Working.md** - Code examples
- **HANDOFF.md** - This file (operational guide)

### External Resources

- **GreenGeeks Support**: support.greengeeks.com
- **MPS Monitors Docs**: (check your MPS account)
- **PHP Documentation**: php.net
- **Apache mod_rewrite**: httpd.apache.org/docs/current/mod/mod_rewrite.html

### API Endpoints for Self-Service

- `/health` - Check API status
- `/endpoints` - List available operations
- `/swagger.json` - Full API specification
- Logs directory - Diagnostic information

---

## VERSION HISTORY

**Version 1.0.0** (October 2024)
- Initial release
- Subdirectory deployment support
- ChatGPT Actions optimization
- Complete REST API coverage
- Comprehensive documentation

---

## FINAL NOTES

This API engine is designed to be:
- **Self-documenting**: Swagger spec + endpoint listing
- **Self-healing**: Comprehensive error handling
- **Self-monitoring**: Built-in health checks and logging
- **Maintainable**: Clear code structure, extensive docs

**You now have complete operational control.**

For questions not covered here:
1. Check error logs first
2. Review documentation files
3. Test with `/health` and `/endpoints`
4. Contact appropriate support (hosting/MPS/docs)

---

**END OF HANDOFF DOCUMENTATION**

**Status:** ✅ Production Ready  
**Deployment:** ✅ Complete  
**Documentation:** ✅ Comprehensive  
**Support:** ✅ Available  

**Next Actions:**
1. Deploy to production (see DEPLOYMENT.md)
2. Integrate with ChatGPT (see Section 7)
3. Build dashboard (see Section 8)
4. Set up monitoring (see Section 9)

**You're ready to go! 🚀**
