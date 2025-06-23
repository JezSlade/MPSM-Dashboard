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
 * Card ▸ Devices Snapshot  (PHP-only edition)
 * ---------------------------------------------------------------
 * Relies on /api/get_devices.php, which must accept:
 *   • CustomerCode   (optional – pulled from session)
 *   • PageNumber     (defaults to 1)
 * Returns JSON identical to original API spec.
 */

// --------------------------------------------------
// Helper: fetch one page from API and return decoded JSON
function fetch_devices(int $page, ?string $customer): ?array
{
    $qs = http_build_query([
        'PageNumber'   => $page,
        'CustomerCode' => $customer,
    ]);

    // Build absolute URL (same host)
    $url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
           $_SERVER['HTTP_HOST'] .
           '/api/get_devices.php?' . $qs;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 10,
    ]);

    $raw  = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        error_log("Device snapshot: CURL error $err");
        return null;
    }

    $json = json_decode($raw, true);
    return $json ?: null;
}

// --------------------------------------------------
// Determine expansion & pagination from query-string
$isExpanded   = isset($_GET['ds_exp']);
$currentPage  = isset($_GET['ds_page']) ? max(1, (int)$_GET['ds_page']) : 1;
$pageSize     = 15;

// Current customer (session set in header.php)
$selectedCustomer = $_SESSION['selectedCustomer'] ?? null;

// --------------------------------------------------
// Always fetch page 1 first to get total count
$firstPageData = fetch_devices(1, $selectedCustomer);
$totalRows     = $firstPageData['TotalRows'] ?? 0;
$totalPages    = max(1, (int)ceil($totalRows / $pageSize));

// If expanded and page>1, fetch that page’s rows
if ($isExpanded && $currentPage > 1) {
    $pageData = fetch_devices($currentPage, $selectedCustomer);
} else {
    $pageData = $firstPageData;
}

$rows = $pageData['Result'] ?? [];


// --------------------------------------------------
// URL helpers for links
function build_url(bool $expand, int $page = 1): string
{
    $params = [
        'view'    => 'sandbox',
    ];
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
                Devices Online <span class="badge"><?php echo $totalRows; ?></span>
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
/* Card shell */
.card.devices-snapshot {
    padding: 1.5rem;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    background: var(--bg-card, rgba(255,255,255,0.08));
    color: var(--text-dark, #f5f5f5);
}

.card.devices-snapshot header a {
    color: inherit;
    text-decoration: none;
}

.card.devices-snapshot h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
}

.badge {
    display: inline-block;
    min-width: 48px;
    text-align: center;
    padding: .2rem .6rem;
    border-radius: 9999px;
    background: var(--bg-light, #2d8cff);
    color: #fff;
    font-weight: 600;
}

/* Table */
table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
th, td { padding: .5rem .75rem; text-align: left; }
thead tr { background: rgba(255,255,255,.1); font-weight: 600; }
tbody tr:nth-child(even) { background: rgba(255,255,255,.05); }

/* Pagination */
.pagination { margin-top: 1rem; text-align: center; }
.pagination a { margin: 0 .5rem; color: var(--text-dark, #aaddff); text-decoration: none; }
.pagination span { margin: 0 .5rem; }
</style>
