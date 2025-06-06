<?php
// config.php

// --- DEBUGGING SETTINGS ---
// IMPORTANT: These settings are for development environments ONLY.
// DO NOT use these on a live production server.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- END DEBUGGING SETTINGS ---


// --- APPLICATION PATHS ---
// Define server-side absolute path to the project root
// This will be the absolute path to the directory containing index.php and config.php
define('SERVER_ROOT_PATH', __DIR__ . '/');

// Define web-facing URL path to the project root
// Adjust '/mpsm/' if your project is directly in your domain root (e.g., '/')
define('WEB_ROOT_PATH', '/mpsm/');


// --- APPLICATION METADATA & SETTINGS ---
define('APP_NAME', 'MPSM Dashboard'); // Your application's name
define('APP_VERSION', '1.0.0');      // Current version of your application

// Default timezone for all date/time functions
date_default_timezone_set('America/New_York'); // Example: Use your desired timezone

// Default module to load if none is specified in the URL
define('DEFAULT_MODULE', 'dashboard');

// Asset version for cache busting (change this value to force CSS/JS reload)
define('ASSET_VERSION', '2025060601'); // Example: YYYYMMDD + increment


// This file is purely for defining global constants and core debugging settings.
// Database environment variables are handled by db.php's load_env function.