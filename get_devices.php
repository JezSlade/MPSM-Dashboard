<?php
$python = "/usr/bin/python3";
$script = "/home/resolut7/public_html/mpsm.resolutionsbydesign.us/mpsm/get_devices.py";

$input = file_get_contents("php://input");
$data = json_decode($input, true);
$customerId = escapeshellarg($data["CustomerId"]);

header("Content-Type: application/json");
$output = shell_exec("$python $script $customerId 2>&1");
echo $output;
?>
