# MPS API Engine - Diagnostics Guide

## New Diagnostic Endpoint

Your updated `index.php` now includes a comprehensive `/diagnostics` endpoint that provides **everything you need** to troubleshoot any issue.

## Quick Diagnostic Check

```bash
curl https://mpsm.resolutionsbydesign.us/mps-api/diagnostics
```

This single endpoint will show you:

### 1. System Information
- PHP version and SAPI
- Required extensions (curl, json, mbstring, openssl)
- Memory and execution limits
- Server software and protocol
- HTTPS status

### 2. Filesystem Status
- Current directory
- Directory writability
- Logs directory status
- All required files existence and permissions

### 3. File Validation
- File sizes
- File permissions (octal)
- Swagger.json validity (JSON parsing, path count)
- .env file readability

### 4. Environment Configuration
- Which config keys are present in .env
- Which required keys are missing
- Line count (to detect empty files)

### 5. Engine Status
- Engine class loaded
- Registry class loaded
- Initialization success/failure
- Health check results
- Number of operations registered
- **Detailed error messages with file/line numbers**

### 6. Performance Metrics
- Request duration (ms)
- Peak memory usage (MB)

### 7. Error Details (in debug mode)
- Startup errors and warnings
- Recent log file entries (last 20 lines)
- Full exception traces

## Using Diagnostics to Fix Issues

### Scenario 1: Engine won't load

**Diagnostic output:**
```json
{
  "engine": {
    "initialization": "failed",
    "error": {
      "message": "Swagger specification file not found",
      "file": "/path/to/SwaggerActionRegistry.php",
      "line": 45
    }
  }
}
```

**Solution:** Upload `Swagger.json` to the server

---

### Scenario 2: Missing .env file

**Diagnostic output:**
```json
{
  "system": {
    "files": {
      ".env": false
    },
    "env_config": {
      "error": ".env file not found"
    }
  }
}
```

**Solution:** Create `.env` file from `.env.example` and configure credentials

---

### Scenario 3: Missing OAuth credentials

**Diagnostic output:**
```json
{
  "system": {
    "env_config": {
      "has_required": {
        "CLIENT_ID": false,
        "CLIENT_SECRET": false,
        "USERNAME": true,
        "PASSWORD": true
      }
    }
  }
}
```

**Solution:** Add `CLIENT_ID` and `CLIENT_SECRET` to `.env` file

---

### Scenario 4: Logs directory not writable

**Diagnostic output:**
```json
{
  "system": {
    "filesystem": {
      "logs_dir_exists": true,
      "logs_dir_writable": false
    }
  }
}
```

**Solution:**
```bash
chmod 755 /path/to/mps-api/logs
chown www-data:www-data /path/to/mps-api/logs  # Apache
```

---

### Scenario 5: Missing PHP extension

**Diagnostic output:**
```json
{
  "system": {
    "php": {
      "extensions": {
        "curl": false,
        "json": true,
        "mbstring": true
      }
    }
  }
}
```

**Solution:**
```bash
sudo apt-get install php-curl
sudo systemctl restart apache2
```

---

## Debug Mode

To get **even more detailed** information, set debug mode in your `.env`:

```bash
MPS_DEBUG=true
```

With debug mode enabled:
- Full exception stack traces
- Recent error log entries (last 20 lines)
- Startup warnings
- File paths in error messages
- Internal error details

## Regular Endpoints

All standard endpoints still work:

| Endpoint | Purpose | Example |
|----------|---------|---------|
| `/` | Status overview | `curl https://mpsm.resolutionsbydesign.us/mps-api/` |
| `/health` | Health check | `curl https://mpsm.resolutionsbydesign.us/mps-api/health` |
| `/diagnostics` | **Full diagnostics** | `curl https://mpsm.resolutionsbydesign.us/mps-api/diagnostics` |
| `/endpoints` | List all actions | `curl https://mpsm.resolutionsbydesign.us/mps-api/endpoints` |
| `/swagger.json` | API documentation | `curl https://mpsm.resolutionsbydesign.us/mps-api/swagger.json` |
| `/query` | Main query endpoint | `curl -X POST ... /query` |

## What Changed in index.php

### New Features

