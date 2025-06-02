<?php
// logout.php
require_once __DIR__ . '/core/bootstrap.php';
logout_user();
header('Location: login.php');
exit;
