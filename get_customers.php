<?php
$python = "/usr/bin/python3";
$script = "/home/resolut7/public_html/mpsm.resolutionsbydesign.us/mpsm/get_customers.py";

header("Content-Type: application/json");
$output = shell_exec("$python $script 2>&1");
echo $output;
?>
