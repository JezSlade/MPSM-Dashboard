# Reference Index

Use this list when you need deeper detail than the condensed wiki provides.

| Topic | Location | Why It Matters |
| ----- | -------- | -------------- |
| Constitution & Standards | `docs/CONSTITUTION.md` | 35 enforceable rules governing all code changes. |
| Onboarding Checklist | `docs/ONBOARDING.md` | Full setup instructions, prerequisites, workflow expectations. |
| Pain Points & Workarounds | `docs/PAIN_POINTS.md` | Historical issues and workarounds; verify PowerShell-era advice against current Python tooling. |
| Current State | `context/current-state.md` | Current cleanup, FTP deployment, live validation, and cache-refresh checkpoint. |
| Panel Message Integration | `PANEL_MESSAGES.md`, `PANEL_INTEGRATION_SUMMARY.md`, `PANEL_LIVE_TEST_REPORT.md` | Everything about webhook payload structure, message storage, live tests, and next steps. |
| Payload Debugger | `PAYLOAD_DEBUGGER_GUIDE.md` | Usage, database schema, test script results, and troubleshooting for the debugger. |
| Background Refresh System | `cms/api/refresh-cache-chunked.php`, `context/current-state.md` | Current guarded staging/cutover refresh path and live checkpoint. |
| Audit Report | `AUDIT_REPORT.md` | Forensic review documenting removed legacy files, fixed bugs, and battle test coverage. |
| Immediate Action List | `IMMEDIATE_ACTION_ITEMS.md` | High-priority unresolved tasks (kept in sync with live work). |
| ADRs | `docs/adr/*.md` | Architectural decisions (CMS/API separation, search pagination, etc.). |
| API Catalog | `.canonical/EndpointCatalog.php`, `.canonical/endpoint_catalog.json`, `docs/MPS_Monitor API` PDFs | Canonical list of 544 vendor actions and reference material gathered by discovery scripts. |
| Discovery Scripts | `scripts/README.md` | How the endpoint discovery pipeline works, execution steps, outputs. |
| Deployment Scripts | `scripts/ftp_backup.py`, `scripts/ftp_deploy.py`, `scripts/live_smoke.py`, `scripts/run_checks.py` | Portable backup, deploy, smoke, and local validation workflow. |
| Chrome Dev Overlay | `dev-overlay-extension/README.md` | HUD extension instructions for runtime capture without modifying the app. |

When in doubt, search the repository (`rg` or VS Code) for the file/path listed above. Keep this table updated when new canonical docs are added.
