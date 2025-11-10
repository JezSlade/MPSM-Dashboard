# MPSM Dashboard Context Wiki

This folder is the curated, battle-tested source of truth for the live platform at `https://mpsm.resolutionsbydesign.us`. Every note here is backed by code in this repository as of November 5, 2025. Start with the overview, then drill into the sections that match the work you need to do.

## How To Use This Wiki

1. **New to the project?** Read `project-overview.md`, then `system-architecture.md` to understand the moving pieces.
2. **Touching the CMS?** See `cms-layer.md` for endpoints, assets, and session behavior.
3. **Working on API integrations or callbacks?** Review `mps-api-layer.md` and `data-flows.md`.
4. **Extending data or analytics?** Use `data-model.md` for table structures and caching rules.
5. **Operations, deployments, or refresh jobs?** Follow `operations-playbook.md` and `diagnostics-and-tooling.md`.
6. **Investigating cache/API behavior?** Study `living-audit-todo.md` end-to-end and update it as you diagnose issues.
7. **Debugging regressions?** Start with `verified-fixes.md` to avoid reopening solved issues.

## File Map

- `project-overview.md` — What the platform does, live dependencies, and guarantees.
- `system-architecture.md` — End-to-end design (browser → CMS → mps-api → MPS Monitor).
- `cms-layer.md` — Auth, UI surfaces, API endpoints, and asset structure in `/cms`.
- `mps-api-layer.md` — Engine internals, configuration loading, caching, and callbacks.
- `data-flows.md` — Request paths, background refresh loop, and panel message lifecycle.
- `data-model.md` — Live MySQL schema, auto-created tables, indexes, and retention expectations.
- `operations-playbook.md` — Daily tasks, credentials used in code, refresh procedures, and deployment triggers.
- `diagnostics-and-tooling.md` — Health checks, logging, scripts, payload debugger, and battle test suite.
- `living-audit-todo.md` — Rolling agent-maintained symptom/TODO log for cache/DB/API work; read it fully before coding and record findings.
- `verified-fixes.md` — Locked-in fixes with pointers to the exact files/lines that shipped.
- `references.md` — Canonical docs inside the repo and when to consult each.

> Every statement in this wiki points to code or documentation already in the repository. If something changes in code, update the relevant context file in the same pull request.
