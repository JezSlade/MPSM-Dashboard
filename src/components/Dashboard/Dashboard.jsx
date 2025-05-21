/**
 * Dashboard.jsx
 * v1.0.0
 * Main dashboard container rendering snapshots or full widget views.
 */

import React, { useState, Suspense, useMemo } from 'react';
import { useWidgetRegistry } from '../../contexts/WidgetRegistryContext';
import { useAuth } from '../../contexts/AuthContext';
import './Dashboard.css';

// Import your widgets here or dynamically via React.lazy for performance
import DashboardWidget from '../widgets/DashboardWidget/DashboardWidget';
import AdminWidget from '../widgets/AdminWidget/AdminWidget';
import CustomersWidget from '../widgets/CustomersWidget/CustomersWidget';
import DevicesWidget from '../widgets/DevicesWidget/DevicesWidget';
import AlertsWidget from '../widgets/AlertsWidget/AlertsWidget';
import ConsumablesWidget from '../widgets/ConsumablesWidget/ConsumablesWidget';

// Widget ID → Component map
const widgetComponents = {
  dashboard: DashboardWidget,
  admin: AdminWidget,
  customers: CustomersWidget,
  devices: DevicesWidget,
  alerts: AlertsWidget,
  consumables: ConsumablesWidget
};

export default function Dashboard({ selectedWidgetId }) {
  const { currentUser } = useAuth();
  const { getWidgetsForRole } = useWidgetRegistry();

  const allowedWidgets = useMemo(() => {
    if (!currentUser) return [];
    return getWidgetsForRole(currentUser.role);
  }, [currentUser, getWidgetsForRole]);

  const WidgetComponent = widgetComponents[selectedWidgetId] || DashboardWidget;

  return (
    <div className="dashboard-container">
      {/* Optional: List of snapshots or summary cards here */}
      <Suspense fallback={<div>Loading widget...</div>}>
        <WidgetComponent />
      </Suspense>
    </div>
  );
}
