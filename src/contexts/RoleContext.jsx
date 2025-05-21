// src/contexts/RoleContext.jsx
import React, { createContext, useContext, useState, useEffect } from 'react';
import { useDebug } from './DebugContext';

const LOCAL_STORAGE_KEY = 'MPSM_Roles';

const defaultRoles = [
  { name: 'Admin', description: 'Full access to all features' },
  { name: 'Dealer', description: 'Dealer-level access' },
  { name: 'Service', description: 'Service technicians access' },
  { name: 'Sales', description: 'Sales and analytics access' }
];

const RoleContext = createContext();

export const RoleProvider = ({ children }) => {
  const debug = useDebug();

  const [roles, setRoles] = useState(() => {
    try {
      const stored = localStorage.getItem(LOCAL_STORAGE_KEY);
      if (stored) return JSON.parse(stored);
    } catch (e) {
      debug.error(`RoleContext: Failed to load roles from storage: ${e.message}`);
    }
    return defaultRoles;
  });

  useEffect(() => {
    try {
      localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(roles));
      debug.log('RoleContext: Roles saved to localStorage');
    } catch (e) {
      debug.error(`RoleContext: Failed to save roles: ${e.message}`);
    }
  }, [roles, debug]);

  const addRole = (role) => {
    if (roles.find(r => r.name.toLowerCase() === role.name.toLowerCase())) {
      throw new Error('Role name must be unique');
    }
    setRoles((prev) => [...prev, role]);
    debug.log(`RoleContext: Role added: ${role.name}`);
  };

  const editRole = (roleName, updates) => {
    setRoles((prev) =>
      prev.map(r => (r.name === roleName ? { ...r, ...updates } : r))
    );
    debug.log(`RoleContext: Role edited: ${roleName}`);
  };

  const removeRole = (roleName) => {
    setRoles((prev) => prev.filter(r => r.name !== roleName));
    debug.log(`RoleContext: Role removed: ${roleName}`);
  };

  return (
    <RoleContext.Provider value={{ roles, addRole, editRole, removeRole }}>
      {children}
    </RoleContext.Provider>
  );
};

export const useRole = () => {
  const context = useContext(RoleContext);
  if (!context) throw new Error('useRole must be used within RoleProvider');
  return context;
};
