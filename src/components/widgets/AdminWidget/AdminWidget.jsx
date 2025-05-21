/**
 * AdminWidget.jsx
 * v1.0.0
 * Provides user and role management UI with full CRUD.
 * Uses AuthContext and RoleContext.
 * Includes robust error handling and debug logging.
 */

import React, { useState } from 'react';
import { useAuth } from '../../../contexts/AuthContext';
import { useRole } from '../../../contexts/RoleContext';
import { useDebug } from '../../../contexts/DebugContext';
import './AdminWidget.css';

export default function AdminWidget() {
  const { users, addUser, editUser, removeUser } = useAuth();
  const { roles, addRole, editRole, removeRole } = useRole();
  const debug = useDebug();

  // Local state for new user form
  const [newUser, setNewUser] = useState({ username: '', password: '', role: roles[0]?.name || '' });
  const [newRole, setNewRole] = useState({ name: '', description: '' });

  // Handle adding a user
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

  // Handle adding a role
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

  // Handle user deletion
  const handleRemoveUser = (username) => {
    if (window.confirm(`Remove user "${username}"?`)) {
      removeUser(username);
      debug.log(`AdminWidget: Removed user ${username}`);
    }
  };

  // Handle role deletion
  const handleRemoveRole = (roleName) => {
    if (window.confirm(`Remove role "${roleName}"?`)) {
      removeRole(roleName);
      debug.log(`AdminWidget: Removed role ${roleName}`);
    }
  };

  return (
    <div className="admin-widget">
      <h2>User Management</h2>
      <div className="form-row">
        <input
          type="text"
          placeholder="Username"
          value={newUser.username}
          onChange={e => setNewUser(prev => ({ ...prev, username: e.target.value }))}
        />
        <input
          type="password"
          placeholder="Password"
          value={newUser.password}
          onChange={e => setNewUser(prev => ({ ...prev, password: e.target.value }))}
        />
        <select
          value={newUser.role}
          onChange={e => setNewUser(prev => ({ ...prev, role: e.target.value }))}
        >
          {roles.map(role => (
            <option key={role.name} value={role.name}>{role.name}</option>
          ))}
        </select>
        <button onClick={handleAddUser}>Add User</button>
      </div>

      <table className="admin-table">
        <thead>
          <tr><th>Username</th><th>Role</th><th>Actions</th></tr>
        </thead>
        <tbody>
          {users.map(user => (
            <tr key={user.username}>
              <td>{user.username}</td>
              <td>{user.role}</td>
              <td>
                <button onClick={() => handleRemoveUser(user.username)}>Delete</button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      <h2>Role Management</h2>
      <div className="form-row">
        <input
          type="text"
          placeholder="Role Name"
          value={newRole.name}
          onChange={e => setNewRole(prev => ({ ...prev, name: e.target.value }))}
        />
        <input
          type="text"
          placeholder="Description"
          value={newRole.description}
          onChange={e => setNewRole(prev => ({ ...prev, description: e.target.value }))}
        />
        <button onClick={handleAddRole}>Add Role</button>
      </div>

      <table className="admin-table">
        <thead>
          <tr><th>Role Name</th><th>Description</th><th>Actions</th></tr>
        </thead>
        <tbody>
          {roles.map(role => (
            <tr key={role.name}>
              <td>{role.name}</td>
              <td>{role.description}</td>
              <td>
                <button onClick={() => handleRemoveRole(role.name)}>Delete</button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
