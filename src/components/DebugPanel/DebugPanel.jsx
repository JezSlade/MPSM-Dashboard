/**
 * DebugPanel.jsx
 * v1.0.0
 * Collapsible debug log panel showing logs from DebugContext.
 * Toggleable visibility and auto-scrolls to newest entries.
 */

import React, { useEffect, useRef } from 'react';
import { useDebug } from '../../contexts/DebugContext';
import './DebugPanel.css';

export default function DebugPanel() {
  const { logs, enabled, toggle, clear } = useDebug();
  const panelRef = useRef(null);

  // Scroll to bottom when logs update
  useEffect(() => {
    if (panelRef.current) {
      panelRef.current.scrollTop = panelRef.current.scrollHeight;
    }
  }, [logs]);

  if (!enabled) {
    return (
      <button className="debug-toggle-btn" onClick={toggle}>
        Show Debug
      </button>
    );
  }

  return (
    <div className="debug-panel">
      <div className="debug-header">
        <span>Debug Logs</span>
        <div className="debug-controls">
          <button onClick={clear}>Clear</button>
          <button onClick={toggle}>Hide</button>
        </div>
      </div>
      <div className="debug-log" ref={panelRef} role="log" aria-live="polite">
        {logs.map(({ timestamp, level, message }, idx) => (
          <div key={idx} className={`debug-entry ${level.toLowerCase()}`}>
            [{new Date(timestamp).toLocaleTimeString()}] [{level}] {message}
          </div>
        ))}
      </div>
    </div>
  );
}
