# Deployment & Configuration Alignment

This document describes the alignment between deployment configuration (deploy.yml), web server configuration (.htaccess files), and the application structure.

## Overview

The application consists of:
1. **Root Index** - Monitoring and testing interface
2. **MPS API Engine** - Main API engine at `/mps-api/`
3. **Canonical Swagger** - Full API specification (544 operations)

---

## File Structure

```
MPSM-Dashboard/
├── .github/
│   └── workflows/
│       └── deploy.yml          # GitHub Actions FTP deployment
├── .canonical/
│   └── Swagger.json            # Full canonical swagger (544 operations)
├── mps-api/
│   ├── .htaccess               # API directory protection & routing
│   ├── index.php               # API router/handler
│   ├── engine.php              # Core API engine
│   ├── SwaggerActionRegistry.php  # Swagger parser
│   ├── config.php              # Configuration loader
│   └── logs/                   # Engine logs (protected)
├── .htaccess                   # Root web server config
├── .env                        # Environment variables (NOT deployed)
├── index.php                   # Root monitoring interface
└── [documentation files]       # Accessible via web
```

---

## 1. Root .htaccess Configuration

**File**: [.htaccess](.htaccess)

### Purpose
- Force HTTPS on all requests
- Protect sensitive files and directories
- Route API requests to `/mps-api/index.php`
- Route all other requests to root `index.php`

### Key Rules

```apache
# Force HTTPS first
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Protect sensitive directories
RewriteRule ^\.env$ - [F,L]
RewriteRule ^\.git - [F,L]
RewriteRule ^db/ - [F,L]
RewriteRule ^logs/ - [F,L]
RewriteRule ^\.canonical/ - [F,L]

# Allow markdown documentation
RewriteCond %{REQUEST_URI} \.(md|txt)$ [NC]
RewriteRule ^ - [L]

# API Engine routing
RewriteCond %{REQUEST_URI} ^/mps-api/ [NC]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^mps-api/(.*)$ mps-api/index.php [L,QSA]

# Root routing
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

### Protected Patterns
- `.env*` files
- `.git` directory
- `.github` directory
- `db/` directory
- `logs/` directories
- `.canonical/` directory (accessible only through API)
- `config.php` files
- `.htaccess` files

---

## 2. MPS API .htaccess Configuration

**File**: [mps-api/.htaccess](mps-api/.htaccess)

### Purpose
- Protect engine internals
- Route all API requests through `index.php`
- Block direct access to sensitive files
- Add security headers

### Key Rules

```apache
RewriteEngine On
RewriteBase /mps-api/

# Deny sensitive files
<FilesMatch "(config\.php|\.env|SwaggerActionRegistry\.php|engine\.php|\.log)$">
    Require all denied
</FilesMatch>

# Block logs directory
RewriteRule ^logs/ - [F,L]

# Route to index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L,QSA]
```

### Protected Files
- `config.php` - Configuration loader
- `engine.php` - Core engine
- `SwaggerActionRegistry.php` - Swagger parser
- `.env` - Environment variables
- `*.log` - Log files
- `logs/` - Log directory

---

## 3. GitHub Actions Deployment

**File**: [.github/workflows/deploy.yml](.github/workflows/deploy.yml)

### Purpose
- Automated FTP deployment on push to `main` branch
- Exclude development and sensitive files
- Generate version tracking

### Deployment Configuration

```yaml
server: ftp.resolutionsbydesign.us
username: mpsm@mpsm.resolutionsbydesign.us
protocol: ftp
port: 21
local-dir: ./
server-dir: /
```

### Excluded from Deployment

```yaml
exclude: |
  .git*                    # Git files
  .git/                    # Git directory
  .github/                 # GitHub Actions
  .gitignore              # Git ignore file
  .env.example            # Example env file
  node_modules/           # Node dependencies
  *.log                   # Log files
  **/logs/*.log           # All log files
  .vscode/                # VS Code settings
  .idea/                  # IDE settings
  *.md                    # Markdown docs (except specific ones)
  README.md               # Readme
  LICENSE                 # License file
  package*.json           # NPM files
  composer.*              # Composer files
  phpunit.xml             # PHPUnit config
  .phpunit.result.cache   # PHPUnit cache
  tests/                  # Test files
  mps-api/test_*.php      # Test scripts
  mps-api/verify_*.py     # Verification scripts
  **/__pycache__/         # Python cache
  *.pyc                   # Python compiled
  .DS_Store               # macOS files
  Thumbs.db               # Windows files
```

### What IS Deployed

✅ Production PHP files (index.php, mps-api/*.php)
✅ .htaccess files (root and mps-api)
✅ .canonical/Swagger.json
✅ Documentation files (for web access)
✅ Static assets (CSS, JS, images if any)
✅ .env file (if exists - contains secrets)

**⚠️ IMPORTANT**: The `.env` file IS deployed. Ensure it contains production credentials and is protected via `.htaccess`.

---

## 4. Root Index.php - Monitoring Interface

**File**: [index.php](index.php)

### Purpose
**Monitoring and testing interface ONLY** - Not the main application

### Features
- ✅ Health check display
- ✅ Engine status monitoring
- ✅ Shows available operations count (544)
- ✅ Test harness for `/query` endpoint
- ✅ Quick links to API endpoints
- ✅ Links to documentation

### Access Points
```
GET  /                           → Monitoring interface
GET  /mps-api/                   → Engine status (JSON)
GET  /mps-api/health             → Health check (JSON)
GET  /mps-api/endpoints          → All 544 operations (JSON)
GET  /mps-api/swagger.json       → Canonical swagger spec
POST /mps-api/query              → Execute operations
```

---

## 5. API Engine Routing

**File**: [mps-api/index.php](mps-api/index.php)

### Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/mps-api/` | Engine info (shows 544 operations) |
| GET | `/mps-api/health` | Health check with diagnostics |
| GET | `/mps-api/endpoints` | List all operations grouped |
| GET | `/mps-api/swagger.json` | Full canonical swagger |
| POST | `/mps-api/query` | Execute any operation |

