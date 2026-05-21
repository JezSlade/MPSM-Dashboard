# Repository Audit

Audit date: 2026-05-20

## Scope

I inspected the current code tree and all Markdown files in the working tree:

- 122 Markdown files.
- 37,870 Markdown lines.
- 249 PHP files outside the vendor/reference documentation bundles.
- 28 JavaScript files outside the vendor/reference documentation bundles.

The repository is a PHP/MySQL MPS Monitor dashboard. The current implementation is spread across:

- `cms/` - browser dashboard, legacy endpoints, REST v1 gateway, assets, diagnostics, runtime state.
- `mps-api/` - MPS Monitor proxy, endpoint catalog, webhook callbacks, file cache.
- `src/` - newer service-layer classes for REST routing, controllers, repositories, middleware, auth, queue, and cache drivers.
- `scripts/` - Python API discovery, validation, FTP backup/deploy, live smoke, and local check utilities.
- `tools/` - one-off diagnostic and command-center maintenance scripts.
- `tests/` - PHP, shell, and SQL test scripts.
- `database/` - SQL migrations, rollback scripts, and diagnostic queries.
- `docs/` and `context/` - a mix of current docs, historical incident reports, generated API references, and deployment notes.

## Code-Verified Facts

- Browser login page is `cms/login.html`.
- Login API is `cms/api/login.php`.
- The REST v1 gateway exists at `cms/api/v1/index.php`.
- ES module files exist under `cms/assets/js/`.
- `bootstrap.php` loads the service container and config from `config/app.php`.
- `config/app.php` now loads root `.env` values through `config/env.php`.
- Runtime/local files are now covered by `.gitignore`, including `.env`, `cms/config.php`, `.runtime/`, logs, lock files, JSON cache files, and `mps-api/cache/storage/`.

## Documentation Mismatches Found

The previous top-level `README.md` contained stale or unsafe statements:

- It pointed to a missing `config/app.php.example`.
- It referenced a missing `tests/` directory and missing test docs.
- It treated historical status as current source of truth.
- It described deployment/configuration in ways that encouraged editing repository config with production secrets.

The previous `cms/README.md` was materially out of date:

- It claimed the CMS had 12 files; the `cms/` tree now contains hundreds of source, diagnostic, archive, cache, lock, and log files.
- It claimed there was no cache; current code and runtime files include CMS JSON caches and `mps-api/cache/ActionCache.php`.
- It described the layer as purely procedural, but the repo also contains a newer `src/` service layer and REST v1 gateway.

The `context/` notes are useful but mixed-current:

- Some files document live architecture and data flows.
- Some files are deployment logs or incident reports that reference removed, archived, or renamed files.
- Several historical files included literal operational secrets or secret-bearing URLs; known FTP/database/OAuth values were redacted during cleanup.

## Remaining Risks

- `cms/config.php` is still present locally and contains deployment-specific constants. It is now ignored by git, but the live value should be managed outside the repository.
- Several legacy API helper secrets are still hardcoded in PHP endpoints and historical docs. They should be replaced with environment-driven constants in a dedicated follow-up.
- `bootstrap.php` registers an `EngineInterface` implementation at `src/Engine/MPSEngine.php`, but that file does not exist in this working tree. Routes that resolve `EngineInterface` will fail until the service registration or class is corrected.
- PHP CLI is available through `flatpak-spawn --host php`; MySQL extensions are loaded from ignored `.runtime/php-ext/` by `scripts/run_checks.py`.
- The git index contained stale deleted entries for a separate TypeScript/Vite build (`node_modules/`, `dist/`, `package.json`, etc.). Those stale index entries were cleared without deleting working files.
- The repository root has been reduced to project metadata and runtime entrypoints; historical docs, tests, tools, SQL, reports, and local reference/runtime bundles were moved into role-specific or ignored directories.

## Cleanup Completed

- Added `.gitignore` for secrets, local config, logs, runtime cache, lock files, dependency folders, build output, and large vendor/reference binaries.
- Added root `.env.example`.
- Added `config/env.php` and removed hardcoded secret fallbacks from `config/app.php`.
- Replaced hardcoded database/FTP credentials in diagnostic scripts with environment-driven values.
- Replaced the top-level `README.md` with a code-verified project map.
- Moved the former `DOCUMENTATION.md` content to `docs/INDEX.md` as the current documentation index.
- Replaced `cms/README.md` with a current CMS-layer guide.
- Replaced `context/README.md` with guidance that treats `context/` as historical notes requiring verification.
- Redacted active agent guidance and historical deployment examples so FTP credentials are not documented in clear text.
- Replaced PowerShell deploy/test scripts with portable Python FTP backup/deploy, local check, and live smoke helpers.
- Backed up and deployed the cleaned repo to live via FTP, then verified live health.
- Moved root-level historical docs into `docs/api/`, `docs/guides/`, `docs/operations/`, `docs/reports/`, and `docs/status/`.
- Moved tests into `tests/`, diagnostic tools into `tools/`, SQL files into `database/`, and generated/local artifacts into ignored runtime or reference folders.

## Recommended Next Cleanup

1. Replace hardcoded endpoint bypass secrets with `getenv()`/`.env` configuration.
2. Resolve or remove the missing `src/Engine/MPSEngine.php` service registration.
3. Let the active chunked cache refresh finish and record final guarded cutover counts.
4. Make the first clean commit from the current source tree after reviewing the untracked source files.
