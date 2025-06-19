# Architecture

## Overview
MPS Monitor Dashboard is a modular PHP SPA with glassmorphic/neumorphic UI. It uses a lightweight templating approach:

- **Front Controller** (`index.php`) routes to views or API stubs.
- **API Stubs** (`/api/*.php`) define `$method`, `$path`, optional `$useCache`, then include `api_bootstrap.php`.
- **Cards** (`/cards/card_*.php`) include `card_bootstrap.php` to fetch data and render HTML.
- **Views** combine header, navigation, preferences modal, and card includes.

## Folder Structure
```
/api/                   # API endpoint stubs
/cards/                 # UI card modules
/components/            # Shared UI (preferences-modal.php, debug-log.php)
/includes/              # Helpers & bootstraps (api_bootstrap.php, card_bootstrap.php, api_functions.php, searchable_dropdown.php, table_helper.php)
/views/                 # Page templates (dashboard.php)
/public/css/            # Stylesheet (styles.css)
/logs/                  # Debug log (debug.log)
/documentation/         # Markdown docs (Architecture.md, CONTRIBUTING.md, MPSM_Bible.md, Handoff_Summary.md)
.env                    # Environment config
```

## New Helpers
- **Searchable Dropdown**: `/includes/searchable_dropdown.php`
- **Data Table Helper**: `/includes/table_helper.php`

## Key Components
- **Preferences Modal**: `/components/preferences-modal.php`
- **Debug Log**: `/components/debug-log.php`
- **Theme Toggle**: in `/includes/header.php` using Feather Icons and localStorage

## Style & Theming
- Tailwind CSS for utility-first styling.
- Dark mode default, toggleable.
- Neumorphic panels, glass blur effects.

