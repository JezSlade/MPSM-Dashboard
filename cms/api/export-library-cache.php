<?php
/**
 * Export Library Cache
 * Prefetches and serves export artifacts so users download cached files instead of triggering live API runs.
 */

require '../config.php';
require '../functions.php';

require_once dirname(__DIR__, 2) . '/.canonical/EndpointCatalog.php';

if (!defined('MPSM_EXPORT_ENDPOINT')) {
    define('MPSM_EXPORT_ENDPOINT', 'https://mpsm.resolutionsbydesign.us/mps-api/query');
}

const EXPORT_CACHE_DIR = __DIR__ . '/../cache/exports';
const EXPORT_CACHE_TTL = 86400; // 24 hours

$isCli = (php_sapi_name() === 'cli');
$action = $_GET['action'] ?? ($isCli && isset($argv[1]) ? $argv[1] : 'list');

// Only require auth for HTTP calls; CLI/cron runs bypass login
if (!$isCli) {
    requireAuth();
}

/**
 * Infer export format label from action/use case.
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

function sanitizeSegment(string $value): string
{
    $sanitized = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', $value);
    return trim($sanitized ?? '', '_') ?: 'export';
}

function ensureCacheDir(string $dir): void
{
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

function getCustomerCodePreference(): string
{
    if (isset($_GET['customerCode']) && $_GET['customerCode'] !== '') {
        return trim((string)$_GET['customerCode']);
    }

    if (isset($_SESSION['user_id'])) {
        $prefs = getUserPreferences($_SESSION['user_id']);
        if (!empty($prefs['customerCode'])) {
            return $prefs['customerCode'];
        }
    }

    return defined('DEFAULT_CUSTOMER_CODE') ? DEFAULT_CUSTOMER_CODE : '';
}

function getDealerContext(): array
{
    return [
        'dealerCode' => defined('DEFAULT_DEALER_CODE') ? DEFAULT_DEALER_CODE : '',
        'dealerId' => defined('DEFAULT_DEALER_ID') ? DEFAULT_DEALER_ID : '',
    ];
}

function manifestPath(string $customerCode): string
{
    $safeCustomer = sanitizeSegment($customerCode ?: 'default');
    return EXPORT_CACHE_DIR . '/' . $safeCustomer . '/manifest.json';
}

function loadManifest(string $customerCode): array
{
    $path = manifestPath($customerCode);
    if (!file_exists($path)) {
        return [];
    }

    $decoded = json_decode(file_get_contents($path), true);
    return is_array($decoded) ? $decoded : [];
}

function saveManifest(string $customerCode, array $manifest): void
{
    $path = manifestPath($customerCode);
    ensureCacheDir(dirname($path));
    file_put_contents($path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function parseStatusCode(array $headers): int
{
    foreach ($headers as $header) {
        if (preg_match('#^HTTP/\\d+\\.\\d+\\s+(\\d{3})#i', $header, $matches)) {
            return (int)$matches[1];
        }
    }
    return 0;
}

/**
 * Dispatch an export request against the mps-api proxy.
 */
function dispatchExport(string $action, array $params): array
{
    $requestBody = json_encode(['action' => $action, 'params' => $params], JSON_UNESCAPED_SLASHES);
    if ($requestBody === false) {
        throw new Exception('Failed to encode request payload.');
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => $requestBody,
            'timeout' => 90,
            'ignore_errors' => true,
        ],
    ]);

    $response = @file_get_contents(MPSM_EXPORT_ENDPOINT, false, $context);
    $statusCode = isset($http_response_header) ? parseStatusCode($http_response_header) : 0;

    if ($response === false) {
        $err = error_get_last();
        $message = $err['message'] ?? 'Export request failed.';
        throw new Exception($statusCode ? "HTTP {$statusCode}: {$message}" : $message);
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        $snippet = substr($response, 0, 500);
        throw new Exception(($statusCode ? "HTTP {$statusCode}: " : '') . "Invalid response: {$snippet}");
    }

    if (empty($decoded['success'])) {
        $error = $decoded['error'] ?? $decoded['message'] ?? 'Export failed';
        throw new Exception(($statusCode ? "HTTP {$statusCode}: " : '') . $error);
    }

    return ['payload' => $decoded, 'status' => $statusCode];
}

function extractFilePayload(array $payload): ?array
{
    $candidates = [
        $payload['file'] ?? null,
        $payload['data'] ?? null,
        $payload,
    ];

    foreach ($candidates as $candidate) {
        if (!is_array($candidate)) {
            continue;
        }

        $base64 = $candidate['data'] ?? $candidate['Base64Content'] ?? $candidate['base64Content'] ?? $candidate['base64'] ?? null;
        $url = $candidate['url'] ?? $candidate['Url'] ?? $candidate['URL'] ?? null;
        $name = $candidate['name'] ?? $candidate['FileName'] ?? $candidate['filename'] ?? $candidate['fileName'] ?? null;
        $contentType = $candidate['content_type']
            ?? $candidate['contentType']
            ?? $candidate['MimeType']
            ?? $candidate['mimeType']
            ?? $candidate['Content-Type']
            ?? null;

        if ($base64) {
            return [
                'data' => $base64,
                'name' => $name,
                'content_type' => $contentType,
            ];
        }

        if ($url) {
            return [
                'url' => $url,
                'name' => $name,
                'content_type' => $contentType,
            ];
        }
    }

    return null;
}

