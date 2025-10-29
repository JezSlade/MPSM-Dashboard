<?php
/**
 * get-export-endpoints.php
 *
 * Returns a curated list of catalogued MPS Monitor endpoints that produce downloadable
 * exports (Excel, CSV, PDF, etc.) so the CMS can present quick-export options.
 */

require '../config.php';
require '../functions.php';

requireAuth();

require_once dirname(__DIR__, 2) . '/.canonical/EndpointCatalog.php';

/**
 * Infer an export format label from an action name or use case.
 */
function inferExportFormat(string $action, ?string $useCase = null): string
{
    $needles = strtolower($action . ' ' . ($useCase ?? ''));

    if (strpos($needles, 'xlsx') !== false || strpos($needles, 'excel') !== false) {
        return 'Excel';
    }
    if (strpos($needles, 'csv') !== false) {
        return 'CSV';
    }
    if (strpos($needles, 'pdf') !== false) {
        return 'PDF';
    }
    if (strpos($needles, 'zip') !== false) {
        return 'ZIP';
    }
    if (strpos($needles, 'json') !== false) {
        return 'JSON';
    }

    return 'File';
}

try {
    EndpointCatalog::init();
    $catalog = EndpointCatalog::getAllEndpoints(false);

    $keywords = ['export', 'download', 'report'];
    $exports = [];

    foreach ($catalog as $entry) {
        $action = $entry['action'] ?? '';
        if ($action === '') {
            continue;
        }

        $haystack = strtolower($action . ' ' . ($entry['use_case'] ?? ''));
        $match = false;
        foreach ($keywords as $keyword) {
            if (strpos($haystack, $keyword) !== false) {
                $match = true;
                break;
            }
        }

        if (!$match) {
            continue;
        }

        $exports[] = [
            'action' => $action,
            'category' => $entry['category'] ?? null,
            'use_case' => $entry['use_case'] ?? null,
            'success' => $entry['success'] ?? null,
            'duration_ms' => $entry['duration_ms'] ?? null,
            'data_type' => $entry['data_type'] ?? null,
            'data_count' => $entry['data_count'] ?? null,
            'format' => inferExportFormat($action, $entry['use_case'] ?? null),
            'last_tested' => $entry['timestamp'] ?? null,
            'prerequisites' => $entry['prerequisites'] ?? [],
            'last_error' => $entry['error'] ?? null,
        ];
    }

    usort($exports, function ($a, $b) {
        return strcmp($a['action'], $b['action']);
    });

    jsonSuccess([
        'exports' => $exports,
        'total' => count($exports)
    ]);
} catch (Throwable $e) {
    jsonError('Failed to load exportable endpoints: ' . $e->getMessage());
}
