# CONTRIBUTING.md

Thank you for contributing! Please follow these conventions to keep the codebase consistent.

## File Structure & Naming
- **API Endpoints** (`/api/*.php`):  
  - Must set `$method`, `$path`, and optional `$useCache`, then `require __DIR__ . '/../includes/api_bootstrap.php';`.
- **Cards** (`/cards/card_*.php`):  
  - Must start with `require __DIR__ . '/../includes/card_bootstrap.php';`.
- **Views** (`/views/*.php`):  
  - Pure layout files. Loop through `card_*.php` files only.  
- **Shared**:  
  - `/components/` for modal & UI components.  
  - `/includes/` for `api_bootstrap.php`, `card_bootstrap.php`, and utility helpers.  
  - `/public/css/styles.css` for all CSS.  
  - `/engine/` for caching logic.  
  - `/cron/` for scheduled tasks.  
  - `/logs/` for debug logs.  

## Coding Standards
- **PHP 8.4+**: Strict types enabled (`declare(strict_types=1)`).
- **No Composer/Autoloaders**: Manual `.env` parsing in includes.
- **Relative Includes**: Always use `__DIR__`.
- **Error Handling**:
  - API endpoints emit HTTP codes and JSON errors.
  - Cards catch exceptions silently but log to `/logs/debug.log`.

## Styling & Frontend
- Use **Tailwind CSS** classes for all styles.
- Components must support **light/dark** modes.
- No external JS frameworks—vanilla JS only.
- Full-width, responsive grid layouts; no hard margins.

## Patch Workflow
1. **Branch** from `main`.
2. Make **one functional change per PR**.
3. **Validate** against existing code locally.
4. Provide a **full, complete code listing** in PR description (no snippets).
5. After review, merge and delete branch.

## Testing & CI/CD
- Lint PHP (`php -l`).
- Ensure `/public/css/styles.css` is referenced (no `/assets/`).
- Deploy pipeline defined in `.github/workflows/deploy.yml`.
