# ENHANCED ERROR HANDLING REFERENCE

**Version:** 1.1.0  
**Purpose:** Complete guide to error handling improvements

---

## ERROR CODE SYSTEM

All errors now include standardized error codes for easier debugging:

```
1000-1999: Configuration Errors
2000-2999: Network Errors  
3000-3999: API Errors
4000-4999: Validation Errors
5000-5999: Internal Errors
```

### Example Error Response
```json
{
  "success": false,
  "error": "Invalid monitor ID format",
  "error_code": 4000,
  "http_code": 400,
  "request_id": "req_671234567890ab",
  "timestamp": "2024-10-15T12:00:00+00:00"
}
```

---

## RETRY LOGIC

### Automatic Retries
Network failures automatically retry up to 3 times with exponential backoff:

```
Attempt 1: Immediate
Attempt 2: Wait 1 second
Attempt 3: Wait 2 seconds  
Attempt 4: Wait 4 seconds (final)
```

### Retryable Errors
- Connection timeout
- Connection refused
- DNS resolution failure
- Receive/send errors
- Server errors (5xx)

### Non-Retryable Errors  
- Invalid credentials (401)
- Not found (404)
- Bad request (400)
- Rate limited (429)
- Client errors (4xx)

---

## LOGGING SYSTEM

### Log Files Created

#### 1. Error Log (`logs/error_YYYY-MM-DD.log`)
Engine errors, API failures, critical issues

```
[2024-10-15 12:00:00] [Code: 2000] CURL Error [7]: Couldn't connect | Context: {"request_id":"req_123","url":"..."}
```

#### 2. Debug Log (`logs/debug_YYYY-MM-DD.log`)
Request details, response times (MPS_DEBUG=true only)

```
[2024-10-15 12:00:00] DEBUG: Request req_123: GET /monitors | {"duration_ms":234}
```

#### 3. Security Log (`logs/security_YYYY-MM-DD.log`)
Security events, suspicious activity

```
[2024-10-15 12:00:00] SECURITY: Path traversal attempt detected | {"uri":"/../etc/passwd","ip":"1.2.3.4"}
```

#### 4. Config Error Log (`logs/config_error_YYYY-MM-DD.log`)
Configuration loading failures

```
[2024-10-15 12:00:00] Configuration Error: Missing required configuration: MPS_API_KEY
```

#### 5. PHP Error Log (`logs/php_errors_YYYY-MM-DD.log`)
PHP runtime errors, warnings

```
[15-Oct-2024 12:00:00 UTC] PHP Warning: Division by zero in file.php on line 42
```

#### 6. Rate Limit Log (`logs/ratelimit_YYYY-MM-DD-HH-ii.log`)
Request tracking for rate limiting

```
192.168.1.1
192.168.1.1
192.168.1.2
```

---

## ERROR HANDLING BY COMPONENT

### Engine (engine.php)

#### Configuration Loading
```php
try {
    $engine = MPSMonitorEngine::getInstance();
} catch (Exception $e) {
    // Error Code: 1000-1999
    // Logged to: config_error_*.log
    // Response: 500 with safe message
}
```

#### API Requests
```php
$result = $engine->getMonitor('id_123');

if (!$result['success']) {
    // Includes:
    // - error: Human-readable message
    // - error_code: Numeric code
    // - http_code: HTTP status
    // - request_id: Tracking ID
    // - retryable: true/false
}
```

#### Validation Errors
```php
$result = $engine->createMonitor(['name' => 'Test']);
// Missing 'url' field

// Response:
{
  "success": false,
  "error": "Missing required fields: url",
  "error_code": 4000
}
```

---

### Router (index.php)

#### Request Size Protection
```php
// Automatic check on entry
// If CONTENT_LENGTH > 1MB:
{
  "success": false,
  "error": "Request too large",
  "max_size": 1048576
}
```

#### Rate Limiting
```php
// After 60 requests in 1 minute:
HTTP/1.1 429 Too Many Requests
Retry-After: 60

{
  "success": false,
  "error": "Rate limit exceeded",
  "retry_after": 60
}
```

#### Path Traversal
```php
// GET /mps-api/../etc/passwd
HTTP/1.1 400 Bad Request

{
  "error": "Invalid path"
}
// Logged to: security_*.log
```

#### Invalid Input
```php
// POST /query with action="hack<script>"
HTTP/1.1 400 Bad Request

{
  "success": false,
  "error": "Invalid action format"
}
```

---

### Configuration (config.php)

#### Missing .env File
```
Exception: .env file not found at: /path/to/.env
Logged to: config_error_*.log + PHP error_log
```

#### Invalid Configuration
```
Exception: MPS_BASE_URL must be a valid URL
Exception: MPS_API_KEY appears to be a placeholder value
Exception: MPS_API_KEY is too short (minimum 10 characters)
```

#### File Lock Failure
```
Exception: Failed to acquire lock on .env file
```

---

## ERROR RESPONSE FORMATS

### Standard Success
```json
{
  "success": true,
  "data": { ... },
  "http_code": 200,
  "request_id": "req_123",
  "duration_ms": 234.56
}
```

### Standard Error
```json
{
  "success": false,
  "error": "Human-readable error message",
  "error_code": 2000,
  "http_code": 500,
  "request_id": "req_123",
  "timestamp": "2024-10-15T12:00:00+00:00"
}
```

### Debug Mode Error (MPS_DEBUG=true)
```json
{
  "success": false,
  "error": "Request failed",
  "error_code": 2000,
  "error_detail": "CURL Error [7]: Couldn't connect to server",
  "http_code": 0,
  "request_id": "req_123",
  "url": "https://api.example.com/monitors",
  "method": "GET",
  "duration_ms": 1000,
  "retryable": true,
  "timestamp": "2024-10-15T12:00:00+00:00"
}
```

