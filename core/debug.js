// core/debug.js
import { dom } from './dom.js';

export const debug = {
  debugMode: false,

  log(msg) {
    if (!this.debugMode) return;
    const panel = dom.get('debug-log');
    if (panel) {
      const entry = document.createElement('div');
      entry.textContent = `[LOG ${new Date().toISOString()}] ${msg}`;
      panel.appendChild(entry);
      panel.scrollTop = panel.scrollHeight;
    }
    console.log(msg);
  },

  warn(msg) {
    if (!this.debugMode) return;
    const panel = dom.get('debug-log');
    if (panel) {
      const entry = document.createElement('div');
      entry.textContent = `[WARN ${new Date().toISOString()}] ${msg}`;
      panel.appendChild(entry);
      panel.scrollTop = panel.scrollHeight;
    }
    console.warn(msg);
  },

  error(msg) {
    if (!this.debugMode) return;
    const panel = dom.get('debug-log');
    if (panel) {
      const entry = document.createElement('div');
      entry.textContent = `[ERROR ${new Date().toISOString()}] ${msg}`;
      panel.appendChild(entry);
      panel.scrollTop = panel.scrollHeight;
    }
    console.error(msg);
  },

  toggle(on) {
    this.debugMode = on;
    const panelElem = dom.get('debug-panel');
    if (panelElem) {
      panelElem.classList.toggle('hidden', !on);
    }
    // Always log the state change (even if disabling, so you know it worked)
    if (on) {
      console.log('🛠 Debug mode ENABLED');
    } else {
      console.log('🛠 Debug mode DISABLED');
    }
  }
};
