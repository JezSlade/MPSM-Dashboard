# Deployment Guide

Verified: 2026-05-22.

Current deployment uses `git push origin main` with the active GitHub Actions workflow (`.github/workflows/deploy.yml`). Direct FTP scripts remain available as manual fallback/recovery.

## Prerequisites

- Python 3.
- PHP available either as `php` or through `flatpak-spawn --host php`.
- FTP credentials outside the repo, in environment variables or ignored `.runtime/ftp.env`:
  - `MPSM_FTP_HOST=ftp.resolutionsbydesign.us`
  - `MPSM_FTP_ROOT=/public_html/mpsm.resolutionsbydesign.us`
  - `MPSM_FTP_USER=...`
  - `MPSM_FTP_PASSWORD=...`

Do not commit `.env`, `cms/config.php`, `.runtime/ftp.env`, backups, cache, logs, or lock files.

## Primary Deployment (GitHub Actions)

```bash
python3 scripts/run_checks.py
git push origin main
```

Then monitor the workflow run and verify live endpoints.

## Manual FTP Deployment (Fallback)

Run from repo root:

```bash
python3 scripts/run_checks.py
python3 scripts/ftp_backup.py
python3 scripts/ftp_deploy.py --delete
python3 scripts/live_smoke.py
```

The deploy helper uploads deployable repo files and deletes stale remote files, while preserving server-managed paths:

- `.env`
- `cms/config.php`
- `cms/api/cache/`
- `cms/data/`
- `cms/locks/`
- `cms/logs/`
- `mps-api/.env`
- `mps-api/cache/storage/`
- `mps-api/logs/`

## Last Verified Deployment

- Date: 2026-05-20.
- Backup: `backups/live-site-20260520-141426`, 3659 files, 0 errors.
- Deploy: 440 files uploaded, 0 FTP errors.
- Removed stale live `cms/api/tmp-secret-bc2f7.php`; live URL returns 404.
- Live smoke passed for `/cms/login.html`, `/cms/`, `/cms/api/cache-status-report.php`, `/cms/api/v1/health`, and `/robots.txt`.
- `/mps-api/health` returned 200 and reported the upstream API connection successful.

## Post-Deploy Verification

Run:

```bash
python3 scripts/live_smoke.py
curl -sS -i "https://mpsm.resolutionsbydesign.us/mps-api/health" | sed -n '1,80p'
curl -sS "https://mpsm.resolutionsbydesign.us/cms/api/cache-status-report.php" | sed -n '1,35p'
curl -sS "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php?action=status"
```

The cache report is text, not JSON. The v1 health endpoint is JSON.

## Cache Refresh After Deploy

Do not use `refresh-cache-enhanced.php` for a full shared-host warmup. The endpoint can time out before completion.

Use the guarded chunked flow:

```bash
curl -sS "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php?action=status"
curl -sS "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php?action=process"
curl -sS "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php?action=cutover"
```

Only call `cutover` when status is `ready_for_cutover`. The script uses staging tables and verifies populated staging rows before replacing live cache tables.

Current checkpoint from 2026-05-20 16:16 America/New_York:

- status: `fetching_drilldowns`
- devices staged: `3370`
- drilldowns staged: `300`
- errors: `0`
- live cache remains on previous tables until guarded cutover succeeds.

## Rollback

Use the latest backup under `backups/` and FTP it back if a live deploy breaks critical paths. Preserve the live server `.env`, `cms/config.php`, cache/log/lock directories, and `mps-api/cache/storage/` unless the rollback specifically targets them.

## Retired Paths

- PowerShell deployment scripts were removed from active `scripts/` and `tests/`.
- `deploy.php` and `DEPLOY_SECRET` HTTP deploy flow are deprecated.
- GitHub Actions deployment is historical in this working tree. Verify a future `.github/workflows/deploy.yml` and configured remote before documenting it as active again.
