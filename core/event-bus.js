/**
 * core/event-bus.js
 * v1.0.0 [EventBus: Pub/Sub with debug]
 */

import debug from './debug.js';

const eventBus = (() => {
  const handlers = {};

  return {
    on(event, fn) {
      handlers[event] = handlers[event] || [];
      handlers[event].push(fn);
      debug.log(`EventBus: listener added for '${event}'`);
    },

    off(event, fn) {
      if (!handlers[event]) return;
      handlers[event] = handlers[event].filter(h => h !== fn);
      debug.log(`EventBus: listener removed for '${event}'`);
    },

    emit(event, payload) {
      debug.log(`EventBus: emitting '${event}'`);
      (handlers[event] || []).forEach(h => {
        try {
          h(payload);
        } catch (err) {
          debug.error(`EventBus: error in '${event}' handler: ${err}`);
        }
      });
    }
  };
})();

export default eventBus;
