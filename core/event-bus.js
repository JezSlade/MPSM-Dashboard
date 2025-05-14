// v1.0.0 [Init: Core Event Bus]
export class EventBus {
  constructor() {
    this.listeners = new Map();
  }

  on(event, callback) {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, []);
    }
    this.listeners.get(event).push(callback);
  }

  emit(event, payload) {
    console.debug(`[emit] ${event}`, payload);
    if (window.DebugPanel) {
      window.DebugPanel.logEvent(event, payload);
    }
    if (this.listeners.has(event)) {
      this.listeners.get(event).forEach(cb => cb(payload));
    }
  }
}

export const eventBus = new EventBus();
