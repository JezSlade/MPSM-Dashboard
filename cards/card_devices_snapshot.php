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
 * Devices Snapshot  – uses shared render_table helper
 * ------------------------------------------------------------------
 * • Calls /api/get_devices.php with the single required filter
 *   (CustomerCode or FilterDealerId).
 * • Renders count + paginated table via includes/table_helper.php
 *   so all cards share identical styling.
 * • Pure PHP, no JS.
 */

// 0 › bring in the shared helper  (correct path = /includes/)
$tableHelperPath = __DIR__ . '/../includes/table_helper.php';
if (is_readable($tableHelperPath)) {
    require_once $tableHelperPath;   // provides render_table()
} else {
    echo '<p class="error">includes/table_helper.php not found.</p>';
    return;
}

// 1 › determine filter param
$customerCode = $_SESSION['selectedCustomer'] ?? null;
$dealerId     = getenv('DEALER_ID') ?: 'SZ13qRwU5GtFLj0i_CbEgQ2';

$params = [
    'PageNumber' => isset($_GET['ds_page']) ? max(1, (int)$_GET['ds_page']) : 1,
];
if ($customerCode) {
    $params['CustomerCode'] = $customerCode;
} else {
    $params['FilterDealerId'] = $dealerId;
}

// 2 › fetch data
$apiUrl =
    (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
    $_SERVER['HTTP_HOST'] .
    '/api/get_devices.php?' .
    http_build_query($params);

$raw  = file_get_contents($apiUrl);
$json = $raw ? json_decode($raw, true) : null;

$totalRows = ($json['IsValid'] ?? false) ? ($json['TotalRows'] ?? 0) : 0;
$rows      = ($json['IsValid'] ?? false) ? ($json['Result'] ?? [])   : [];

// 3 › pagination maths
$pageSize    = 15;
$currentPage = $params['PageNumber'];
$totalPages  = max(1, (int)ceil($totalRows / $pageSize));
$isExpanded  = isset($_GET['ds_exp']);

function self_url(bool $expand, int $page = 1): string
{
    $p = ['view' => 'sandbox'];
    if ($expand) {
        $p['ds_exp']  = '1';
        $p['ds_page'] = $page;
    }
    return '/index.php?' . http_build_query($p);
}

// 4 › headers config for helper
$tableHeaders = [
    'ExternalIdentifier' => 'Equipment ID',
    'Model'              => 'Model',
    'IpAddress'          => 'IP Address',
    'Department'         => 'Department',
];
?>
<!-- ────────── CARD ────────── -->
<div class="card devices-snapshot">
    <header>
        <h2>
            <a href="<?= htmlspecialchars(self_url(!$isExpanded)); ?>">
                Devices Online <span class="badge"><?= $totalRows; ?></span>
            </a>
        </h2>
    </header>

<?php if ($isExpanded): ?>
    <?= render_table($tableHeaders, $rows); ?>

    <div class="pagination">
        <?php if ($currentPage > 1): ?>
            <a href="<?= htmlspecialchars(self_url(true, $currentPage - 1)); ?>">&larr; Prev</a>
        <?php endif; ?>
        <span><?= $currentPage; ?> / <?= $totalPages; ?></span>
        <?php if ($currentPage < $totalPages): ?>
            <a href="<?= htmlspecialchars(self_url(true, $currentPage + 1)); ?>">Next &rarr;</a>
        <?php endif; ?>
    </div>
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
/* Pagination (helper already styles table) */
.pagination{text-align:center;margin-top:1rem}
.pagination a{margin:0 .5rem;color:var(--text-dark,#aaddff);text-decoration:none}
.pagination span{margin:0 .5rem}
</style>
