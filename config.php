<?php
// config.php

// Define server-side absolute path to the project root
// This will be the absolute path to the directory containing index.php and config.php
define('SERVER_ROOT_PATH', __DIR__ . '/');

// Define web-facing URL path to the project root
// Adjust '/mpsm/' if your project is directly in your domain root (e.g., '/')
define('WEB_ROOT_PATH', '/mpsm/');

// This file is purely for defining global paths.
// Database environment variables are handled by db.php's load_env function.