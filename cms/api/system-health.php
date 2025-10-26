<?php
/**
 * System Health Check API
 * Following Engineering Standards Rule 6: Always Show Errors
 */

require '../config.php';
require '../functions.php';

requireAuth();

try {
    $health = getSystemHealth();
    jsonSuccess($health);
} catch (Exception $e) {
    jsonError($e->getMessage());
}
