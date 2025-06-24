<?php declare(strict_types=1);
require_once __DIR__.'/../includes/env_parser.php';
require_once __DIR__.'/../includes/plugin_auth.php'; verify_plugin_bearer();
require_once __DIR__.'/../includes/cors.php'; send_cors_headers();
require_once __DIR__.'/../includes/logger.php'; log_request();
require_once __DIR__.'/../includes/api_client.php';
header('Content-Type:application/json');
$p=isset($_GET['PageNumber'])?(int)$_GET['PageNumber']:1;
$r=isset($_GET['PageRows'])?(int)$_GET['PageRows']:15;
$sc=$_GET['SortColumn']??'Description';
$so=in_array($_GET['SortOrder']??'Asc',['Asc','Desc'],true)?$_GET['SortOrder']:'Asc';
try {
  $resp=api_request('Customer/GetCustomers',[
    'DealerCode'=>DEALER_CODE,'PageNumber'=>$p,'PageRows'=>$r,
    'SortColumn'=>$sc,'SortOrder'=>$so
  ]);
  http_response_code($resp['status']);
  echo json_encode($resp['data']);
}catch(RuntimeException$e){
  http_response_code(502);
  echo json_encode(['error'=>'Upstream failed','message'=>$e->getMessage()]);
}
