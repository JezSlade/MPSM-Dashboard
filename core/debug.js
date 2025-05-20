// core/debug.js
// v1.0.0 [Refactor: Fixed syntax, added toggle button, baked in logging]

// Panel and toggle references
let debugPanel;
let debugToggleBtn;

/**
 * Create the debug toggle button and panel, hidden by default.
 */
function createDebugPanel() {
  // 1) Toggle button
  debugToggleBtn = document.createElement('button');
  debugToggleBtn.id = 'debug-toggle';
  debugToggleBtn.textContent = '🖧 Debug';
  Object.assign(debugToggleBtn.style, {
    position: 'fixed',
    top: '1rem',
    right: '1rem',
    zIndex: '10000',
    padding: '0.5rem 1rem',
    background: '#003366',
    color: '#fff',
    border: 'none',
    borderRadius: '4px',
    cursor: 'pointer'
  });
  debugToggleBtn.addEventListener('click', toggleDebug);
  document.body.appendChild(debugToggleBtn);

  // 2) Debug panel
  debugPanel = document.createElement('div');
  debugPanel.id = 'debug-panel';
  Object.assign(debugPanel.style, {
    position: 'fixed',
    bottom: '0',
    left: '0',
    right: '0',
    maxHeight: '200px',
    backgroundColor: 'rgba(0,0,0,0.85)',
    color: '#0f0',
    fontFamily: 'monospace',
    fontSize: '12px',
    overflowY: 'auto',
    padding: '0.5rem',
    display: 'none',
    zIndex: '9999'
  });
  document.body.appendChild(debugPanel);
}

/**
 * Toggle the visibility of the debug panel.
 */
function toggleDebug() {
  if (!debugPanel) return;
  debugPanel.style.display = debugPanel.style.display === 'none' ? 'block' : 'none';
}

/**
 * Format a timestamped message.
 * @param {string} level  'log' | 'warn' | 'error'
 * @param {string} msg
 */
function formatMessage(level, msg) {
  const ts = new Date().toISOString();
  return `[${ts}] [${level.toUpperCase()}] ${msg}`;
}

/**
 * Append a line of text to the debug panel.
 * @param {string} text  already-formatted message
 * @param {string} color optional CSS color override
 */
function appendLine(text, color) {
  if (!debugPanel) return;
  const line = document.createElement('div');
  line.textContent = text;
  if (color) line.style.color = color;
  debugPanel.appendChild(line);
  debugPanel.scrollTop = debugPanel.scrollHeight;
}

/**
 * Public API
 */
function log(msg) {
  const m = formatMessage('log', msg);
  console.log(m);
  appendLine(m);
}

function warn(msg) {
  const m = formatMessage('warn', msg);
  console.warn(m);
  appendLine(m, 'yellow');
}

function error(msg) {
  const m = formatMessage('error', msg);
  console.error(m);
  appendLine(m, 'red');
}

// Initialize panel & toggle on DOM ready
document.addEventListener('DOMContentLoaded', createDebugPanel);

// Catch uncaught errors and promise rejections
window.addEventListener('error', evt => {
  error(`Uncaught Error: ${evt.message} @ ${evt.filename}:${evt.lineno}`);
});
window.addEventListener('unhandledrejection', evt => {
  error(`Unhandled Promise Rejection: ${evt.reason}`);
});

// Export default debug interface
export default {
  log,
  warn,
  error,
  toggle: toggleDebug
};
