# MPSM Dev Overlay Extension

Developer-only Chrome extension that overlays a real-time HUD on top of `mpsm.resolutionsbydesign.us` and captures everything useful: network calls, console output, DOM mutations, storage churn, navigation events, WebSocket chatter, and beacon pings.

## Features

- **Network tap** - wraps `fetch`, `XMLHttpRequest`, `sendBeacon`, and WebSockets, surfacing method/stage, URL, status, payload snippets, and duration.
- **Console mirror** - captures `console.log/info/warn/error`, global errors, and unhandled rejections with stack traces.
- **DOM radar** - MutationObserver summarises node additions/removals and attribute flips (id/class/style/data-*).
- **Storage diff** - intercepts `localStorage`/`sessionStorage` writes and clears for instant visibility.
- **Navigation trail** - records push/replace state, hash changes, popstate, unloads, and page load entry.
- **HUD overlay** - dockable panel (toggle from action popup or on-page controls) with pause/resume capture, filter chips, search, expandable payloads, per-event copy, bookmarks, highlight mode, and badge counts.
- **Export friendly** - one-click NDJSON export for sharing or replay tooling (UTF-8 safe).

## Installation

1. Open Chrome and visit `chrome://extensions`.
2. Enable **Developer mode** (top-right).
3. Click **Load unpacked**; select `dev-overlay-extension/`.
4. Navigate to the sandbox site (`https://mpsm.resolutionsbydesign.us/...`).
5. Click the extension action:
   - **Show HUD** to pin the overlay.
   - **Refresh** to pull the current session buffer from the background worker.
   - **Clear** to zero the timeline (overlay + background).
   - **Export NDJSON** to download the captured log for analysis.

## Development Notes

- Manifest V3 (service worker background).
- Content script runs at `document_start` to wrap fetch/XHR/WebSocket/sendBeacon before the app bootstraps.
- Event buffer retained per tab in-memory inside `background.js`; badge text reflects size.
- Export bundles the buffer as UTF-8 NDJSON, base64-encodes with `TextEncoder`, and streams back to popup/HUD for download.
- No project source modifications required; the extension monkey-patches runtime APIs inside the page context.

Press `Clear` before collecting a new scenario, keep HUD hidden if you just need recording, and export often if you expect to exceed 500 queued events. Further enhancements (playback, annotations, custom emitters) can layer on top without touching the core dashboard.

## HUD Controls

- **Capture** toggles live recording (stops auto ingest while keeping UI usable).
- **Auto Follow** keeps the inspector latched to the newest event.
- **Highlight** turns on crosshair mode; click any element to log its structure.
- **Copy Selection / Bookmark** provide quick clipboard export or a pinned breadcrumb in the timeline.
- **Export (Filters) / Export All** let you dump just the view you are studying or the entire session log in NDJSON.
- Timeline filter chips + search box narrow scope instantly; sections can be collapsed or copied individually.
