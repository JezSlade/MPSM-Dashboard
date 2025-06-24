<?php
require_once __DIR__.'/../includes/card_base.php';
require_once __DIR__.'/../includes/env_parser.php';
require_once __DIR__.'/../includes/api_client.php';
require_once __DIR__.'/../includes/table_helper.php';
try {
  $resp=api_request('Customer/GetCustomers',[
    'DealerCode'=>DEALER_CODE,'PageNumber'=>1,'PageRows'=>15,
    'SortColumn'=>'Description','SortOrder'=>'Asc'
  ]);
  $rows=($resp['status']===200&&is_array($resp['data']['Result']??null))
      ?$resp['data']['Result']:[];
  $err=null;
}catch(RuntimeException$e){
  $rows=[]; $err=$e->getMessage();
}
$cols=['CustomerCode'=>'Code','Description'=>'Description'];
?>
<div class="card customers-card">
  <header class="card-header">
    <h2>Customers</h2>
    <form method="get"><button type="submit">Refresh</button></form>
  </header>
  <div class="card-body">
    <?php if($err):?>
      <div class="error">Failed: <?=htmlspecialchars($err,ENT_QUOTES)?></div>
    <?php endif;?>
    <?php renderDataTable($rows,['columns'=>$cols,'rowsPerPage'=>15]);?>
  </div>
</div>
