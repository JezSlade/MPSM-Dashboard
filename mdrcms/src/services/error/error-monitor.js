/**
 * Error reporting interface
 */
export class ErrorMonitor {
  report(error, context) { throw new Error("Not implemented"); }
  info(message, context) { throw new Error("Not implemented"); }
  warn(message, context) { throw new Error("Not implemented"); }
}