### Query Format

```json
POST /mps-api/query
{
  "action": "Device/List",
  "params": {
    "request": {
      "customerCode": "CUST001",
      "pageNumber": 1,
      "pageSize": 50
    }
  }
}
```

---

## 6. Security Alignment

### Layer 1: Web Server (.htaccess)
- ✅ Force HTTPS
- ✅ Block `.env` files
- ✅ Block `.git` directories
- ✅ Block `logs/` directories
- ✅ Block `db/` directory
- ✅ Block `.canonical/` direct access
- ✅ Protect `config.php` files

### Layer 2: API Directory (mps-api/.htaccess)
- ✅ Block engine internals (engine.php, config.php)
- ✅ Block swagger parser (SwaggerActionRegistry.php)
- ✅ Block log files
- ✅ Route all requests through index.php

### Layer 3: Deployment (deploy.yml)
- ✅ Exclude development files
- ✅ Exclude test files
- ✅ Exclude git files
- ✅ Exclude logs
- ⚠️ Deploy .env (protected by .htaccess)

### Layer 4: Application (config.php, engine.php)
- ✅ Prevent direct file access
- ✅ Validate configuration
- ✅ Sanitize input
- ✅ Rate limiting
- ✅ Request size limits
- ✅ Security headers

---

## 7. Environment Configuration

### Required in .env

```bash
# API Configuration
MPS_BASE_URL=https://api.mpsmonitor.com
MPS_API_KEY=your-production-api-key-here

# Optional OAuth (if not using API key)
# AUTH_MODE=oauth_password
# TOKEN_URL=https://api.mpsmonitor.com/token
# CLIENT_ID=your-client-id
# CLIENT_SECRET=your-client-secret
# USERNAME=your-username
# PASSWORD=your-password
# SCOPE=api

# Engine Settings
MPS_TIMEOUT=30
MPS_CONNECT_TIMEOUT=10
MPS_DEBUG=false
MPS_MAX_RETRIES=3
```

---

## 8. Canonical Swagger Integration

### Location
`.canonical/Swagger.json` (1.2MB, 544 operations)

### Access Methods

1. **Via API**: `GET /mps-api/swagger.json`
2. **Via SwaggerActionRegistry**: Automatically loaded by engine
3. **Direct File**: Blocked by `.htaccess`

### Priority Order
```php
// SwaggerActionRegistry search order:
1. dirname(__DIR__) . '/.canonical/Swagger.json'  ← Priority #1
2. dirname(__DIR__) . '/Swagger.json'
3. __DIR__ . '/swagger.json'
4. [legacy paths...]
```

---

## 9. Deployment Checklist

### Pre-Deployment
- [ ] .env file has production credentials
- [ ] MPS_DEBUG=false in .env
- [ ] Test local deployment
- [ ] Verify .htaccess rules
- [ ] Check file permissions

### Deployment
- [ ] Push to main branch
- [ ] Monitor GitHub Actions
- [ ] Verify FTP upload completes

### Post-Deployment
- [ ] Visit root URL (should show monitoring interface)
- [ ] Check `/mps-api/health` (should return 200)
- [ ] Verify `/mps-api/endpoints` (should show 544 operations)
- [ ] Test `/mps-api/swagger.json` (should return canonical spec)
- [ ] Test sample query via monitoring interface
- [ ] Check logs directory permissions (should be blocked)
- [ ] Verify .env is not directly accessible

---

## 10. Troubleshooting Alignment Issues

### Issue: 404 on /mps-api/*
**Check**: Root `.htaccess` routing rules
**Fix**: Ensure `RewriteRule ^mps-api/(.*)$ mps-api/index.php [L,QSA]`

### Issue: Can access sensitive files
**Check**: `.htaccess` protection rules
**Fix**: Verify `FilesMatch` and `RewriteRule` blocks

### Issue: API not loading swagger
**Check**: SwaggerActionRegistry path priority
**Fix**: Verify `.canonical/Swagger.json` exists

### Issue: Deployment includes logs
**Check**: `deploy.yml` exclude rules
**Fix**: Add `**/logs/*.log` to exclude list

### Issue: HTTPS redirect loop
**Check**: Server HTTPS detection
**Fix**: Verify `RewriteCond %{HTTPS} off` condition

---

## Summary

✅ **Root .htaccess** - Forces HTTPS, protects sensitive files, routes API requests
✅ **API .htaccess** - Protects engine internals, routes to index.php
✅ **deploy.yml** - Excludes dev files, deploys production code
✅ **index.php** - Monitoring interface only (not main app)
✅ **Canonical Swagger** - Protected but accessible via API

**All layers are properly aligned for secure production deployment!**
