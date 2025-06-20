<?php
// /api/get_customers.php
header('Content-Type: application/json');
echo json_encode(['customers'=>[
    ['Code'=>'ACME','Description'=>'ACME Corp'],
    ['Code'=>'FOO','Description'=>'Foo Industries']
]]);
