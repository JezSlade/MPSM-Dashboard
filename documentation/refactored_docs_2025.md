## MPS Monitor Dashboard - Refactored Documentation (Aug 2025)

---

### 1. SYSTEM ARCHITECTURE OVERVIEW

```
/                         → Entry Point (index.php only)
/controllers             → Route and HTTP logic (FrontController.php)
/views                   → Layout and UI wrappers
/services                → PHP backend services (WidgetService, DashboardManager)
/widgets                 → Standalone widget render files (see WIDGET_REQUIREMENTS)
/assets/css              → dashboard.css, component-based styles
/assets/js               → drag-behavior.js, dashboard-core.js
/includes                → Global bootstrap, config, token utils
```

### 2. ENTRY POINT: index.php
- Only loads `FrontController.php`
- No logic, markup, session, or routing allowed
- FrontController determines page and delegates rendering

### 3. WIDGET REQUIREMENTS
Reference: `WIDGET_REQUIREMENTS.TXT`

- All widget files must:
  - Be stored in `/widgets/`
  - Define `$_widget_config`
  - Provide a `render_{widget}_widget()` function
  - Use `<?php echo render_xxx_widget(); ?>` in final line
  - Contain both `.compact-content` and `.expanded-content`
  - Avoid `<?= ?>`, inline output, or includes

Sanitize dynamic output using `htmlspecialchars()`.

### 4. STYLING & BEHAVIOR
- CSS lives in `assets/css/dashboard.css`
- Drag/resize behavior lives in `assets/js/drag-behavior.js`
- Avoid inline `<style>` or global widget-side CSS
- Use BEM syntax for new classes

### 5. SERVICE LAYER
#### WidgetService.php
- Handles:
  - `renderAll()` — calls all widgets
  - `getWidget($id)` — single widget loader
  - Widget sorting/position lookup (from config or cookies)

#### DashboardManager.php
- Maintains layout model
- Handles client-side drag/drop state
- Caches position data (via Redis or flatfile)

### 6. CONTROLLER
#### FrontController.php
- Entry point after `index.php`
- Handles session, sets current view, and loads WidgetService
- Calls correct `views/dashboard.php`, etc.

### 7. API HANDLING
Files:
- `/api/get_customers.php`
- `/api/get_token.php`

Core logic flows:
- Each file includes `api_bootstrap.php`
- Uses `call_api()` and `get_token()` from `api_functions.php`

.env required values:
- `CLIENT_ID`, `CLIENT_SECRET`, `API_BASE_URL`, `USERNAME`, `PASSWORD`, `SCOPE`

All responses in valid JSON.

### 8. FRONTEND INTERACTION
#### Searchable Dropdown
- Calls internal endpoint via JS fetch()
- Saves selected customer via cookie
- Page reload on change to maintain stateless logic

#### Data Tables
- Created via `renderDataTable()`
- All data pre-injected into JS var
- Sort, filter, paginate all client-side

### 9. STATE MANAGEMENT
- Stateless architecture
- Persistent data in:
  - Cookies (client selected values)
  - `.cache/` files (API response cache)

Session usage permitted in widgets only (if bootstrapped).

### 10. PATCH PROTOCOL
Refactor/patch rules:
- NEVER modify `index.php` beyond delegating
- No logic inside views (call services/controllers only)
- Widget changes must maintain render structure
- All patches go through AI_Patch_Validation_Protocol.md

### 11. SECURITY (OPTIONAL)
- Tokens handled via OAuth2 (Bearer)
- Output escaped with `htmlspecialchars()`
- `.htaccess` protection for `/cache/`

> Final Note: All documentation aligns with Refactor Option [3] of Aug 2025 initiative.

