<?php declare(strict_types=1);
// includes/navigation.php
if(strpos($_SERVER['REQUEST_URI'],'/api/')===0) return;
require_once __DIR__ . '/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');
$payload=['DealerCode'=>$config['DEALER_CODE']??'','PageNumber'=>1,'PageRows'=>2147483647,'SortColumn'=>'Description','SortOrder'=>'Asc'];
try{$resp=call_api($config,'POST','Customer/GetCustomers',$payload);$customers=$resp['customers']??$resp['Result']??[];}catch(Throwable$e){$customers=[];}
$currentCode=$_GET['customer']??$_COOKIE['customer']??'';$currentName='';
foreach($customers as$c){if(($c['Code']??'')===$currentCode){$currentName=$c['Description']??$c['Name']??'';break;}}
?>
<nav class="flex items-center p-4 bg-gray-800 bg-opacity-50 backdrop-blur-sm glass">
  <div class="w-64">
    <label for="nav-customer-combobox" class="sr-only">Choose Customer</label>
    <input list="nav-customer-list" id="nav-customer-combobox"
      class="h-8 w-full text-sm bg-transparent text-white border border-gray-600 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
      placeholder="— choose a customer —"
      value="<?=htmlspecialchars($currentName)?>"/>
    <datalist id="nav-customer-list">
      <?php foreach($customers as$c):?>
        <?php $code=htmlspecialchars($c['Code']??''); $name=htmlspecialchars($c['Description']??$c['Name']??$code); ?>
        <option data-code="<?=$code?>" value="<?=$name?>"></option>
      <?php endforeach;?>
    </datalist>
  </div>
</nav>
<script>
document.addEventListener('DOMContentLoaded',()=>{
  const inp=document.getElementById('nav-customer-combobox');
  const opts=document.getElementById('nav-customer-list').options;
  inp.addEventListener('change',()=>{
    const sel=Array.from(opts).find(o=>o.value===inp.value);
    const code=sel?sel.dataset.code:'';
    if(code){
      document.cookie=`customer=${encodeURIComponent(code)};path=/;max-age=${60*60*24*365}`;
      location.href=`${location.pathname}?customer=${encodeURIComponent(code)}`;
    }
  });
});
</script>
