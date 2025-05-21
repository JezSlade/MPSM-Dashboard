/**
 * UserContext.jsx
 * v1.0.0
 * Provides current user data and updates across the app.
 * Separates user data from Auth context for modularity.
 */

import React, { createContext, useContext, useState, useEffect } from 'react';
import debug from './DebugContext';
import { useAuth } from './AuthContext';

const UserContext = createContext();

export const UserProvider = ({ children }) => {
  const { currentUser } = useAuth();
  const [userData, setUserData] = useState(null);

  // Load or update user data when currentUser changes
  useEffect(() => {
    if (!currentUser) {
      setUserData(null);
      debug.log('UserContext: No current user logged in');
      return;
    }
    // For now, userData mirrors currentUser
    // Later, can fetch more detailed profile info here
    setUserData(currentUser);
    debug.log(`UserContext: User data set for ${currentUser.username}`);
  }, [currentUser]);

  // Placeholder for user updates
  const updateUser = (updates) => {
    setUserData((prev) => ({ ...prev, ...updates }));
    debug.log(`UserContext: User data updated`);
  };

  return (
    <UserContext.Provider value={{ userData, updateUser }}>
      {children}
    </UserContext.Provider>
  );
};

// Hook for easy access
export const useUser = () => {
  const context = useContext(UserContext);
  if (!context) throw new Error('useUser must be used within UserProvider');
  return context;
};
