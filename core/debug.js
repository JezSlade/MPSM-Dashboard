/**
 * v1.1.2 [Fix: panel visible by default; remove 'minimized' on load]
 */
import { get } from './dom.js';

const panel    = get('debug-panel');
const logsWrap = get('debug-logs');
const toggleEl = get('debug-toggle');
const closeEl  = get('debug-close');

let buffer = [];
let visible = true; // start visible

// Append helper
function _append({ ts, level, msg }) {
  if (!logsWrap) return;
  const entry = document.createElement('div');
  entry.textContent = `[${ts}] [${level}] ${msg}`;
  entry.classList.add('debug-entry', level);
  logsWrap.appendChild(entry);
  logsWrap.scrollTop = logsWrap.scrollHeight;
}

// Public API
export const debug = {
  log(msg)   { const e = { ts:new Date().toISOString(), level:'LOG',   msg }; buffer.push(e); if(visible) _append(e); console.log(msg); },
  warn(msg)  { const e = { ts:new Date().toISOString(), level:'WARN',  msg }; buffer.push(e); if(visible) _append(e); console.warn(msg); },
  error(msg) { const e = { ts:new Date().toISOString(), level:'ERROR', msg }; buffer.push(e); if(visible) _append(e); console.error(msg); },
  toggle() {
    visible = !visible;
    if (panel) {
      panel.classList.toggle('minimized', !visible);
      if (visible) {
        logsWrap.innerHTML = '';
        buffer.forEach(_append);
      }
    }
  }
};
export default debug;

// Set initial panel state on load
document.addEventListener('DOMContentLoaded', () => {
  // Ensure panel shows immediately
  if (panel) panel.classList.remove('minimized');
  // Ensure toggle matches state
  if (toggleEl) toggleEl.checked = true;

  // Wire up toggle & close after we've set initial state
  toggleEl?.addEventListener('change', () => debug.toggle());
  closeEl?.addEventListener('click', () => {
    toggleEl.checked = false;
    debug.toggle();
  });

  // Flush any pre-DOM logs
  buffer.forEach(_append);
});
