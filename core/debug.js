// v2.1.0 [LCARS Toggle Refined, Debug Starts Hidden, Logging Restored]
export class DebugPanel {
  constructor() {
    const container = document.createElement('div');
    container.id = 'debug-panel';

    const toggle = document.createElement('button');
    toggle.id = 'debug-toggle';
    toggle.innerText = 'DEBUG';
    toggle.title = 'Toggle debug panel';
    toggle.onclick = () => {
      container.classList.toggle('visible');
    };

    const log = document.createElement('div');
    log.id = 'debug-log';

    container.appendChild(log);
    document.body.appendChild(container);
    document.body.appendChild(toggle);
  }

  logEvent(event, payload) {
    this._append(`[event] ${event}: ${JSON.stringify(payload)}`);
  }

  logError(msg, error) {
    this._append(`[error] ${msg}: ${error?.message || error}`, true);
  }

  _append(text, isError = false) {
    const log = document.getElementById('debug-log');
    if (!log) return;
    const entry = document.createElement('div');
    entry.className = 'log-entry' + (isError ? ' error' : '');
    entry.innerText = text;
    log.appendChild(entry);
    log.scrollTop = log.scrollHeight;
  }
}

window.DebugPanel = new DebugPanel();
