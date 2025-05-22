// src/components/widgets/DashboardWidget/DashboardWidget.jsx
import React, { useEffect, useState } from 'react';
import './DashboardWidget.css';
import { useWidgetRegistry } from '../../../contexts/WidgetRegistryContext';
import { useAuth } from '../../../contexts/AuthContext';
import { useDebug } from '../../../contexts/DebugContext';

const snapshotFetchers = {
  customers: async () => ({ count: 34 }),
  devices: async () => ({ errors: 5, total: 120 }),
  alerts: async () => ({ active: 7 }),
  consumables: async () => ({ lowStock: 3 }),
  admin: async () => ({ users: 4, roles: 4 }),
};

export default function DashboardWidget({ onCardClick }) {
  const { currentUser } = useAuth();
  const { getWidgetsForRole } = useWidgetRegistry();
  const debug = useDebug();

  const [snapshots, setSnapshots] = useState({});

  useEffect(() => {
    async function fetchSnapshots() {
      if (!currentUser) return;
      const widgets = getWidgetsForRole(currentUser.role);
      const data = {};
      for (const w of widgets) {
        try {
          if (snapshotFetchers[w.id]) {
            data[w.id] = await snapshotFetchers[w.id]();
            debug.log(`DashboardWidget: fetched snapshot for ${w.id}`);
          }
        } catch (err) {
          debug.error(`DashboardWidget: failed snapshot ${w.id} - ${err.message}`);
        }
      }
      setSnapshots(data);
    }
    fetchSnapshots();
  }, [currentUser, getWidgetsForRole, debug]);

  const widgets = getWidgetsForRole(currentUser?.role || '');

  return (
    <div className="dashboard-widget">
      {widgets.map((widget) => {
        const snapshot = snapshots[widget.id];
        return (
          <div
            key={widget.id}
            className="dashboard-card"
            onClick={() => onCardClick && onCardClick(widget.id)}
            tabIndex={0}
            onKeyDown={e => { if (e.key === 'Enter' && onCardClick) onCardClick(widget.id); }}
            role="button"
            aria-label={`Open ${widget.name} widget`}
          >
            <h3>{widget.name}</h3>
            <p>{widget.description}</p>
            <div className="dashboard-snapshot">
              {widget.id === 'customers' && snapshot ? (
                <span>{snapshot.count} Customers</span>
              ) : widget.id === 'devices' && snapshot ? (
                <span>{snapshot.errors} Errors / {snapshot.total} Devices</span>
              ) : widget.id === 'alerts' && snapshot ? (
                <span>{snapshot.active} Active Alerts</span>
              ) : widget.id === 'consumables' && snapshot ? (
                <span>{snapshot.lowStock} Low Stock</span>
              ) : widget.id === 'admin' && snapshot ? (
                <span>{snapshot.users} Users / {snapshot.roles} Roles</span>
              ) : (
                <span>Loading...</span>
              )}
            </div>
          </div>
        );
      })}
    </div>
  );
}
