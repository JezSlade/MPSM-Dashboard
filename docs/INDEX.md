# Documentation Index

Verified against the working tree on 2026-05-20.

This file replaces the older "complete documentation" report, which mixed current architecture with historical deployment notes and stale paths.

## Current Entry Points

- [../README.md](../README.md) - current project map, configuration, runtime surfaces, and operations.
- [../cms/README.md](../cms/README.md) - current CMS layer guide.
- [REPOSITORY_AUDIT.md](REPOSITORY_AUDIT.md) - Markdown verification results and cleanup notes.
- [../context/README.md](../context/README.md) - how to use historical context notes.
- [../scripts/README.md](../scripts/README.md) - API discovery, local validation, FTP deploy, backup, and live smoke tooling.
- [adr/README.md](adr/README.md) - architectural decision records.

## Current Code Areas

- `cms/` - dashboard, login page, legacy APIs, REST v1 gateway, assets, diagnostics, runtime cache/lock/log directories.
- `mps-api/` - MPS Monitor proxy, Swagger action registry, endpoint catalog, callbacks, file cache.
- `src/` - newer REST/service layer classes.
- `config/` - repository-managed config and `.env` loader.
- `scripts/` - Python discovery, endpoint probing, local checks, FTP backup/deploy, and live smoke helpers.
- `tools/` - one-off diagnostics and command-center maintenance tools.
- `tests/` - PHP, shell, SQL tests and generated test reports.
- `database/` - migrations, rollback SQL, and diagnostic queries.

## Historical Documents

Historical Markdown files are grouped by purpose. They are retained as history, but they are not guaranteed to describe current code. Verify any claim against the files in the working tree before using it for implementation.

Examples of historical documents:

- `reports/BATTLE_TEST_REPORT.md`
- `reports/CACHE_SYSTEM_AUDIT.md`
- `status/DEPLOY-STATUS.md`
- `reports/FIX_SUMMARY.md`
- `api/FLEET_COVERAGE_TEST_REPORT.md`
- `status/REFACTOR_STATUS.md`
- `status/SESSION_CONTEXT.md`

## Known Documentation Gaps

- PHP CLI is available from this environment through `flatpak-spawn --host php`; `scripts/run_checks.py` also loads ignored MySQL extensions from `.runtime/php-ext/` when present.
- There is no active `.github/workflows/deploy.yml` and no configured git remote in this working tree.
- Several historical docs reference deployment or API helper secrets; these should be rotated and moved into environment variables.
- Some generated reference files under `output/` are useful for API work but should be regenerated from `Swagger.json` when the vendor API changes.
