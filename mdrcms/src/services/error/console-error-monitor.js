import { ErrorMonitor } from "./error-monitor.js";

export class ConsoleErrorMonitor extends ErrorMonitor {
  report(error, context) {
    console.error("[Error]", context ?? "", error);
  }
  info(message, context) {
    console.info("[Info]", context ?? "", message);
  }
  warn(message, context) {
    console.warn("[Warn]", context ?? "", message);
  }
}