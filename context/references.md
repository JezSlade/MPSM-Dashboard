# Reference Index

Use this list when you need deeper detail than the condensed wiki provides.

| Topic | Location | Why It Matters |
| ----- | -------- | -------------- |
| Constitution & Standards | `docs/CONSTITUTION.md` | 35 enforceable rules governing all code changes. |
| Onboarding Checklist | `docs/ONBOARDING.md` | Full setup instructions, prerequisites, workflow expectations. |
| Pain Points & Workarounds | `docs/PAIN_POINTS.md` | Catalog of historic issues and proven workarounds (PowerShell, search limits, exports). |
| Comprehensive Documentation | `DOCUMENTATION.md` | 1 000+ line deep dive into features, architecture, and deployment steps. Last updated Nov 3 2025. |
| Panel Message Integration | `PANEL_MESSAGES.md`, `PANEL_INTEGRATION_SUMMARY.md`, `PANEL_LIVE_TEST_REPORT.md` | Everything about webhook payload structure, message storage, live tests, and next steps. |
| Payload Debugger | `PAYLOAD_DEBUGGER_GUIDE.md` | Usage, database schema, test script results, and troubleshooting for the debugger. |
| Background Refresh System | `BACKGROUND_REFRESH_SYSTEM.md` | Detailed design of enhanced cache refresh, database schema, and maintenance guide. |
| Audit Report | `AUDIT_REPORT.md` | Forensic review documenting removed legacy files, fixed bugs, and battle test coverage. |
| Immediate Action List | `IMMEDIATE_ACTION_ITEMS.md` | High-priority unresolved tasks (kept in sync with live work). |
| ADRs | `docs/adr/*.md` | Architectural decisions (CMS/API separation, search pagination, etc.). |
| API Catalog | `.canonical/EndpointCatalog.php`, `.canonical/endpoint_catalog.json`, `docs/MPS_Monitor API` PDFs | Canonical list of 544 vendor actions and reference material gathered by discovery scripts. |
| Discovery Scripts | `scripts/README.md` | How the endpoint discovery pipeline works, execution steps, outputs. |
| Deployment Scripts | `deploy-*.ps1` | FTP automation templates for pushing targeted changes. |
| Chrome Dev Overlay | `dev-overlay-extension/README.md` | HUD extension instructions for runtime capture without modifying the app. |

When in doubt, search the repository (`rg` or VS Code) for the file/path listed above. Keep this table updated when new canonical docs are added.
