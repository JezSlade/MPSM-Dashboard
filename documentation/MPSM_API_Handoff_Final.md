
# MPSM Dashboard – Final Developer Handoff & API Forensics (Printer View Edition)

## 🧠 Jez's Preferences from This Project (Confirmed from Full Thread)

### 💡 Design & UX
- Default dark theme with glassmorphism and CMYK-style glow
- Neumorphic card components
- Mobile-first responsive layout
- UX must stay clean, minimal, and deeply visual
- Tailwind optional, no JS frameworks

### 🧱 Architecture Overview
- `/api/*.php` – Each file performs one API call:
  - Self-contained
  - Loads `.env` directly
  - Performs token logic and POST call
  - Returns clean `application/json`
- `/cards/*.php` – Each card:
  - Loads its own data (via `$_GET`)
  - Renders its own HTML
  - Designed to be included from dashboard
  - View-agnostic and fully reusable
- `/views/dashboard.php`
  - Responsible only for including cards
  - Passes parameters like `?customer=...` via URL
- `/includes/navigation.php`
  - Generates customer dropdown
  - Submits to same page using `GET`

---

## 🔩 Printer Card Behavior (printer-card.php)

### 🔗 Accepts:
- `$_GET['customer']` – The customer Code (NOT Id)
- `$_GET['dashboard']` – Optional, used for tagging context
- `$_GET['page']` – Optional, for pagination

### 🔍 Function:
- Calls `/api/get_devices.php?customer=...`
- Parses full list of devices
- Extracts all keys from first page for header columns
- Displays devices in a single table with full field visibility
- Implements pagination (`15 per page`)

---

## 🧩 Issues Encountered & Solutions

### 1. ❌ Customer dropdown used `Id` instead of `Code`
- ✅ Fixed to use `Code` in `<option value=...>` so devices match

### 2. ❌ API payload to `/Device/List` was incorrect
- ✅ Fixed: uses `FilterDealerId` and `FilterCustomerCodes[]` per SDK

### 3. ❌ Each device rendered in its own card
- ✅ Fixed: dashboard now includes printer-card.php ONCE
- ✅ Card queries and displays all devices inside a single table

### 4. ❌ Pagination missing
- ✅ Fixed: `$_GET['page']` controls page #
- ✅ Card uses `array_slice()` to show 15 devices per page
- ✅ Next / Prev links auto-rendered at bottom of card

### 5. ❌ Misleading error from navigation.php
- ✅ Root cause: file existed but PHP cache failed to reload
- ✅ Resolved via hard refresh (not a persistent code issue)

---

## ✅ Dev Instructions – Build a Card the Right Way

1. **Set up the API call in `/api/`**
```php
$payload = [
  'FilterDealerId' => $env['DEALER_ID'],
  'FilterCustomerCodes' => [$customerCode],
  'Status' => 1,
  'PageNumber' => 1,
  'PageRows' => 2147483647,
  'SortColumn' => 'Id',
  'SortOrder' => 0
];
```

2. **Create `/cards/my-card.php`**
```php
$customerCode = $_GET['customer'] ?? null;
$response = file_get_contents(...);
$data = json_decode(...);
foreach ($data['Result'] as $item) { echo ... }
```

3. **From `/views/dashboard.php`, just call:**
```php
include __DIR__ . '/../cards/my-card.php';
```

4. **Add `?customer=CODE` in nav or URL**
```php
<select name="customer" onchange="this.form.submit()">
<option value="<?= $cust['Code'] ?>">...</option>
```

5. **Style with `.device-card`, `.device-table`, `.pagination-nav` classes**

---

## 🧠 Component Interactions

| File                        | Role                                     | Notes                                 |
|-----------------------------|------------------------------------------|---------------------------------------|
| `.env`                      | Stores secrets                           | Loaded directly in all `/api/` files  |
| `get_customers.php`         | Lists customers                          | Used by `navigation.php`              |
| `get_devices.php`           | Lists devices for customer               | Used by `printer-card.php`            |
| `printer-card.php`          | Table card for all devices               | Fully dynamic and paginated           |
| `dashboard.php`             | View manager                             | Includes cards only, no logic         |
| `navigation.php`            | Customer dropdown                        | Posts `?customer=...` via GET         |

---

## ✅ Summary

This handoff ensures any developer can:
- Rebuild the full printer table card from scratch
- Extend pagination, sorting, filtering as needed
- Drop in additional cards following the same structure
- Avoid past pitfalls (like using wrong customer identifier or over-including cards)

