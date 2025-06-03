<?php
// login.php

require_once __DIR__ . '/src/EnvLoader.php';
require_once __DIR__ . '/src/Db.php';

EnvLoader::load(__DIR__ . '/.env');
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $pdo = Db::connect();
    $stmt = $pdo->prepare("SELECT id, password_hash, is_admin FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = (bool)$user['is_admin'];
        header('Location: /index.php');
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login – MPSM Dashboard</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <div style="padding:2rem; max-width:400px; margin:auto; background: var(--surface); box-shadow: var(--glass-shadow); border-radius:6px;">
    <h2>Login</h2>
    <?php if (!empty($error)): ?>
      <p style="color:#f66;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
      <label>Username</label><br>
      <input type="text" name="username" required style="width:100%; padding:0.5rem; margin:0.5rem 0;"><br>
      <label>Password</label><br>
      <input type="password" name="password" required style="width:100%; padding:0.5rem; margin:0.5rem 0;"><br>
      <button type="submit" style="padding:0.5rem 1rem; background:var(--neon-cyan); border:none; color:#000; cursor:pointer;">
        Login
      </button>
    </form>
  </div>
</body>
</html>
