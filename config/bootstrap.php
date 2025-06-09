<?php
session_start();

require_once __DIR__ . '/paths.php';
require_once CONFIG_PATH . '/functions.php';

// 1) Autoload classes
spl_autoload_register(function($class){
    $cands = [
        SRC_PATH   . "/{$class}.php",
        MODULE_PATH. "/{$class}.php",
        ADMIN_PATH . "/controllers/{$class}.php",
    ];
    foreach ($cands as $f) {
        if (file_exists($f)) {
            require_once $f;
            return;
        }
    }
});

// 2) Load .env
try {
    $env = loadEnv(PROJECT_ROOT . '/.env');
} catch (Exception $e) {
    die('Config error: ' . h($e->getMessage()));
}

// 3) Init subsystems
require_once CONFIG_PATH . '/Database.php';
require_once CONFIG_PATH . '/Auth.php';
require_once SRC_PATH    . '/ModuleManager.php';

// Database
Database::connect($env);
Database::initialize();
Database::seedRoles(['developer','admin','dealer','service','sales','guest']);
Database::seedUsers([
    ['username'=>$env['SEED_USERNAME'],'password'=>$env['SEED_PASSWORD'],'role'=>$env['SEED_ROLE']],
    ['username'=>'dev','password'=>'dev123','role'=>'developer'],
]);

// Auth & Modules
Auth::init();
ModuleManager::init();
