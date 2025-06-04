<?php
/**
 * config/router.php
 */
return [
    'Dashboard'  => \Modules\Dashboard\DashboardController::class,
    'Customers'  => \Modules\Customers\CustomersController::class,
    'Admin'      => \Modules\Admin\RolesController::class,
    'DevTools'   => \Modules\DevTools\DevToolsController::class,
];
?>