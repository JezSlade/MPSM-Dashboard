# Context Notes

Verified against the working tree on 2026-05-20.

`context/` is a historical operating log and project memory vault. It is useful for understanding incidents, deployment decisions, cache behavior, panel callback work, and follow-up tasks, but it is not uniformly current.

For the current deploy/runtime/cache checkpoint, start with [current-state.md](current-state.md). It supersedes older GitHub Actions, PowerShell, and enhanced-cache-only instructions unless current code proves those paths have been restored.

## How To Use This Folder

1. Start with [../README.md](../README.md) for the current code map.
2. Read [../docs/INDEX.md](../docs/INDEX.md) and [../docs/REPOSITORY_AUDIT.md](../docs/REPOSITORY_AUDIT.md) for the current documentation map and audit results.
3. Use the files in this folder as evidence trails, then verify claims against current code before implementing changes.

## Current High-Signal Files

- [project-overview.md](project-overview.md) - platform overview and live dependencies.
- [current-state.md](current-state.md) - current cleanup, FTP deploy, validation, and cache-refresh checkpoint.
- [system-architecture.md](system-architecture.md) - browser, CMS, mps-api, and database flow.
- [cms-layer.md](cms-layer.md) - CMS endpoints and session behavior.
- [mps-api-layer.md](mps-api-layer.md) - proxy configuration, cache, and callback internals.
- [data-flows.md](data-flows.md) - request paths and cache refresh flow.
- [data-model.md](data-model.md) - schema notes.
- [operations-playbook.md](operations-playbook.md) - operational procedures.
- [diagnostics-and-tooling.md](diagnostics-and-tooling.md) - health checks and diagnostic scripts.
- [living-audit-todo.md](living-audit-todo.md) - rolling technical debt and bug list.
- [verified-fixes.md](verified-fixes.md) - shipped fixes with pointers.

## Caution

Several context files are dated incident reports. Some reference files that have moved, archived endpoints, deployment workflows that are not present in this working tree, or literal operational secrets that should be rotated and moved into environment variables. Treat them as historical notes unless the current code confirms the claim.
