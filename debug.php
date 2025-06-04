<?php
/**
 * debug.php
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
echo "<h1>Debug Info</h1>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Session User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'none') . "</li>";
echo "<li>PDO Available: " . (class_exists('PDO') ? 'Yes' : 'No') . "</li>";
echo "</ul>";
?>