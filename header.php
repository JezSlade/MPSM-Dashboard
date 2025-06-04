<?php
// File: header.php
// -----------------
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'MPSM Dashboard'; ?></title>
  <link rel="stylesheet" href="/assets/css/style.css" type="text/css" />
</head>
<body>
  <header class="site-header">
    <nav class="nav-main">
      <ul>
        <li><a href="/index.php">Home</a></li>
        <li><a href="/customer_list.php">Customers</a></li>
        <li><a href="/device_list.php">Devices</a></li>
        <li><a href="/diagnose.php">Diagnose</a></li>
      </ul>
    </nav>
  </header>
  <main class="site-content">
