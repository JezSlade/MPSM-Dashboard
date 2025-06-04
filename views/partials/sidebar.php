<?php
// /public/mpsm/views/partials/sidebar.php
$menuItems = [
    'dashboard' => [
        'label' => 'Dashboard',
        'icon'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                     <path d="M3 13h8V3H3v10z"></path>
                     <path d="M13 21h8v-6h-8v6z"></path>
                     <path d="M13 13h8V3h-8v10z"></path>
                     <path d="M3 21h8v-4H3v4z"></path>
                   </svg>',
    ],
    'customers' => [
        'label' => 'Customers',
        'icon'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                     <path d="M20 21v-2a4 4 0 0 0-3-3.87"></path>
                     <path d="M4 21v-2a4 4 0 0 1 3-3.87"></path>
                     <circle cx="12" cy="7" r="4"></circle>
                   </svg>',
    ],
    'developer' => [
        'label' => 'Dev Tools',
        'icon'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                     <polyline points="16 18 22 12 16 6"></polyline>
                     <polyline points="8 6 2 12 8 18"></polyline>
                   </svg>',
    ],
];

$currentModule = isset($_GET['module']) ? $_GET['module'] : 'dashboard';
?>

<aside class="app-sidebar">
  <nav>
    <ul>
      <?php foreach ($menuItems as $key => $item): ?>
        <?php if (canViewModule($key)): ?>
          <li class="<?= $currentModule === $key ? 'active' : '' ?>">
            <a href="index.php?module=<?= $key ?>">
              <span class="icon"><?= $item['icon'] ?></span>
              <span class="label"><?= $item['label'] ?></span>
            </a>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
  </nav>
</aside>
