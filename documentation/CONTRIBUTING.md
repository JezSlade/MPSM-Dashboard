# CONTRIBUTING.md

## Ground Rules
- ⛔️ **One patch per reply.**
- ✅ **All PHP includes must use `__DIR__` for safe relative pathing.**
- ❌ **No Composer, autoloaders, or root-relative paths.**
- ✅ **`.env` must be parsed manually inside every API file.**
- ✅ All code must run in isolation on a basic PHP server (e.g., cPanel with PHP 8.4+)

## File Standards

### Cards
- Belong in `/cards/`
- Must pull their own data
- Must be reusable across multiple views
- Support view-level preferences (pagination, visibility)

### API Files
- Belong in `/api/`
- Fully self-contained
- Perform token handling, env loading, and API request internally

### Views
- Reside in `/views/`
- Use dynamic card discovery and rendering
- Include a gear icon to manage card visibility via preferences panel

### Components
- Reside in `/components/`
- Used for shared UI like modals

## Styling
- Neumorphic + Glassmorphic + CMYK aesthetic
- All layout containers must use full-width with internal padding
- Tables are compact with minimal whitespace

## UX Patterns
- Drilldown modal component reused across cards
- Default customer: Cape Fear Valley Med Ctr (`W9OPXL0YDK`)
- Default rows per page = 15
- Default theme: Dark

## Patch Behavior
- Do not combine unrelated patches.
- Only consolidate features if they are functionally tied together.
- Check file path safety (`__DIR__`) before finalizing patch.
