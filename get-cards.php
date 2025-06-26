<?php
$cardDir = __DIR__ . '/cards/';
$files = scandir($cardDir);
$cards = [];

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $cards[] = pathinfo($file, PATHINFO_FILENAME);
    }
}

header('Content-Type: application/json');
echo json_encode($cards);