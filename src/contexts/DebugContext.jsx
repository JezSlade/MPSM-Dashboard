import React, { createContext, useContext, useState } from 'react';

const DebugContext = createContext();

export const DebugProvider = ({ children }) => {
  const [logs, setLogs] = useState([]);
  const [enabled, setEnabled] = useState(true);

  const appendLog = (level, message) => {
    const timestamp = new Date().toISOString();
    setLogs(prev => [...prev, { timestamp, level, message }]);
  };

  const log = (msg) => appendLog('LOG', msg);
  const warn = (msg) => appendLog('WARN', msg);
  const error = (msg) => appendLog('ERROR', msg);
  const toggle = () => setEnabled(e => !e);
  const clear = () => setLogs([]);

  return (
    <DebugContext.Provider value={{ logs, enabled, log, warn, error, toggle, clear }}>
      {children}
    </DebugContext.Provider>
  );
};

export const useDebug = () => {
  const context = useContext(DebugContext);
  if (!context) throw new Error('useDebug must be used within DebugProvider');
  return context;
};
