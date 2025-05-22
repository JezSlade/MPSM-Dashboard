<?php
// login.php
require_once __DIR__ . '/core/bootstrap.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login_user($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: index.php');
        exit;
    }
    $error = 'Invalid username or password';
}
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="flex items-center justify-center min-h-screen">
  <form method="POST" class="neu p-6 space-y-4">
    <h2 class="text-xl">Log In</h2>
    <?php if($error): ?>
      <div class="text-red-600"><?=$error?></div>
    <?php endif; ?>
    <input name="username" placeholder="Username" required class="border p-1 w-full">
    <input type="password" name="password" placeholder="Password" required class="border p-1 w-full">
    <button type="submit" class="w-full p-2 neu">Login</button>
  </form>
</body>
</html>
