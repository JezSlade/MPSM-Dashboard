<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

require_once __DIR__ . '/../includes/config.php';

$customerCode = $_GET['customer'] ?? 'W9OPXL0YDK';
$currentPage = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;

$apiUrl = APP_BASE_URL . "api/get_devices.php?customer=" . urlencode($customerCode);
$response = @file_get_contents($apiUrl);
$data = json_decode($response, true);

if (!isset($data['Result']) || !is_array($data['Result'])) {
    echo "<div class='device-card error'>Unable to fetch device list.</div>";
    return;
}

$devices = $data['Result'];
$totalDevices = count($devices);
$totalPages = ceil($totalDevices / $perPage);
$offset = ($currentPage - 1) * $perPage;
$paginatedDevices = array_slice($devices, $offset, $perPage);

$columns = ['Equipment ID', 'Department', 'IpAddress', 'SerialNumber'];
?>

<div class="device-card"
     data-card-id="printer_card"
     data-dashboard="<?= htmlspecialchars($_GET['dashboard'] ?? 'default') ?>"
     data-customer-code="<?= htmlspecialchars($customerCode) ?>">

  <div class="card-header compact-header">
    <input type="text" id="device-search" placeholder="Search devices..." class="search-box" />
  </div>

  <?php if (empty($paginatedDevices)): ?>
    <p>No devices found for this page.</p>
  <?php else: ?>
    <div class="device-table-container">
      <table class="device-table" id="device-table">
        <thead>
          <tr>
            <?php foreach ($columns as $key): ?>
              <th><?= htmlspecialchars($key) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($paginatedDevices as $device): ?>
            <tr class="device-row">
              <?php foreach ($columns as $key): ?>
                <?php if ($key === 'Equipment ID'): ?>
                  <td>
                    <?= htmlspecialchars($device[$key] ?? '') ?>
                    <?php if (!empty($device['Id'])): ?>
                      <button class="drilldown-btn" data-device-id="<?= htmlspecialchars($device['Id']) ?>" title="View Details">
                        <span class="icon">🔍</span>
                      </button>
                    <?php endif; ?>
                  </td>
                <?php else: ?>
                  <td><?= htmlspecialchars($device[$key] ?? '') ?></td>
                <?php endif; ?>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="pagination-nav">
      <?php if ($currentPage > 1): ?>
        <a href="?customer=<?= urlencode($customerCode) ?>&page=<?= $currentPage - 1 ?>" class="page-link">← Prev</a>
      <?php endif; ?>
      <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>
      <?php if ($currentPage < $totalPages): ?>
        <a href="?customer=<?= urlencode($customerCode) ?>&page=<?= $currentPage + 1 ?>" class="page-link">Next →</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Modal -->
<div id="device-detail-modal" class="modal hidden">
  <div class="modal-content">
    <button class="modal-close" onclick="hideModal()">×</button>
    <div id="modal-body">Loading device details...</div>
  </div>
</div>

<style>
.card-header.compact-header {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding: 0.5rem 1rem;
  margin-bottom: 0.5rem;
}
.search-box {
  padding: 0.3rem 0.8rem;
  border-radius: 0.4rem;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: rgba(255, 255, 255, 0.08);
  color: white;
  font-size: 0.9rem;
  min-width: 250px;
}

.device-table-container {
  overflow-x: auto;
  border-radius: 1rem;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(8px);
  padding: 1rem;
}

.device-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
  color: inherit;
}

.device-table th,
.device-table td {
  padding: 0.4rem 0.6rem;
  text-align: left;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  white-space: nowrap;
}

.device-table thead {
  background: rgba(255, 255, 255, 0.08);
  font-weight: bold;
}

.drilldown-btn {
  background: none;
  border: none;
  padding: 0;
  margin-left: 0.4rem;
  cursor: pointer;
  vertical-align: middle;
}
.drilldown-btn .icon {
  font-size: 0.75rem;
  line-height: 1;
  display: inline-block;
  transform: translateY(1px);
}

.pagination-nav {
  margin-top: 1rem;
  text-align: center;
}
.page-link {
  display: inline-block;
  padding: 0.4rem 0.8rem;
  margin: 0 0.2rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.4rem;
  color: white;
  text-decoration: none;
  transition: background 0.3s, transform 0.2s;
}
.page-link:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: scale(1.05);
}

.modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal.hidden { display: none; }
.modal-content {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  padding: 1.5rem;
  border-radius: 1rem;
  max-width: 90%;
  max-height: 80vh;
  overflow: auto;
  backdrop-filter: blur(10px);
}
.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  font-size: 1.5rem;
  background: none;
  color: white;
  border: none;
  cursor: pointer;
}
</style>

<script>
function showModal(content) {
  document.getElementById('modal-body').innerHTML = content;
  document.getElementById('device-detail-modal').classList.remove('hidden');
}
function hideModal() {
  document.getElementById('device-detail-modal').classList.add('hidden');
}

// Drilldown binding
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.drilldown-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.getAttribute('data-device-id');
      if (!id) return;
      showModal('Loading device details...');
      try {
        const res = await fetch(`api/get_device_detail.php?id=${encodeURIComponent(id)}`);
        const json = await res.json();
        if (json.success && json.data?.Result) {
          const detail = json.data.Result;
          let output = '<table>';
          for (const [key, val] of Object.entries(detail)) {
            const cleaned = (typeof val === 'string' ? val.trim() : val);
            if (
              cleaned === null ||
              cleaned === '' ||
              cleaned === '0' ||
              cleaned === 0 ||
              cleaned === 'DEFAULT' ||
              (Array.isArray(val))
            ) continue;
            output += `<tr><td><strong>${key}</strong></td><td>${cleaned}</td></tr>`;
          }
          output += '</table>';
          showModal(output);
        } else {
          showModal('No device detail found.');
        }
      } catch (e) {
        showModal('Error loading detail.');
      }
    });
  });

  // Filter table by any text
  const searchBox = document.getElementById('device-search');
  if (searchBox) {
    searchBox.addEventListener('input', () => {
      const term = searchBox.value.toLowerCase();
      const rows = document.querySelectorAll('.device-row');
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
      });
    });
  }
});
</script>
