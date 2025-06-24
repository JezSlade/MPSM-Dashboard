<?php
require_once __DIR__ . '/../includes/card_base.php';
?>
<div class="card customers-card">
  <header class="card-header"><h2>Customers</h2></header>
  <div class="card-body" id="customers-container"></div>
  <footer class="card-footer"><button data-action="refresh">Refresh</button></footer>
</div>
<script type="module">
import { fetchJson } from '/js/api.js';
import { renderTable } from '/js/ui_helpers.js';
const cont = document.getElementById('customers-container'), PAGE=15;
async function load(p=1){
  try{
    const data=await fetchJson(`/api/get_customers.php?PageNumber=${p}&PageRows=${PAGE}`);
    cont.innerHTML=renderTable({columns:['CustomerCode','Description'],rows:data.Result,page:p,totalPages:Math.ceil(data.TotalRows/PAGE),onPageChange:load});
  }catch(e){cont.innerHTML='<div class="error">Failed to load</div>';console.error(e);}
}
document.querySelector('.customers-card [data-action="refresh"]').addEventListener('click',()=>load());
load();
</script>
