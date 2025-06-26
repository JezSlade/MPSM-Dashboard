# 📘 Device HTTP Helper Usage Guide (PHP)

These PHP helper files simplify interaction with the device-related API endpoints grouped by HTTP method (`GET`, `POST`, `PUT`, `DELETE`).

---

## 🔧 Prerequisites

- PHP 7.x or 8.x with `cURL` extension enabled.
- Base API URL from your environment or configuration.

---

## 📂 File Structure

Each file contains:
- A **shared function** `executeHttpRequest(...)` to DRY out request logic.
- Dedicated helper functions for individual endpoints.

### Files:
- `DeviceGetHelpers.php`
- `DevicePostHelpers.php`
- `DevicePutHelpers.php`
- `DeviceDeleteHelpers.php`

---

## 🧪 Example Usage

```php
require_once 'DevicePutHelpers.php'; // Include the correct helper

$baseUrl = 'https://api.example.com';
$payload = [
    'deviceIds' => ['device1', 'device2'],
    'officeId' => 'office123'
];

try {
    $response = assignOfficeToDevicesByDeviceId($baseUrl, $payload);
    print_r($response);
} catch (Exception $e) {
    echo 'API call failed: ' . $e->getMessage();
}
```

---

## 🧩 Function Signatures

### PUT Examples:
```php
assignOfficeToDevicesByDeviceId($baseUrl, $payload, $headers = []);
assignOfficeToDevicesBySerialNumber($baseUrl, $payload, $headers = []);
updateDevicesBySerialNumbers($baseUrl, $payload, $headers = []);
restoreDeletedDevice($baseUrl, $payload, $headers = []);
```

### GET Example:
```php
getDeviceAdditionalInfos($baseUrl, $id, $headers = []);
```

### POST and DELETE
Use similarly structured functions with proper payloads or query parameters.

---

## 📌 Notes

- `$headers` can be used to pass auth tokens or custom headers.
- All functions return decoded JSON responses.
- Exceptions are thrown on `cURL` errors.
