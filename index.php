<?php
// index.php
// -------------------------------------------------------------------
// Entrypoint for the SPA. Loads header, view, and footer.
// -------------------------------------------------------------------

// 0) Enable error reporting in dev
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 1) Global header (logo + theme toggle), no nav here
require_once __DIR__ . '/includes/header.php';

// 2) Render the main view
//    You can switch this out (e.g. dashboard, settings, etc.)
include __DIR__ . '/views/dashboard.php';

// 3) Global footer (copyright, version)
require_once __DIR__ . '/includes/footer.php';
