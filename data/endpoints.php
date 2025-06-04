<?php
function getAllEndpoints(): array {
    $json = file_get_contents(__DIR__ . '/../AllEndpoints.json');
    return json_decode($json, true)['paths'] ?? [];
}
