// v1.3.0 [Production Debug Toggle + Panel Visibility]
export class DebugPanel {
  constructor() {
    const toggle = document.createElement('button');
    toggle.id = 'debug-toggle';
    toggle.innerText = 'DEBUG';
    toggle.title = 'Toggle debug panel';
    toggle.onclick = () => {
      const panel = document.getElementById('debug-panel');
      panel.classList.toggle('visible');
    };

    const panel = document.createElement('div');
    panel.id = 'debug-panel';
    panel.classList.add('visible'); // Start visible

    const log = document.createElement('div');
    log.id = 'debug-log';

    panel.appendChild(log);
    document.body.appendChild(panel);
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
