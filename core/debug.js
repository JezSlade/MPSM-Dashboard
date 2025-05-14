// v1.0.0 [Init: Debug Panel]
export class DebugPanel {
  constructor() {
    this.container = document.createElement('div');
    this.container.id = 'debug-panel';
    this.container.innerHTML = \`
      <div id="debug-toggle">⚙️</div>
      <div id="debug-log"></div>
    \`;
    document.body.appendChild(this.container);
    document.getElementById('debug-toggle').onclick = () => {
      this.container.classList.toggle('open');
    };
  }

  logEvent(event, payload) {
    const log = document.getElementById('debug-log');
    const div = document.createElement('div');
    div.className = 'log-entry';
    div.innerText = \`[event] \${event}: \${JSON.stringify(payload)}\`;
    log.prepend(div);
  }

  logError(msg, error) {
    const log = document.getElementById('debug-log');
    const div = document.createElement('div');
    div.className = 'log-entry error';
    div.innerText = \`[error] \${msg}: \${error?.message || error}\`;
    log.prepend(div);
  }
}

window.DebugPanel = new DebugPanel();