function guessExtension(?string $filename, ?string $contentType, ?string $format): string
{
    $nameExt = $filename && str_contains($filename, '.') ? strtolower(pathinfo($filename, PATHINFO_EXTENSION)) : '';
    if ($nameExt) {
        return $nameExt;
    }

    $fmt = strtolower($format ?? '');
    if ($fmt === 'excel') return 'xlsx';
    if ($fmt === 'csv') return 'csv';
    if ($fmt === 'pdf') return 'pdf';
    if ($fmt === 'zip') return 'zip';
    if ($fmt === 'json') return 'json';

    $mime = strtolower($contentType ?? '');
    if (str_contains($mime, 'csv')) return 'csv';
    if (str_contains($mime, 'excel') || str_contains($mime, 'sheet')) return 'xlsx';
    if (str_contains($mime, 'pdf')) return 'pdf';
    if (str_contains($mime, 'zip')) return 'zip';
    if (str_contains($mime, 'json')) return 'json';
    if (str_contains($mime, 'xml')) return 'xml';

    return 'bin';
}

function saveFileToCache(string $customerCode, string $action, array $filePayload, ?string $format): array
{
    $safeAction = sanitizeSegment($action);
    $safeCustomer = sanitizeSegment($customerCode ?: 'default');
    $cacheDir = EXPORT_CACHE_DIR . '/' . $safeCustomer;
    ensureCacheDir($cacheDir);

    $extension = guessExtension($filePayload['name'] ?? null, $filePayload['content_type'] ?? null, $format);
    $filename = "{$safeAction}_{$safeCustomer}.{$extension}";
    $path = $cacheDir . '/' . $filename;

    if (isset($filePayload['data'])) {
        $binary = base64_decode($filePayload['data'], true);
        if ($binary === false) {
            throw new Exception('Failed to decode export payload.');
        }
        file_put_contents($path, $binary);
    } elseif (!empty($filePayload['url'])) {
        $download = @file_get_contents($filePayload['url']);
        if ($download === false) {
            throw new Exception('Failed to download export file from provided URL.');
        }
        file_put_contents($path, $download);
    } else {
        throw new Exception('Export did not include downloadable content.');
    }

    return [
        'filename' => $filename,
        'path' => $path,
        'size_bytes' => filesize($path),
        'content_type' => $filePayload['content_type'] ?? 'application/octet-stream',
        'updated_at' => date(DATE_ATOM),
        'status' => 'ready',
    ];
}

function buildDefaultParams(array $entry, string $customerCode): array
{
    $params = [];
    $prereqs = $entry['prerequisites'] ?? [];
    $dealer = getDealerContext();

    if (in_array('dealerCode', $prereqs, true) || in_array('FilterDealerCodes', $prereqs, true)) {
        $params['FilterDealerCodes'] = [$dealer['dealerCode']];
    }
    if (in_array('dealerId', $prereqs, true) || in_array('FilterDealerId', $prereqs, true)) {
        $params['FilterDealerId'] = $dealer['dealerId'];
    }
    if (in_array('customerCode', $prereqs, true) || in_array('FilterCustomerCodes', $prereqs, true)) {
        $params['FilterCustomerCodes'] = [$customerCode];
        $params['customerCode'] = $customerCode;
    }

    // Sensible defaults for date-bounded exports
    $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $from = $now->sub(new DateInterval('P30D'));
    if (in_array('fromDate', $prereqs, true) || in_array('toDate', $prereqs, true)) {
        $params['fromDate'] = $from->format('Y-m-d\T00:00:00');
        $params['toDate'] = $now->format('Y-m-d\T23:59:59');
    }

    return $params;
}

function mergeCatalogWithManifest(array $catalog, array $manifest, string $customerCode): array
{
    $rows = [];
    $now = time();

    foreach ($catalog as $entry) {
        $action = $entry['action'] ?? '';
        if ($action === '') {
            continue;
        }
        $format = inferExportFormat($action, $entry['use_case'] ?? null);
        $manifestEntry = $manifest[$action] ?? null;

        $row = [
            'action' => $action,
            'category' => $entry['category'] ?? null,
            'use_case' => $entry['use_case'] ?? null,
            'format' => $format,
            'status' => 'pending',
            'updated_at' => null,
            'size_bytes' => null,
            'download_url' => null,
            'customer_code' => $customerCode,
            'last_error' => null,
        ];

        if ($manifestEntry) {
            $row = array_merge($row, $manifestEntry);
            $row['status'] = $manifestEntry['status'] ?? 'ready';

            // Mark stale if beyond TTL
            if (!empty($manifestEntry['updated_at'])) {
                $age = $now - strtotime($manifestEntry['updated_at']);
                if ($age > EXPORT_CACHE_TTL && $row['status'] === 'ready') {
                    $row['status'] = 'stale';
                }
            }

            if (!empty($manifestEntry['filename'])) {
                $row['download_url'] = sprintf(
                    'api/export-library-cache.php?action=download&customerCode=%s&export=%s',
                    rawurlencode($customerCode),
                    rawurlencode($action)
                );
            }
        }

        $rows[] = $row;
    }

    return $rows;
}

