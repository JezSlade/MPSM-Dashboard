# AI Patch Instructions - SPA Modular Refactor Alignment

### Updated Repository Structure (as of Aug 2025)
```
/                         → Entry point: index.php (Front Controller)
/controllers             → Request handlers (e.g. FrontController.php)
/views                   → Templated UI layout handlers
/services                → PHP logic (WidgetService.php, DashboardManager.php)
/includes                → Configs and boot logic
/widgets                 → PHP widget files (unchanged)
/assets/css              → dashboard.css, themed styles
/assets/js               → Modular JS (drag-behavior.js, dashboard-core.js)
```

### Architectural Guidance
- All logic begins in `controllers/FrontController.php`
- `index.php` is no longer used for direct HTML or session logic
- Services and Controllers must remain decoupled; no direct output

### Patch Guidelines Update
- Never modify `index.php` beyond front controller delegation
- JS/CSS changes must go in `assets/`
- UI patches must call through `WidgetService->render()` or registered views

### Widget Compliance
- Widgets remain as defined in `WIDGET_REQUIREMENTS.TXT`
- No SPA-specific changes apply to widget design
- Widgets must not depend on JS functions globally (scope them)

---

Maintainer Note: Always validate refactored patches against the updated directory map. Avoid cross-layer bleed (e.g. controller logic in views).