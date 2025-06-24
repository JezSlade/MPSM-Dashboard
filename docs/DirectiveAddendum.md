# Directive Addendum

All API endpoints must:
1. `require` these includes in order:
   - `env_parser.php`
   - `auth.php`
   - `cors.php` + `send_cors_headers()`
   - `logger.php`
   - `api_client.php`
2. Use `api_request(path, body)` for **all** downstream HTTP calls.
3. Echo **only** JSON responses; no HTML or debug text.
4. Read inputs from `$_GET` or `php://input` and validate them.
5. Return appropriate HTTP status codes on error.

Project structure:
```
/api/*.php
/includes/{env_parser,auth,cors,logger,api_client}.php
/cards/CustomersCard.php
/views/{dashboard.php,detail_modal.php}
/js/{api.js,ui_helpers.js}
/public/css/styles.css
/.well-known/ai-plugin.json
/openapi.yaml
/docs/DirectiveAddendum.md
```

This ensures each endpoint is a **ChatGPT plugin action** matching our OpenAPI spec.