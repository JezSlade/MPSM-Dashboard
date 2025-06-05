<?php
require_once BASE_PATH . 'config.php';
$db = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
?>