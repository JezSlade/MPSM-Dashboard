// core/store.js
// v1.1.1 [Fix: Export named & default store]

import debug from './debug.js';

const store = (() => {
  const data = {};
  const subs  = {};

  return {
    set(key, value) {
      data[key] = value;
      debug.log(`Store: '${key}' updated`);
      (subs[key] || []).forEach(fn => {
        try { fn(value); }
        catch (err) { debug.error(`Store subscriber error for '${key}': ${err}`); }
      });
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

// Named export for `import { store }`…
export { store };
// …and default export to preserve `import store from` usage.
export default store;
