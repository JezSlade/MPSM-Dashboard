// v1.0.1 [Fix: Properly Render Object Errors in Debug Panel]
export class DebugPanel {
  constructor() {
    const container = document.createElement('div');
    container.id = 'debug-panel';
    container.className = 'open';

    container.innerHTML = `
      <div id="debug-toggle">⚙️ DEBUG</div>
      <div id="debug-log"></div>
    `;

    document.body.appendChild(container);

    document.getElementById('debug-toggle').onclick = () => {
      container.classList.toggle('open');
    };
  }

  logEvent(event, payload) {
    this._append(`[event] ${event}: ${JSON.stringify(payload)}`);
  }

  logError(msg, error) {
    const log = document.getElementById('debug-log');
    const div = document.createElement('div');
    div.className = 'log-entry error';

    const payload =
      typeof error === 'object'
        ? JSON.stringify(error, null, 2)
        : String(error);

    div.innerText = `[error] ${msg}:\n${payload}`;
    log.prepend(div);
  }

  _append(text) {
    const log = document.getElementById('debug-log');
    const entry = document.createElement('div');
    entry.className = 'log-entry';
    entry.innerText = text;
    log.prepend(entry);
  }
}

window.DebugPanel = new DebugPanel();
