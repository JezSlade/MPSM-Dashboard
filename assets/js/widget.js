// assets/js/widget.js
// loader, renderer, export, dev console

window.devConsole = {
  setTablePageSize: n => window.tableConfig.pageSize = n,
  setExportFormats: arr => window.tableConfig.exportFormats = arr,
};

(async function(){
  window.globalSettings = await (await fetch('get_settings.php')).json();
  initApp();
})();

function initApp(){
  const defs = window.widgetDefinitions, perms = window.userPermissions;
  buildMenu(defs, perms);
  loadWidget('DashboardOverview');
}

function buildMenu(defs, perms){
  const menu = document.getElementById('widget-menu');
  defs.forEach(w => {
    if (!perms.includes(w.permission)) return;
    const li = document.createElement('li');
    li.className = 'p-2 cursor-pointer neu mb-2 flex justify-between items-center';
    li.textContent = w.displayName;
    if (w.description){
      const tip = document.createElement('span');
      tip.textContent = '❓'; tip.title = w.description;
      li.append(tip);
    }
    li.onclick = ()=> loadWidget(w.name);
    menu.append(li);
  });
}

function loadWidget(name){
  const w = window.widgetDefinitions.find(d=>d.name===name);
  const c = document.getElementById('main-content');
  c.innerHTML=''; if(!w)return;
  if(w.method==='dashboard') return renderDashboard(c);
  const limit = parseInt(window.globalSettings.debug_widget_row_limit,10);
  fetchAndRender(c,w,limit);
  if(w.name==='DebugConsole'){
    const intv = parseInt(window.globalSettings.widget_refresh_interval,10);
    if(intv>0) setInterval(()=>fetchAndRender(c,w,limit),intv*1000);
  }
}

function renderDashboard(container){
  const defs = window.widgetDefinitions.filter(w=>w.method!=='dashboard');
  let html='<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">';
  defs.forEach(d=> html+=`<div class="card"><h4>${d.displayName}</h4><div id="snap-${d.name}">…</div></div>`);
  html+='</div>'; container.innerHTML=html;
  defs.forEach(d=>{
    const snap=document.getElementById(`snap-${d.name}`);
    if(d.method==='count'){
      fetch(d.endpoint).then(r=>r.json()).then(data=>snap.textContent = Array.isArray(data)?data.length:(data.count||0));
    } else snap.textContent='OK';
  });
}

async function fetchAndRender(c,w,limit){
  const url=new URL(w.endpoint,location.origin);
  if(w.params) for(const[k,v] of Object.entries(w.params)){
    url.searchParams.set(k, v===null&&k==='limit'?limit:v);
  }
  try{
    const data=await (await fetch(url)).json();
    renderWidget(c,w,data);
    addExport(c,w,data);
  }catch(e){
    c.innerHTML=`<div class="text-red-600">Error loading ${w.displayName}</div>`;
    console.error(e);
  }
}

function renderWidget(c,w,data){
  c.innerHTML = `<h3 class="widget-header mb-2 flex justify-between items-center">
                   ${w.displayName}
                   ${w.helpLink?`<a href="${w.helpLink}" target="_blank" class="text-sm">Learn more</a>`:''}
                 </h3>`;
  if(w.method==='custom'){ c.innerHTML+=data.html; return; }
  switch(w.method){
    case 'count':
      const cnt = Array.isArray(data)?data.length:(data.count||0);
      c.innerHTML+=`<div class="text-3xl">${cnt}</div>`; break;
    case 'list':
      c.innerHTML+=`<ul class="list-disc list-inside">${data.map(i=>`<li>${JSON.stringify(i)}</li>`).join('')}</ul>`; break;
    case 'table':
      if(!Array.isArray(data)||!data.length){ c.innerHTML+='<div>No data</div>'; break; }
      const cols=Object.keys(data[0]);
      let tbl='<table class="w-full mb-2"><thead><tr>'+cols.map(c=>`<th class="p-2 border">${c}</th>`).join('')+'</tr></thead><tbody>';
      data.forEach(r=>tbl+='<tr>'+cols.map(c=>`<td class="p-2 border">${r[c]||''}</td>`).join('')+'</tr>');
      tbl+='</tbody></table>'; c.innerHTML+=tbl; break;
    default:
      c.innerHTML+=`<pre>${JSON.stringify(data,null,2)}</pre>`;
  }
}

function addExport(c,w,data){
  const btn=document.createElement('button');
  btn.textContent='Export'; btn.className='neu p-1 mb-2';
  btn.onclick=()=>exportData(w,data);
  c.insertBefore(btn,c.firstChild);
}

function exportData(w,data){
  const fmts=(window.globalSettings.table_export_formats||'csv,json').split(',');
  if(fmts.includes('csv')&&Array.isArray(data)&&data.length){
    const cols=Object.keys(data[0]);
    const rows=[cols.join(','),...data.map(r=>cols.map(c=>`"${(r[c]||'').toString().replace(/"/g,'""')}"`).join(','))];
    download(rows.join('\n'),`${w.name}.csv`,'text/csv');
  } else download(JSON.stringify(data,null,2),`${w.name}.json`,'application/json');
}

function download(content,fn,mime){
  const blob=new Blob([content],{type:mime});
  const a=document.createElement('a');
  a.href=URL.createObjectURL(blob);
  a.download=fn; a.click();
  URL.revokeObjectURL(a.href);
}
