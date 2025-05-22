<?php
// index.php
require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/core/widgets.php';
require_login();

$user    = current_user();
$widgets = get_user_widgets();
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>MPSM Dashboard</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="min-h-screen flex">
  <!-- Sidebar -->
  <aside class="w-64 p-4 bg-gray-100 dark:bg-gray-800 overflow-auto">
    <h2 class="text-xl mb-4">Widgets</h2>
    <ul id="widget-menu"></ul>
  </aside>

  <!-- Main area -->
  <div class="flex-1 flex flex-col">
    <header class="p-4 flex justify-between items-center neu">
      <h1 class="text-2xl">MPSM Dashboard</h1>
      <div>
        <span>Hi, <?=htmlspecialchars($user['username'])?></span>
        <a href="logout.php" class="ml-4 underline">Logout</a>
        <button id="theme-toggle" class="ml-4">Toggle Theme</button>
      </div>
    </header>
    <main id="main-content" class="p-4 overflow-auto"></main>
  </div>

  <script>
    window.widgetDefinitions = <?=json_encode($widgets)?>;
    window.userPermissions   = <?=json_encode(get_user_permissions())?>;
  </script>
  <script src="assets/js/widget.js"></script>
  <script>
    document.getElementById('theme-toggle').onclick = () =>
      document.documentElement.classList.toggle('dark');
  </script>
</body>
</html>
