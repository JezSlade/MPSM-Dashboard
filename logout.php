<?php
require_once 'core/config.php';
require_once 'core/auth.php';

// Logout user
Auth::logout();

// Redirect to login page
header('Location: login.php');
exit;
