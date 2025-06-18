<?php
require_once __DIR__ . '/../includes/api_functions.php';

// No inputs needed
$customers = get_customers();
echo json_encode([
  'Result'    => $customers,
  'IsValid'   => true,
  'Errors'    => [],
  'ReturnValue' => ''
], JSON_PRETTY_PRINT);
