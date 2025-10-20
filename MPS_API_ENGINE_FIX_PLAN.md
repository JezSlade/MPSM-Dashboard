# MPS API Engine Fix Plan - Apply Discovery Learnings

## Executive Summary

**Goal**: Fix the mps-api/ engine to work reliably with the MPSM API, enabling a Custom GPT Action that can query MPSM data through a subdomain.

**Current Status**: Engine has foundation but likely has authentication and payload generation issues similar to what we discovered today.

**Key Discovery**: The API requires OAuth Bearer tokens on **ALL** endpoints (not documented in Swagger), and specific dealer/customer codes must be provided in query parameters.

---

## Critical Findings from Discovery Process

### 1. Authentication Issues

**Problem Discovered**:
- Swagger.json doesn't document security requirements
- All endpoints require OAuth Bearer tokens
- Token must be refreshed before expiration (30 min / 1800s)

**Current Engine Status**:
- ✅ Has OAuth implementation in engine.php
- ❓ Unknown if tokens are being sent on all requests
- ❓ Unknown if token refresh is working correctly

**Required Fix**:
- Default ALL endpoints to require OAuth authentication
- Ensure `Authorization: Bearer {token}` header on every request
- Implement automatic token refresh 5 minutes before expiry
- Cache tokens in memory (not just per-request)

### 2. Payload Generation Issues

**Problem Discovered**:
- Required query parameters (like `code`) were generating "null" strings
- Swagger 2.0 format has parameters directly, not in nested `schema`
- Many endpoints need dealer/customer codes from environment

**Current Engine Status**:
- ✅ Has SwaggerActionRegistry to parse endpoints
- ✅ Has parameter handling in dispatchAction()
- ❓ Unknown if dealer codes are auto-populated

**Required Fix**:
- Auto-populate `code` parameter with `DEALER_CODE` from config when parameter name is "code"
- Auto-populate `dealerId` with `DEALER_ID` from config
- Add intelligent defaults for common parameters
- Handle Swagger 2.0 parameter format correctly

### 3. Response Handling

**Problem Discovered**:
- API returns 200 status even for logical errors
- Actual result is in nested structure: `{"Result": {...}, "IsValid": true/false, "Errors": [...]}`
- Must check `IsValid` field, not just HTTP status

**Current Engine Status**:
- ❓ Unknown if checking `IsValid` field
- ❓ Unknown if extracting `Errors` array

**Required Fix**:
- After receiving 200 response, check `IsValid` field
- If `IsValid` === false, treat as error and extract `Errors` array
- Return errors in standardized format for GPT

---

## Implementation Plan

### Phase 1: Authentication Hardening (HIGH PRIORITY)

#### Task 1.1: Force OAuth on All Endpoints
**File**: `mps-api/SwaggerActionRegistry.php`

**Changes**:
```php
// When parsing operations, default requires_auth to true
private function parseOperation($path, $method, $operation) {
    // ...existing code...

    // CRITICAL FIX: Swagger doesn't document security, but API requires auth
    $requiresAuth = true; // Default to requiring auth for all endpoints

    // Only set to false if explicitly marked as public (which never happens in this API)
    if (isset($operation['security']) && empty($operation['security'])) {
        $requiresAuth = false;
    }

    $result['requires_auth'] = $requiresAuth;
}
```

#### Task 1.2: Ensure Authorization Header on All Requests
**File**: `mps-api/engine.php` - Method: `executeRequest()`

**Changes**:
```php
private function executeRequest($url, $method, $data, $requestId, $attempt, $options) {
    // Build headers
    $headers = [];

    // CRITICAL: Always add auth header (unless explicitly disabled)
    if ($this->requiresAuth()) {
        if (self::$accessToken) {
            $headers[] = 'Authorization: Bearer ' . self::$accessToken;
        } else {
            // No token available - this is an error
            return $this->errorResponse('No auth token available', self::ERR_API);
        }
    }

    // ...rest of method...
}
```

