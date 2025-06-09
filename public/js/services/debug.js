// public/js/services/debug.js
// ------------------------------------------------------------------
// DebugService: intercepts fetch, JS errors, and unhandled rejections.
// Logs everything to a slide-out panel and console.debug.
// ------------------------------------------------------------------
class DebugService {
  static init() {
    this.createPanel();
    this.hookFetch();
    window.addEventListener('error', e => {
      this.log('ERROR', e.message, { file: e.filename, line: e.lineno });
    });
    window.addEventListener('unhandledrejection', e => {
      this.log('ERROR', e.reason, {});
    });
  }

  static createPanel() {
    this.panel = document.createElement('div');
    this.panel.id = 'debug-panel';
    Object.assign(this.panel.style, {
      position: 'fixed', bottom: '0', left: '0',
      width: '100%', maxHeight: '40%', overflowY: 'auto',
      backdropFilter: 'blur(8px)', background: 'rgba(25,25,25,0.75)',
      color: '#0f0', fontFamily: 'monospace', fontSize: '12px',
      zIndex: '10000', transform: 'translateY(100%)',
      transition: 'transform 0.3s ease-in-out'
    });
    this.panel.innerHTML = `
      <div style="padding:5px;text-align:right;">
        <button id="debug-toggle">🐞</button>
        <button id="debug-clear">Clear</button>
      </div>
      <ul id="debug-list" style="list-style:none;padding:0 10px;margin:0;"></ul>
    `;
    document.body.appendChild(this.panel);

    document.getElementById('debug-toggle').onclick = () => {
      if (this.panel.style.transform === 'translateY(0%)') {
        this.panel.style.transform = 'translateY(100%)';
      } else {
        this.panel.style.transform = 'translateY(0%)';
      }
    };
    document.getElementById('debug-clear').onclick = () => {
      document.getElementById('debug-list').innerHTML = '';
    };
  }

  static hookFetch() {
    const origFetch = window.fetch;
    window.fetch = (...args) => {
      const [url, opts] = args;
      const start = Date.now();
      return origFetch(...args)
        .then(res => {
          const time = Date.now() - start;
          res.clone().text().then(body => {
            this.log('FETCH', `${res.status} ${res.url} (${time}ms)`, { request: opts, response: body });
          });
          return res;
        })
        .catch(err => {
          this.log('FETCH-ERROR', err.toString(), {});
          throw err;
        });
    };
  }

  static log(type, msg, meta) {
    console.debug(`[${type}]`, msg, meta);
    const li = document.createElement('li');
    li.textContent = `[${(new Date()).toISOString()}] ${type}: ${msg}`;
    document.getElementById('debug-list').appendChild(li);
  }
}

// Initialize as soon as possible
window.addEventListener('DOMContentLoaded', () => DebugService.init());
