<?php
// File: header.php
// -----------------
// This file is included at the top of every page.
// It defines <html><head> (including the CSS link) and opens <body>.
//
// IMPORTANT:
//   • Ensure your webserver’s document root is set to this folder.
//   • The CSS file must reside at /assets/css/style.css relative to document root.
//   • All other PHP/HTML pages should simply `require 'header.php'` at the top.

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- ========== Page Title (override per-page below) ========== -->
  <title>
    <?php
      // If a page sets $pageTitle before including header.php, use that. Otherwise fallback.
      echo isset($pageTitle)
        ? htmlspecialchars($pageTitle)
        : 'MPSM Dashboard';
    ?>
  </title>

  <!-- ========== Link to the neon-on-black stylesheet ========== -->
  <!-- Root-relative path (/assets/css/style.css) ensures correct loading from any directory. -->
  <link rel="stylesheet" href="/assets/css/style.css" type="text/css" />

  <!-- ========== Any additional per-page <meta> or <script> can go here ========== -->
  <?php
    // If a page defines $extraHeadContent, echo it here.
    if (isset($extraHeadContent) && is_string($extraHeadContent)) {
      echo $extraHeadContent;
    }
  ?>
</head>

<body>
  <!-- ========== Begin Site Header / Navigation (common to all pages) ========== -->
  <header class="site-header">
    <nav class="nav-main">
      <ul>
        <li><a href="/index.php">Home</a></li>
        <li><a href="/customer_list.php">Customers</a></li>
        <li><a href="/device_list.php">Devices</a></li>
        <!-- Add other navigation items as needed -->
      </ul>
    </nav>
  </header>

  <main class="site-content">
    <!-- Page-specific content begins below -->
