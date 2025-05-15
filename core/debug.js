// v1.1.0 [Enhanced Debug Panel: LCARS style, expanded size, fixed toggle]
export class DebugPanel {
  constructor() {
    const container = document.createElement('div');
    container.id = 'debug-panel';
    container.className = 'open';

    container.innerHTML = [
      '<div id="debug-toggle">⚙️ DEBUG CONSOLE</div>',
      '<div id="debug-log"></div>'
    ].join('');

    document.body.appendChild(container);

    document.getElementById('debug-toggle').onclick = () => {
      container.classList.toggle('open');
    };
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
