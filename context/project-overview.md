# Project Overview

**Live CMS:** `https://mpsm.resolutionsbydesign.us/cms/`  
**Primary Repos:** `cms/` (presentation + admin UI), `mps-api/` (OAuth + upstream API proxy)  
**Reference Docs:** `README.md`, `DOCUMENTATION.md`, `PANEL_MESSAGES.md`, `PAYLOAD_DEBUGGER_GUIDE.md`

## Core Mission

Deliver a web dashboard that surfaces dealer-wide device telemetry from MPS Monitor with instant response times, real-time alerting, and auditable diagnostics. The platform now bundles:

- **Dashboard UI:** Rich cards, device drill-down, search, alert management (`cms/index.php`, `cms/assets/app.js`).
- **Command Center:** Panel message monitor plus payload debugger (pending UI tab integration) (`cms/panel-message-monitor.php`, `cms/payload-debugger.php`).
- **Background Cache:** Database-backed caches for devices and drill-down payloads (`cms/api/refresh-cache-enhanced.php`).
- **API Engine:** Hardened proxy and automation layer for the official MPS Monitor API (`mps-api/index.php`, `mps-api/engine.php`).
- **Panel Message Intake:** Live webhook endpoint with full persistence and UI visualization (`mps-api/callbacks/panel-message.php`, `cms/api/get-panel-messages.php`).
- **Mobile Landing:** Dedicated phone-first entry at `/cms/mobile.php` with quick alerts, device lookup, lifecycle access, and links back to admin/dev tools.

## Non-Negotiable Standards

- **Engineering Constitution:** 35 rules enforced across the codebase (`docs/CONSTITUTION.md`, `docs/ONBOARDING.md`).
- **Separation of Concerns:** Browser ➝ CMS PHP ➝ `mps-api` ➝ MPS Monitor. The frontend never talks directly to `mps-api` or the vendor API (see ADR-0001).
- **Visible Failures:** All PHP endpoints surface explicit JSON errors via `jsonError()` (`cms/functions.php`).
- **Procedural PHP + Vanilla JS:** No frameworks, no composer, no npm.
- **Database Auto-Bootstrapping:** Tables created on demand through `initializeTables()` and callback scripts.

## Current Capabilities (November 5, 2025)

| Area | Status | Notes |
| ---- | ------ | ----- |
| Authentication | ✅ | Session cookies with SameSite Lax + HTTPS detection (`cms/config.php` lines 41-83). |
| Device Search | ✅ | Global search caches all pages client-side; fallbacks documented in ADR-0005. |
| Deep Dive Modal | ✅ | Aggregates Device/List + Counter/ListDetailed + SDS actions + Supply alerts + Panel history (`cms/api/get-device-deep-dive.php`). |
| Panel Messages | ✅ | 10+ messages stored; monitor UI auto-refreshes every 30 s (`PANEL_MESSAGES.md`). |
| Payload Debugger | ✅ (standalone) | `/cms/payload-debugger.php` provides auto-refreshing log viewer. Embed into command center is pending. |
| Background Cache | ⚠ Pending data | `cms/api/refresh-cache-enhanced.php` runs but returned `devices_cached = 0` because upstream API is returning no devices (see `IMMEDIATE_ACTION_ITEMS.md`). Dealer code `NY06AGDWUQ` confirmed in `.env` and `cms/config.php`. |
| Battle Test Suite | ✅ | `battle_test.html` exercises live endpoints as per `AUDIT_REPORT.md`. |

## High-Value Initiatives In Flight

1. **Cache Population:** Confirm `refresh-cache-enhanced.php` succeeds against live API, then wire CMS endpoints to database cache.
2. **Command Center Integration:** Add payload debugger tab/iframe inside `cms/panel-message-monitor.php`.
3. **Panel Message UI Enhancements:** Add modal tab to visualize `panelHistory` from `get-device-deep-dive.php`.

## External Dependencies

- **MPS Monitor API:** `https://api.abassetmanagement.com/api3/` (username/password OAuth via `mps-api/config.php`).
- **Database:** MySQL 5.7+ with schema `resolut7_mpsm` (`cms/config.php`).
- **Scheduled Jobs:** Expected to run `cms/api/refresh-cache-enhanced.php` via cron or Windows Task Scheduler every 5 minutes (not yet verified on server).
- **Chrome Dev Overlay:** Optional debugging extension (`dev-overlay-extension/`).

## Quick Orientation Checklist

1. Sign in at `/cms/login.html` (default credentials `admin/admin`, auto-created if table empty).
2. Visit the command center (`/cms/panel-message-monitor.php`) and payload debugger (`/cms/payload-debugger.php`) to confirm live logging.
3. Run `refresh-cache-enhanced.php` manually and inspect `cms/logs/cache-refresh-*.log`.
4. Review `PAYLOAD_DEBUGGER_GUIDE.md` and `PANEL_INTEGRATION_SUMMARY.md` for the exact payload contract sent to us by MPS Monitor.
5. Keep `IMMEDIATE_ACTION_ITEMS.md` open for the short list of unresolved follow-ups.
