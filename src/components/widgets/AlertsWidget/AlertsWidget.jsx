// src/components/widgets/AlertsWidget/AlertsWidget.jsx
import React, { useState, useEffect } from 'react';
import { useDebug } from '../../../contexts/DebugContext';
import './AlertsWidget.css';

export default function AlertsWidget() {
  const debug = useDebug();
  const [alerts, setAlerts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchAlerts() {
      setLoading(true);
      setError(null);
      try {
        const res = await fetch('get_alerts.php'); // Adjust endpoint as needed
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!Array.isArray(data.Result)) throw new Error('Invalid response');
        setAlerts(data.Result);
        debug.log(`AlertsWidget: fetched ${data.Result.length} alerts`);
      } catch (err) {
        debug.error(`AlertsWidget: fetch failed - ${err.message}`);
        setError(err.message);
      } finally {
        setLoading(false);
      }
    }
    fetchAlerts();
  }, [debug]);

  return (
    <div className="alerts-widget">
      <h2>Active Alerts</h2>
      <div className="snapshot">
        <span>{loading ? 'Loading...' : `${alerts.length} Active Alerts`}</span>
      </div>
      {error && <div className="error">Error: {error}</div>}
      {!error && (
        <ul className="alert-list">
          {alerts.map(alert => (
            <li key={alert.Id}>
              <strong>{alert.DeviceName || 'Unknown Device'}</strong>: {alert.Message || 'No message'}
              <br />
              <small>{new Date(alert.Timestamp).toLocaleString()}</small>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
