/**
 * WidgetRegistryContext.jsx
 * v1.0.0
 * Central registry of all available widgets.
 * Manages role-based widget permissions and configs.
 */

import React, { createContext, useContext, useState } from 'react';
import debug from './DebugContext';

// Example widget metadata structure
// {
//   id: 'customers',
//   name: 'Customers',
//   description: 'Manage customers',
//   rolesAllowed: ['Admin', 'Dealer', 'Service', 'Sales'],
//   component: React.lazy(() => import('../components/widgets/CustomersWidget'))
// }

const defaultWidgets = [
  {
    id: 'dashboard',
    name: 'Dashboard',
    description: 'Overview snapshot dashboard',
    rolesAllowed: ['Admin', 'Dealer', 'Service', 'Sales'],
  },
  {
    id: 'admin',
    name: 'Admin Panel',
    description: 'User and role management',
    rolesAllowed: ['Admin'],
  },
  {
    id: 'customers',
    name: 'Customers',
    description: 'Customer management and info',
    rolesAllowed: ['Admin', 'Dealer', 'Service', 'Sales'],
  },
  {
    id: 'devices',
    name: 'Devices',
    description: 'Device monitoring and management',
    rolesAllowed: ['Admin', 'Dealer', 'Service', 'Sales'],
  },
  {
    id: 'alerts',
    name: 'Alerts',
    description: 'Real-time alerts and notifications',
    rolesAllowed: ['Admin', 'Service'],
  },
  {
    id: 'consumables',
    name: 'Consumables',
    description: 'Consumables usage and inventory',
    rolesAllowed: ['Admin', 'Dealer', 'Service'],
  }
];

const WidgetRegistryContext = createContext();

export const WidgetRegistryProvider = ({ children }) => {
  const [widgets] = useState(defaultWidgets);

  // Get widgets allowed for a given role
  const getWidgetsForRole = (role) => {
    const allowed = widgets.filter(w => w.rolesAllowed.includes(role));
    debug.log(`WidgetRegistry: fetched ${allowed.length} widgets for role: ${role}`);
    return allowed;
  };

  return (
    <WidgetRegistryContext.Provider value={{ widgets, getWidgetsForRole }}>
      {children}
    </WidgetRegistryContext.Provider>
  );
};

// Hook for easy use
export const useWidgetRegistry = () => {
  const context = useContext(WidgetRegistryContext);
  if (!context) throw new Error('useWidgetRegistry must be used within WidgetRegistryProvider');
  return context;
};
