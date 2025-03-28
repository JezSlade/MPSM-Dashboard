<?php
$python = "/usr/bin/python3";
$script = "/home/resolut7/public_html/mpsm.resolutionsbydesign.us/mpsm/api_call.py";
$endpoint = isset($_GET['endpoint']) ? escapeshellarg($_GET['endpoint']) : "";

header("Content-Type: application/json");
$output = shell_exec("$python $script $endpoint 2>&1");
echo $output;
?>
