
# Enhanced MPS Monitor API Integration Guide

This guide consolidates all PHP files, routing patterns, and token flows to provide a robust overview of MPS Monitor API usage.

---

## 🔐 Authentication Workflow

Tokens are obtained using the password grant type with the following configuration:

- **Endpoint**: `POST /Token`
- **Content-Type**: `application/x-www-form-urlencoded`
- **Payload**:
```text
grant_type=password
username={USERNAME}
password={PASSWORD}
client_id={CLIENT_ID}
client_secret={CLIENT_SECRET}
scope={SCOPE}
```

- **Response**:
```json
{
  "access_token": "...",
  "token_type": "bearer",
  "expires_in": 3600,
  "refresh_token": "..."
}
```

The token is automatically handled by the shared `get_token()` function in `api_functions.php`.

---

## 🧰 API Bootstrapping and Routing

All API calls are dispatched through this pattern:

```php
$method   = 'POST';
$path     = 'Customer/GetCustomers';
$useCache = true;
require __DIR__ . '/../includes/api_bootstrap.php';
```

- **`api_bootstrap.php`**:
  - Loads `.env` via `parse_env_file()`
  - Fetches a fresh OAuth token
  - Sends cURL request to `{API_BASE_URL}/{path}`
  - Caches response in Redis if enabled
  - Emits response as JSON

---

## 🧩 Example: Customer Retrieval

File: `get_customers.php`

```php
<?php declare(strict_types=1);
$method   = 'POST';
$path     = 'Customer/GetCustomers';
$useCache = true;

require __DIR__ . '/../includes/api_bootstrap.php';
```

This performs a POST to `Customer/GetCustomers` using a valid OAuth token and returns JSON.

---

## 🔄 Token Debug Endpoint

File: `get_token.php`

```php
require __DIR__ . '/../includes/api_functions.php';

$config = parse_env_file(__DIR__ . '/../.env');
$token  = get_token($config);

header('Content-Type: application/json');
echo json_encode(['access_token' => $token]);
```

Use this for debugging OAuth configuration or inspecting environment issues.

---

## 🔧 Environment Variables (Required)

These must be present in `.env`:
- `API_BASE_URL`
- `TOKEN_URL`
- `USERNAME`
- `PASSWORD`
- `CLIENT_ID`
- `CLIENT_SECRET`
- `SCOPE`

---

## 💡 Supporting Utilities

### `api_functions.php`
- Contains:
  - `parse_env_file()`
  - `get_token()`
  - `call_api()` for sending authorized JSON HTTP requests

### `config.php`
- Defines defaults and constants
- Provides a built-in debug mode
- Simplifies token initialization on include

---

This document reflects the actual implementation from your working environment and documentation standards.