#### Task 1.3: Improve Token Refresh Logic
**File**: `mps-api/engine.php` - Method: `prepareAuthorization()`

**Changes**:
```php
private function prepareAuthorization() {
    // Check if token exists and is still valid (with 5-minute buffer)
    $now = time();
    $bufferSeconds = 300; // 5 minutes

    if (self::$accessToken && self::$accessTokenExpiresAt > ($now + $bufferSeconds)) {
        // Token is still valid
        return true;
    }

    // Need to fetch or refresh token
    return $this->acquireToken();
}

private function acquireToken() {
    // Use refresh_token if available, otherwise password grant
    if (self::$refreshToken) {
        $result = $this->refreshToken();
        if ($result['success']) {
            return true;
        }
        // Fall through to password grant if refresh fails
    }

    // Perform password grant
    $result = $this->passwordGrant();
    if ($result['success']) {
        return true;
    }

    return $this->errorResponse('Authentication failed: ' . $result['error'], self::ERR_API);
}
```

---

### Phase 2: Intelligent Parameter Population (HIGH PRIORITY)

#### Task 2.1: Add Default Parameter Provider
**File**: `mps-api/engine.php` - New method

**Add**:
```php
/**
 * Get default value for a parameter based on its name and context
 */
private function getDefaultParameterValue($paramName, $paramType = 'string') {
    $paramLower = strtolower($paramName);

    // Auto-populate dealer information
    if ($paramLower === 'code' || $paramLower === 'dealercode' || $paramLower === 'dealer_code') {
        return self::$config['DEALER_CODE'] ?? null;
    }

    if ($paramLower === 'dealerid' || $paramLower === 'dealer_id') {
        return self::$config['DEALER_ID'] ?? null;
    }

    // Auto-populate customer information (if available in config)
    if ($paramLower === 'customercode' || $paramLower === 'customer_code') {
        return self::$config['CUSTOMER_CODE'] ?? null;
    }

    if ($paramLower === 'customerid' || $paramLower === 'customer_id') {
        return self::$config['CUSTOMER_ID'] ?? null;
    }

    // Pagination defaults
    if ($paramLower === 'page' || $paramLower === 'pagenumber') {
        return 1;
    }

    if ($paramLower === 'pagesize' || $paramLower === 'limit') {
        return 50;
    }

    // No default available
    return null;
}
```

#### Task 2.2: Update Parameter Handling in dispatchAction
**File**: `mps-api/engine.php` - Method: `dispatchAction()`

**Changes**:
```php
// Resolve query parameters
foreach ($operation['queryParams'] as $name => $meta) {
    if (array_key_exists($name, $remaining)) {
        $query[$name] = $remaining[$name];
        unset($remaining[$name]);
    } elseif ($meta['required']) {
        // Try to get default value
        $defaultValue = $this->getDefaultParameterValue($name, $meta['type'] ?? 'string');

        if ($defaultValue !== null) {
            $query[$name] = $defaultValue;
            // Log that we auto-populated
            self::logDebug("Auto-populated required parameter '{$name}' with default value");
        } else {
            return $this->errorResponse("Missing required query parameter: {$name}", self::ERR_VALIDATION);
        }
    }
}
```

---

### Phase 3: Response Validation (MEDIUM PRIORITY)

#### Task 3.1: Add Response Validator
**File**: `mps-api/engine.php` - New method

