<?php declare(strict_types=1);
require_once __DIR__.'/env_parser.php';
require_once __DIR__.'/api_client.php';
$resp=api_request('Customer/GetCustomers',[
  'DealerCode'=>DEALER_CODE,'PageNumber'=>1,'PageRows'=>9999,
  'SortColumn'=>'Description','SortOrder'=>'Asc'
]);
$list=($resp['status']===200&&is_array($resp['data']['Result']??null))
    ? $resp['data']['Result'] : [];
?>
<nav class="main-nav">
  <label for="customer-select">Customer:</label>
  <select id="customer-select" name="CustomerCode">
    <?php foreach($list as $c):
      $code=(string)($c['CustomerCode']??'');
      $desc=(string)($c['Description']??'');
      if($code==='') continue;
    ?>
      <option value="<?=htmlspecialchars($code,ENT_QUOTES)?>">
        <?=htmlspecialchars($desc,ENT_QUOTES)?>
      </option>
    <?php endforeach;?>
  </select>
</nav>
