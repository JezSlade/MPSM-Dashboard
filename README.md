# MPSM Dashboard

Verified against the working tree on 2026-05-20.

## What This Is

MPSM Dashboard is a PHP/MySQL dashboard and proxy layer for MPS Monitor data. The live application is served from `cms/`, calls the local `mps-api/` proxy for vendor API access, and stores operational data in MySQL plus filesystem caches.

Main capabilities present in this repository:

- CMS dashboard at `cms/index.php` with login at `cms/login.html`.
- Legacy CMS endpoints under `cms/api/*.php`.
- REST v1 gateway at `cms/api/v1/index.php`.
- MPS Monitor proxy, endpoint catalog, webhook intake, and file cache under `mps-api/`.
- Procedural CMS helpers in `cms/functions.php`.
- Newer service-layer classes under `src/` for routing, controllers, repositories, middleware, cache drivers, auth, jobs, and queues.
- Discovery/reference scripts under `scripts/`.
- Operational diagnostics under `tools/`, tests under `tests/`, SQL under `database/`, and curated docs under `docs/`.

## Requirements

- PHP with PDO and cURL extensions.
- MySQL or compatible database.
- A web server that serves the repository root or `cms/` and `mps-api/` paths.
- Optional Redis if `CACHE_DRIVER=redis` is used.

There is no active Composer, npm, Vite, or PHPUnit manifest in the current working tree.

## Configuration

Runtime secrets are expected in `.env` or server environment variables. Start from [.env.example](.env.example).

Important files:

- [config/app.php](config/app.php) is the repository-managed application config used by `bootstrap.php` and `src/`.
- [config/env.php](config/env.php) loads `.env` values when process environment variables are not already set.
- `cms/config.php` is a local deployment config for legacy CMS files and is intentionally ignored by git.
- [cms/config.php.example](cms/config.php.example) is the legacy config template.
- [mps-api/.env.example](mps-api/.env.example) documents the proxy-specific environment variables.

Do not commit `.env`, `cms/config.php`, runtime logs, cache storage, or FTP sync state.

## Application Layout

```text
.
├── bootstrap.php              # Service container bootstrap for src/ and REST v1
├── config/
│   ├── app.php                # Repository-managed config
│   └── env.php                # Lightweight .env loader
├── cms/
│   ├── index.php              # Main dashboard
│   ├── login.html             # Login page
│   ├── functions.php          # Legacy CMS helper functions
│   ├── api/                   # Legacy endpoints and REST v1 gateway
│   └── assets/                # Dashboard JavaScript and CSS
├── mps-api/
│   ├── index.php              # Vendor API proxy entry point
│   ├── engine.php             # MPS API engine
│   ├── callbacks/             # Panel message webhook handlers
│   └── cache/                 # Action cache implementation
├── src/
│   ├── Auth/
│   ├── Cache/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Queue/
│   └── Repositories/
├── scripts/                   # API discovery, checks, backup, and deployment helpers
│   └── shell/                 # Shell probes and population helpers
├── tools/
│   ├── command-center/        # One-off command-center maintenance tools
│   └── diagnostics/           # Local diagnostic PHP tools
├── tests/
│   ├── php/
│   ├── shell/
│   └── sql/
├── database/
│   ├── migrations/
│   ├── queries/
│   └── rollback/
├── context/                   # Historical project notes and operating memory
├── docs/                      # Curated documentation, reports, guides, and ADRs
└── reference/                 # Local vendor/reference exports ignored by git
```

## Key HTTP Surfaces

Legacy CMS endpoints:

```text
POST /cms/api/login.php
GET  /cms/api/get-devices.php
GET  /cms/api/get-device-deep-dive.php
GET  /cms/api/get-panel-messages.php
GET  /cms/api/get-dealer-summary.php
GET  /cms/api/system-health.php
```

REST v1 endpoints registered in [cms/api/v1/index.php](cms/api/v1/index.php):

```text
GET  /cms/api/v1/health
GET  /cms/api/v1/devices
GET  /cms/api/v1/devices/stats
GET  /cms/api/v1/devices/:serial
GET  /cms/api/v1/devices/:serial/drilldown
POST /cms/api/v1/devices/search
POST /cms/api/v1/devices/cache
GET  /cms/api/v1/panel-messages
GET  /cms/api/v1/panel-messages/stats
GET  /cms/api/v1/panel-messages/device/:serial
POST /cms/api/v1/panel-messages
```

MPS API proxy and callbacks:

```text
GET/POST /mps-api/index.php
POST     /mps-api/callbacks/panel-message.php
POST     /mps-api/callbacks/panel-message-debug.php
```

## Operations

Common CLI commands when PHP is available:

```bash
php worker.php --stats
php worker.php --cleanup
php worker.php --retry-failed
php scripts/test_auth.php
python3 scripts/run_discovery.py
python3 scripts/run_checks.py
python3 scripts/ftp_backup.py
python3 scripts/ftp_deploy.py --delete
python3 scripts/live_smoke.py
```

## Documentation

Start here:

- [docs/INDEX.md](docs/INDEX.md) is the current documentation index.
- [docs/REPOSITORY_AUDIT.md](docs/REPOSITORY_AUDIT.md) records the Markdown audit and code/doc mismatches found during cleanup.
- [cms/README.md](cms/README.md) documents the CMS layer.
- [context/README.md](context/README.md) explains how to treat the historical context notes.
- [docs/adr/README.md](docs/adr/README.md) lists architectural decision records.

Historical incident reports, deployment notes, and dated status files live under `docs/reports/`, `docs/status/`, and `docs/operations/`. Verify them against current code before using them as implementation guidance.