1. **Custom error handler** - Captures all PHP errors and warnings during startup
2. **System diagnostics function** - Checks PHP, server, filesystem, files
3. **Engine diagnostics function** - Tests engine initialization in isolation
4. **Performance metrics** - Tracks request duration and memory usage
5. **Debug mode support** - Respects `MPS_DEBUG` environment variable
6. **Diagnostic route** - `/diagnostics` endpoint available even if engine fails
7. **Enhanced error responses** - Includes full context in debug mode, sanitized in production

### Backward Compatible

All existing functionality remains unchanged:
- Same routes
- Same response formats
- Same security features (rate limiting, CORS, headers)
- Same error handling for production (hides internals)

## Troubleshooting Workflow

1. **Check diagnostics endpoint first:**
   ```bash
   curl https://mpsm.resolutionsbydesign.us/mps-api/diagnostics | jq
   ```

2. **Look at the key sections:**
   - `system.files` - Are all files present?
   - `system.env_config.has_required` - Are credentials configured?
   - `engine.initialization` - Did engine load successfully?
   - `engine.error` - What was the exact error?

3. **Enable debug mode if needed:**
   ```bash
   # Add to .env
   MPS_DEBUG=true

   # Check diagnostics again
   curl https://mpsm.resolutionsbydesign.us/mps-api/diagnostics | jq
   ```

4. **Check specific error logs:**
   ```bash
   tail -50 /path/to/mps-api/logs/php_errors_*.log
   ```

5. **Fix the issue** based on diagnostic output

6. **Verify fix:**
   ```bash
   curl https://mpsm.resolutionsbydesign.us/mps-api/health
   ```

7. **Disable debug mode** (production):
   ```bash
   MPS_DEBUG=false
   ```

## Example Diagnostic Output

Here's what a **healthy** system looks like:

```json
{
  "status": "diagnostics",
  "debug_mode": true,
  "system": {
    "timestamp": "2025-10-20T14:45:00+00:00",
    "php": {
      "version": "8.1.0",
      "sapi": "fpm-fcgi",
      "os": "Linux",
      "extensions": {
        "curl": true,
        "json": true,
        "mbstring": true,
        "openssl": true
      }
    },
    "filesystem": {
      "logs_dir_exists": true,
      "logs_dir_writable": true
    },
    "files": {
      "engine.php": true,
      "SwaggerActionRegistry.php": true,
      "Swagger.json": true,
      ".env": true
    },
    "swagger": {
      "valid_json": true,
      "path_count": 544
    },
    "env_config": {
      "readable": true,
      "has_required": {
        "CLIENT_ID": true,
        "CLIENT_SECRET": true,
        "USERNAME": true,
        "PASSWORD": true,
        "DEALER_CODE": true,
        "API_BASE_URL": true
      }
    }
  },
  "engine": {
    "initialization": "success",
    "engine_loaded": true,
    "registry_loaded": true,
    "registry_operations": 544,
    "health": {
      "status": "ok",
      "service": "MPS Monitors API Engine",
      "version": "1.1.0"
    }
  },
  "performance": {
    "duration_ms": 125.5,
    "memory_peak_mb": 8.2
  }
}
```

## Pro Tips

1. **Always check `/diagnostics` first** - It's faster than checking logs
2. **Use `jq` to format JSON** - Makes it easier to read: `curl ... | jq`
3. **Compare before/after** - Check diagnostics before and after making changes
4. **Keep debug mode off in production** - Only enable when troubleshooting
5. **Check file permissions** - Most issues are filesystem related
6. **Verify .env syntax** - One typo breaks everything

## Quick Commands

```bash
# Full diagnostics
curl https://mpsm.resolutionsbydesign.us/mps-api/diagnostics | jq

# Just check if files exist
curl https://mpsm.resolutionsbydesign.us/mps-api/diagnostics | jq '.system.files'

# Just check env config
curl https://mpsm.resolutionsbydesign.us/mps-api/diagnostics | jq '.system.env_config'

# Just check engine status
curl https://mpsm.resolutionsbydesign.us/mps-api/diagnostics | jq '.engine'

# Check if healthy
curl https://mpsm.resolutionsbydesign.us/mps-api/diagnostics | jq '.engine.initialization'
# Should return: "success"
```

## Next Steps

1. **Deploy updated index.php** to server
2. **Check diagnostics endpoint** - This will tell you exactly what's wrong
3. **Fix issues** based on diagnostic output
4. **Verify health endpoint** works
5. **Test query endpoint** with real action
6. **Disable debug mode** for production

---

**The diagnostics endpoint gives you everything you need to debug and fix any issue!** 🎯
