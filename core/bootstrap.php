<?php
$env = parse_ini_file(__DIR__ . '/.env');

define('SYSOP_USERNAME', $env['SYSOP_USERNAME']);
define('SYSOP_PASSWORD_HASH', $env['SYSOP_PASSWORD_HASH']);
