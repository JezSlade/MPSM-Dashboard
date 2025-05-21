import React, { useState } from 'react';
import { useAuth } from '../../../contexts/AuthContext';
import { useRole } from '../../../contexts/RoleContext';
import { useDebug } from '../../../contexts/DebugContext';
import './AdminWidget.css';

export default function AdminWidget() {
  const { users, addUser, removeUser } = useAuth();
  const { roles, addRole, removeRole } = useRole();
  const debug = useDebug();

  const [newUser, setNewUser] = useState({ username: '', password: '', role: roles[0]?.name || '' });
  const [newRole, setNewRole] = useState({ name: '', description: '' });

  const handleAddUser = () => {
    try {
      addUser(newUser);
      debug.log(`AdminWidget: Added user ${newUser.username}`);
      setNewUser({ username: '', password: '', role: roles[0]?.name || '' });
    } catch (err) {
      debug.error(`AdminWidget: Failed to add user - ${err.message}`);
      alert(`Error adding user: ${err.message}`);
    }
  };

  const handleAddRole = () => {
    try {
      addRole(newRole);
      debug.log(`AdminWidget: Added role ${newRole.name}`);
      setNewRole({ name: '', description: '' });
    } catch (err) {
      debug.error(`AdminWidget: Failed to add role - ${err.message}`);
      alert(`Error adding role: ${err.message}`);
    }
  };

  const handleRemoveUser = (username) => {
    if (window.confirm(`Remove user "${username}"?`)) {
      removeUser(username);
      debug.log(`AdminWidget: Removed user ${username}`);
    }
  };

  const handleRemoveRole = (roleName) => {
    if (window.confirm(`Remove role "${roleName}"?`)) {
      removeRole(roleName);
      debug.log(`AdminWidget: Removed role ${roleName}`);
    }
  };

  return (
    <div className="admin-widget">
      {/* Form and table code here as before */}
    </div>
  );
}
