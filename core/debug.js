// core/debug.js
// v2.2.2 [Resilient Debug System with Hard Bind, Fallbacks, Trace Logs]
export class DebugPanel {
  constructor() {
    this.queue = [];
    this.logEl = null;

    const container = document.createElement('div');
    container.id = 'debug-panel';
    container.classList.remove('visible'); // start hidden

    const toggle = document.createElement('button');
    toggle.id = 'debug-toggle';
    toggle.innerText = 'DEBUG';
    toggle.title = 'Toggle debug panel';
    toggle.onclick = () => {
      container.classList.toggle('visible');
      if (!this.logEl) this.logEl = document.getElementById('debug-log');
    };

    const clearBtn = document.createElement('button');
    clearBtn.innerText = 'Clear';
    clearBtn.className = 'clear-debug';
    clearBtn.onclick = () => {
      const log = document.getElementById('debug-log');
      if (log) log.innerHTML = '';
    };

    const log = document.createElement('div');
    log.id = 'debug-log';
    log.style.minHeight = '40px';

    container.appendChild(clearBtn);
    container.appendChild(log);
    document.body.appendChild(container);
    document.body.appendChild(toggle);

    // Retry DOM bind if missed
    const tryBind = () => {
      this.logEl = document.getElementById('debug-log');
      if (this.logEl) {
        this.queue.forEach(({ text, type }) => this._append(text, type));
        this.queue = [];
      } else {
        setTimeout(tryBind, 500);
      }
    };

    document.addEventListener('DOMContentLoaded', tryBind);
  }

  _timestamp() {
    const d = new Date();
    return d.toISOString().split('T')[1].replace('Z', '');
  }

  logEvent(event, payload) {
    this._append(`[${this._timestamp()}] [event] ${event}: ${JSON.stringify(payload)}`, 'event');
  }

  logError(msg, error) {
    this._append(`[${this._timestamp()}] [error] ${msg}: ${error?.message || error}`, 'error');
  }

  logInfo(msg) {
    this._append(`[${this._timestamp()}] [info] ${msg}`, 'info');
  }

  _append(text, type = 'info') {
    if (!this.logEl) {
      this.queue.push({ text, type });
      console.log(`[debug queue] ${text}`);
      return;
    }

    const entry = document.createElement('div');
    entry.className = 'log-entry ' + type;
    entry.innerText = text;
    this.logEl.appendChild(entry);
    this.logEl.scrollTop = this.logEl.scrollHeight;
  }
}

window.DebugPanel = new DebugPanel();
