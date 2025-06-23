#!/usr/local/bin/php
<?php
// This script is designed to be run from a cron job once daily.
// It loads your caching engine and prints results to cron log or suppresses with >/dev/null 2>&1

require_once __DIR__ . '/../engine/cache_engine.php';
