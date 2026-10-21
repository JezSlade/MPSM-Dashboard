<?php
/**
 * Get Dealers API
 * Lists dealers the current user's MPS Monitor login can access, for the
 * dealer switcher. Falls back to the single service-account dealer when the
 * user has no personal MPS Monitor login saved.
 */
require '../config.php';
require '../functions.php';

requireAuth();

try {
    $data = queryMpsApi('Dealer/GetDealers', [
        'PageNumber' => 1,
        'PageRows' => 100,
        'SortColumn' => 'Description',
        'SortOrder' => 'Asc'
    ], $_SESSION['user_id']);

    $dealers = $data['data']['Items'] ?? $data['data'] ?? [];

    jsonSuccess([
        'dealers' => $dealers,
        'total' => count($dealers)
    ]);
} catch (Exception $e) {
    jsonError('Failed to fetch dealers: ' . $e->getMessage());
}
