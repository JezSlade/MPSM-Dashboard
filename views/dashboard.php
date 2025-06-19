<?php declare(strict_types=1);
// /views/dashboard.php

// — DEBUG BLOCK — (at very top)
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('log_errors','1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// 0) Helpers + config
require_once __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// 1) Customer context
$customerCode = $_GET['customer'] ?? $_COOKIE['customer'] ?? $config['DEALER_CODE'] ?? '';
if (isset($_GET['customer'])) {
  setcookie('customer', $customerCode, time()+31536000, '/');
}
$customerName = $customerCode ?: 'All Customers';
try {
  $resp = call_api($config,'POST','Customer/GetCustomers',[
    'DealerCode'=>$config['DEALER_CODE']??'',
    'PageNumber'=>1,'PageRows'=>2147483647,
    'SortColumn'=>'Description','SortOrder'=>'Asc',
  ]);
  foreach($resp['Result']??[] as $c){
    if(($c['Code']??'')===$customerCode){
      $customerName = $c['Description']??$c['Name']??$customerCode;
      break;
    }
  }
} catch(\Throwable$e){}

// 2) Cards visibility
$cardsDir = __DIR__ . '/../cards/';
$all      = array_filter(scandir($cardsDir),fn($f)=>pathinfo($f,PATHINFO_EXTENSION)==='php');
if(isset($_COOKIE['visible_cards'])){
  $sel = array_filter(explode(',',$_COOKIE['visible_cards']),'strlen');
  $visibleCards = array_values(array_intersect($sel,$all));
}else{
  $visibleCards = $all;
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
  <meta charset="UTF-8">
  <title>Dashboard – <?= htmlspecialchars($customerName) ?></title>
</head>
<body class="flex flex-col h-full bg-gray-900 text-gray-100">

  <!-- Dashboard header: Company name -->
  <header class="dashboard-header flex items-center justify-between px-6 py-3 bg-gray-800 bg-opacity-50 backdrop-blur-sm">
    <h1 class="text-xl font-semibold"><?= htmlspecialchars($customerName) ?></h1>
    <button
      class="gear-icon p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-400"
      onclick="togglePreferencesModal(true)"
      title="Preferences"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-300" fill="none"
           viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 6V4m0 16v-2m8-8h2M4 12H2m15.364
                 6.364l1.414-1.414M6.343 6.343l1.414-1.414
                 m0 12.728l-1.414-1.414M17.657
                 6.343l1.414 1.414"/>
      </svg>
    </button>
  </header>

  <?php include __DIR__ . '/../components/preferences-modal.php'; ?>

  <main class="flex-1 overflow-auto p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach($visibleCards as $c): include $cardsDir.$c; endforeach; ?>
    </div>
  </main>

  <script>
    function togglePreferencesModal(show){
      document.getElementById('preferences-modal').classList.toggle('hidden',!show);
    }
  </script>
</body>
</html>
