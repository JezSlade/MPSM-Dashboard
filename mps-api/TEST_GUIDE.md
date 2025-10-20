# MPS API Engine Test Guide

## Testing the Three Critical Fixes

This guide will help you test all three critical fixes implemented in the engine based on the API discovery findings.

## Prerequisites

- PHP 7.4+ installed and configured
- Web server (Apache/Nginx) or PHP built-in server
- `.env` file configured with OAuth credentials and dealer information
- MPSM API access

## Quick Start: Running Tests

### Option 1: Using PHP Built-in Server (Recommended for Testing)

```bash
cd mps-api
php -S localhost:8080
```

Then open another terminal and run:

```bash
# Run automated test suite
php test.php

# Or access via browser/curl
curl http://localhost:8080/
curl http://localhost:8080/health
```

### Option 2: Using Existing Web Server

If the API is already deployed on a web server:

```bash
# Run the test suite
php /path/to/mps-api/test.php

# Or test via HTTP
curl https://your-domain.com/mps-api/health
```

## Test Suite Overview

The `test.php` file contains 10 comprehensive tests:

### Fix #1: OAuth Authentication Tests

1. **Test 1: Engine Health Check** - Verifies engine initializes correctly
2. **Test 2: OAuth Token Acquisition** - Confirms OAuth tokens are acquired and cached
3. **Test 8: End-to-End Integration** - Real API call with OAuth authentication

### Fix #2: Smart Parameter Population Tests

4. **Test 3: Dealer Code Auto-population** - Verifies `code`/`dealerCode` parameters are auto-filled from config
5. **Test 4: Pagination Defaults** - Verifies `page` and `pageSize` get default values (1, 50)
6. **Test 9: Explicit Parameter Override** - Confirms manual parameters override auto-population

### Fix #3: MPSM Response Validation Tests

7. **Test 5: Valid Response Handling** - Validates `IsValid=true` responses extract `Result` field
8. **Test 6: Error Response Detection** - Validates `IsValid=false` responses are caught despite HTTP 200
9. **Test 7: HTTP Error Handling** - Confirms real HTTP errors (4xx, 5xx) are properly handled

### Additional Tests

10. **Test 10: Registry Integration** - Verifies Swagger operations are loaded correctly

## Manual Testing via HTTP Requests

### Test 1: Health Check

```bash
curl -X GET http://localhost:8080/health
```

**Expected Response:**
```json
{
  "status": "ok",
  "service": "MPS Monitors API Engine",
  "version": "1.1.0",
  "config": {
    "auth_mode": "oauth_password",
    "dealer_code_configured": true
  }
}
```

### Test 2: OAuth + Auto-population (getDealerInfo)

This tests both Fix #1 (OAuth) and Fix #2 (dealer code auto-population):

```bash
curl -X POST http://localhost:8080/query \
  -H "Content-Type: application/json" \
  -d '{"action": "getDealerInfo", "params": {}}'
```

**What to look for:**
- Should NOT return "Missing required parameter: code" error
- Dealer code should be auto-populated from config
- Should return dealer information or a properly formatted MPSM error

### Test 3: Pagination Auto-population (getCustomers)

This tests Fix #2 (pagination defaults):

```bash
curl -X POST http://localhost:8080/query \
  -H "Content-Type: application/json" \
  -d '{"action": "getCustomers", "params": {}}'
```

**What to look for:**
- Should NOT return "Missing required parameter: page" error
- Page should default to 1, pageSize to 50
- Should return customer list or a properly formatted error

### Test 4: MPSM Error Response Handling

This tests Fix #3 (IsValid field detection):

```bash
curl -X POST http://localhost:8080/query \
  -H "Content-Type: application/json" \
  -d '{"action": "getDealerInfo", "params": {"code": "INVALID_CODE"}}'
```

**What to look for:**
- If MPSM returns HTTP 200 with `IsValid: false`, engine should convert this to an error response
- Error message should contain the MPSM error description from the `Errors` array

### Test 5: List Available Endpoints

```bash
curl -X GET http://localhost:8080/endpoints
```

