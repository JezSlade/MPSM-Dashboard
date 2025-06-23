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
 * • Compact view shows “Devices Online (n)”.
 * • Clicking header reloads page with ?ds_exp=1 to reveal a paginated
 *   table (15 rows per page).
 * • No JavaScript needed.
 *
 * Filtering logic
 * ---------------------------------------------------------------
 *   1. If the session already has `selectedCustomer`, we pass that
 *      value as  CustomerCode=<code>  to /api/get_devices.php.
 *   2. Otherwise we fall back to a dealer-wide list by sending
 *      FilterDealerId=<DEALER_ID from .env>.
 *   3. A red ⚠️ badge appears whenever the fallback is in effect so
 *      you know you’re not scoped to a user-chosen customer.
 */

// ------------------------------------------------------------------
// 1) Resolve customer or dealer scope
$selectedCustomer   = $_SESSION['selectedCustomer'] ?? null;
$usingDefaultTenant = $selectedCustomer === null;

// Dealer ID from .env (parse_env_file runs in includes/config.php)
$dealerId = getenv('DEALER_ID') ?: 'SZ13qRwU5GtFLj0i_CbEgQ2'; // safe fallback

// ------------------------------------------------------------------
// 2) Pagination / expansion flags (query-string)
$isExpanded  = isset($_GET['ds_exp']);
$currentPage = isset($_GET['ds_page']) ? max(1, (int)$_GET['ds_page']) : 1;
$pageSize    = 15;

// ------------------------------------------------------------------
// 3) Helper → fetch devices page from API
function fetch_devices(int $page, ?string $customer, string $dealerId): ?array
{
    $params = ['PageNumber' => $page];

    if ($customer) {
        $params['CustomerCode']   = $customer;
    } else {
        $params['FilterDealerId'] = $dealerId;
    }

    $url =
        (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
        $_SERVER['HTTP_HOST'] .
        '/api/get_devices.php?' .
        http_build_query($params);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);

    return $raw ? json_decode($raw, true) : null;
}

// ------------------------------------------------------------------
// 4) Retrieve data
$firstPageData = fetch_devices(1, $selectedCustomer, $dealerId);
$totalRows     = $firstPageData['TotalRows'] ?? 0;
$totalPages    = max(1, (int)ceil($totalRows / $pageSize));

$pageData = ($isExpanded && $currentPage > 1)
    ? fetch_devices($currentPage, $selectedCustomer, $dealerId)
    : $firstPageData;

$rows = $pageData['Result'] ?? [];

// ------------------------------------------------------------------
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
            <a href="<?php echo htmlspecialchars(build_url(!$isExpanded)); ?>">
                Devices Online
                <span class="badge"><?php echo $totalRows; ?></span>
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
/* ─────────── Card Shell ─────────── */
.card.devices-snapshot{
    padding:1.5rem;
    border-radius:12px;
    backdrop-filter:blur(10px);
    background:var(--bg-card,rgba(255,255,255,0.08));
    color:var(--text-dark,#f5f5f5)
}
.card.devices-snapshot header a{
    color:inherit;text-decoration:none
}
.card.devices-snapshot h2{
    margin:0;font-size:1.25rem;font-weight:700
}
/* ─────────── Badges ─────────── */
.badge{
    display:inline-block;min-width:48px;text-align:center;
    padding:.2rem .6rem;border-radius:9999px;
    background:var(--bg-light,#2d8cff);color:#fff;font-weight:600
}
.badge.warn{background:#d9534f}
/* ─────────── Table ─────────── */
table{width:100%;border-collapse:collapse;margin-top:1rem}
th,td{padding:.5rem .75rem;text-align:left}
thead tr{background:rgba(255,255,255,.1);font-weight:600}
tbody tr:nth-child(even){background:rgba(255,255,255,.05)}
/* ─────────── Pagination ─────────── */
.pagination{text-align:center;margin-top:1rem}
.pagination a{
    margin:0 .5rem;
    color:var(--text-dark,#aaddff);
    text-decoration:none
}
.pagination span{margin:0 .5rem}
</style>
