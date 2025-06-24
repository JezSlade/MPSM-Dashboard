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
