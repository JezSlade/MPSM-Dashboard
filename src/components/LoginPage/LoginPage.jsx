/**
 * LoginPage.jsx
 * Simple login form using AuthContext.
 */

import React, { useState } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import './LoginPage.css';

export default function LoginPage() {
  const { login } = useAuth();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState(null);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (login(username, password)) {
      setError(null);
    } else {
      setError('Invalid username or password');
    }
  };

  return (
    <div className="login-page">
      <form onSubmit={handleSubmit} className="login-form" noValidate>
        <h2>Login</h2>
        {error && <div className="error">{error}</div>}
        <input
          type="text"
          placeholder="Username"
          value={username}
          onChange={e => setUsername(e.target.value)}
          autoComplete="username"
          required
        />
        <input
          type="password"
          placeholder="Password"
          value={password}
          onChange={e => setPassword(e.target.value)}
          autoComplete="current-password"
          required
        />
        <button type="submit">Log In</button>
      </form>
    </div>
  );
}
