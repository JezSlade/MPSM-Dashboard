<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/debug.php';   // unified debug

/*──────────────────────────────────────────────────────────
 | 1. helper: render_table() – fallback if main helper missing
 *─────────────────────────────────────────────────────────*/
$helper = __DIR__ . '/../includes/table_helper.php';
if (is_readable($helper)) {
    require_once $helper;
}
if (!function_exists('render_table')) {
    // cell formatter → handles scalars & arrays
    function cell_html($v): string
    {
        if (is_array($v)) {
            $h = '<table class="ds-nested"><tbody>';
            foreach ($v as $k => $val) {
                $h .= '<tr><th>'.htmlspecialchars($k).'</th><td>'.
                      htmlspecialchars(is_scalar($val)?(string)$val:json_encode($val)).'</td></tr>';
            }
            return $h.'</tbody></table>';
        }
        return htmlspecialchars((string)$v);
    }

    function render_table(array $headers, array $rows): string
    {
        $o = '<table class="ds"><thead><tr>';
        foreach ($headers as $label) $o .= '<th>'.htmlspecialchars($label).'</th>';
        $o .= '</tr></thead><tbody>';
        if (!$rows) {
            $o .= '<tr><td colspan="'.count($headers).'">No data</td></tr>';
        } else {
            foreach ($rows as $row) {
                $o .= '<tr>';
                foreach ($headers as $key => $_) {
                    $o .= '<td>'.cell_html($row[$key] ?? '').'</td>';
                }
                $o .= '</tr>';
            }
        }
        return $o.'</tbody></table>';
    }
}

/*──────────────────────────────────────────────────────────
 | 2. build request to /api/get_devices.php
 *─────────────────────────────────────────────────────────*/
$customer = $_SESSION['selectedCustomer'] ?? null;
$dealer   = getenv('DEALER_ID') ?: 'SZ13qRwU5GtFLj0i_CbEgQ2';

$pageSize = 15;
$page     = isset($_GET['ds_page']) ? max(1, (int)$_GET['ds_page']) : 1;

$body = [
    'PageNumber' => $page,
    'PageRows'   => $pageSize,
    'SortColumn' => 'ExternalIdentifier',   // valid column
    'SortOrder'  => 'Asc',
] + ($customer ? ['CustomerCode'=>$customer] : ['FilterDealerId'=>$dealer]);

$apiUrl =
    (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
    $_SERVER['HTTP_HOST'] .
    '/api/get_devices.php';

$ch = curl_init($apiUrl);
curl_setopt_array($ch,[
    CURLOPT_POST=>true,
    CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
    CURLOPT_POSTFIELDS=>json_encode($body),
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_TIMEOUT=>10
]);
$raw  = curl_exec($ch);
curl_close($ch);

$data  = $raw ? json_decode($raw, true) : null;
$total = ($data['IsValid'] ?? false) ? ($data['TotalRows'] ?? 0) : 0;
$rows  = ($data['IsValid'] ?? false) ? ($data['Result']    ?? []) : [];

/*──────────────────────────────────────────────────────────
 | 3. dynamic headers from first row
 *─────────────────────────────────────────────────────────*/
$headers = [];
if ($rows) {
    foreach (array_keys($rows[0]) as $key) {
        // prettify key → "Human Label"
        $label = ucwords(str_replace(['_', '-'], ' ',
                 preg_replace('/(?<!^)([A-Z])/', ' $1', $key)));
        $headers[$key] = $label;
    }
}

/*──────────────────────────────────────────────────────────
 | 4. pagination helpers
 *─────────────────────────────────────────────────────────*/
$expanded   = isset($_GET['ds_exp']);
$totalPages = max(1, (int)ceil($total / $pageSize));

function self_url(bool $exp, int $p = 1): string
{
    $q = ['view' => 'sandbox'];
    if ($exp) {
        $q['ds_exp']  = 1;
        $q['ds_page'] = $p;
    }
    return '/index.php?' . http_build_query($q);
}
?>
<div class="card devices-snapshot">
  <h2 style="margin:0;font-size:1.25rem;font-weight:700">
    <a href="<?= htmlspecialchars(self_url(!$expanded)); ?>" style="text-decoration:none;color:inherit">
        Devices Online <span class="badge"><?= $total; ?></span>
    </a>
  </h2>

<?php if ($expanded): ?>
  <?= render_table($headers, $rows); ?>

  <div class="pagination" style="text-align:center;margin-top:1rem">
    <?php if ($page > 1): ?>
      <a href="<?= htmlspecialchars(self_url(true, $page - 1)); ?>">&larr; Prev</a>
    <?php endif; ?>
    <span><?= $page; ?> / <?= $totalPages; ?></span>
    <?php if ($page < $totalPages): ?>
      <a href="<?= htmlspecialchars(self_url(true, $page + 1)); ?>">Next &rarr;</a>
    <?php endif; ?>
  </div>
<?php endif; ?>
</div>

<style>
.card.devices-snapshot{
    padding:1.5rem;border-radius:12px;
    backdrop-filter:blur(10px);
    background:var(--bg-card,rgba(255,255,255,.08));
    color:var(--text-dark,#f5f5f5)
}
.badge{
    display:inline-block;min-width:48px;text-align:center;
    padding:.2rem .6rem;border-radius:9999px;
    background:var(--bg-light,#2d8cff);color:#fff;font-weight:600
}
/* main table */
.ds th,.ds td{padding:.5rem .75rem;text-align:left}
.ds thead tr{background:rgba(255,255,255,.1);font-weight:600}
.ds tbody tr:nth-child(even){background:rgba(255,255,255,.05)}
/* nested mini-table for array cells */
.ds-nested{border-collapse:collapse;margin:0}
.ds-nested th,.ds-nested td{padding:2px 4px;font-size:.75rem;text-align:left}
.ds-nested th{background:rgba(255,255,255,.15);font-weight:600}
.ds-nested tr:nth-child(even){background:rgba(255,255,255,.05)}
.pagination a{margin:0 .5rem;color:var(--text-dark,#aaddff);text-decoration:none}
</style>
