<?php
// Path to Python executable and your script
$python = "/usr/bin/python3";
$script = "/home/resolut7/public_html/mpsm.resolutionsbydesign.us/mpsm/api_call.py";

// Output JSON response
header("Content-Type: application/json");
$output = shell_exec("$python $script 2>&1");
echo $output;
?>