**Expected Response:**
```json
{
  "success": true,
  "count": 544,
  "operations": [
    {
      "action": "getDealerInfo",
      "method": "GET",
      "path": "/dealers/{code}",
      "summary": "Get dealer information"
    },
    ...
  ]
}
```

## Expected Test Results

When running `php test.php`, you should see:

```
================================================================================
MPS API ENGINE TEST SUITE
================================================================================
Testing critical fixes from API discovery

[TEST 1] Engine Health Check
--------------------------------------------------------------------------------
[PASS] Engine is healthy
  > Service: MPS Monitors API Engine
  > Version: 1.1.0
  > Auth Mode: oauth_password

[TEST 2] OAuth Token Acquisition (Fix #1: OAuth on all endpoints)
--------------------------------------------------------------------------------
[PASS] OAuth token acquired successfully
  > Token acquired: eyJhbGciOiJSUzI1NiI...

[TEST 3] Smart Parameter Population - Dealer Code (Fix #2)
--------------------------------------------------------------------------------
[PASS] Dealer code auto-populated successfully
  > Retrieved dealer info without providing dealerCode parameter
  > Dealer Code: YOUR_DEALER_CODE

... (more tests)

================================================================================
TEST SUMMARY
================================================================================
Total Tests:  10
Passed:       10 (100.0%)
Failed:       0
```

## Troubleshooting

### Test 2 Fails: OAuth Token Acquisition

**Error:** "Failed to acquire OAuth token"

**Solutions:**
1. Check `.env` file has correct credentials:
   ```
   CLIENT_ID=your_client_id
   CLIENT_SECRET=your_client_secret
   USERNAME=your_username
   PASSWORD=your_password
   ```
2. Verify MPSM API OAuth endpoint is accessible
3. Check logs in `mps-api/logs/` for detailed error messages

### Test 3 Fails: Dealer Code Not Auto-populated

**Error:** "Missing required parameter: code"

**Solutions:**
1. Verify `.env` has `DEALER_CODE` configured
2. Check `engine.php` lines 918-953 for `getDefaultParameterValue()` method
3. Check `engine.php` lines 302-314 for query parameter integration

### Test 5/6/7 Fail: Response Validation Not Working

**Error:** MPSM errors not being caught

**Solutions:**
1. Verify `validateMPSMResponse()` method exists at `engine.php:843-907`
2. Check integration at `engine.php:598-628` in `executeRequest()` method
3. Enable debug mode: `MPS_DEBUG=true` in `.env`

### All Tests Fail: Engine Won't Initialize

**Error:** "FATAL: Failed to initialize engine"

**Solutions:**
1. Check PHP version: `php -v` (requires 7.4+)
2. Verify all files are present: `engine.php`, `SwaggerActionRegistry.php`, `Swagger.json`
3. Check file permissions
4. Review PHP error logs

## Integration Testing with Custom GPT

Once all tests pass, you can proceed to:

1. **Deploy to subdomain** (e.g., `https://mps-api.yourdomain.com`)
2. **Generate OpenAPI 3.0 spec** for Custom GPT
3. **Configure Custom GPT** with the deployed API endpoint
4. **Test natural language queries** like:
   - "Get my dealer information"
   - "Show me all customers"
   - "List vehicles in inventory"

## Success Criteria

All three fixes are working correctly when:

✅ **Fix #1 (OAuth)**: All API requests include `Authorization: Bearer <token>` header
✅ **Fix #2 (Parameters)**: Requests work without explicitly providing dealer codes or pagination params
✅ **Fix #3 (Validation)**: MPSM errors (IsValid=false) are properly caught and returned as error responses

## Next Steps

After successful testing:

1. Review logs in `mps-api/logs/` for any warnings
2. Deploy to production subdomain with SSL
3. Create OpenAPI 3.0 specification for Custom GPT
4. Configure Custom GPT Actions
5. Test end-to-end with natural language queries

## Support

If tests fail and you cannot resolve the issues:

1. Check logs in `mps-api/logs/`
2. Enable debug mode: `MPS_DEBUG=true`
3. Review the API discovery results in `output/endpoint_reference.yaml`
4. Compare with working curl examples in `output/curl_recipes.md`
