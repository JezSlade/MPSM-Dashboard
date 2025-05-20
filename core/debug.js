// core/debug.js
// v1.0.5 [Fix: Named export and toggle hide/show]

class Debug {
  constructor() {
    this.logs = [];
    this.panel = null;
    this.isVisible = false;
    document.addEventListener('DOMContentLoaded', () => {
      this._createPanel();
      // Replay buffered logs
      this.logs.forEach(entry => this._append(entry));
    });
  }

  _createPanel() {
    // Create the debug panel container
    this.panel = document.createElement('div');
    this.panel.id = 'debug-panel';
    Object.assign(this.panel.style, {
      position: 'fixed',
      bottom: '0',
      left: '0',
      right: '0',
      maxHeight: '40vh',
      overflowY: 'auto',
      background: 'rgba(0,0,0,0.85)',
      color: '#0f0',
      fontFamily: 'monospace',
      fontSize: '12px',
      padding: '8px',
      zIndex: '9999',
      display: 'none' // start hidden
    });
    document.body.appendChild(this.panel);
  }

  _append({ timestamp, level, msg }) {
    if (!this.panel) return;
    const line = document.createElement('div');
    line.textContent = `[${timestamp}] [${level}] ${msg}`;
    this.panel.appendChild(line);
    this.panel.scrollTop = this.panel.scrollHeight;
  }

  log(msg)    { this._record('LOG', msg); }
  warn(msg)   { this._record('WARN', msg); }
  error(msg)  { this._record('ERROR', msg); }

  _record(level, msg) {
    const entry = {
      timestamp: new Date().toISOString(),
      level,
      msg
    };
    this.logs.push(entry);
    if (this.isVisible) this._append(entry);
  }

  toggle() {
    if (!this.panel) return;
    this.isVisible = !this.isVisible;
    this.panel.style.display = this.isVisible ? 'block' : 'none';
  }
}

// Export a singleton debug instance
export const debug = new Debug();
