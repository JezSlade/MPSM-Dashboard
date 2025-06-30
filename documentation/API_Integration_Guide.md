
# MPS Monitor API Integration Guide

A consolidated guide from all documents, this guide summarizes the process for authenticating, caching, and calling MOPS Monitor APIs.

---

## 🔐 Authentication

Tokens are obtained from the /Token endpoint via basic user password grant.

### Token Request
- ENDPOINT: /Token
- METHOD: POST
- PAYLOAD:
```
grant_type=password
username={USERNAME}
password={PASSWORD}
```
- RESPONSE:
```json
{
  "access_token": "...",
  "token_type": "bearer",
  "expires_in": 3600,
  "refresh_token": "..."
}
```

### Token Refresh
- PAYLOAD:
```
grant_type=refresh_token
refresh_token={refresh_token}
```

---

## 🧭 API Routing Template

Inside each `/api/*.php` endpoint:
```php
$method   = 'POST';
$path     = 'Device/Get';
$useCache = true;
require __DIR__ . '/../includes/api_bootstrap.php';
```

- `api_bootstrap.php`:
  - Loads .env
  - Handles OAuth flow
  - Manages Redis cache
  - Sends cURL request to API_BASE_URL
  - Emits JSON response

---

## 👥 Customer APIs

- **List All Customers**:  
  `GET /Customer/GetCustomers`
- **Get by ID**:  
  `GET /Customer/GetCustomer?id={id}`
- **Get by Code**:  
  `GET /Customer/GetCustomerByCode?code={code}`
- **Create**:  
  `POST /Customer/CreateCustomer` with `model`
- **Update**:  
  `POST /Customer/UpdateCustomer` with `model`
- **Delete**:  
  `DELETE /Customer/DeleteCustomer?id={id}`

---

## 🧱 Technical Infrastructure Requirements

- `.env` values used:
  - `API_BASE_URL`
  - `USERNAME`
  - `PASSWORD`
  - `CLIENT_ID`, etc.
- Manual parsing of `.env` only; no external PHP libraries
- Relative paths only (`__DIR__`)
- No external dependencies
- Each API file is self-contained
- Caching via `engine/cache_engine.php`
- Debug logs at `logs/debug.log`

---

## 🔬 Patch Protocol Highlights (from Ai_Patch_Validation_Protocol.md)

- **No assumptions** — everything must follow doc exactly
- Parameter names and types must match documentation verbatim
- Existing functionality cannot be altered without explicit instruction
- All changes must be fully isolated
- JSON schema and error responses must match samples exactly

---

This guide is consistent with the rules in Ai_Patch_Validation_Protocol from the repository.

Ready to be committed to your repo! 
