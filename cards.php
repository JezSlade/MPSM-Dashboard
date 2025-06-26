<?php
$files = array_filter(glob(__DIR__ . '/cards/Card*.php'), 'is_file');
$names = array_map(function ($file) {
  return basename($file, '.php');
});
header('Content-Type: application/json');
echo json_encode($names);
?>
