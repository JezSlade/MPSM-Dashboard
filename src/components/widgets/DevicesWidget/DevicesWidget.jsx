/**
 * DevicesWidget.jsx
 * v1.0.0
 * Displays a list of devices with snapshot error counts.
 * Fetches data from get_devices.php backend.
 * Robust debug logging and error handling.
 */

import React, { useState, useEffect } from 'react';
import './DevicesWidget.css';
import { useDebug } from '../../../contexts/DebugContext';

export default function DevicesWidget() {
  const debug = useDebug();

  const [devices, setDevices] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Fetch devices on mount
  useEffect(() => {
    async function fetchDevices() {
      setLoading(true);
      setError(null);
      try {
        const res = await fetch('get_devices.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ customerId: null }) // You can add customer filter later
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!Array.isArray(data.Result)) throw new Error('Invalid response');
        setDevices(data.Result);
        debug.log(`DevicesWidget: fetched ${data.Result.length} devices`);
      } catch (err) {
        debug.error(`DevicesWidget: fetch failed - ${err.message}`);
        setError(err.message);
      } finally {
        setLoading(false);
      }
    }
    fetchDevices();
  }, [debug]);

  // Compute error count snapshot
  const errorCount = devices.filter(d => d.AlertOnDisplay || d.IsAlertGenerator || d.IsOffline).length;

  return (
    <div className="devices-widget">
      <h2>Devices</h2>
      <div className="snapshot">
        <span>{loading ? 'Loading...' : `${errorCount} Devices with Errors`}</span>
      </div>
      {error && <div className="error">Error: {error}</div>}
      {!error && (
        <table className="devices-table">
          <thead>
            <tr>
              <th>SEID</th>
              <th>Brand</th>
              <th>Model</th>
              <th>Serial Number</th>
              <th>IP Address</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {devices.map(device => (
              <tr key={device.AssetNumber || device.ExternalIdentifier || device.Id}>
                <td>{device.AssetNumber || device.ExternalIdentifier || 'N/A'}</td>
                <td>{device.Product?.Brand || 'N/A'}</td>
                <td>{device.Product?.Model || 'N/A'}</td>
                <td>{device.SerialNumber || 'N/A'}</td>
                <td>{device.IpAddress || 'N/A'}</td>
                <td>
                  {device.AlertOnDisplay ? 'Error' : device.IsOffline ? 'Offline' : 'OK'}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}
