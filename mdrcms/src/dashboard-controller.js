/**
 * Orchestrates app boot and high level flows
 */
export class DashboardController {
  /**
   * @param {object} deps
   * @param {import('./repositories/widget-repository.js').WidgetRepository} deps.widgetRepo
   * @param {import('./repositories/settings-repository.js').SettingsRepository} deps.settingsRepo
   * @param {import('./ui-service.js').UIService} deps.ui
   * @param {HTMLElement} deps.mount
   * @param {import('./services/error/error-monitor.js').ErrorMonitor} deps.errors
   * @param {import('./utils/event-bus.js').EventBus} deps.events
   */
  constructor({ widgetRepo, settingsRepo, ui, mount, errors, events }) {
    this.widgetRepo = widgetRepo;
    this.settingsRepo = settingsRepo;
    this.ui = ui;
    this.mount = mount;
    this.errors = errors;
    this.events = events;
  }

  async init() {
    try {
      const [settings, widgets] = await Promise.all([
        this.settingsRepo.get(),
        this.widgetRepo.getAll()
      ]);

      let list = widgets;
      if (list.length === 0) {
        list = [{
          id: crypto.randomUUID?.() ?? String(Date.now()),
          title: "Welcome",
          type: "basic",
          content: "This is your first widget."
        }].map(w => ({ ...w }));
        await this.widgetRepo.saveAll(list);
      }

      this.applySettings(settings);
      this.ui.renderWidgets(list, this.mount);
      this.wireEvents();
    } catch (e) {
      this.errors.report(e, "Dashboard init failed");
    }
  }

  applySettings(settings) {
    const root = document.querySelector("body.app");
    if (!root) return;
    root.dataset.theme = settings.theme;
    root.dataset.layout = settings.layout;
  }

  wireEvents() {
    // Reserved for future interactions
    // Example: this.events.on("widget:add", payload => { ... })
  }
}