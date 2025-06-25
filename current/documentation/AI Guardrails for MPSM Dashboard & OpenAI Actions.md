# MPSM Dashboard — Consolidated Standards & Guardrails

_Last updated: 2025-06-24_

This single, authoritative document merges and updates our old CONTRIBUTING.md, MPSM_Bible.md, Architecture.md, AI Patch & Audit protocols, and instructions README. It reflects our **current mandate**:

- **Card-first modular UI**  
- **Self-contained, spec-driven API endpoints**  
- **Seamless ChatGPT Custom-GPT actions**  
- **One-patch, one-purpose** workflow  
- **Full inline code** in every change  

---

## 1. Directory Structure

```
/api/                   # One PHP file per endpoint
/includes/              # Shared helpers: env_parser, auth, cors, logger, api_client
/cards/                 # One PHP file per card (e.g. CustomersCard.php)
/views/                 # Simple layout pages (dashboard.php, detail_modal.php)
/js/                    # Vanilla JS helpers: api.js, ui_helpers.js
/public/css/            # Global CSS (styles.css)
/public/images/         # Logos/icons for plugin manifest
/.well-known/           # ai-plugin.json
/openapi.yaml           # OpenAPI spec matching /api/*.php
/docs/                  # Markdown docs (DirectiveAddendum.md, AiGuardrails.md)
.env                    # Environment variables
```

---

## 2. API Endpoint Guidelines

Every file in `/api/*.php` **must**:

1. **Begin** with:
   ```php
   declare(strict_types=1);
   require_once __DIR__.'/../includes/env_parser.php';
   require_once __DIR__.'/../includes/auth.php';
   require_once __DIR__.'/../includes/cors.php'; send_cors_headers();
   require_once __DIR__.'/../includes/logger.php'; log_request();
   require_once __DIR__.'/../includes/api_client.php';
   ```
2. **Read & validate** inputs (`$_GET` or `php://input`), supply defaults for optional params.
3. **Call** `api_request('Path/Operation', $payload)` exactly as defined in **AllEndpoints.json** (method, path, required parameters).
4. **Return** only JSON:
   - On success: `echo json_encode($response);`
   - On failure:  
     ```php
     http_response_code(502);
     echo json_encode(['error'=>'Upstream failed','details'=>$e->getMessage()]);
     ```
5. **Never** duplicate HTTP, cURL, token logic, CORS headers or JSON parsing: use the shared helpers.

---

## 3. Card Development Standards

Each file in `/cards/` **must**:

1. **Require** only the card-base wrapper:
   ```php
   <?php require_once __DIR__.'/../includes/card_base.php'; ?>
   ```
2. **Contain** its entire markup + full `<script type="module">…</script>` JS inline—no external card-specific files.
3. **Import** only the shared JS modules:
   ```js
   import { fetchJson }    from '/js/api.js';
   import { renderTable }  from '/js/ui_helpers.js';
   ```
4. **Handle** empty-state, error-state, and pagination via `renderTable()` helper.
5. **Use** only columns that match your `/api/*.php` response schema and OpenAPI spec.

---

## 4. OpenAI Plugin Alignment

1. **Manifest** (`.well-known/ai-plugin.json`):
   - Lists each `/api/*.php` endpoint, auth type `service_http` + `bearer`.
2. **OpenAPI** (`openapi.yaml`):
   - Paths, methods, parameters, response schemas mirror `/api/*.php` exactly.
3. **CORS** (`includes/cors.php`):
   ```php
   header('Access-Control-Allow-Origin: https://chat.openai.com');
   header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
   header('Access-Control-Allow-Headers: Content-Type, Authorization');
   if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
   ```
4. **Auth** (`includes/auth.php`):  
   - Implements client-credentials flow, caches token in `.token_cache.json`.

---

## 5. Styling & Theming

- **Global CSS** in `/public/css/styles.css` (glassmorphic/neumorphic—etched-glass + neon CMYK glow).  
- **No per-card style tags**; use utility classes only.  
- **Light/dark** theme toggles supported via shared CSS variables and a single `<body class="dark">` toggle.

---

## 6. One-Patch, One-Purpose Workflow

1. **Branch** from `main`.  
2. **Modify** only the target file(s).  
3. **Provide** full, inline code listings in PR description.  
4. **Follow** the include order and helper usage above.  
5. **Run** lint checks:
   - PHP (`php -l`)  
   - JS (`eslint`)  
   - CSS (`stylelint`)  
6. **Validate** OpenAPI vs `/api/` folder (paths, methods, schemas).  
7. **Merge** only after CI passes and manual smoke-tests (endpoints + cards) succeed.

---

## 7. AI Patch & Audit Protocol

When drafting or reviewing patches, AI or human must:

- **Spec-verify** each endpoint against AllEndpoints.json (**zero tolerance** for mismatch).  
- **Prevent regression**: existing endpoints and UI must behave identically if unchanged.  
- **Isolate changes**: no side effects on unrelated files.  
- **Never assume** undocumented behavior—ask for clarification.  
- **Document** any new guardrail in `/docs/DirectiveAddendum.md` or add “Addendum” section to this file.

---

## 8. Addendum Process

