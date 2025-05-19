// v1.2.1 [LCARS Toggle: Glowing Panel Control + Highlight Accents]
export class DebugPanel {
  constructor() {
    const container = document.createElement('div');
    container.id = 'debug-panel';
    container.classList.add('open');

    const toggle = document.createElement('div');
    toggle.id = 'debug-toggle';
    toggle.innerHTML = '<span class="lcars-glow">☰</span> LCARS DEBUG';

    const log = document.createElement('div');
    log.id = 'debug-log';

    toggle.onclick = () => {
      container.classList.toggle('open');
    };

    container.appendChild(toggle);
    container.appendChild(log);
    document.body.appendChild(container);
  }

  logEvent(event, payload) {
    this._append(`[event] ${event}: ${JSON.stringify(payload)}`);
  }

  logError(msg, error) {
    this._append(`[error] ${msg}: ${error?.message || error}`, true);
  }

  _append(text, isError = false) {
    const log = document.getElementById('debug-log');
    const entry = document.createElement('div');
    entry.className = 'log-entry' + (isError ? ' error' : '');
    entry.innerText = text;
    log.appendChild(entry);
    log.scrollTop = log.scrollHeight;
  }
}

window.DebugPanel = new DebugPanel();
