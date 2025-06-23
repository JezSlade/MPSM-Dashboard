<?php
declare(strict_types=1);

/* bootstrap */
require_once __DIR__ . '/includes/debug.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';

/* --------  customer selector logic  -------- */
session_start();                        // if not already started elsewhere

// save a new customer code arriving via ?customer=...
if (isset($_GET['customer']) && $_GET['customer'] !== '') {
    $_SESSION['selectedCustomer'] = trim($_GET['customer']);
}

// set default if still empty
if (empty($_SESSION['selectedCustomer'])) {
    $_SESSION['selectedCustomer'] = 'W9OPXL0YDK';   // project-wide default
}

/* navigation (includes the search bar) */
require_once __DIR__ . '/includes/navigation.php';

/* always render the dashboard */
render_view('views/dashboard.php');

require_once __DIR__ . '/includes/footer.php';
