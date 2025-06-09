<!DOCTYPE html>
<html><head>
  <meta charset="utf-8">
  <title>CMS</title>
  <link rel="stylesheet" href="<?= APP_BASE?>/themes/dark/style.css">
</head>
<body class="dark">
  <header><?php ModuleManager::renderPosition('header') ?></header>
  <aside><?php ModuleManager::renderPosition('sidebar') ?></aside>
  <main><?php ModuleManager::renderPosition('main') ?></main>
  <footer><?php ModuleManager::renderPosition('footer') ?></footer>
</body>
</html>
