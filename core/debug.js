// core/debug.js
// v1.0.0 [Debug Panel Implementation]
// This module creates a toggleable debug panel and patches eventBus.emit
// so that all events are logged automatically.

import { eventBus } from './event-bus.js';

class DebugPanel {
  constructor() {
    this._initStyles();
    this._createPanel();
    this._createToggle();
    this._patchEventBus();
  }

  _initStyles() {
    const style = document.createElement('style');
    style.innerHTML = `
      #debug-panel.hidden { display: none; }
      #debug-panel {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 25%;
        background: rgba(0, 0, 0, 0.85);
        color: #0f0;
        font-family: monospace;
        font-size: 12px;
        overflow-y: auto;
        padding: 8px;
        z-index: 9999;
        box-sizing: border-box;
      }
      .debug-toggle {
        position: fixed;
        top: 12px;
        right: 12px;
        background: #007acc;
        color: #fff;
        padding: 6px 10px;
        border-radius: 4px;
        font-family: sans-serif;
        font-size: 14px;
        cursor: pointer;
        z-index: 10000;
        user-select: none;
      }
      .debug-toggle input {
        margin-right: 6px;
        vertical-align: middle;
      }
      #debug-panel .log-entry {
        margin: 2px 0;
        line-height: 1.4;
      }
    `;
    document.head.appendChild(style);
  }

  _createPanel() {
    this.panel = document.createElement('div');
    this.panel.id = 'debug-panel';
    this.panel.classList.add('hidden');
    document.body.appendChild(this.panel);
  }

  _createToggle() {
    this.toggle = document.createElement('label');
    this.toggle.className = 'debug-toggle';
    this.toggle.innerHTML = `
      <input type="checkbox" id="debug-toggle-input" />
      Debug
    `;
    document.body.appendChild(this.toggle);

    this.checkbox = this.toggle.querySelector('#debug-toggle-input');
    this.checkbox.addEventListener('change', (e) => {
      if (e.target.checked) {
        this.panel.classList.remove('hidden');
      } else {
        this.panel.classList.add('hidden');
      }
    });
  }

  _patchEventBus() {
    const originalEmit = eventBus.emit.bind(eventBus);
    eventBus.emit = (eventName, payload) => {
      try {
        this.logEvent(eventName, payload);
      } catch (err) {
        console.error('DebugPanel logEvent error:', err);
      }
      return originalEmit(eventName, payload);
    };
  }

  _append(level, msg) {
    const entry = document.createElement('div');
    entry.className = 'log-entry';
    const timestamp = new Date().toLocaleTimeString();
    entry.textContent = `[${level} ${timestamp}] ${msg}`;
    this.panel.appendChild(entry);
    this.panel.scrollTop = this.panel.scrollHeight;
  }

  log(msg) {
    this._append('INFO', msg);
  }

  warn(msg) {
    this._append('WARN', msg);
  }

  error(msg) {
    this._append('ERROR', msg);
  }

  logEvent(name, payload) {
    const data = payload !== undefined
      ? (typeof payload === 'string' ? payload : JSON.stringify(payload))
      : '';
    this._append(`EVENT:${name}`, data);
  }

  logError(description, err) {
    const detail = err instanceof Error ? err.stack || err.message : String(err);
    this._append('ERROR', `${description} — ${detail}`);
  }
}

// Instantiate and expose globally
window.DebugPanel = new DebugPanel();
