<?php
$python = "/usr/bin/python3";
$script = "/home/resolut7/public_html/mpsm.resolutionsbydesign.us/mpsm/get_devices.py";

$rawInput = file_get_contents("php://input");
$payload = json_decode($rawInput, true);

$customerId = $payload["CustomerId"] ?? null;

header("Content-Type: application/json");

if (!$customerId) {
    echo json_encode(["status" => "error", "message" => "Missing CustomerId"]);
    exit;
}

$escapedId = escapeshellarg($customerId);
$output = shell_exec("$python $script $escapedId 2>&1");
echo $output;
?>
