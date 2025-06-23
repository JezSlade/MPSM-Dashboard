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
 * Card ▸ Devices Snapshot  (PHP-only)
 * ---------------------------------------------------------------
 * • Shows total devices online for the active customer.
 * • Click header to expand a paginated table (15 / page).
 * • Pure PHP, no JavaScript.
 *
 * Fallback logic:
 *   – If $_SESSION['selectedCustomer'] is not set, we default to the
 *     project’s mandatory tenant “W9OPXL0YDK” and display a ⚠ badge
 *     to remind you this is NOT the user-selected customer.
 */

// --------------------------------------------------
// Grab customer (with fallback)
$selectedCustomer   = $_SESSION['selectedCustomer'] ?? 'W9OPXL0YDK';
$usingDefaultTenant = !isset($_SESSION['selectedCustomer']);

// Query-string flags
$isExpanded  = isset($_GET['ds_exp']);
$currentPage = isset($_GET['ds_page']) ? max(1, (int)$_GET['ds_page']) : 1;
$pageSize    = 15;

// --------------------------------------------------
// Helper → call API safely
function fetch_devices(int $page, string $customer): ?array
{
    // Build params
    $params = ['PageNumber' => $page];
    if ($customer !== '') {
        $params['CustomerCode'] = $customer;
    }
    $qs  = http_build_query($params);

    $url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
           $_SERVER['HTTP_HOST'] .
           '/api/get_devices.php?' . $qs;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);

    $json = $raw ? json_decode($raw, true) : null;
    return $json ?: null;
}

// Fetch first page to get totals
$firstPageData = fetch_devices(1, $selectedCustomer);
$totalRows     = $firstPageData['TotalRows'] ?? 0;
$totalPages    = max(1, (int)ceil($totalRows / $pageSize));

// Fetch current page rows (may be page 1 again)
$pageData = ($isExpanded && $currentPage > 1)
    ? fetch_devices($currentPage, $selectedCustomer)
    : $firstPageData;

$rows = $pageData['Result'] ?? [];

// --------------------------------------------------
// URL helper
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
            <a href="<?php echo htmlspecialchars(build_url(!$isExpanded)); ?>">
                Devices Online
                <span class="badge"><?php echo $totalRows; ?></span>
                <?php if ($usingDefaultTenant): ?>
                    <span class="badge warn" title="Default customer in use">⚠️</span>
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
                    <td><?php echo htmlspecialchars($dev['ExternalIdentifier'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($dev['Model'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($dev['IpAddress'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($dev['Department'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="<?php echo htmlspecialchars(build_url(true, $currentPage - 1)); ?>">&larr; Prev</a>
            <?php endif; ?>

            <span><?php echo $currentPage; ?> / <?php echo $totalPages; ?></span>

            <?php if ($currentPage < $totalPages): ?>
                <a href="<?php echo htmlspecialchars(build_url(true, $currentPage + 1)); ?>">Next &rarr;</a>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
</div>

<style>
.card.devices-snapshot{
    padding:1.5rem;border-radius:12px;
    backdrop-filter:blur(10px);
    background:var(--bg-card,rgba(255,255,255,0.08));
    color:var(--text-dark,#f5f5f5)
}
.card.devices-snapshot header a{color:inherit;text-decoration:none}
.card.devices-snapshot h2{margin:0;font-size:1.25rem;font-weight:700}
.badge{
    display:inline-block;min-width:48px;text-align:center;
    padding:.2rem .6rem;border-radius:9999px;
    background:var(--bg-light,#2d8cff);color:#fff;font-weight:600
}
.badge.warn{background:#d9534f}
table{width:100%;border-collapse:collapse;margin-top:1rem}
th,td{padding:.5rem .75rem;text-align:left}
thead tr{background:rgba(255,255,255,.1);font-weight:600}
tbody tr:nth-child(even){background:rgba(255,255,255,.05)}
.pagination{text-align:center;margin-top:1rem}
.pagination a{margin:0 .5rem;color:var(--text-dark,#aaddff);text-decoration:none}
.pagination span{margin:0 .5rem}
</style>
