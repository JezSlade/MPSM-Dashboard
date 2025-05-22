// src/contexts/UserContext.jsx
import React, { createContext, useContext, useState, useEffect } from 'react';
import { useDebug } from './DebugContext';
import { useAuth } from './AuthContext';

const UserContext = createContext();

export const UserProvider = ({ children }) => {
  const debug = useDebug();
  const { currentUser } = useAuth();
  const [userData, setUserData] = useState(null);

  useEffect(() => {
    if (!currentUser) {
      setUserData(null);
      debug.log('UserContext: No current user logged in');
      return;
    }
    setUserData(currentUser);
    debug.log(`UserContext: User data set for ${currentUser.username}`);
  }, [currentUser, debug]);

  const updateUser = (updates) => {
    setUserData((prev) => ({ ...prev, ...updates }));
    debug.log('UserContext: User data updated');
  };

  return (
    <UserContext.Provider value={{ userData, updateUser }}>
      {children}
    </UserContext.Provider>
  );
};

export const useUser = () => {
  const context = useContext(UserContext);
  if (!context) throw new Error('useUser must be used within UserProvider');
  return context;
};
