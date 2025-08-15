# Modular Dashboard Refactor

Goals
- Strict ES modules with real composition root
- Dependency inversion at the controller boundary
- Renderer strategy for widgets
- Async storage contract for future backends
- Namespaced CSS with simple responsive grid
- No innerHTML for user content
- Removed Gemini service entirely

Structure
- index.html
- public/css/styles.css
- src/main.js
- src/controllers/dashboard-controller.js
- src/ui-service.js
- src/renderers/widget-renderer.js
- src/renderers/basic-widget-renderer.js
- src/repositories/widget-repository.js
- src/repositories/settings-repository.js
- src/services/storage/storage-service.js
- src/services/storage/localstorage-service.js
- src/services/error/error-monitor.js
- src/services/error/console-error-monitor.js
- src/utils/event-bus.js
- src/utils/dom.js
- src/utils/sanitize.js
- src/constants/storage-keys.js
- src/constants/schema.js
- src/models/widget-model.js
- src/models/settings-model.js