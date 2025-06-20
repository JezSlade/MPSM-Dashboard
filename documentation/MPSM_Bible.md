# MPSM_Bible.md

## Core Philosophies
- **One patch per reply**, functionally cohesive.
- **Full inline code** in every change—no partial snippets.
- **Templating system** for endpoints and cards:
  - API: `$method/$path` + `requires api_bootstrap.php`.
  - Cards: `requires card_bootstrap.php`.
- **Manual `.env` parsing** only in bootstraps; no hidden dependencies.
- **Relative paths** exclusively via `__DIR__`.
- **No external PHP libraries**; lightweight, self-contained PHP.

## Folder Doctrine
```
/api/        → Endpoint stubs using template
/public/css/ → Global styling
/cards/      → UI modules (card_*.php)
/components/ → Shared UI widgets & modals
/includes/   → Bootstraps & helpers
/views/      → Layout templates
/engine/     → Caching logic
/cron/       → Scheduled tasks
/logs/       → Debug logs
```

## Required Behaviors
1. **Customer Context**:
   - Default `CustomerCode` from `.env` → cookie → GET.
   - Persist selection in cookie.
2. **Visibility Preferences**:
   - Cookie `visible_cards` controls which `card_*.php` render.
   - Defaults to all cards if cookie not set.
3. **Cache Warming**:
   - Cron warms Redis cache via `cache_engine.php`.
4. **Debug Logging**:
   - All errors write to `/logs/debug.log`.
5. **Complete Code Delivery**:
   - Every change shipped with full-file context.
