/**
 * core/debug.js
 * v1.0.2 [Debug: Auto-inject UI panel & toggle, fix visibility]
 */

const debug = (() => {
  // Create toggle
  const toggle = document.createElement('button');
  toggle.id = 'debug-toggle';
  toggle.textContent = '🛠️ Debug';
  Object.assign(toggle.style, {
    position: 'fixed',
    top: '10px',
    right: '10px',
    zIndex: '9999',
    padding: '6px 12px',
    background: '#005f9e',
    color: '#fff',
    border: 'none',
    borderRadius: '4px',
    cursor: 'pointer',
    fontSize: '14px'
  });

  // Create panel
  const panel = document.createElement('div');
  panel.id = 'debug-panel';
  Object.assign(panel.style, {
    display: 'none',
    position: 'fixed',
    bottom: '0',
    left: '0',
    width: '100%',
    maxHeight: '200px',
    background: 'rgba(0,0,0,0.85)',
    color: '#0ff',
    overflowY: 'auto',
    fontFamily: 'monospace',
    fontSize: '12px',
    padding: '8px',
    boxSizing: 'border-box',
    zIndex: '9998'
  });

  const logList = document.createElement('ul');
  logList.id = 'debug-log';
  Object.assign(logList.style, {
    margin: '0',
    padding: '0',
    listStyle: 'none'
  });
  panel.appendChild(logList);

  // Toggle behavior
  toggle.addEventListener('click', () => {
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
  });

  // Inject into DOM immediately
  document.body.appendChild(toggle);
  document.body.appendChild(panel);

  function append(type, msg) {
    const li = document.createElement('li');
    li.textContent = `[${type.toUpperCase()}] ${msg}`;
    logList.appendChild(li);
    panel.scrollTop = panel.scrollHeight;
  }

  return {
    log(msg)   { console.log(msg);   append('log', msg); },
    warn(msg)  { console.warn(msg);  append('warn', msg); },
    error(msg) { console.error(msg); append('err', msg); }
  };
})();

export default debug;
