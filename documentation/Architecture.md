# Architecture.md

## Overview
This project is a modular, glassmorphic Single Page Application (SPA) built in PHP. It follows a lightweight templating system:

- **API Endpoints** (`/api/*.php`) set:
  ```php
  <?php
  $method   = 'POST';
  $path     = 'Device/Get';
  $useCache = true;
  require __DIR__ . '/../includes/api_bootstrap.php';
  ```
  All request logic (env parsing, OAuth, cURL, caching, JSON header) lives in `includes/api_bootstrap.php`.

- **Cards** (`/cards/card_*.php`) begin with:
  ```php
  <?php
  require __DIR__ . '/../includes/card_bootstrap.php';
  ```
  Card bootstrap handles payload assembly (cookies/GET), `call_api()`, and provides `$data` for rendering.

- **Views** (`/views/*.php`) are pure layout files that:
  1. Include helpers (`api_functions.php`).
  2. Determine customer context.
  3. Scan `/cards/` for files named `card_*.php`.
  4. Loop to include each card module inside a responsive grid.

## Folder Structure
```
/api/                   # Stub endpoints using api_bootstrap.php
/public/css/            # Global CSS (styles.css)
/cards/                 # Card modules (card_*.php) with card_bootstrap.php
/components/            # Shared UI (preferences-modal, drilldown-modal, debug-log)
/includes/              # Bootstraps & helpers (api_bootstrap.php, card_bootstrap.php, api_functions.php)
/views/                 # Page views (dashboard.php)
/engine/                # Cache engine (cache_engine.php)
/cron/                  # Scheduled tasks (cache.daily.php)
/logs/                  # Debug logs (cache_debug.log)
/documentation/         # This docset: Architecture.md, CONTRIBUTING.md, MPSM_Bible.md
/.env                   # Environment variables (CLIENT_ID, DEALER_CODE, etc.)
```

## Rendering Flow
1. **index.php**
   - Loads `includes/header.php` (HTML head, theme toggle, nav).
   - Calls `render_view('dashboard')`.

2. **dashboard.php**
   - Parses `.env` via `api_functions.php`.
   - Determines active customer (`$_GET['customer']` or cookie).
   - Fetches list of all customers to resolve display name.
   - Scans `/cards/` for `card_*.php` files.
   - Applies visibility preferences (cookie `visible_cards`).
   - Includes each selected card inside a Tailwind grid.

3. **Card Modules**
   - Bootstrapped by `includes/card_bootstrap.php`.
   - Pull payload fields (e.g. `CustomerCode`) from GET/cookie.
   - Calls `call_api($config, $method, $path, $payload)`.
   - Renders HTML table or widget with glassmorphic classes.

4. **API Bootstrapping**
   - `includes/api_bootstrap.php` handles:
     - Manual `.env` loading.
     - OAuth token request.
     - Request/response caching via Redis (`engine/cache_engine.php`).
     - cURL call to `$baseUrl . $path`.
     - Emits JSON with `header('Content-Type: application/json')`.

5. **Utilities**
   - **Cron Job** (`cron/cache.daily.php`): warms cache nightly.
   - **Components**:
     - Preferences modal (`components/preferences-modal.php`).
     - Drilldown modal (`components/drilldown-modal.php`).
     - Debug-log popup (`components/debug-log.php`).

## Styling & Theming
- Glassmorphic/neumorphic design via Tailwind CSS (CDN) + custom props.
- Dark mode by default (`<html class="dark">`).
- All global styles in `/public/css/styles.css`.

## Guardrails
- **One patch per reply**, functionally cohesive.
- **Always provide full inline code**; no snippets only.
- **Relative paths only**: use `__DIR__`.
- **No external libraries**: manual `.env` parsing in bootstraps.
- **Consistency**: CSS under `/public/css`, remove any `/assets/` references.
