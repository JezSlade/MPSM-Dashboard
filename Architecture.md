## 🏗️ MPSM Architecture Overview

### 📁 Project Structure

```
/project-root
├── api/                  # All backend PHP API proxy files
│   ├── get_token.php     # Retrieves OAuth2 token from TOKEN_URL
│   └── get_customers.php # Calls Customer/GetCustomers securely
├── public/               # Frontend assets (JS, CSS)
├── cards/                # Server-rendered dashboard cards
├── index.php             # SPA entry point
├── .env                  # Local environment config
└── CONTRIBUTING.md       # Developer workflow & integration rules
```

### 🔒 Environment Configuration

`.env` must contain:

* CLIENT\_ID
* CLIENT\_SECRET
* USERNAME
* PASSWORD
* SCOPE
* TOKEN\_URL
* BASE\_URL
* DEALER\_CODE
* DEALER\_ID
* DEVICE\_PAGE\_SIZE

### 🔁 API Call Design Pattern

All frontend requests **must go through local PHP proxies** under `/api/`.

**✅ Example Flow:**

1. JS requests `/api/get_token.php`
2. PHP loads `.env`, builds the token form request
3. Sends to `TOKEN_URL` via curl
4. Returns JSON `{ access_token, expires_in, ... }`

**❌ Never do this in JS:**

```js
fetch('https://api.abassetmanagement.com/...')
```

### 📐 PHP Proxy Standards

Each PHP file in `/api/` must:

* Load `.env` locally (not from globals)
* Validate required keys (`if (empty($env[...]))`)
* Require `Authorization: Bearer` headers if token needed
* Respond with only `application/json` data
* Use curl with JSON headers:

```php
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer {$token}",
  "Content-Type: application/json",
  "Accept: application/json"
]);
```

### 🌐 JS Fetch Standard

**Uniform pattern for all JS fetch calls:**

```js
fetch('/api/get_customers.php', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${window.authToken}`,
    'Accept': 'application/json'
  }
})
.then(async res => {
  const text = await res.text();
  if (!res.ok) throw new Error(text);
  return JSON.parse(text);
})
```

### 🛡️ Error Handling

* All errors (token failures, API errors) must return:

```json
{ "error": "..." }
```

* No HTML error pages.
* No stack traces or raw PHP warnings.

### 📚 Extension Guidance

To add another API:

* Create `/api/get_devices.php`
* Copy `get_customers.php` as base
* Change `Url` and payload in `$request`
* Update frontend to call the new PHP endpoint

This ensures consistency and security across the stack.
