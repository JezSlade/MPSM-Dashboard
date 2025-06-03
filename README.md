# MPSM Dashboard – Modular, Dark Neumorphic, Vue 3 + PHP

## Overview
This project is built as a fully modular PHP + Vue 3 application.
- **No React.**
- **No build tools or bundlers.** Vue 3 is loaded via CDN.
- **All styling** comes from a single CSS file (`assets/css/style.css`) using dark neumorphic and neon-underglow principles.
- **Tables** are standardized via `.mpsm-table` (pagination, sorting, column-toggles baked in).
- **Each module** is isolated under `/modules/ModuleName/ModuleName.php` (backend) and `/modules/ModuleName/ModuleName.js` (frontend).

## Folder Structure
```
/             ← Project Root
  .env
  index.php
  login.php
  logout.php
  /src
    EnvLoader.php
    Db.php
    Auth.php
    DebugLogger.php
    ApiClient.php
    Installer.php
  /assets
    /css
      style.css
    /js
      app.js
  /modules
    /BlankModule
      BlankModule.php
      BlankModule.js
    /CustomerSelect
      CustomerSelect.php
      CustomerSelect.js
    /DeviceList
      DeviceList.php
      DeviceList.js
    /DeviceDrill
      DeviceDrill.php
      DeviceDrill.js
    /DebugPanel
      DebugPanel.php
      DebugPanel.js
  /storage
    debug.log
  README.md
```

## Installation & Setup

1. **Configure `.env`.**  
   ```dotenv
   DB_HOST=localhost
   DB_NAME=your_db_name
   DB_USER=your_db_user
   DB_PASS=your_db_pass

   CLIENT_ID=...
   CLIENT_SECRET=...
   USERNAME=...
   PASSWORD=...
   DEALER_CODE=SZ13qRwU5GtFLj0i_CbEgQ2

   MPSM_BASE_URL=https://api.abassetmanagement.com/api3

   ADMIN_USER=admin
   ADMIN_PASS=supersecret
   ```

2. **Ensure `storage/` is writable.**  
   ```
   chmod -R 775 storage
   ```

3. **Point your web server’s document root** to this project.  
   - E.g.: `DocumentRoot /path/to/mpsm_dashboard_updated`.

4. **Visit** `/index.php`.  
   - The installer will automatically drop and recreate the database, create `users` table, and insert `ADMIN_USER`.
   - You will then be redirected to the login page if not logged in.

5. **Log in** at `/login.php` using `ADMIN_USER` / `ADMIN_PASS`.
   - After login, you’ll see:
     - “Blank Module Loaded” box.
     - “Select Customer” dropdown.
     - Once a customer is chosen, the device list table (with pagination, sorting, column toggles).
     - Clicking an SEID will show the drilldown panel.
     - The debug panel at the bottom auto-refreshes every 5 seconds.

## Adding/Modifying Modules

1. **Create PHP Endpoint**  
   - `modules/MyModule/MyModule.php`
   - Begin with `require_once __DIR__ . '/../../src/Auth.php'; Auth::checkLogin();`
   - Use `ApiClient` or custom logic.
   - Return JSON or an HTML fragment.

2. **Create Vue Component**  
   - `modules/MyModule/MyModule.js`
   - Register with `app.component('my-module', {...})`
   - Add `<my-module></my-module>` in `index.php` or within another component.

3. **Styling**  
   - If the module needs custom CSS, create `modules/MyModule/MyModule.css` and import it in `index.php` or `app.js`.
   - Otherwise, use the global theme in `style.css`.

4. **Debug Logging**  
   - In PHP: `DebugLogger::log('message');`
   - In JS: `console.log('…')` or call a PHP debug endpoint if needed.

5. **Permissions & Roles**  
   - Initial version only uses `is_admin` from `users` table.
   - Future versions can expand `Auth.php` to support roles/permissions.

## Future Roadmap

1. **Role & Permission System**  
   - Add a “Sysop” UI in `/modules/Sysop` for role management.
   - Extend `Auth.php` to check `user_roles` and `role_permissions`.

2. **Additional Modules**  
   - Supply Alerts (`modules/SupplyAlert`)
   - Counters (`modules/Counters`)
   - Customer Dashboard Summaries (`modules/CustomerSummary`)

3. **User Settings & Preferences**  
   - Persist table column visibility, page size, etc. in a `settings` table.
   - Apply preferences on module load.

4. **UI Polish**  
   - Make dashboard responsive.
   - Add animations to glass panels, neon highlights.
   - Refine typography & spacing.

5. **Documentation & Tests**  
   - Unit tests for `ApiClient`, `DebugLogger`, `Auth`.
   - Document non-obvious design decisions.

6. **Deployment & CI**  
   - Automate deployment to staging.
   - Run lint checks for CSS/JS.
   - Smoke tests for critical endpoints.

— **Dashboard v2.0** (2025-06-03)
