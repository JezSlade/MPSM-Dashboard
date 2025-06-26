<?php
/**
 * Central API Helper Router
 * Dynamically dispatches calls to helper functions
 */

require_once 'CustomerGetHelpers.php';
require_once 'CustomerPostHelpers.php';
require_once 'CustomerPutHelpers.php';
require_once 'CustomerDeleteHelpers.php';

require_once 'CounterGetHelpers.php';
require_once 'CounterPostHelpers.php';
require_once 'CounterPutHelpers.php';
require_once 'CounterDeleteHelpers.php';

require_once 'AlertLimit2GetHelpers.php';
require_once 'AlertLimit2PostHelpers.php';
require_once 'AlertLimit2PutHelpers.php';
require_once 'AlertLimit2DeleteHelpers.php';

// Add additional require_once lines as more modules are added

class ApiHelperRouter {
    public static function call($module, $method, $function, ...$args) {
        $func = strtolower("{$module}{$method}helpers\{$function}");
        if (function_exists($func)) {
            return call_user_func_array($func, $args);
        }
        throw new Exception("Function $func not found.");
    }
}
?>
