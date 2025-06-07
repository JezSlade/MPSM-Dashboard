<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Login</title></head>
<body>
  <h2>Login</h2>
  <?php if (!empty($error)): ?>
    <p style="color:red;"><?= h($error) ?></p>
  <?php endif; ?>
  <form method="post" action="<?= APP_BASE?>/?path=login">
    <label>Username:<input name="username"></label><br>
    <label>Password:<input type="password" name="password"></label><br>
    <button type="submit">Log In</button>
  </form>
</body></html>
