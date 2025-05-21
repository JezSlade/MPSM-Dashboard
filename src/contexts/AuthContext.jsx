/**
 * AuthContext.jsx
 * v1.0.0
 * Provides authentication state and methods.
 * Stores user info and token in localStorage for persistence.
 * Seeds default admin/admin user on first load.
 */

import React, { createContext, useContext, useState, useEffect } from 'react';
import debug from './DebugContext'; // Assumes DebugContext export

// User data example structure:
// { username: 'admin', password: 'admin', role: 'Admin' }

const LOCAL_STORAGE_KEY = 'MPSM_Auth_UserList';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  // List of users (seed with admin)
  const [users, setUsers] = useState(() => {
    const stored = localStorage.getItem(LOCAL_STORAGE_KEY);
    if (stored) return JSON.parse(stored);
    return [{ username: 'admin', password: 'admin', role: 'Admin' }];
  });

  // Currently logged-in user { username, role }
  const [currentUser, setCurrentUser] = useState(null);

  // Persist users list to localStorage when it changes
  useEffect(() => {
    try {
      localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(users));
      debug.log('AuthContext: User list saved to localStorage');
    } catch (e) {
      debug.error(`AuthContext: Failed to save users: ${e.message}`);
    }
  }, [users]);

  // Login method: simple username/password match
  const login = (username, password) => {
    const user = users.find(
      (u) => u.username === username && u.password === password
    );
    if (user) {
      setCurrentUser({ username: user.username, role: user.role });
      debug.log(`AuthContext: User logged in: ${username}`);
      return true;
    } else {
      debug.warn(`AuthContext: Failed login attempt for: ${username}`);
      return false;
    }
  };

  // Logout method
  const logout = () => {
    debug.log(`AuthContext: User logged out: ${currentUser?.username ?? 'none'}`);
    setCurrentUser(null);
  };

  // Add user (used by Admin widget)
  const addUser = (user) => {
    if (users.find((u) => u.username === user.username)) {
      throw new Error('Username already exists');
    }
    setUsers((prev) => [...prev, user]);
    debug.log(`AuthContext: User added: ${user.username}`);
  };

  // Edit user
  const editUser = (username, updates) => {
    setUsers((prev) =>
      prev.map((u) => (u.username === username ? { ...u, ...updates } : u))
    );
    debug.log(`AuthContext: User edited: ${username}`);
  };

  // Remove user
  const removeUser = (username) => {
    setUsers((prev) => prev.filter((u) => u.username !== username));
    debug.log(`AuthContext: User removed: ${username}`);
  };

  return (
    <AuthContext.Provider
      value={{ currentUser, login, logout, users, addUser, editUser, removeUser }}
    >
      {children}
    </AuthContext.Provider>
  );
};

// Hook for easy access
export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) throw new Error('useAuth must be used within AuthProvider');
  return context;
};
