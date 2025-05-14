// v1.0.0 [Init: Shared State Store]
export class Store {
  constructor() {
    this.state = {};
    this.listeners = {};
  }

  set(key, value) {
    this.state[key] = value;
    if (this.listeners[key]) {
      this.listeners[key].forEach(cb => cb(value));
    }
  }

  get(key) {
    return this.state[key];
  }

  subscribe(key, callback) {
    if (!this.listeners[key]) this.listeners[key] = [];
    this.listeners[key].push(callback);
  }
}

export const store = new Store();