If any new rule conflicts with this document or previous addenda:

1. **Amend** `/docs/DirectiveAddendum.md` with a clear “Addendum” section.  
2. **Reference** this consolidated doc’s section (e.g. “See AI Guardrails §3, Card Development”).  
3. **Do not** remove or rename existing docs—just append.

---

By following this consolidated, up-to-date guide, we ensure our **modular cards**, **self-contained APIs**, and **ChatGPT plugin actions** remain rock-solid, maintainable, and future-proof.
# Addendum: After-Action Report & Lessons Learned

This addendum captures the pitfalls we encountered, the hard-won fixes, and updated guardrails to keep future development smooth and aligned with our MPSM Dashboard, Custom GPT Actions, and glassmorphic/neon styling mandates.

---

## 1. Environment Loading & Constants

**Issue:**
– Over-eager stubbing of all `.env` keys in `constants.php` prevented real values from loading (empty defaults never overridden).
– Duplicate-definition warnings when reloading constants.

**Fix & Lesson:**
– **Remove** the constants stub.
– Load `.env` exactly once in `env_parser.php`, defining each constant only if not already defined.
– Throw early if any required key is missing or empty.

> **Guardrail:** Every API file must `require_once 'env_parser.php'` at top; no other constant definitions anywhere.

---

## 2. Auth & Token URL Sanitation

**Issue:**
– Malformed `TOKEN_URL` (extra quotes/spaces) caused cURL failures.
– Unhelpful “URL rejected” exceptions lacking context.

**Fix & Lesson:**
– Trim whitespace/quotes from `TOKEN_URL` before validation.
– Validate with `filter_var(..., FILTER_VALIDATE_URL)` and echo the raw input on error.

> **Guardrail:** `get_bearer_token()` must sanitize and validate all upstream URLs.

---

## 3. CORS & Header Emissions

**Issue:**
– Views and navigation includes inadvertently triggered output before `cors.php`, leading to “headers already sent” warnings.

**Fix & Lesson:**
– **Isolate** CORS headers to **API endpoints only** (`/api/*.php`).
– Ensure all view includes (e.g. `navigation.php`, `header.php`) are pure HTML/PHP templates, with no header or HTTP-status calls.

> **Guardrail:** Only files under `/api/` may `require_once 'cors.php'` and call `send_cors_headers()`.

---

## 4. Styling Pipeline

**Issue:**
– Attempted to use Tailwind’s `@apply` in plain CSS led to unprocessed rules.
– Charted incremental CSS patches got out of sync with markup, causing visual regressions.

**Fix & Lesson:**
– Switch to **Tailwind CDN** and apply utilities directly in markup.
– Consolidate all theme definitions (glassmorphic/backdrop-blur, neon CMYK accents, card shadows) in inline HTML classes.

> **Guardrail:** No build step for Tailwind—always use CDN and utility classes in markup.

---

## 5. Table Helper & Card Logic

**Issue:**
– Pivoting cards to JavaScript introduced duplication and mismatched logic.
– Default columns (only “Description”) failed to hide others on first paint, causing flicker.
– Extending tables with settings panel grew unwieldy without proper inline-style guards.

**Fix & Lesson:**
– Reverted all cards to server-rendered PHP, using a single `renderDataTable()` helper.
– Refactored `table_helper.php` to:

1. Dynamically discover **all** API-returned columns.
2. Accept a PHP option `defaultVisibleColumns` (defaults to `['Description']`).
3. Emit inline `style="display:none;"` on both `<th>` and `<td>` for hidden columns—no flash of unstyled content.
4. Append a small settings icon to each table for per-table configuration (rows-per-page, column toggles), saved in `localStorage`.
5. Center pagination and tighten row padding via two minimal edits.

> **Guardrail:** **All** table-rendering logic must live in `includes/table_helper.php`. Card files only wrap tables, never reimplement search/sort/pagination.

---

## 6. Custom GPT Actions Alignment

**Issue:**
– Initial API files contained assumptions about `SortColumn`/`SortOrder` defaults not aligned with `AllEndpoints.json`.
– Mismatch between nav dropdown logic and card-table logic meant inconsistent data loads.

**Fix & Lesson:**
– Every API endpoint now pulls parameter definitions directly from `AllEndpoints.json` (method names, payload shapes, default values).
– Shared `api_request()` function unifies error handling, respect of `DealerCode`, and response wrapping.
– Navigation and card both call the same `/api/get_customers.php` with identical query parameters, ensuring data consistency.

> **Guardrail:** Before merging any API change, validate method, URL, and payload against `AllEndpoints.json`. Nav and cards must share the same underlying call.

---

### Next Steps

1. **Document** this addendum in `Documentation/Addendum.md`.
2. **Lock down** `table_helper.php` and `env_parser.php` as core libraries—no more bespoke overrides.
3. **Automate** a CI check that parses `AllEndpoints.json` and verifies each `/api/*.php` file includes every required parameter.
4. **Expand** card settings to persist theme preferences and table columns globally via Custom GPT Actions in future sprints.

---

*By codifying these lessons, our codebase will remain modular, thematically consistent, and seamlessly callable as Custom GPT Actions.*
