<?php
// index.php
require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/core/widgets.php';
require_login();

// $user    = current_user();
$widgets = get_user_widgets();
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>MPSM Dashboard</title>

  <!-- Tailwind via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="min-h-screen flex">
  <!-- rest of your dashboard markup unchanged -->
