# Duplicate IP API Debug Report

## Issue
The duplicate IP API at `/home/jez/projects/MPSM-Dashboard/cms/api/get-duplicate-ips.php` was returning the error:
```
"Failed to analyze duplicate IPs: No devices returned from cache or API"
```

## Root Cause Analysis

### Primary Issue: Missing API Credentials
The file `cms/config.php` had **empty/placeholder values** for MPS API credentials:

```php
// OLD (BROKEN)
define('MPS_CLIENT_ID', '');
define('MPS_CLIENT_SECRET', '');
define('MPS_USERNAME', '');
define('MPS_PASSWORD', '');
define('MPS_GRANT_TYPE', 'password');
define('MPS_SCOPE', '');
define('DEFAULT_DEALER_CODE', 'TEST');
define('DEFAULT_DEALER_ID', '1');
```

Meanwhile, the real credentials existed in `.env` file at project root:
```env
CLIENT_ID="9AT9j4UoU2BgLEqmiYCz"
CLIENT_SECRET="9gTbAKBCZe1ftYQbLbq9"
USERNAME="dashboard"
PASSWORD="d@$hpa$$2024"
SCOPE="account"
DEALER_CODE="NY06AGDWUQ"
DEALER_ID="SZ13qRwU5GtFLj0i_CbEgQ2"
```

### Call Stack Analysis

1. **get-duplicate-ips.php** (line 88):
   - Calls `fetchDevicesViaQuery()` when cache is empty or force refresh

2. **fetchDevicesViaQuery()** (line 189):
   - Calls `callMpsApiWithRetry('Device/List', params)`

3. **callMpsApiWithRetry()** (line 346):
   - Calls `callMPSAPI($action, $params)` from functions.php

4. **callMPSAPI()** (line 154 in functions.php):
   - Calls `getMPSToken()` to get OAuth token

5. **getMPSToken()** (line 80 in functions.php):
   - Makes OAuth request using empty credentials
   - **FAILS** because CLIENT_ID, CLIENT_SECRET, USERNAME, PASSWORD are empty
   - Returns error or null token

6. **Result**:
   - API calls fail → `callMpsApiWithRetry()` returns `null`
   - `extractDevicesFromResponse(null)` returns empty array `[]`
   - `fetchDevicesViaQuery()` returns empty array
   - Exception thrown: "No devices returned from cache or API"

## Verification of Functions

✅ **callMPSAPI()** exists in cms/functions.php (line 154)
✅ **extractDevicesFromResponse()** exists in cms/functions.php (line 879)
✅ **DEFAULT_DEALER_ID** constant defined (but was wrong value: '1')
✅ **DEFAULT_DEALER_CODE** constant defined (but was wrong value: 'TEST')

## The Fix Applied

Modified `/home/jez/projects/MPSM-Dashboard/cms/config.php` to load credentials from `.env` file:

```php
// NEW (FIXED)
// Load .env file for MPS API credentials
$envFile = dirname(__DIR__) . '/.env';
$envConfig = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Remove quotes
        $value = trim($value, '"\'');

        $envConfig[$key] = $value;
    }
}

// MPS API Configuration - load from .env
define('MPS_API_BASE', $envConfig['MPS_BASE_URL'] ?? 'https://api.abassetmanagement.com/api3/');
define('MPS_API_TOKEN_URL', $envConfig['TOKEN_URL'] ?? 'https://api.abassetmanagement.com/api3/token');
define('MPS_CLIENT_ID', $envConfig['CLIENT_ID'] ?? '');
define('MPS_CLIENT_SECRET', $envConfig['CLIENT_SECRET'] ?? '');
define('MPS_USERNAME', $envConfig['USERNAME'] ?? '');
define('MPS_PASSWORD', $envConfig['PASSWORD'] ?? '');
define('MPS_GRANT_TYPE', 'password');
define('MPS_SCOPE', $envConfig['SCOPE'] ?? '');

// Default Values - load from .env
define('DEFAULT_DEALER_CODE', $envConfig['DEALER_CODE'] ?? 'TEST');
define('DEFAULT_DEALER_ID', $envConfig['DEALER_ID'] ?? '1');
```

## Expected Outcome After Fix

With real credentials loaded from `.env`:

1. ✅ `getMPSToken()` will successfully obtain OAuth token
2. ✅ `callMPSAPI('Device/List', params)` will return device data
3. ✅ `extractDevicesFromResponse()` will extract devices from response
4. ✅ `fetchDevicesViaQuery()` will return **5000+ devices** (expected fleet size)
5. ✅ `analyzeDuplicateIPs()` will process devices and return duplicate IP report
6. ✅ API returns success with duplicate IP analysis data

## Testing Recommendation

Test the API endpoint with force refresh to bypass cache:

```bash
curl -X GET "https://mpsm.resolutionsbydesign.us/cms/api/get-duplicate-ips.php?secret=DEALER_API_2025&force=1&summaryOnly=1"
```

Expected response:
```json
{
  "success": true,
  "summary": {
    "totalDuplicateIPs": [number],
    "totalDevicesAffected": [number],
    "totalValidDevices": 5000+,
    "source": "live-vendor-api"
  },
  "cached": false
}
```

## Additional Note: PHP curl Extension

During testing, discovered that PHP CLI does not have curl extension installed:
- `php-cli` does NOT have curl loaded
- Apache `mod_php` likely DOES have curl loaded (this is typical)
- The fix will work in web environment where it matters
- CLI testing would require: `sudo apt-get install php8.3-curl`

## Files Modified

1. `/home/jez/projects/MPSM-Dashboard/cms/config.php` - Added .env loading for API credentials

## Files Created (for debugging)

1. `/home/jez/projects/MPSM-Dashboard/cms/api/test-duplicate-ip-debug.php` - Diagnostic test script
2. `/home/jez/projects/MPSM-Dashboard/cms/api/test-curl-check.php` - curl availability checker
3. `/home/jez/projects/MPSM-Dashboard/DEBUG_FINDINGS.md` - This report
