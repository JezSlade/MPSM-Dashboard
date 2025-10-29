<?php
/**
 * Endpoint Catalog API
 * Provides read-only access to canonical endpoint metadata for the admin console.
 */

require '../config.php';
require '../functions.php';

requireAuth();

require_once dirname(__DIR__, 2) . '/.canonical/EndpointCatalog.php';

/**
 * Normalize an endpoint entry into a consistent structure for the UI.
 *
 * @param string $action
 * @param array|null $fallback
 * @return array<string, mixed>
 */
function formatEndpointEntry(string $action, ?array $fallback = null): array
{
    $metadata = EndpointCatalog::getMetadata($action) ?? [];

    $source = array_merge($fallback ?? [], $metadata);

    return [
        'action' => $action,
        'category' => $source['category'] ?? null,
        'success' => $source['success'] ?? null,
        'data_type' => $source['data_type'] ?? null,
        'data_count' => $source['data_count'] ?? null,
        'duration_ms' => $source['duration_ms'] ?? null,
        'use_case' => $source['use_case'] ?? null,
        'prerequisites' => $source['prerequisites'] ?? [],
    ];
}

/**
 * Build category summaries with working counts.
 *
 * @param array<string, array<string, mixed>> $categories
 * @return array<int, array<string, mixed>>
 */
function buildCategorySummaries(array $categories): array
{
    $summaries = [];

    foreach ($categories as $key => $info) {
        $working = EndpointCatalog::getEndpointsByCategory($key, true);
        $summaries[] = [
            'id' => $key,
            'description' => $info['description'] ?? '',
            'endpoint_count' => count($working),
        ];
    }

    return $summaries;
}

try {
    EndpointCatalog::init();

    $category = isset($_GET['category']) ? trim((string) $_GET['category']) : null;
    $search = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
    $limit = isset($_GET['limit']) ? max(1, min((int) $_GET['limit'], 500)) : 200;

    $categories = EndpointCatalog::getCategories();
    $categorySummaries = buildCategorySummaries($categories);

    $endpoints = [];

    if ($search !== '') {
        $matches = EndpointCatalog::searchEndpoints($search);
        foreach (array_slice($matches, 0, $limit) as $entry) {
            $action = $entry['action'] ?? null;
            if (!$action) {
                continue;
            }
            $endpoints[] = formatEndpointEntry($action, $entry);
        }
    } elseif ($category !== null && isset($categories[$category])) {
        $items = EndpointCatalog::getEndpointsByCategory($category);
        foreach (array_slice($items, 0, $limit) as $entry) {
            $action = $entry['action'] ?? null;
            if (!$action) {
                continue;
            }
            $endpoints[] = formatEndpointEntry($action, $entry);
        }
    } else {
        $top = EndpointCatalog::getTopEndpointsByVolume($limit);
        foreach ($top as $entry) {
            $action = $entry['action'] ?? null;
            if (!$action) {
                continue;
            }
            $endpoints[] = formatEndpointEntry($action, $entry);
        }
    }

    $statistics = EndpointCatalog::getStatistics();

    jsonSuccess([
        'categories' => $categorySummaries,
        'endpoints' => $endpoints,
        'statistics' => $statistics,
    ]);
} catch (Throwable $e) {
    jsonError('Failed to load endpoint catalog: ' . $e->getMessage());
}
