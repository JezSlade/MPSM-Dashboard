# 📘 Dashboard HTTP Helper Usage Guide (PHP)

These PHP helper files provide access to Dashboard-related endpoints grouped by HTTP method.

---

## 🔧 Requirements

- PHP 7.x/8.x with cURL enabled
- API Base URL and request schema

---

## 📂 Files Included

- `DashboardGetHelpers.php`
- `DashboardPostHelpers.php`

---

## 🧪 Example

```php
require_once 'DashboardGetHelpers.php';

$baseUrl = 'https://api.example.com';
$code = 'customer123';

$response = customerdashboard($code, $baseUrl);
print_r($response);
```

---

## 🧩 Function Signatures

### GET:
```php
customerdashboard($code, $baseUrl, $headers = []);
customerdashboard_pages($code, $baseUrl, $headers = []);
sdsaction_getdeviceactionsdashboard(...params..., $baseUrl, $headers = []);
```

### POST:
```php
customerdashboard_get($payload, $baseUrl, $headers = []);
customerdashboard_devices($payload, $baseUrl, $headers = []);
customerdashboard_connectors($payload, $baseUrl, $headers = []);
```
