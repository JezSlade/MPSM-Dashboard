<?php
declare(strict_types=1);

// ------------------------------------------------------------------
// DEBUG BLOCK (Always Keep at Top)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ------------------------------------------------------------------

/**
 * Card ▸ Devices Snapshot  (pure-PHP, POST-based)
 * ------------------------------------------------------------------
 * • Compact view shows “Devices Online (n)”.
 * • Clicking header reloads with ?ds_exp=1 to reveal a paginated table.
 * • Uses POST /Device/List (spec-compliant) with either:
 *       CustomerCode      – when the user selected a customer
 *       FilterDealerId    – fallback to dealer-wide view
 * • No JavaScript.
 */

// ──────────────────────────────────────────────────────────────────
// 1) Determine scope
$selectedCustomer   = $_SESSION['selectedCustomer'] ?? null;
$usingDefaultTenant = $selectedCustomer === null;

// Dealer + DealerCode pulled from .env (already parsed in config.php)
$dealerId   = getenv('DEALER_ID')   ?: 'SZ13qRwU5GtFLj0i_CbEgQ2';
$dealerCode = getenv('DEALER_CODE') ?: 'NY06AGDWUQ'; // kept for future use

// ──────────────────────────────────────────────────────────────────
// 2) Pagination / expansion flags (query-string)
$isExpanded  = isset($_GET['ds_exp']);
$currentPage = isset($_GET['ds_page']) ? max(1, (int)$_GET['ds_page']) : 1;
$pageSize    = 15;   // API default; keep for clarity

// ──────────────────────────────────────────────────────────────────
// 3) Helper → call /Device/List via POST
function fetch_devices(int $page, ?string $customer, string $dealerId): ?array
{
    // Build body according to AllEndpoints.json
    $body = [
        'PageNumber' => $page,
        'PageRows'   => 15,
    ];
    if ($customer) {
        $body['CustomerCode']   = $customer;
    } else {
        $body['FilterDealerId'] = $dealerId;
    }

    $url =
        (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
        $_SERVER['HTTP_HOST'] .
        '/api/get_devices.php';   // wrapper that forwards to /Device/List

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        error_log("Device snapshot CURL error: $err");
        return null;
    }
    return json_decode($raw, true) ?: null;
}

// ──────────────────────────────────────────────────────────────────
// 4) Retrieve data
$firstPageData = fetch_devices(1, $selectedCustomer, $dealerId);

if (!$firstPageData || !($firstPageData['IsValid'] ?? false)) {
    // API failure – log and show count 0
    $apiErrorMsg = $firstPageData['Errors'][0]['Description'] ?? 'Unknown API error';
    error_log("Device snapshot API error: $apiErrorMsg");
    $totalRows  = 0;
    $totalPages = 1;
    $rows       = [];
} else {
    $totalRows  = $firstPageData['TotalRows'] ?? 0;
    $totalPages = max(1, (int)ceil($totalRows / $pageSize));

    $pageData = ($isExpanded && $currentPage > 1)
        ? fetch_devices($currentPage, $selectedCustomer, $dealerId)
        : $firstPageData;

    $rows = ($pageData['IsValid'] ?? false) ? ($pageData['Result'] ?? []) : [];
}

// ──────────────────────────────────────────────────────────────────
// 5) URL helper for self-navigation
function build_url(bool $expand, int $page = 1): string
{
    $params = ['view' => 'sandbox'];
    if ($expand) {
        $params['ds_exp']  = '1';
        $params['ds_page'] = $page;
    }
    return '/index.php?' . http_build_query($params);
}
?>

<!-- ─────────── Card Mark-up ─────────── -->
<div class="card devices-snapshot">
    <header>
        <h2>
            <a href="<?= htmlspecialchars(build_url(!$isExpanded)); ?>">
                Devices Online
                <span class="badge"><?= $totalRows; ?></span>
                <?php if ($usingDefaultTenant): ?>
                    <span class="badge warn" title="Using dealer-wide fallback">⚠️</span>
                <?php endif; ?>
            </a>
        </h2>
    </header>

<?php if ($isExpanded): ?>
    <section>
        <table>
            <thead>
            <tr>
                <th>Equipment ID</th>
                <th>Model</th>
                <th>IP Address</th>
                <th>Department</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $dev): ?>
                <tr>
                    <td><?= htmlspecialchars($dev['ExternalIdentifier'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($dev['Model'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($dev['IpAddress'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($dev['Department'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="<?= htmlspecialchars(build_url(true, $currentPage - 1)); ?>">&larr; Prev</a>
            <?php endif; ?>

            <span><?= $currentPage; ?> / <?= $totalPages; ?></span>

            <?php if ($currentPage < $totalPages): ?>
                <a href="<?= htmlspecialchars(build_url(true, $currentPage + 1)); ?>">Next &rarr;</a>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
</div>

<style>
/* Card shell */
.card.devices-snapshot{
    padding:1.5rem;border-radius:12px;
    backdrop-filter:blur(10px);
    background:var(--bg-card,rgba(255,255,255,0.08));
    color:var(--text-dark,#f5f5f5)
}
.card.devices-snapshot header a{color:inherit;text-decoration:none}
.card.devices-snapshot h2{margin:0;font-size:1.25rem;font-weight:700}

/* Badges */
.badge{
    display:inline-block;min-width:48px;text-align:center;
    padding:.2rem .6rem;border-radius:9999px;
    background:var(--bg-light,#2d8cff);color:#fff;font-weight:600
}
.badge.warn{background:#d9534f}

/* Table */
table{width:100%;border-collapse:collapse;margin-top:1rem}
th,td{padding:.5rem .75rem;text-align:left}
thead tr{background:rgba(255,255,255,.1);font-weight:600}
tbody tr:nth-child(even){background:rgba(255,255,255,.05)}

/* Pagination */
.pagination{text-align:center;margin-top:1rem}
.pagination a{margin:0 .5rem;color:var(--text-dark,#aaddff);text-decoration:none}
.pagination span{margin:0 .5rem}
</style>
