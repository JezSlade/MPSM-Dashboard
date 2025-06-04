<?php
// /public/mpsm/views/partials/header.php
$currentRole = getCurrentRole();
$allRoles    = getAllRoles();
?>

<header class="app-header">
  <div class="header-left">
    <h1 class="app-title">MPSM Dashboard</h1>
  </div>
  <div class="header-right">
    <span class="version-tag">v0.1</span>
    <div class="role-switcher">
      <label for="roleSelect">Role:</label>
      <select id="roleSelect">
        <?php foreach ($allRoles as $role): ?>
          <option value="<?= htmlspecialchars($role) ?>"
            <?= $role === $currentRole ? 'selected' : '' ?>>
            <?= ucfirst($role) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
</header>

<script>
  // When the role changes, reload with ?action=switch&role=<newRole>
  document.getElementById('roleSelect').addEventListener('change', function() {
    var role = this.value;
    window.location.href = 'index.php?action=switch&role=' + role;
  });
</script>