function listExports(string $customerCode): void
{
    EndpointCatalog::init();
    $catalog = EndpointCatalog::getAllEndpoints(false);
    $manifest = loadManifest($customerCode);

    $exports = mergeCatalogWithManifest($catalog, $manifest, $customerCode);
    $lastRefreshed = null;
    foreach ($exports as $entry) {
        if (!empty($entry['updated_at'])) {
            $lastRefreshed = max($lastRefreshed ?? $entry['updated_at'], $entry['updated_at']);
        }
    }

    jsonSuccess([
        'customer_code' => $customerCode,
        'ttl_seconds' => EXPORT_CACHE_TTL,
        'last_refreshed' => $lastRefreshed,
        'exports' => $exports,
        'total' => count($exports),
    ]);
}

function downloadExport(string $customerCode, string $action): void
{
    $manifest = loadManifest($customerCode);
    $entry = $manifest[$action] ?? null;

    if (!$entry || empty($entry['filename'])) {
        jsonError('Cached export not found', 404);
    }

    $safeCustomer = sanitizeSegment($customerCode ?: 'default');
    $path = EXPORT_CACHE_DIR . '/' . $safeCustomer . '/' . $entry['filename'];

    if (!file_exists($path)) {
        jsonError('Cached export file missing on disk', 404);
    }

    $contentType = $entry['content_type'] ?? 'application/octet-stream';
    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
    readfile($path);
    exit;
}

function refreshExports(string $customerCode): array
{
    EndpointCatalog::init();
    $catalog = EndpointCatalog::getAllEndpoints(false);
    $manifest = loadManifest($customerCode);

    foreach ($catalog as $entry) {
        $action = $entry['action'] ?? '';
        if ($action === '') {
            continue;
        }

        $format = inferExportFormat($action, $entry['use_case'] ?? null);
        $params = buildDefaultParams($entry, $customerCode);

        try {
            $result = dispatchExport($action, $params);
            $filePayload = extractFilePayload($result['payload']);
            if (!$filePayload) {
                throw new Exception('Export succeeded but no downloadable file was returned.');
            }

            $saved = saveFileToCache($customerCode, $action, $filePayload, $format);
            $manifest[$action] = array_merge([
                'action' => $action,
                'category' => $entry['category'] ?? null,
                'use_case' => $entry['use_case'] ?? null,
                'format' => $format,
                'customer_code' => $customerCode,
                'params_used' => $params,
                'status' => 'ready',
                'last_error' => null,
            ], $saved);
        } catch (Throwable $e) {
            $manifest[$action] = [
                'action' => $action,
                'category' => $entry['category'] ?? null,
                'use_case' => $entry['use_case'] ?? null,
                'format' => $format,
                'customer_code' => $customerCode,
                'status' => 'error',
                'last_error' => $e->getMessage(),
                'updated_at' => date(DATE_ATOM),
            ];
        }
    }

    saveManifest($customerCode, $manifest);

    return [
        'success' => true,
        'customer_code' => $customerCode,
        'refreshed_at' => date(DATE_ATOM),
        'items' => count($manifest),
    ];
}

// =========================
// Controller
// =========================

switch ($action) {
    case 'list':
        $customerCode = getCustomerCodePreference();
        listExports($customerCode);
        break;

    case 'download':
        if ($isCli) {
            throw new Exception('Download not supported via CLI');
        }
        $customerCode = getCustomerCodePreference();
        $exportAction = $_GET['export'] ?? '';
        if ($exportAction === '') {
            jsonError('export parameter required', 400);
        }
        downloadExport($customerCode, $exportAction);
        break;

    case 'refresh':
        if (!$isCli) {
            // Allow HTTP refresh only with secure key to avoid user-triggered heavy jobs
            if (!isset($_GET['secret']) || $_GET['secret'] !== (defined('SECURE_KEY') ? SECURE_KEY : '')) {
                jsonError('Unauthorized', 403);
            }
        }

        $customerCode = $isCli
            ? ($argv[2] ?? (defined('DEFAULT_CUSTOMER_CODE') ? DEFAULT_CUSTOMER_CODE : ''))
            : getCustomerCodePreference();

        $result = refreshExports($customerCode);
        if ($isCli) {
            echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
            exit(0);
        }
        jsonSuccess($result);
        break;

    default:
        jsonError('Invalid action', 400);
}

/*
CHANGELOG
2025-11-25 Codex
- Added export library caching API with list/download/refresh actions, 24h TTL, and CLI/cron support to prefetch export files for on-demand downloads.
*/
