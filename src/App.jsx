// src/App.jsx
import React, { useState } from 'react';
import { AuthProvider } from './contexts/AuthContext';
import { UserProvider } from './contexts/UserContext';
import { RoleProvider } from './contexts/RoleContext';
import { WidgetRegistryProvider } from './contexts/WidgetRegistryContext';
import { DebugProvider, useDebug } from './contexts/DebugContext';
import Sidebar from './components/Sidebar/Sidebar';
import Dashboard from './components/Dashboard/Dashboard';
import DebugPanel from './components/DebugPanel/DebugPanel';
import LoginPage from './components/LoginPage/LoginPage';
import { useAuth } from './contexts/AuthContext';
import './theme.css';

function AppContent() {
  const { currentUser } = useAuth();
  const [selectedWidget, setSelectedWidget] = useState('dashboard');
  const debug = useDebug();

  if (!currentUser) {
    return <LoginPage />;
  }

  return (
    <div className="app-container">
      <Sidebar
        activeWidgetId={selectedWidget}
        onSelectWidget={(id) => {
          setSelectedWidget(id);
          debug.log(`App: switched to widget ${id}`);
        }}
      />
      <Dashboard selectedWidgetId={selectedWidget} />
      <DebugPanel />
    </div>
  );
}

export default function App() {
  return (
    <DebugProvider>
      <AuthProvider>
        <UserProvider>
          <RoleProvider>
            <WidgetRegistryProvider>
              <AppContent />
            </WidgetRegistryProvider>
          </RoleProvider>
        </UserProvider>
      </AuthProvider>
    </DebugProvider>
  );
}
