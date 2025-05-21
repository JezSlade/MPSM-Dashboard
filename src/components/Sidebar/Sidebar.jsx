/**
 * Sidebar.jsx
 * v1.0.0
 * Left navigation sidebar displaying Dashboard and role-allowed widgets.
 * Highlights active widget.
 * Allows switching main content area by selecting widgets.
 */

import React, { useState, useEffect } from 'react';
import { useWidgetRegistry } from '../../contexts/WidgetRegistryContext';
import { useAuth } from '../../contexts/AuthContext';
import './Sidebar.css'; // Assume scoped styles with neumorphic design

export default function Sidebar({ onSelectWidget, activeWidgetId }) {
  const { currentUser } = useAuth();
  const { getWidgetsForRole } = useWidgetRegistry();

  const [widgets, setWidgets] = useState([]);

  useEffect(() => {
    if (currentUser?.role) {
      const ws = getWidgetsForRole(currentUser.role);
      // Dashboard widget always first
      const dashboard = ws.find(w => w.id === 'dashboard');
      const others = ws.filter(w => w.id !== 'dashboard');
      setWidgets(dashboard ? [dashboard, ...others] : ws);
    } else {
      setWidgets([]);
    }
  }, [currentUser, getWidgetsForRole]);

  return (
    <nav className="sidebar">
      <ul>
        {widgets.map(widget => (
          <li
            key={widget.id}
            className={widget.id === activeWidgetId ? 'active' : ''}
            onClick={() => onSelectWidget(widget.id)}
            role="button"
            tabIndex={0}
            onKeyDown={e => { if (e.key === 'Enter') onSelectWidget(widget.id); }}
            aria-label={`Open ${widget.name} widget`}
          >
            {widget.name}
          </li>
        ))}
      </ul>
    </nav>
  );
}
