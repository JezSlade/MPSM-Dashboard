# Contributing

Follow these guidelines to ensure consistency:

## Code Structure
- **API Stubs**: set `$method`, `$path`, `$useCache` then include `api_bootstrap.php`.
- **Cards**: use `card_bootstrap.php`.
- **Views**: include header, navigation, preferences modal, and loop cards.

## Helpers
- `/includes/searchable_dropdown.php`
- `/includes/table_helper.php`

## Standards
- PHP 8.4+ with `strict_types=1`.
- **Full inline code**: deliver entire files.
- No external libraries; use Feather Icons CDN.
- Relative paths via `__DIR__`.

## Documentation
- Keep MD files in `/documentation/` updated:
  - Architecture.md
  - CONTRIBUTING.md
  - MPSM_Bible.md
  - Handoff_Summary.md

## Workflow
1. One functional change per PR.
2. Validate with `php -l`.
3. Merge with full code listing.

