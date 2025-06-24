<?php
// ──────────── ENV LOADING ────────────
require_once __DIR__ . '/env_parser.php';       // bring in parse_env_file()
parse_env_file(__DIR__ . '/../.env');           // load .env into constants

// includes/card_base.php — Wraps each card in the glassmorphic container
?>
<div class="card-wrapper p-4 flex justify-center">
  <!-- Card markup (with .card) follows in each card file -->
