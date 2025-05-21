/**
 * DebugContext.jsx
 * v1.0.0
 * Central debug log provider.
 * Provides methods to log info, warnings, errors.
 * Maintains a log history buffer.
 */

import React, { createContext, useContext, useState, useEffect } from 'react';

const DebugContext = createContext();

export const DebugProvider = ({ children }) => {
  const [logs, setLogs] = useState([]);
  const [enabled, setEnabled] = useState(true);

  // Append a log entry with timestamp and level
  const appendLog = (level, message) => {
    const timestamp = new Date().toISOString();
    const entry = { timestamp, level, message };
    setLogs((prev) => [...prev, entry]);
    // Optionally: throttle or limit log size here
  };

  // Public API for logging
  const log = (msg) => appendLog('LOG', msg);
  const warn = (msg) => appendLog('WARN', msg);
  const error = (msg) => appendLog('ERROR', msg);

  // Toggle debug panel visibility
  const toggle = () => setEnabled((e) => !e);

  // Clear logs
  const clear = () => setLogs([]);

  // Expose debug state and methods
  return (
    <DebugContext.Provider
      value={{ logs, enabled, log, warn, error, toggle, clear }}
    >
      {children}
    </DebugContext.Provider>
  );
};

// Hook to use debug context easily
export const useDebug = () => {
  const context = useContext(DebugContext);
  if (!context) throw new Error('useDebug must be used within DebugProvider');
  return context;
};