**Add**:
```php
/**
 * Validate MPSM API response format
 * API returns 200 even for errors, must check IsValid field
 */
private function validateMPSMResponse($responseData, $httpStatus) {
    // If not 200, it's a real HTTP error
    if ($httpStatus !== 200) {
        return [
            'valid' => false,
            'error' => "HTTP {$httpStatus} error",
            'details' => $responseData
        ];
    }

    // Check for MPSM-specific response structure
    if (!is_array($responseData)) {
        return [
            'valid' => true, // Might be raw data
            'data' => $responseData
        ];
    }

    // Check for IsValid field (MPSM standard)
    if (isset($responseData['IsValid'])) {
        if ($responseData['IsValid'] === false) {
            // Extract errors
            $errors = $responseData['Errors'] ?? [];
            $errorMessages = [];

            foreach ($errors as $error) {
                if (isset($error['Description'])) {
                    $errorMessages[] = $error['Description'];
                } elseif (isset($error['Code'])) {
                    $errorMessages[] = $error['Code'];
                }
            }

            return [
                'valid' => false,
                'error' => implode('; ', $errorMessages) ?: 'Request failed',
                'details' => $errors
            ];
        }

        // Valid response - extract Result
        return [
            'valid' => true,
            'data' => $responseData['Result'] ?? $responseData
        ];
    }

    // No IsValid field - assume valid
    return [
        'valid' => true,
        'data' => $responseData
    ];
}
```

#### Task 3.2: Use Validator in executeRequest
**File**: `mps-api/engine.php` - Method: `executeRequest()`

**Changes**:
```php
// After getting response and parsing JSON
$responseData = json_decode($responseBody, true);

// Validate MPSM-specific response format
$validation = $this->validateMPSMResponse($responseData, $httpCode);

if (!$validation['valid']) {
    return $this->errorResponse(
        $validation['error'],
        self::ERR_API,
        ['details' => $validation['details']]
    );
}

return $this->successResponse($validation['data'], [
    'http_status' => $httpCode,
    'request_id' => $requestId,
    // ...other metadata...
]);
```

---

### Phase 4: Use Discovery Results (MEDIUM PRIORITY)

#### Task 4.1: Import Discovered Endpoints
**Location**: `mps-api/discovered_endpoints/`

**Action**:
1. Copy `output/endpoint_reference.yaml` to `mps-api/discovered_endpoints/reference.yaml`
2. Copy `output/samples/` to `mps-api/discovered_endpoints/samples/`
3. Create loader to read discovered endpoints and compare with Swagger

#### Task 4.2: Create Endpoint Validator
**File**: `mps-api/EndpointValidator.php` - New class

**Purpose**:
```php
class EndpointValidator {
    private $discoveredEndpoints;

    public function __construct() {
        // Load discovered endpoints from YAML
        $this->discoveredEndpoints = $this->loadDiscoveredEndpoints();
    }

    /**
     * Validate if an action/endpoint is known to work
     */
    public function isEndpointDiscovered($action) {
        return isset($this->discoveredEndpoints[$action]);
    }

    /**
     * Get known-good parameters for an action
     */
    public function getDiscoveredParameters($action) {
        if (!$this->isEndpointDiscovered($action)) {
            return null;
        }

        return $this->discoveredEndpoints[$action]['params'] ?? [];
    }

    /**
     * Get sample request for an action (for debugging)
     */
    public function getSampleRequest($action) {
        $sampleFile = __DIR__ . "/discovered_endpoints/samples/{$action}_request.json";
        if (file_exists($sampleFile)) {
            return json_decode(file_get_contents($sampleFile), true);
        }
        return null;
    }
}
```

---

### Phase 5: OpenAPI Spec for Custom GPT (HIGH PRIORITY)

#### Task 5.1: Generate GPT-Friendly OpenAPI Spec
**File**: `mps-api/openapi.yaml` - New file

**Purpose**: Create a simplified OpenAPI 3.0 spec that describes the engine's API (not the MPSM API)

**Structure**:
```yaml
openapi: 3.0.0
info:
  title: MPSM Dashboard API Engine
  version: 1.2.0
  description: |
    Proxy API for querying MPSM Monitor data.
    Handles OAuth authentication and parameter population automatically.

servers:
  - url: https://mps-api.yourdomain.com
    description: Production API Engine

security:
  - ApiKeyAuth: []

paths:
  /action:
    post:
      summary: Execute an MPSM API action
      operationId: executeAction
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required:
                - action
              properties:
                action:
                  type: string
                  description: Name of the action to execute (from Swagger)
                  example: "Dealer/Get"
                params:
                  type: object
                  description: Parameters for the action
                  example:
                    code: "NY06AGDWUQ"
      responses:
        '200':
          description: Success
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                  data:
                    type: object
                  metadata:
                    type: object

  /actions:
    get:
      summary: List all available actions
      operationId: listActions
      responses:
        '200':
          description: List of actions

  /health:
    get:
      summary: Health check
      operationId: healthCheck
      responses:
        '200':
          description: Engine is healthy

components:
  securitySchemes:
    ApiKeyAuth:
      type: apiKey
      in: header
      name: X-API-Key
      description: API key for the engine (separate from MPSM OAuth)
```

