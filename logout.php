<?php
// logout.php

session_start();
session_unset();
session_destroy();

$base = dirname($_SERVER['SCRIPT_NAME']);
$redirectPath = rtrim($base, '/') . '/login.php';
header("Location: {$redirectPath}");
exit;
