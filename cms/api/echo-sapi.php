<?php
// Simple script that just echoes SAPI name
echo "SAPI: " . php_sapi_name() . "\n";
echo "Is CLI: " . (php_sapi_name() === 'cli' ? 'YES' : 'NO') . "\n";
