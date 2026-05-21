# Data Model & Storage

> Defined across: `cms/functions.php`, `mps-api/callbacks/*.php`, `cms/api/refresh-cache-chunked.php`, `cms/api/refresh-cache-enhanced.php`, `PANEL_MESSAGES.md`, `BACKGROUND_REFRESH_SYSTEM.md`

## Database Overview

- **Engine:** MySQL 5.7+ (PDO connection in `cms/functions.php`).
- **Schema:** `resolut7_mpsm` (per `cms/config.php`).
- **Prefix:** `mpsm_` — applied to every table created by CMS utilities and callbacks.
- Tables auto-create on first use; no migrations required.

## Core Tables

### `mpsm_users`
Created by `initializeTables()` (`cms/functions.php` lines 200-219).

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | INT AUTO_INCREMENT | Primary key |
| `username` | VARCHAR(100) | Unique index `idx_username` |
| `password` | VARCHAR(255) | Bcrypt hash via `password_hash()` |
| `created_at` | TIMESTAMP | Defaults to `CURRENT_TIMESTAMP` |

Seeded with `admin/admin` if empty.

### `mpsm_user_preferences`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | INT AUTO_INCREMENT | Primary key |
| `user_id` | INT | Unique key `unique_user`, foreign key to `mpsm_users` |
| `preferences` | TEXT | JSON-encoded preferences (theme, cards, etc.) |
| `updated_at` | TIMESTAMP | Auto-updated |

### `mpsm_visitor_log`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | INT AUTO_INCREMENT | Primary key |
| `user_id` | INT NULL | Optional FK to `mpsm_users` |
| `username` | VARCHAR(100) | Snapshot for analytics |
| `ip_address` | VARCHAR(45) | IPv4/IPv6 |
| `user_agent` | TEXT | Raw UA string |
| `page_url` | TEXT | Visited path |
| `visited_at` | TIMESTAMP | Default `CURRENT_TIMESTAMP` |

Indexes: `idx_user_id`, `idx_visited_at`.

## Panel Message Tables

### `mpsm_panel_messages`
Created in both callback scripts (`mps-api/callbacks/panel-message.php` lines 64-92; debug variant identical).

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | INT AUTO_INCREMENT | Primary key |
| `received_at` | TIMESTAMP | Defaults to current timestamp |
| `customer_code` | VARCHAR(100) | Optional; indexed |
| `customer_description` | VARCHAR(255) | Optional |
| `device_serial` | VARCHAR(150) | Optional; indexed |
| `maintenance_alert_code` | VARCHAR(150) | Optional |
| `maintenance_alert_id` | VARCHAR(150) | Optional |
| `panel_configuration` | VARCHAR(255) | Optional |
| `source_ip` | VARCHAR(45) | Request origin |
| `payload` | JSON | Full payload as received |
| `processed` | TINYINT(1) | Defaults to 0; index `idx_processed` |

Used by `cms/api/get-panel-messages.php` and the device deep dive.

### `mpsm_panel_callback_debug`
Created in debug callback and API (`panel-message-debug.php` lines 118-150; `get-payload-debug-logs.php` lines 28-56).

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | INT AUTO_INCREMENT | Primary key |
| `timestamp` | DATETIME | When request arrived |
| `ip_address` | VARCHAR(45) | Source IP |
| `http_method` | VARCHAR(10) | Expected `POST` |
| `content_type` | VARCHAR(255) | Captured header |
| `user_agent` | VARCHAR(500) | Captured header |
| `headers` | JSON NULL | All `HTTP_*` headers |
| `raw_body` | TEXT NULL | Raw request body (truncated) |
| `status` | VARCHAR(20) | PROCESSING/SUCCESS/ERROR |
| `message` | VARCHAR(500) | Error or success log |
| `http_code` | INT NULL | Response code |

Indexes: `idx_timestamp`, `idx_ip_address`, `idx_status`.

## Cache Tables

The live cache tables are read by the dashboard and cache-backed endpoints. The current production refresh path is `cms/api/refresh-cache-chunked.php`, which populates staging tables first and cuts over only after a successful run. `cms/api/refresh-cache-enhanced.php` remains in the repository as a historical/legacy full-refresh implementation and should not be used as the default live path.

### `mpsm_cache_devices`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | INT AUTO_INCREMENT | Primary key |
| `serial_number` | VARCHAR(150) | Unique key (`UNIQUE KEY serial_unique`) |
| `device_data` | JSON | Raw device object from `Device/List` |
| `customer_code` | VARCHAR(100) | For filtering |
| `is_uninstalled` | TINYINT(1) | 1 if from `Device/Deleted/List` |
| `cached_at` | TIMESTAMP | Last refresh time |

Indexes: `idx_customer`, `idx_uninstalled`, `idx_cached`.

### `mpsm_cache_device_drilldown`

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | INT AUTO_INCREMENT | Primary key |
| `serial_number` | VARCHAR(150) | Unique key |
| `drilldown_data` | JSON | Full payload from `Device/Get` |
| `has_alerts` | TINYINT(1) | Quick filter |
| `has_supplies` | TINYINT(1) | Quick filter |
| `cached_at` | TIMESTAMP | Last refresh |

Indexes: `idx_serial`, `idx_alerts`, `idx_cached`.

### Chunked Refresh Staging Tables

`refresh-cache-chunked.php` creates staging tables with the same logical shape as `mpsm_cache_devices` and `mpsm_cache_device_drilldown`, then uses the checkpoint/status flow to determine whether those staged rows are safe to promote. During the 2026-05-20 documentation pass, `action=status` reported 3,370 staged device rows, 300 staged drilldown rows, and 0 errors.

## File-Based Cache

- Stored under `cms/api/cache/` via `cacheStore()` and `cacheGet()` for legacy endpoints.
- Cache files contain `{data, cached_at, expires_at, ttl}` JSON structure.
- Cache stats surfaced via `getCacheStats()` and system health diagnostics.

## Logging Artifacts

- **Panel Message Logs:** `mps-api/logs/panel-message-YYYY-MM-DD.log`.
- **Cache Refresh Logs:** `cms/logs/cache-refresh-YYYY-MM-DD.log`.
- **PHP Error Logs:** `cms/logs/php_errors.log` and `mps-api/logs/php_errors_YYYY-MM-DD.log`.
- **Battle Test Output:** `battle_test_results.txt`.

## Data Retention Notes

- Panel message history currently unbounded; plan to archive or prune after 90 days.
- Cache tables refreshed every run; `cached_at` can be used to detect stale entries.
- Debug table (`mpsm_panel_callback_debug`) will grow quickly if left enabled; consider periodic cleanup via `DELETE WHERE timestamp < NOW() - INTERVAL 30 DAY`.

## Access Patterns

- Read-heavy via `get-device-deep-dive.php`, `get-panel-messages.php`, `get-cached-devices.php`.
- Write-heavy only during cache refresh or callback spikes.
- All writes use parameterised queries with `PDO::prepare()` to avoid SQL injection.
