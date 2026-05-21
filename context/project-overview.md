# Project Overview

**Live CMS:** `https://mpsm.resolutionsbydesign.us/cms/`  
**Primary Repos:** `cms/` (presentation + admin UI), `mps-api/` (OAuth + upstream API proxy)  
**Reference Docs:** `README.md`, `docs/INDEX.md`, `context/current-state.md`, `docs/operations/DEPLOY-INSTRUCTIONS.md`

## Core Mission

Deliver a web dashboard that surfaces dealer-wide device telemetry from MPS Monitor with instant response times, real-time alerting, and auditable diagnostics. The platform now bundles:

- **Dashboard UI:** Rich cards, device drill-down, search, alert management (`cms/index.php`, `cms/assets/app.js`).
- **Command Center:** Panel message monitor plus payload debugger (pending UI tab integration) (`cms/command-center.php?tab=panel`, `cms/payload-debugger.php`).
- **Background Cache:** Database-backed live cache with guarded staging/cutover via `cms/api/refresh-cache-chunked.php`; `refresh-cache-enhanced.php` remains in the tree but timed out during a live full warmup.
- **API Engine:** Hardened proxy and automation layer for the official MPS Monitor API (`mps-api/index.php`, `mps-api/engine.php`).
- **Panel Message Intake:** Live webhook endpoint with full persistence and UI visualization (`mps-api/callbacks/panel-message.php`, `cms/api/get-panel-messages.php`).
- **Mobile Landing:** Dedicated phone-first entry at `/cms/mobile.php` with quick alerts, device lookup, lifecycle access, and links back to admin/dev tools.

## Non-Negotiable Standards

- **Engineering Constitution:** 35 rules enforced across the codebase (`docs/CONSTITUTION.md`, `docs/ONBOARDING.md`).
- **Separation of Concerns:** Browser ➝ CMS PHP ➝ `mps-api` ➝ MPS Monitor. The frontend never talks directly to `mps-api` or the vendor API (see ADR-0001).
- **Visible Failures:** All PHP endpoints surface explicit JSON errors via `jsonError()` (`cms/functions.php`).
- **Procedural PHP + Vanilla JS:** No frameworks, no composer, no npm.
- **Database Auto-Bootstrapping:** Tables created on demand through `initializeTables()` and callback scripts.

## Current Capabilities (Verified 2026-05-20)

| Area | Status | Notes |
| ---- | ------ | ----- |
| Authentication | ✅ | Session cookies with SameSite Lax + HTTPS detection (`cms/config.php` lines 41-83). |
| Device Search | ✅ | Global search caches all pages client-side; fallbacks documented in ADR-0005. |
| Deep Dive Modal | ✅ | Aggregates Device/List + Counter/ListDetailed + SDS actions + Supply alerts + Panel history (`cms/api/get-device-deep-dive.php`). |
| Panel Messages | ✅ | 70k+ callback records present on live status report; monitor UI auto-refreshes every 30s. |
| Payload Debugger | ✅ (standalone) | `/cms/payload-debugger.php` provides auto-refreshing log viewer. Embed into command center is pending. |
| Background Cache | ⚠ In progress | Live cache report has 3351 devices and 1425 drill-downs from previous live tables. A new chunked run is staging 3370 devices and 300 drill-downs with 0 errors; live cache will update only after guarded cutover. |
| Portable Operations | ✅ | `scripts/run_checks.py`, `ftp_backup.py`, `ftp_deploy.py`, and `live_smoke.py` replace PowerShell deploy/test flows. |

## High-Value Initiatives In Flight

1. **Cache Population:** Let `refresh-cache-chunked.php` finish drill-down staging, then call guarded cutover and verify final live counts.
2. **Command Center Integration:** Add payload debugger tab/iframe inside `cms/command-center.php?tab=panel`.
3. **Panel Message UI Enhancements:** Add modal tab to visualize `panelHistory` from `get-device-deep-dive.php`.

## External Dependencies

- **MPS Monitor API:** `https://api.abassetmanagement.com/api3/` (username/password OAuth via `mps-api/config.php`).
- **Database:** MySQL 5.7+ with schema `resolut7_mpsm` (`cms/config.php`).
- **Deployment:** Direct FTP via portable Python scripts. This working tree has no configured git remote or `.github/workflows/deploy.yml`.
- **Scheduled Jobs:** Chunked cache processing may be triggered by cPanel cron or manual `action=process` calls. Verify live state through `refresh-cache-chunked.php?action=status`.
- **Chrome Dev Overlay:** Optional debugging extension (`dev-overlay-extension/`).

## Quick Orientation Checklist

1. Sign in at `/cms/login.html` (default credentials `admin/admin`, auto-created if table empty).
2. Visit the command center (`/cms/command-center.php?tab=panel`) and payload debugger (`/cms/payload-debugger.php`) to confirm live logging.
3. Run `python3 scripts/run_checks.py` before deployment and `python3 scripts/live_smoke.py` after deployment.
4. Check `refresh-cache-chunked.php?action=status` before touching cache state.
5. Treat older context reports as historical unless current files and live endpoints confirm them.
