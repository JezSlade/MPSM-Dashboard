## MPS Monitor Dashboard CMS - Official Documentation

### Updated Architectural Overview (SPA Modular - Aug 2025)

#### Entry Point
- `index.php` now acts solely as a front controller.
- All request logic is routed through `controllers/FrontController.php`.

#### Folder Structure
```
/                         → Entry point only
/controllers             → HTTP controller logic (e.g. FrontController.php)
/views                   → Layouts and UI wrappers
/services                → Logic classes (e.g. WidgetService.php)
/includes                → Config files and global constants
/widgets                 → Unchanged; still stores all widget PHP files
/assets/css              → Modular, scoped CSS (e.g. dashboard.css)
/assets/js               → Modular JS split: core logic, drag behavior, widget UI
```

#### CSS Refactor Notes
- `dashboard.css` now lives under `assets/css/`
- Drag logic and animation behavior now lives in `assets/js/drag-behavior.js`
- Avoid inline `<style>` usage in widgets; use `.compact-content`, `.expanded-content` with BEM naming.

#### Routing and Execution Flow
```
index.php → FrontController.php
           └── loads View/Layout
           └── invokes WidgetService
                         └── renders widgets using render_*()
```

#### Compatibility Notes
- All widgets remain compliant with existing `WIDGET_REQUIREMENTS.TXT`
- No refactor required at widget level
- Legacy behavior (e.g., POST handlers) delegated to controller layer

---

This update aligns with Option [3] of the August 2025 refactor and is 100% backward-compatible with widget logic and appearance.