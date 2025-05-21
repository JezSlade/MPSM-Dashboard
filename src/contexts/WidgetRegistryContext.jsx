// src/contexts/WidgetRegistryContext.jsx
import React, { createContext, useContext, useState } from 'react';
import { useDebug } from './DebugContext';

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
  const debug = useDebug();

  const [widgets] = useState(defaultWidgets);

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

export const useWidgetRegistry = () => {
  const context = useContext(WidgetRegistryContext);
  if (!context) throw new Error('useWidgetRegistry must be used within WidgetRegistryProvider');
  return context;
};
