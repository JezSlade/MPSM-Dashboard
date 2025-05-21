import React, { useState, useEffect } from 'react';
import { useWidgetRegistry } from '../../contexts/WidgetRegistryContext';
import { useAuth } from '../../contexts/AuthContext';
import { useDebug } from '../../contexts/DebugContext';
import './Sidebar.css';

export default function Sidebar({ onSelectWidget, activeWidgetId }) {
  const { currentUser } = useAuth();
  const { getWidgetsForRole } = useWidgetRegistry();
  const debug = useDebug();

  const [widgets, setWidgets] = useState([]);

  useEffect(() => {
    if (currentUser?.role) {
      const ws = getWidgetsForRole(currentUser.role);
      const dashboard = ws.find(w => w.id === 'dashboard');
      const others = ws.filter(w => w.id !== 'dashboard');
      setWidgets(dashboard ? [dashboard, ...others] : ws);
      debug.log(`Sidebar: Loaded widgets for role ${currentUser.role}`);
    } else {
      setWidgets([]);
      debug.warn('Sidebar: No current user or role');
    }
  }, [currentUser, getWidgetsForRole, debug]);

  return (
    <nav className="sidebar">
      <ul>
        {widgets.map(widget => (
          <li
            key={widget.id}
            className={widget.id === activeWidgetId ? 'active' : ''}
            onClick={() => {
              onSelectWidget(widget.id);
              debug.log(`Sidebar: Selected widget ${widget.id}`);
            }}
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
