
# MPS Monitor Developer Onboarding Guide

This guide provides everything a developer needs to replicate the logic, behavior, and integration patterns used in the MPSM Dashboard to interface with the MPS Monitor API.

---

## 🔐 1. Authentication Flow

All access to MPS Monitor’s API requires an OAuth2 token. The app uses the **password grant** method.

### 🔑 Token Acquisition
- Endpoint: `POST /Token`
- Headers:
  - `Content-Type: application/x-www-form-urlencoded`
- Body:
  ```
  grant_type=password
  username=<env:USERNAME>
  password=<env:PASSWORD>
  client_id=<env:CLIENT_ID>
  client_secret=<env:CLIENT_SECRET>
  scope=<env:SCOPE>
  ```
- Successful response includes:
  ```json
  {
    "access_token": "string",
    "token_type": "bearer",
    "expires_in": 3600,
    "refresh_token": "string"
  }
  ```

Tokens are stored in memory or Redis for reuse and expiry checking.

### ♻️ Token Refresh
- Triggered when `access_token` is expired or close to expiration.
- Send:
  ```
  grant_type=refresh_token
  refresh_token=<valid_refresh_token>
  ```

---

## 🧰 2. Universal API Request Logic

### ✅ Core Behavior
- All API calls are routed through a bootstrap logic module.
- The flow:
  1. Define method (`GET` or `POST`), endpoint path, and caching preference.
  2. Load environment secrets from `.env`.
  3. Ensure a valid token is available.
  4. Create HTTP request with headers and payload.
  5. Cache and return the response in JSON.

### 💡 Sample Routing Definition
```php
$method = 'POST';
$path = 'Customer/GetCustomers';
$useCache = true;
```

---

## 📇 3. Customer Listing

### 🔎 Fetch All Customers
- Endpoint: `POST /Customer/GetCustomers`
- Requires `Authorization: Bearer <access_token>`
- Returns:
```json
[
  {
    "id": 1,
    "name": "Company A",
    "code": "C001",
    "dealerCode": "D123"
  }
]
```

### 🔁 Interaction Logic
- Dropdown is populated with this data.
- User selects a customer.
- The selected customer’s ID/code is stored in `localStorage` (or a session).

---

## 🖨️ 4. Printer and Device Management

### 🔧 Endpoints Include:
- `Device/GetDevices` — all devices under a customer
- `Device/GetDeviceDetails?id={id}` — full specs
- `Device/GetDeviceCounters?id={id}` — usage stats
- `Device/GetSupplies?id={id}` — toner/ink levels
- `Device/GetSNMP?id={id}` — errors, traps

Each call uses a token and returns JSON.

### 📋 Display Flow
- Data is displayed in cards, widgets, or reports.
- Clicking a printer shows expanded details.
- Supply levels and errors are color-coded.

---

## ⚙️ 5. Environment Requirements

The app uses a `.env` configuration with these keys:

```
API_BASE_URL=
TOKEN_URL=
USERNAME=
PASSWORD=
CLIENT_ID=
CLIENT_SECRET=
SCOPE=
```

Load this config via a `.env` parser or PHP equivalent.

---

## 📦 6. Caching, Logging & Errors

- Redis is used to cache tokens and API responses.
- Logs are written to a debug log file.
- Failures from MPS Monitor are passed back with the HTTP status and JSON error object.

---

## 🧪 7. Developer Tips

- Use the token debug endpoint to inspect token problems.
- All responses are JSON — use `fetch()` or Axios in frontends.
- UI widgets store selected customer IDs locally.
- Always validate API payloads match MPSM docs.
- Don’t hardcode — everything comes from `.env`.

---

This guide contains everything your dev team needs to emulate the behavior of the current MPSM Dashboard system, using modular, token-aware, API-first PHP and JavaScript logic.

