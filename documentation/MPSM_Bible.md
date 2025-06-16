# MPSM_Bible.md

## Project Doctrine

### Core Philosophies
- One patch per reply
- Fully modular design
- Cards = standalone UI modules
- API = standalone PHP files (env + token + curl + JSON)
- Views = grid renderers for cards
- CSS = minimal, responsive, global styling

### Required Behaviors
- Use `Cape Fear Valley Med Ctr (W9OPXL0YDK)` as default if none provided
- Display compact tables and cards
- Always include PHP debugging at top of file
- Use full horizontal space — no hard margins
- Only include usable data in drilldowns (no `null`, `[]`, `0`, or `DEFAULT`)

### Feature Summary

#### ✅ Device Alerts
- Consolidate all supply alerts by `DeviceId`
- Merge `Warning` and `SuggestedConsumable` into comma-separated lists
- Paginate by **device**, not raw alerts
- Sort by ExternalIdentifier (Equipment ID)

#### ✅ Device Details
- Accessed via 🔍 drilldown icon
- Calls `Device/Get` API with `Id`
- Displays non-empty values only

#### ✅ Global Layout
- Responsive card grid
- Sidebar, header, and main layout with neumorphic styling
- Supports light/dark themes
- Drilldown modal in `/components/drilldown-modal.php`

### Enforcement Summary
- `.env` loading is mandatory inside each API file
- Relative path enforcement: always use `__DIR__`
- No placeholder content allowed
- No JS frameworks or external dependencies
- No combining patches unless they are functionally related
