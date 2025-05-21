// src/components/widgets/ConsumablesWidget/ConsumablesWidget.jsx
import React, { useState, useEffect } from 'react';
import { useDebug } from '../../../contexts/DebugContext';
import './ConsumablesWidget.css';

export default function ConsumablesWidget() {
  const debug = useDebug();
  const [consumables, setConsumables] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchConsumables() {
      setLoading(true);
      setError(null);
      try {
        const res = await fetch('get_consumables.php'); // Adjust as needed
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!Array.isArray(data.Result)) throw new Error('Invalid response');
        setConsumables(data.Result);
        debug.log(`ConsumablesWidget: fetched ${data.Result.length} consumables`);
      } catch (err) {
        debug.error(`ConsumablesWidget: fetch failed - ${err.message}`);
        setError(err.message);
      } finally {
        setLoading(false);
      }
    }
    fetchConsumables();
  }, [debug]);

  const lowStockCount = consumables.filter(c => c.Level < c.LowThreshold).length;

  return (
    <div className="consumables-widget">
      <h2>Consumables</h2>
      <div className="snapshot">
        <span>{loading ? 'Loading...' : `${lowStockCount} Low Stock Items`}</span>
      </div>
      {error && <div className="error">Error: {error}</div>}
      {!error && (
        <table className="consumables-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Level</th>
              <th>Low Threshold</th>
              <th>Device</th>
            </tr>
          </thead>
          <tbody>
            {consumables.map(c => (
              <tr key={c.Id}>
                <td>{c.Name}</td>
                <td>{c.Level}</td>
                <td>{c.LowThreshold}</td>
                <td>{c.DeviceName || 'N/A'}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}
