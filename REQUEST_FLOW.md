# Request Flow Diagram

This document shows how requests flow through the system.

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                         INCOMING REQUEST                         │
│                    https://mpsm.example.com                      │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
                    ┌───────────────────────┐
                    │  Root .htaccess       │
                    │  - Force HTTPS        │
                    │  - Protect files      │
                    │  - Route requests     │
                    └───────────┬───────────┘
                                │
                ┌───────────────┴──────────────┐
                │                              │
                ▼                              ▼
    ┌───────────────────────┐    ┌──────────────────────────┐
    │   Root Routes         │    │   /mps-api/* Routes      │
    │   (/)                 │    │                          │
    └───────────┬───────────┘    └───────────┬──────────────┘
                │                             │
                ▼                             ▼
    ┌───────────────────────┐    ┌──────────────────────────┐
    │   index.php           │    │  mps-api/.htaccess       │
    │   (Monitoring UI)     │    │  - Protect internals     │
    │                       │    │  - Route to index.php    │
    │   Shows:              │    └───────────┬──────────────┘
    │   • Health status     │                │
    │   • Test harness      │                ▼
    │   • Quick links       │    ┌──────────────────────────┐
    │   • 544 ops info      │    │  mps-api/index.php       │
    └───────────────────────┘    │  (API Router)            │
                                 │                          │
                                 │  Routes:                 │
                                 │  • /health               │
                                 │  • /endpoints            │
                                 │  • /swagger.json         │
                                 │  • /query                │
                                 └───────────┬──────────────┘
                                             │
                                             ▼
                                 ┌──────────────────────────┐
                                 │  engine.php              │
                                 │  (Core Engine)           │
                                 │                          │
                                 │  • Loads config          │
                                 │  • Handles auth          │
                                 │  • Dispatches actions    │
                                 └───────────┬──────────────┘
                                             │
                                             ▼
                                 ┌──────────────────────────┐
                                 │  SwaggerActionRegistry   │
                                 │                          │
                                 │  Loads:                  │
                                 │  .canonical/Swagger.json │
                                 │  (544 operations)        │
                                 └───────────┬──────────────┘
                                             │
                                             ▼
                                 ┌──────────────────────────┐
                                 │  HTTP Request to         │
                                 │  MPS Monitor API         │
                                 │  (External)              │
                                 └──────────────────────────┘
```

---

## Request Flow Examples

### 1. Root Access - Monitoring Interface

```
User → https://mpsm.example.com/
         │
         ▼
    .htaccess (root)
         │ [no match, pass to index.php]
         ▼
    index.php
         │ [renders monitoring UI]
         ├─→ Calls /mps-api/health for status
         └─→ Displays form for testing
```

**Response**: HTML monitoring interface with health check

---

### 2. API Health Check

```
User → https://mpsm.example.com/mps-api/health
         │
         ▼
    .htaccess (root)
         │ [matches ^/mps-api/]
         │ [routes to mps-api/index.php]
         ▼
    mps-api/.htaccess
         │ [rewrite to index.php]
         ▼
    mps-api/index.php
         │ [path = /health]
         │ [calls engine.healthCheck()]
         ▼
    engine.php
         │ [makes request to MPS API]
         └─→ Returns diagnostics
```

**Response**: JSON with health status
```json
{
  "status": "online",
  "action_count": 544,
  "version": "1.1.0",
  ...
}
```

---

### 3. List All Endpoints

```
User → https://mpsm.example.com/mps-api/endpoints
         │
         ▼
    .htaccess (root)
         │ [routes to mps-api/index.php]
         ▼
    mps-api/.htaccess
         │ [rewrite to index.php]
         ▼
    mps-api/index.php
         │ [path = /endpoints]
         │ [calls engine.getAvailableEndpoints()]
         ▼
    engine.php
         │ [loads SwaggerActionRegistry]
         ▼
    SwaggerActionRegistry
         │ [parses .canonical/Swagger.json]
         └─→ Returns 544 operations
```

**Response**: JSON with all operations
```json
{
  "success": true,
  "count": 544,
  "operations": [...],
  "groups": {...}
}
```

---

### 4. Get Canonical Swagger

```
User → https://mpsm.example.com/mps-api/swagger.json
         │
         ▼
    .htaccess (root)
         │ [routes to mps-api/index.php]
         ▼
    mps-api/.htaccess
         │ [rewrite to index.php]
         ▼
    mps-api/index.php
         │ [path = /swagger.json]
         │ [reads .canonical/Swagger.json]
         └─→ Returns raw JSON
```

**Response**: Full canonical swagger (1.2MB, 544 operations)

---

### 5. Execute API Query

```
User → POST https://mpsm.example.com/mps-api/query
       {
         "action": "Device/List",
         "params": {"request": {...}}
       }
         │
         ▼
    .htaccess (root)
         │ [routes to mps-api/index.php]
         ▼
    mps-api/.htaccess
         │ [rewrite to index.php]
         ▼
    mps-api/index.php
         │ [path = /query, method = POST]
         │ [extracts action and params]
         │ [calls engine.dispatchAction()]
         ▼
    engine.php
         │ [loads operation from registry]
         ▼
    SwaggerActionRegistry
         │ [finds Device/List operation]
         │ [returns method, path, params info]
         ▼
    engine.php
         │ [builds request]
         │  - Path: /Device/List
         │  - Method: POST
         │  - Body: params.request
         │  - Auth: Bearer token
         │
         │ [makes HTTP request]
         ▼
    MPS Monitor API (External)
         │ [processes request]
         └─→ Returns device list
         ▼
    engine.php
         │ [parses response]
         └─→ Returns to client
```

**Response**: JSON with device list
```json
{
  "success": true,
  "data": {
    "items": [...],
    "totalCount": 50,
    "pageNumber": 1
  }
}
```

---

## Security Layers

### Layer 1: Root .htaccess
```
Blocks:
  ✓ .env files
  ✓ .git directory
  ✓ .github directory
  ✓ db/ directory
  ✓ logs/ directories
  ✓ .canonical/ (direct access)

Forces:
  ✓ HTTPS on all requests
```

### Layer 2: API .htaccess (mps-api/)
```
Blocks:
  ✓ engine.php (direct access)
  ✓ config.php (direct access)
  ✓ SwaggerActionRegistry.php (direct access)
  ✓ logs/ directory
  ✓ *.log files

Routes:
  ✓ All requests → index.php
```

### Layer 3: Application (config.php)
```
Validates:
  ✓ MPS_ENGINE_ACCESS constant
  ✓ Required configuration
  ✓ API key format
  ✓ URL format

Returns 403 if accessed directly
```

### Layer 4: Engine (index.php)
```
Validates:
  ✓ HTTP method
  ✓ Request size (max 1MB)
  ✓ Rate limiting (60 req/min)
  ✓ Content-Type
  ✓ JSON format
  ✓ Required fields

Sanitizes:
  ✓ User input
  ✓ Path traversal attempts
  ✓ Null bytes
```

---

## File Access Matrix

| File/Directory | Direct Web Access | Via API | Via Include |
|----------------|-------------------|---------|-------------|
| `index.php` | ✅ Yes | ❌ No | ❌ No |
| `mps-api/index.php` | ✅ Yes (routed) | ❌ No | ❌ No |
| `mps-api/engine.php` | ❌ Blocked | ✅ Used internally | ✅ Yes |
| `mps-api/config.php` | ❌ Blocked | ✅ Used internally | ✅ Yes |
| `mps-api/SwaggerActionRegistry.php` | ❌ Blocked | ✅ Used internally | ✅ Yes |
| `.env` | ❌ Blocked | ❌ No | ✅ Yes |
| `.canonical/Swagger.json` | ❌ Blocked | ✅ Via /swagger.json | ✅ Yes |
| `mps-api/logs/*.log` | ❌ Blocked | ❌ No | ✅ Yes |
| `*.md` files | ✅ Yes | ❌ No | ❌ No |

---

## Deployment Flow

```
Developer → git push main
                │
                ▼
        GitHub Actions
                │
                ▼
        Checkout Repository
                │
                ▼
        Generate version.js
                │
                ▼
        FTP Deploy Action
                │
                ├─→ Exclude: .git*, tests/, *.log, etc.
                └─→ Include: *.php, .htaccess, .canonical/, .env
                │
                ▼
        FTP Server
        ftp.resolutionsbydesign.us
                │
                ▼
        Production Directory
        /public_html/
                │
                ├─→ index.php
                ├─→ .htaccess
                ├─→ .env (protected)
                ├─→ .canonical/Swagger.json
                └─→ mps-api/
                    ├─→ index.php
                    ├─→ .htaccess
                    ├─→ engine.php
                    ├─→ config.php
                    └─→ SwaggerActionRegistry.php
```

---

## Summary

| Aspect | Configuration |
|--------|---------------|
| **Root Access** | Monitoring interface (`index.php`) |
| **API Access** | JSON API (`/mps-api/*`) |
| **Swagger Source** | `.canonical/Swagger.json` (544 ops) |
| **Auth Mode** | API Key or OAuth (from `.env`) |
| **Protection** | 4-layer security (htaccess + app) |
| **Deployment** | GitHub Actions → FTP |
| **Routing** | .htaccess → index.php |

**All request flows are properly configured and secure!** ✅
