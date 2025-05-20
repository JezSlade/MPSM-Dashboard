/**
 * v1.1.1 [Named + default export; toggle & replay buffer]
 */
import { get } from './dom.js';

const panel    = get('debug-panel');
const logsWrap = get('debug-logs');
const toggleEl = get('debug-toggle');
const closeEl  = get('debug-close');

let buffer = [];
let visible = false;

// build panel once DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  toggleEl?.addEventListener('change', () => debug.toggle());
  closeEl?.addEventListener('click', () => {
    toggleEl.checked = false;
    debug.toggle();
  });
});

function _append({ ts, level, msg }) {
  if (!logsWrap) return;
  const entry = document.createElement('div');
  entry.textContent = `[${ts}] [${level}] ${msg}`;
  entry.classList.add('debug-entry', level);
  logsWrap.appendChild(entry);
  logsWrap.scrollTop = logsWrap.scrollHeight;
}

export const debug = {
  log(msg)   { const e={ts:new Date().toISOString(),level:'LOG',msg}; buffer.push(e); if(visible)_append(e); console.log(msg); },
  warn(msg)  { const e={ts:new Date().toISOString(),level:'WARN',msg}; buffer.push(e); if(visible)_append(e); console.warn(msg); },
  error(msg) { const e={ts:new Date().toISOString(),level:'ERROR',msg}; buffer.push(e); if(visible)_append(e); console.error(msg); },
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
