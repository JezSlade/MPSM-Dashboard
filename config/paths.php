<?php
// Filesystem paths
define('PROJECT_ROOT', realpath(__DIR__ . '/..'));      // e.g. /home/.../mpsm
define('CONFIG_PATH',   PROJECT_ROOT . '/config');
define('SRC_PATH',      PROJECT_ROOT . '/src');
define('MODULE_PATH',   PROJECT_ROOT . '/modules');
define('ADMIN_PATH',    PROJECT_ROOT . '/admin');
define('PUBLIC_PATH',   PROJECT_ROOT . '/public');
define('THEMES_PATH',   PUBLIC_PATH   . '/themes');

// URL‐base for links and redirects (no trailing slash)
define('APP_BASE', '/mpsm');