#### Task 5.2: Add Action List Endpoint
**File**: `mps-api/index.php` - Add route

**Add**:
```php
// List all available actions
if ($route === 'actions' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $registry = SwaggerActionRegistry::getInstance();
    $actions = $registry->getAllActions();

    // Format for GPT consumption
    $formattedActions = [];
    foreach ($actions as $action => $details) {
        $formattedActions[] = [
            'action' => $action,
            'method' => $details['method'],
            'path' => $details['path'],
            'summary' => $details['summary'] ?? '',
            'parameters' => array_keys($details['queryParams'] ?? []),
            'required_params' => array_keys(array_filter(
                $details['queryParams'] ?? [],
                fn($p) => $p['required'] ?? false
            ))
        ];
    }

    echo json_encode([
        'success' => true,
        'actions' => $formattedActions,
        'count' => count($formattedActions)
    ]);
    exit;
}
```

---

### Phase 6: Testing & Validation (HIGH PRIORITY)

#### Task 6.1: Create Test Suite
**File**: `mps-api/tests/test_engine.php` - New file

**Tests**:
1. OAuth token acquisition
2. Token refresh before expiry
3. Auto-parameter population (dealer code)
4. Response validation (IsValid field)
5. Error handling (Errors array extraction)
6. Known endpoints from discovery

#### Task 6.2: Create Postman Collection
**File**: `mps-api/tests/postman_collection.json`

**Include**:
- Health check
- List actions
- Execute common actions (Dealer/Get, Device/List, etc.)
- Error scenarios
- Token expiry scenarios

---

### Phase 7: Deployment Preparation (MEDIUM PRIORITY)

#### Task 7.1: Environment Setup
**Files**:
- `mps-api/.env.example` (template)
- `mps-api/DEPLOYMENT.md` (instructions)

**Required .env variables**:
```env
# MPS API Configuration
MPS_BASE_URL=https://api.abassetmanagement.com/api3/
TOKEN_URL=https://api.abassetmanagement.com/api3/token

# OAuth Credentials
CLIENT_ID=your_client_id
CLIENT_SECRET=your_client_secret
USERNAME=your_username
PASSWORD=your_password
SCOPE=account

# Dealer Information (auto-populated in requests)
DEALER_CODE=YOUR_DEALER_CODE
DEALER_ID=YOUR_DEALER_ID

# Optional: Customer defaults
CUSTOMER_CODE=
CUSTOMER_ID=

# Engine Configuration
MPS_TIMEOUT=30
MPS_CONNECT_TIMEOUT=10
MPS_MAX_RETRIES=3
MPS_DEBUG=false

# Engine Security (for GPT access)
ENGINE_API_KEY=generate_secure_random_key_here
ALLOWED_ORIGINS=https://chat.openai.com
```

#### Task 7.2: Subdomain Configuration
**File**: `mps-api/SUBDOMAIN_SETUP.md`

**Instructions**:
1. Create subdomain: `mps-api.yourdomain.com`
2. Point to `public_html/mpsm.resolutionsbydesign.us/mps-api/`
3. Configure SSL certificate
4. Test endpoint: `https://mps-api.yourdomain.com/health`
5. Verify CORS headers for chat.openai.com

---

### Phase 8: Custom GPT Configuration (HIGH PRIORITY)

#### Task 8.1: GPT Instructions
**File**: `mps-api/GPT_INSTRUCTIONS.md`

