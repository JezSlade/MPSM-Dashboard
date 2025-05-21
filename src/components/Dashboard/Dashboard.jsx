// src/components/Dashboard/Dashboard.jsx
import React, { useMemo, Suspense } from 'react';
import { useWidgetRegistry } from '../../contexts/WidgetRegistryContext';
import { useAuth } from '../../contexts/AuthContext';
import './Dashboard.css';

import DashboardWidget from '../widgets/DashboardWidget/DashboardWidget';
import AdminWidget from '../widgets/AdminWidget/AdminWidget';
import CustomersWidget from '../widgets/CustomersWidget/CustomersWidget';
import DevicesWidget from '../widgets/DevicesWidget/DevicesWidget';
import AlertsWidget from '../widgets/AlertsWidget/AlertsWidget';
import ConsumablesWidget from '../widgets/ConsumablesWidget/ConsumablesWidget';

const widgetComponents = {
  dashboard: DashboardWidget,
  admin: AdminWidget,
  customers: CustomersWidget,
  devices: DevicesWidget,
  alerts: AlertsWidget,
  consumables: ConsumablesWidget,
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
      <Suspense fallback={<div>Loading widget...</div>}>
        <WidgetComponent />
      </Suspense>
    </div>
  );
}
