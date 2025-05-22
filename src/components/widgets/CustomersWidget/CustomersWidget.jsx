// src/components/widgets/CustomersWidget/CustomersWidget.jsx
import React, { useState, useEffect } from 'react';
import { useDebug } from '../../../contexts/DebugContext';
import './CustomersWidget.css';

export default function CustomersWidget() {
  const debug = useDebug();
  const [customers, setCustomers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchCustomers() {
      setLoading(true);
      setError(null);
      try {
        const res = await fetch('get_customers.php');
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!Array.isArray(data.Result)) throw new Error('Invalid response');
        setCustomers(data.Result);
        debug.log(`CustomersWidget: fetched ${data.Result.length} customers`);
      } catch (err) {
        debug.error(`CustomersWidget: fetch failed - ${err.message}`);
        setError(err.message);
      } finally {
        setLoading(false);
      }
    }
    fetchCustomers();
  }, [debug]);

  return (
    <div className="customers-widget">
      <h2>Customers</h2>
      <div className="snapshot">
        <span>{loading ? 'Loading...' : `${customers.length} Customers`}</span>
      </div>
      {error && <div className="error">Error: {error}</div>}
      {!error && (
        <ul className="customer-list">
          {customers.map(c => (
            <li key={c.Code}>
              <strong>{c.Description}</strong> (Code: {c.Code})
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
