// core/debug.js
// v2.2.0 [Fixed Logging, Timestamped, Clear Button, Color Coded]
export class DebugPanel {
  constructor() {
    const container = document.createElement('div');
    container.id = 'debug-panel';
    container.classList.remove('visible'); // start hidden

    const toggle = document.createElement('button');
    toggle.id = 'debug-toggle';
    toggle.innerText = 'DEBUG';
    toggle.title = 'Toggle debug panel';
    toggle.onclick = () => {
      container.classList.toggle('visible');
    };

    const clearBtn = document.createElement('button');
    clearBtn.innerText = 'Clear';
    clearBtn.className = 'clear-debug';
    clearBtn.onclick = () => {
      log.innerHTML = '';
    };

    const log = document.createElement('div');
    log.id = 'debug-log';

    container.appendChild(clearBtn);
    container.appendChild(log);
    document.body.appendChild(container);
    document.body.appendChild(toggle);

    this.logEl = log;
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
    if (!this.logEl) return;
    const entry = document.createElement('div');
    entry.className = 'log-entry ' + type;
    entry.innerText = text;
    this.logEl.appendChild(entry);
    this.logEl.scrollTop = this.logEl.scrollHeight;
  }
}

window.DebugPanel = new DebugPanel();
