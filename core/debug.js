// core/debug.js
// v1.0.4 — now also provides a default export so both
// `import debug from './debug.js'` and `import { debug }…` work

let debugMode = false;
const logPanel = () => document.getElementById('debug-log');

function toggleDebug(mode) {
  debugMode = mode;
  const panel = logPanel();
  if (panel) {
    panel.hidden = !mode;
    panel.innerHTML = '';
  }
}

function log(msg) {
  if (!debugMode) return;
  const panel = logPanel();
  if (!panel) return;
  const ts = new Date().toISOString();
  const entry = document.createElement('div');
  entry.textContent = `[${ts}] [LOG] ${msg}`;
  panel.appendChild(entry);
}

function warn(msg) {
  if (!debugMode) return;
  const panel = logPanel();
  if (!panel) return;
  const ts = new Date().toISOString();
  const entry = document.createElement('div');
  entry.textContent = `[${ts}] [WARN] ⚠️ ${msg}`;
  panel.appendChild(entry);
}

function error(msg) {
  if (!debugMode) return;
  const panel = logPanel();
  if (!panel) return;
  const ts = new Date().toISOString();
  const entry = document.createElement('div');
  entry.textContent = `[${ts}] [ERROR] ❌ ${msg}`;
  panel.appendChild(entry);
}

export const debug = { toggleDebug, log, warn, error };
// <— ADD THIS:
export default debug;
