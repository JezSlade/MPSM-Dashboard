<?php
$python = "/usr/bin/python3";
$script = "/home/resolut7/public_html/mpsm.resolutionsbydesign.us/mpsm/working_token.py";

header("Content-Type: text/plain");
$output = shell_exec("$python $script 2>&1");
echo $output;
?>
