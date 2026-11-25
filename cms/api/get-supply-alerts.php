<?php
/**
 * Get Supply Alerts API
 * Proxies SupplyAlert/List for a given dealer/customer/supply type. Uses the
 * current user's personal MPS Monitor login when saved, so switched dealers
 * resolve (see cms/toner-alerts.php).
 */
require '../config.php';
require '../functions.php';
require_once __DIR__ . '/supply-alert-filters.php';

requireAuth();

$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;
$customerCode = $_GET['customerCode'] ?? null; // Optional - null returns all customers
$supplyType = $_GET['supplyType'] ?? null; // Optional - null returns all types (ManteinanceKit, PhotoConductor, Toner)
$pageNumber = isset($_GET['pageNumber']) ? (int)$_GET['pageNumber'] : 1;
$pageRows = isset($_GET['pageRows']) ? (int)$_GET['pageRows'] : 50;
$sortColumn = $_GET['sortColumn'] ?? 'InitialDate';
$sortOrder = $_GET['sortOrder'] ?? 'Desc';
$currentOnly = filter_var($_GET['currentOnly'] ?? false, FILTER_VALIDATE_BOOLEAN);

if ($pageNumber < 1) {
    $pageNumber = 1;
}

if ($pageRows < 1) {
    $pageRows = 1;
} elseif ($pageRows > 200) {
    $pageRows = 200;
}

$sortOrder = strtoupper($sortOrder) === 'ASC' ? 'Asc' : 'Desc';

try {
    $requestPage = static function (int $number, int $rows) use (
        $dealerCode,
        $customerCode,
        $supplyType,
        $sortColumn,
        $sortOrder,
        $currentOnly
    ): array {
        $params = buildSupplyAlertListParams(
            (string)$dealerCode,
            is_string($customerCode) ? $customerCode : null,
            is_string($supplyType) ? $supplyType : null,
            $number,
            $rows,
            (string)$sortColumn,
            $sortOrder,
            $currentOnly
        );
        return queryMpsApi('SupplyAlert/List', $params, $_SESSION['user_id']);
    };

    if ($currentOnly) {
        $filtered = [];
        $upstreamPage = 1;
        $upstreamRows = 200;

        do {
            $data = $requestPage($upstreamPage, $upstreamRows);
            $raw = $data['data'] ?? [];
            $items = supplyAlertResponseRows($data);
            $currentItems = array_filter(
                $items,
                static fn($item): bool => is_array($item) && isCurrentTonerAlert($item)
            );
            $filtered = array_merge($filtered, array_values($currentItems));

            $meta = isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : [];
            $upstreamTotal = (int)($meta['total_rows'] ?? $meta['total_count'] ?? $raw['TotalRows'] ?? count($items));
            $upstreamPage++;
        } while ($items && (($upstreamPage - 1) * $upstreamRows) < $upstreamTotal && $upstreamPage <= 100);

        $historyBySerial = [];
        $serials = array_values(array_unique(array_filter(array_map(
            static fn(array $alert): string => strtoupper(trim((string)($alert['SerialNumber'] ?? ''))),
            $filtered
        ))));
        if ($serials) {
            try {
                $pdo = getDatabase();
                $placeholders = [];
                $historyParams = [];
                foreach ($serials as $index => $serial) {
                    $placeholder = ':serial' . $index;
                    $placeholders[] = $placeholder;
                    $historyParams[$placeholder] = $serial;
                }
                $historyTable = DB_PREFIX . 'panel_messages';
                $historySql = "SELECT device_serial, received_at, maintenance_alert_code, panel_configuration, payload
                    FROM {$historyTable}
                    WHERE device_serial IN (" . implode(',', $placeholders) . ")
                      AND maintenance_alert_code IN ('1101', '1104')
                    ORDER BY received_at DESC";
                $historyStmt = $pdo->prepare($historySql);
                $historyStmt->execute($historyParams);
                foreach ($historyStmt->fetchAll(PDO::FETCH_ASSOC) as $historyRow) {
                    $serialKey = strtoupper(trim((string)($historyRow['device_serial'] ?? '')));
                    $historyBySerial[$serialKey][] = $historyRow;
                }
            } catch (Throwable $historyError) {
                error_log('Toner panel-history lookup failed: ' . $historyError->getMessage());
            }
        }

        foreach ($filtered as &$alert) {
            $serialKey = strtoupper(trim((string)($alert['SerialNumber'] ?? '')));
            $color = $alert['ColorType'] ?? $alert['SupplyTypeDescription'] ?? '';
            $alert['AlertOpenedDate'] = tonerPanelEventOpenedDate($historyBySerial[$serialKey] ?? [], $color);
            $alert['AlertDateSource'] = $alert['AlertOpenedDate'] ? 'panel_messages' : null;
        }
        unset($alert);
        sortTonerAlertsByOpenedDate($filtered);

        $total = count($filtered);
        $alerts = array_slice($filtered, ($pageNumber - 1) * $pageRows, $pageRows);
        jsonSuccess([
            'alerts' => $alerts,
            'total' => $total,
            'page' => ['number' => $pageNumber, 'size' => $pageRows],
        ]);
        return;
    }

    $data = $requestPage($pageNumber, $pageRows);

    $raw = $data['data'];
    $meta = isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : [];
    $alerts = [];

    if (isset($raw['Items']) && is_array($raw['Items'])) {
        $alerts = $raw['Items'];
        $meta['total_rows'] = $meta['total_rows'] ?? (int)($raw['TotalRows'] ?? 0);
    } elseif (is_array($raw)) {
        $alerts = $raw;
    }

    $total = $meta['total_rows'] ?? $meta['total_count'] ?? count($alerts);

    jsonSuccess([
        'alerts' => $alerts,
        'total' => (int) $total,
        'page' => [
            'number' => $pageNumber,
            'size' => $pageRows,
        ],
    ]);
} catch (Throwable $e) {
    jsonError('Failed to fetch supply alerts: ' . $e->getMessage());
}

/*
CHANGELOG
2026-11-25 Codex
- Added optional current-only upstream filtering with accurate post-filter pagination.
- Added an opt-in current-only mode that excludes installed, canceled, hidden, and stock-generated supply records upstream.
- Replaced slow per-device MPSM history calls with one batched panel-history query keyed by serial and toner color.
*/