**Sample**:
```markdown
You are an expert assistant for MPSM Dashboard data queries.

You have access to the MPSM API through the configured action.
Always use the `executeAction` operation.

Common actions:
- "Dealer/Get" - Get dealer information (no params needed, auto-uses dealer code)
- "Device/List" - List all devices
- "Customer/List" - List all customers
- "Counter/Device/List" - Get device counters

When calling actions:
1. Use the exact action name (case-sensitive)
2. Only include params that the user specifically provides
3. Dealer code is auto-populated, don't send it unless overriding

Always explain the data you receive in user-friendly terms.
```

#### Task 8.2: Action Configuration in GPT
**Location**: Custom GPT Settings → Actions

**Add**:
1. Import `openapi.yaml`
2. Set authentication: API Key (header `X-API-Key`)
3. Set privacy policy URL
4. Test with sample action

---

## Priority Order for Implementation

### Immediate (Week 1)
1. ✅ **Phase 1: Authentication** - Fix OAuth on all endpoints
2. ✅ **Phase 2: Parameters** - Auto-populate dealer codes
3. ✅ **Phase 3: Response Validation** - Check IsValid field

### Short-term (Week 2)
4. ✅ **Phase 5: OpenAPI Spec** - Create GPT-friendly spec
5. ✅ **Phase 7: Deployment** - Get engine on subdomain
6. ✅ **Phase 8: GPT Setup** - Configure Custom GPT

### Medium-term (Week 3-4)
7. ⏸️ **Phase 4: Discovery Results** - Import endpoint reference
8. ⏸️ **Phase 6: Testing** - Comprehensive test suite

---

## Success Criteria

### Minimal Viable Product (MVP)
- [ ] Engine successfully authenticates with OAuth
- [ ] Dealer information auto-populated
- [ ] Responses validated and errors extracted
- [ ] OpenAPI spec created for GPT
- [ ] Engine deployed on subdomain with SSL
- [ ] Custom GPT can execute at least 5 common actions

### Full Success
- [ ] All 200+ GET endpoints testable
- [ ] Response validation handles all MPSM formats
- [ ] Comprehensive error messages for GPT
- [ ] Test suite with 90%+ coverage
- [ ] Documentation complete
- [ ] GPT can handle complex multi-step queries

---

## Risk Mitigation

### Risk 1: OAuth Token Expiry Mid-Conversation
**Mitigation**: Refresh token 5 minutes before expiry, cache in shared memory

### Risk 2: Rate Limiting from MPSM API
**Mitigation**: Implement caching layer for common queries, respect rate limits

### Risk 3: GPT Sends Invalid Parameters
**Mitigation**: Strong validation in engine, return helpful error messages with examples

### Risk 4: Discovery Results Don't Match Current API
**Mitigation**: Run discovery regularly (monthly), version the endpoint reference

---

## Files to Create/Modify

### New Files
- [ ] `mps-api/EndpointValidator.php`
- [ ] `mps-api/openapi.yaml`
- [ ] `mps-api/tests/test_engine.php`
- [ ] `mps-api/tests/postman_collection.json`
- [ ] `mps-api/.env.example`
- [ ] `mps-api/DEPLOYMENT.md`
- [ ] `mps-api/SUBDOMAIN_SETUP.md`
- [ ] `mps-api/GPT_INSTRUCTIONS.md`
- [ ] `mps-api/discovered_endpoints/reference.yaml` (copied from discovery)

### Modified Files
- [ ] `mps-api/engine.php` - Auth, parameters, response validation
- [ ] `mps-api/SwaggerActionRegistry.php` - Force auth on all endpoints
- [ ] `mps-api/index.php` - Add /actions route
- [ ] `mps-api/config.php` - Add new config options

---

## Next Steps

1. **Review this plan** - Confirm approach aligns with goals
2. **Start with Phase 1** - Fix authentication (highest impact)
3. **Test incrementally** - Verify each phase before moving on
4. **Document learnings** - Update plan as you discover edge cases

---

**Generated**: Based on API Discovery findings
**Last Updated**: October 20, 2025
**Status**: Ready for implementation
