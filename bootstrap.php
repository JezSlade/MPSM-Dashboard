<?php
// PHP Debugging Lines - START
// Enable all error reporting for development purposes.
// This helps in identifying and debugging issues quickly.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

// bootstrap.php
require_once __DIR__ . '/backend/core/EnvLoader.php';
require_once __DIR__ . '/backend/core/TokenManager.php';
require_once __DIR__ . '/backend/core/ApiCaller.php';
