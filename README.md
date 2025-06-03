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
  setup_admin.php
  /src
    EnvLoader.php
    Db.php
    Auth.php
    DebugLogger.php
    ApiClient.php
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

   CLIENT_ID=…          # from MPSM
   CLIENT_SECRET=…      # from MPSM
   USERNAME=…           # MPSM username
   PASSWORD=…           # MPSM password
   DEALER_CODE=SZ13qRwU5GtFLj0i_CbEgQ2

   MPSM_BASE_URL=https://api.abassetmanagement.com/api3

   ADMIN_USER=admin
   ADMIN_PASS=supersecret
   ```

2. **Seed the Admin User.**  
   From CLI or via browser, run:
   ```
   php setup_admin.php
   ```

3. **Ensure `storage/` is writable.**  
   ```
   chmod -R 775 storage
   ```

4. **Point your web server’s document root** to the project root.  
   - e.g. if using Apache:
     ```
     DocumentRoot /path/to/project/root
     ```
   - Ensure PHP is enabled.

5. **Visit** `/login.php`.  
   - Log in using `ADMIN_USER` / `ADMIN_PASS`.  
   - On success, you’ll be redirected to `/index.php`.

6. **You should see:**  
   - The “Blank Module Loaded” box (verifying module wiring).  
   - A “Select Customer” dropdown (pulling live data from MPSM).  
   - Once you pick a customer, the Device List table appears, complete with pagination, sorting, and column toggles.  
   - Clicking an SEID cell opens the drilldown in an embossed-glass panel.  
   - The debug panel at bottom auto-refreshes every 5 seconds.

## Adding/Modifying Modules

1. **Create PHP Endpoint**  
   - `modules/MyModule/MyModule.php`  
   - Call `Auth::checkLogin()` at top to secure it.  
   - Use `ApiClient` or custom logic.  
   - Return JSON or HTML fragment.

2. **Create Vue Component**  
   - `modules/MyModule/MyModule.js`  
   - Call `app.component('my-module', {...})`.  
   - Use `<my-module></my-module>` in `index.php` or inside another component.

3. **Styling**  
   - If you need module-specific CSS, create `modules/MyModule/MyModule.css` and import it from `index.php` or `app.js`.  
   - Otherwise, rely on global theme in `style.css`.

4. **Debug Logging**  
   - In PHP: `DebugLogger::log('message');`  
   - In JS: `console.log('…')` or send AJAX to a PHP debug endpoint if needed.

5. **Permissions & Roles**  
   - Right now, only “is_admin” from `users` table matters.  
   - Future expansions may add role tables and module-level permissions in `Auth.php`.

## Future Roadmap

1. **Role & Permission System**  
   - Build a “Sysop” UI under `/modules/Sysop` to manage roles & permissions.  
   - Extend `Auth.php` to check `user_roles` and `role_permissions`.

2. **Additional Modules**  
   - Supply Alerts (`modules/SupplyAlert`)  
   - Counters (`modules/Counters`)  
   - Customer Dashboard Summaries (`modules/CustomerSummary`)

3. **User Settings & Preferences**  
   - Persist table column visibility, page size, etc. in a `settings` table.  
   - Read & apply on module mount.

4. **UI Polish**  
   - Make the dashboard responsive.  
   - Add animations to glass panels, neon highlights.  
   - Polish typography & spacing.

5. **Documentation & Tests**  
   - Write unit tests for `ApiClient`, `DebugLogger`, `Auth`.  
   - Document any non-obvious choices.

6. **Deployment & CI**  
   - Set up a script to deploy onto staging, run automated checks (lint CSS/JS, run a smoke test on key endpoints).

— **Dashboard v1.0** (2025-06-03)