### Production Mode Error (MPS_DEBUG=false)
```json
{
  "success": false,
  "error": "Request failed",
  "error_code": 2000,
  "http_code": 0,
  "request_id": "req_123",
  "timestamp": "2024-10-15T12:00:00+00:00"
}
```

---

## VALIDATION FRAMEWORK

### Required Fields Validation
```php
// In index.php
validateRequiredFields($data, ['name', 'url']);

// If missing:
{
  "success": false,
  "error": "Missing required fields: url",
  "required_fields": ["name", "url"]
}
```

### ID Validation
```php
// Validates format: alphanumeric + hyphen + underscore
// Max length: 255 characters
// Blocks: ../, \, null bytes

$monitorId = validateAndSanitizeId($input, 'Monitor');

// If invalid:
{
  "error": "Invalid monitor ID format"
}
```

### Input Sanitization
```php
$name = sanitizeInput($_POST['name'], 'string');  // Trim, string
$count = sanitizeInput($_POST['count'], 'int');   // Cast to int
$active = sanitizeInput($_POST['active'], 'bool'); // Cast to bool
$filters = sanitizeInput($_POST['filters'], 'array'); // Ensure array
```

---

## DEBUGGING GUIDE

### Enable Debug Mode
```env
MPS_DEBUG=true
```

### What Debug Mode Does
1. **Detailed Logging**: All requests/responses logged
2. **Full Error Details**: Stack traces, file paths included
3. **Request Tracking**: Request ID in every log entry
4. **Performance Data**: Duration, memory usage tracked
5. **Context Included**: All error context preserved

### Production vs Debug

| Feature | Production | Debug |
|---------|-----------|-------|
| Error Details | Generic | Full stack trace |
| File Paths | Hidden | Visible |
| Request Data | Minimal | Complete |
| Performance | Optimized | Verbose logging |
| Security | Maximized | Reduced |

---

## COMMON ERROR SCENARIOS

### Scenario 1: Invalid API Key
```
Error: MPS API Error [401]: Unauthorized
Logged: error_*.log
Fix: Update MPS_API_KEY in .env
```

### Scenario 2: API Timeout
```
Error: Request failed after 3 attempts
Logged: error_*.log with retry details
Fix: Increase MPS_TIMEOUT or check network
```

### Scenario 3: Rate Limited
```
Error: Rate limit exceeded
Response: 429 with Retry-After header
Fix: Wait 60 seconds or reduce request frequency
```

### Scenario 4: Invalid Input
```
Error: Invalid monitor ID format
Logged: security_*.log (potential attack)
Fix: Client sends valid ID format
```

### Scenario 5: Configuration Missing
```
Error: Missing required configuration: MPS_API_KEY
Logged: config_error_*.log
Fix: Create .env file from .env.example
```

---

## ERROR RECOVERY STRATEGIES

### Automatic Recovery
1. **Network Failures**: Auto-retry with backoff
2. **Timeouts**: Retry with increased timeout
3. **5xx Errors**: Retry (may be temporary)

### Manual Recovery
1. **4xx Errors**: Fix request data
2. **Auth Failures**: Update credentials
3. **Config Errors**: Fix .env file
4. **Rate Limits**: Wait and retry

---

## MONITORING RECOMMENDATIONS

### Daily Checks
```bash
# Check for errors
tail -100 logs/error_$(date +%Y-%m-%d).log

# Check for security events  
tail -100 logs/security_$(date +%Y-%m-%d).log

# Check for rate limiting
ls -lh logs/ratelimit_*.log
```

### Weekly Reviews
```bash
# Count error types
grep -o '\[Code: [0-9]*\]' logs/error_*.log | sort | uniq -c

# Count security events
wc -l logs/security_*.log

# Monitor log sizes
du -sh logs/
```

### Alerts to Set Up
1. **High error rate**: >10 errors/minute
2. **Security events**: Any path traversal attempts
3. **Config failures**: Any config_error_*.log entries
4. **Rate limit hits**: >5 per hour
5. **API auth failures**: Any 401 responses

---

## BEST PRACTICES

### Do's ✅
- Enable debug mode during development
- Disable debug mode in production
- Monitor error logs daily
- Set up log rotation
- Use request IDs for tracking
- Review security logs regularly

### Don'ts ❌
- Don't ignore security logs
- Don't disable error logging
- Don't expose debug mode in production
- Don't ignore rate limit warnings
- Don't bypass input validation
- Don't suppress exceptions

---

## APPENDIX: Full Error Code List

```
Configuration Errors (1000-1999)
  1000: Configuration file not found/readable
  1001: Invalid configuration format
  1002: Missing required configuration
  1003: Invalid configuration values

Network Errors (2000-2999)
  2000: CURL initialization failed
  2001: Connection timeout
  2002: Connection refused
  2003: DNS resolution failure
  2004: SSL/TLS error
  2005: Receive/send error

API Errors (3000-3999)
  3000: Invalid API response
  3001: JSON parse error
  3002: Unexpected redirect
  3003: Server error (5xx)
  3004: Client error (4xx)

Validation Errors (4000-4999)
  4000: Invalid input format
  4001: Missing required fields
  4002: Invalid ID format
  4003: Invalid URL format
  4004: Request too large

Internal Errors (5000-5999)
  5000: Unhandled exception
  5001: Singleton violation
  5002: File operation failure
  5003: Memory exhaustion
```

---

**Status:** ✅ Complete Error Handling System  
**Version:** 1.1.0  
**Documentation:** Comprehensive  
**Production Ready:** YES

---

**END OF ERROR HANDLING REFERENCE**
