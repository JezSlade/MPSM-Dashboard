import { LocalStorageService } from "./services/storage/localstorage-service.js";
import { ConsoleErrorMonitor } from "./services/error/console-error-monitor.js";
import { WidgetRepository } from "./repositories/widget-repository.js";
import { SettingsRepository } from "./repositories/settings-repository.js";
import { UIService } from "./ui-service.js";
import { DashboardController } from "./dashboard-controller.js";
import { EventBus } from "./utils/event-bus.js";

async function boot() {
  const storage = new LocalStorageService();
  const errors = new ConsoleErrorMonitor();
  const widgetRepo = new WidgetRepository(storage);
  const settingsRepo = new SettingsRepository(storage);
  const ui = new UIService();
  const events = new EventBus();
  const mount = document.getElementById("dashboard");

  const app = new DashboardController({ widgetRepo, settingsRepo, ui, mount, errors, events });
  await app.init();
}

boot().catch(err => console.error("Fatal boot error", err));