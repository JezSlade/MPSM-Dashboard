/**
 * core/store.js
 * v1.0.0 [Store: Reactive key/value store]
 */

import debug from './debug.js';

const store = (() => {
  const data = {};
  const subs = {};

  return {
    set(key, value) {
      data[key] = value;
      debug.log(`Store: '${key}' updated`);
      (subs[key] || []).forEach(fn => fn(value));
    },

    get(key) {
      return data[key];
    },

    subscribe(key, fn) {
      subs[key] = subs[key] || [];
      subs[key].push(fn);
      debug.log(`Store: subscriber added for '${key}'`);
    }
  };
})();

export default store;
