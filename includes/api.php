<?php
function api_get_customers() {
    $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . \$_SERVER['HTTP_HOST'] . '/api/get_customers.php';
    \$ch = curl_init(\$url);
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    \$response = curl_exec(\$ch);
    curl_close(\$ch);
    \$json = json_decode(\$response, true);
    return \$json['data'] ?? [];
}
?>