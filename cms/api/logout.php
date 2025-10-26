<?php
/**
 * Logout API
 */

require '../config.php';
require '../functions.php';

requireAuth();

logoutUser();
jsonSuccess(['message' => 'Logged out successfully']);
