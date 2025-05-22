<?php
// diagnostics.php — Concise, Detailed Server Snapshot
header('Content-Type: application/json');
require __DIR__ . '/core/bootstrap.php';

function h($p){return md5_file($p)?:'';}
function scan($d,&$o){foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($d,FilesystemIterator::SKIP_DOTS)) as $f){$r=substr($f->getPathname(),strlen(__DIR__)+1);if($f->isFile()&&!preg_match('#^(vendor|logs)/#',$r))$o[$r]=h($f->getPathname());}}

$r = [];

// PHP + INI
$r['php'] = [
  'version'=>PHP_VERSION,
  'extensions'=>get_loaded_extensions(),
  'ini'=>[
    'display_errors'=>ini_get('display_errors'),
    'error_reporting'=>ini_get('error_reporting'),
    'memory_limit'=>ini_get('memory_limit'),
    'max_execution_time'=>ini_get('max_execution_time')
  ]
];

// ENV (.env masked)
if(is_readable($e=__DIR__.'/.env')){
  foreach(parse_ini_file($e,false,INI_SCANNER_RAW) as $k=>$v){
    $r['env'][$k] = preg_match('/(PASS|SECRET)/',$k)?'*****':$v;
  }
}

// Database
try{
  $db=get_db();
  $tbl=$db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
  $r['db']=[
    'name'=>DB_NAME,
    'tables'=>$tbl,
    'counts'=>array_map(fn($t)=>$db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn(),$tbl),
    'migrations'=>$db->query("SELECT COUNT(*) FROM migrations")->fetchColumn()
  ];
}catch(Exception$e){$r['db_error']=$e->getMessage();}

// ACL
try{
  $r['acl']=[
    'roles'=>$db->query("SELECT name FROM roles")->fetchAll(PDO::FETCH_COLUMN),
    'permissions'=>$db->query("SELECT name FROM permissions")->fetchAll(PDO::FETCH_COLUMN)
  ];
}catch(Exception$e){$r['acl_error']=$e->getMessage();}

// Widgets
try{
  $all=get_all_widgets();
  $usr=$_SESSION['user_id']?get_user_widgets():[];
  $r['widgets']=[
    'total'=>count($all),
    'user'=>count($usr),
    'sample'=>array_slice(array_column($all,'name'),0,5)
  ];
}catch(Exception$e){$r['widgets_error']=$e->getMessage();}

// File hashes
scan(__DIR__,$files);
$r['files_md5']=$files;

// Output
echo json_encode($r, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
