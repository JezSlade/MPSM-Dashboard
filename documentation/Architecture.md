# Architecture.md

## Overview
This project is a modular, glassmorphic Single Page Application (SPA) built in PHP with strict self-contained API endpoints. Each "card" is an independent UI module that fetches its own data and renders inside a parent view (e.g., `dashboard.php`).

## Folder Structure
```
/api/                   # Standalone PHP API endpoint scripts
/assets/                # Global styles (e.g., styles.css)
/cards/                 # Modular display cards (printer list, alert list, etc.)
/components/            # Shared frontend includes (e.g., drilldown modal)
/includes/              # Configuration loader, .env parser
/views/                 # Page views (e.g., dashboard.php)
/logs/                  # Debug logging
```

## Rendering Flow
1. `index.php` uses `render_view()` to display `/views/[view].php`.
2. Views dynamically scan `/cards/` for card files and render them using visibility preferences.
3. Each card is fully self-contained and uses internal `file_get_contents()` to retrieve data via `/api/` calls.
4. Drill-down modals and UI events are handled using minimal JS and shared UI components.
5. Preferences (such as card visibility or pagination limits) are persisted using cookies or localStorage.

## UI Architecture
- Responsive glassmorphic + neumorphic layout.
- Light and dark themes supported.
- All layouts are grid-based with full-width containers.

## Global Guardrails
- Never use absolute includes — always use `__DIR__`.
- No use of Composer, PSR autoloading, or external PHP libraries.
- All `.env` parsing must be manual.
- Only one patch per reply unless patches are functionally dependent.
