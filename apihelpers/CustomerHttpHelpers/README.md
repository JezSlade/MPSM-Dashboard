# 📘 Customer HTTP Helper Usage Guide (PHP)

These PHP helper files provide access to Customer-related endpoints grouped by HTTP method.

---

## 🔧 Requirements

- PHP 7.x/8.x with cURL enabled
- API Base URL and payload/query input

---

## 📂 Files Included

- `CustomerGetHelpers.php`
- `CustomerPostHelpers.php`
- `CustomerPutHelpers.php`
- `CustomerDeleteHelpers.php`

---

## 🧪 Example

```php
require_once 'CustomerPostHelpers.php';

$baseUrl = 'https://api.example.com';
$payload = [
    'code' => 'CUST001'
];

$response = customer_get($payload, $baseUrl);
print_r($response);
```

---

## 🧩 Function Signatures

Each file contains specific PHP functions matching the API operationId and method.

### GET:
```php
// Examples may include:
customer_getdetails($code, $baseUrl, $headers = []);
```

### POST:
```php
customer_get($payload, $baseUrl, $headers = []);
```

... and similar for PUT/DELETE methods.
