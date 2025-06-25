<?php
/**
 * index.php — Entrypoint with manual drag-and-drop grid layout (no SortableJS)
 *
 * Changelog:
 * - Replaced SortableJS with manual HTML5 drag-and-drop handlers based on provided demo.
 * - Introduced a 12×8 CSS grid with explicit spans for card sizes.
 * - Added controls: Save Layout, Reset Layout, Toggle Debug.
 * - Appended full changelog at end after closing </html>.
 */
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');

// Placeholder constant
define('DEALER_CODE', getenv('DEALER_CODE') ?: 'N/A');
?>
<!DOCTYPE html>
<html lang="en" class="h-full dark" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard for <?php echo htmlspecialchars(DEALER_CODE, ENT_QUOTES); ?></title>

  <!-- Prevent favicon 404 -->
  <link rel="icon" href="data:;base64,">

  <!-- Tailwind CSS & Global styles -->
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:Consolas, monospace; background:#f5f7fa; padding:20px; }
    .dashboard-container { max-width:1200px; margin:0 auto; }
    .controls { margin-bottom:20px; text-align:center; }
    .btn {
      background:#667eea; color:#fff; border:none; padding:10px 20px;
      border-radius:6px; cursor:pointer; margin:0 5px; font-size:14px;
      transition:background .3s;
    }
    .btn:hover { background:#5a67d8; }
    .dashboard-grid {
      display:grid;
      grid-template-columns:repeat(12,1fr);
      grid-template-rows:repeat(8,120px);
      gap:15px; min-height:80vh;
      background:#fff; padding:20px;
      border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,.1);
      position:relative;
    }
    .dashboard-card {
      background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
      border-radius:8px; padding:20px; color:#fff;
      cursor:move; box-shadow:0 2px 10px rgba(0,0,0,.15);
      transition:all .3s; display:flex; flex-direction:column;
      justify-content:space-between; position:relative; overflow:hidden;
    }
    .dashboard-card.dragging {
      opacity:.7; transform:rotate(5deg); z-index:1000;
      box-shadow:0 10px 30px rgba(0,0,0,.3);
    }
    .card-header { font-size:18px; font-weight:600; margin-bottom:10px; }
    .card-metric { font-size:32px; font-weight:700; margin:10px 0; }
    .card-content { font-size:14px; line-height:1.4; opacity:.9; }
    /* size classes */
    .card-small  { grid-column:span 3; grid-row:span 1; }
    .card-medium { grid-column:span 4; grid-row:span 2; }
    .card-large  { grid-column:span 6; grid-row:span 2; }
    .card-tall   { grid-column:span 3; grid-row:span 3; }
    .position-info {
      position:fixed; top:20px; right:20px; background:#fff;
      padding:15px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,.1);
      font-family:monospace; font-size:12px; max-width:300px;
      display:none;
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <div class="controls">
      <button class="btn" onclick="saveLayout()">Save Layout</button>
      <button class="btn" onclick="resetLayout()">Reset Layout</button>
      <button class="btn" onclick="toggleDebug()">Toggle Debug</button>
    </div>

    <div class="dashboard-grid" id="dashboardGrid">
      <?php
        // auto-discover cards
        $cardsDir = __DIR__ . '/cards/';
        $files = array_filter(scandir($cardsDir), fn($f) => pathinfo($f,PATHINFO_EXTENSION)==='php');
        foreach ($files as $file):
          // assign dummy size by index for demo:
          static $i=0; $sizes=['small','medium','large','tall','small'];
          $size = $sizes[$i++ % count($sizes)];
          $id = pathinfo($file,PATHINFO_FILENAME);
      ?>
      <div class="dashboard-card card-<?php echo $size?>" draggable="true"
           data-card-id="<?php echo $id?>" data-size="<?php echo $size?>">
        <div class="card-header"><?php echo $id?></div>
        <div class="card-metric">#<?php echo rand(10,999)?></div>
        <div class="card-content">Dummy content for <?php echo $id?></div>
      </div>
      <?php endforeach;?>
    </div>

    <div class="position-info" id="positionInfo">
      <strong>Card Positions:</strong><br><div id="positionData"></div>
    </div>
  </div>

  <script>
    let dragged=null, size=null, positions={}, debug=false;
    const gridCols=12, gridRows=8;
    document.addEventListener('DOMContentLoaded', ()=>{
      let grid = document.getElementById('dashboardGrid');
      grid.addEventListener('dragover', e=>{ e.preventDefault(); });
      document.querySelectorAll('.dashboard-card').forEach(card=>{
        card.addEventListener('dragstart', e=>{
          dragged=e.target; size=e.target.dataset.size;
          e.target.classList.add('dragging');
        });
        card.addEventListener('dragend', e=>{
          e.target.classList.remove('dragging');
          updateDebug(); saveToStorage();
        });
      });
      grid.addEventListener('drop', e=>{
        e.preventDefault();
        if(!dragged) return;
        let rect=grid.getBoundingClientRect(),
            x=e.clientX-rect.left-20, y=e.clientY-rect.top-20;
        let col=Math.floor(x/((rect.width-40)/gridCols))+1,
            row=Math.floor(y/((rect.height-40)/gridRows))+1;
        moveCard(dragged,col,row);
        dragged=null;
      });
      loadFromStorage(); updateDebug();
    });

    function getConfig(sz){
      return {small:{c:3,r:1}, medium:{c:4,r:2}, large:{c:6,r:2}, tall:{c:3,r:3}}[sz];
    }
    function moveCard(c, x,y){
      let cfg=getConfig(c.dataset.size);
      if(x+cfg.c-1>gridCols||y+cfg.r-1>gridRows) return;
      c.style.gridColumnStart=x; c.style.gridRowStart=y;
      positions[c.dataset.cardId]={x,y,sz:c.dataset.size};
    }
    function saveLayout(){
      localStorage.setItem('dashboardLayout',JSON.stringify(positions));
      alert('Layout saved!');
    }
    function loadFromStorage(){
      let data=localStorage.getItem('dashboardLayout');
      if(data) positions=JSON.parse(data);
      for(let id in positions){
        let c=document.querySelector(`[data-card-id="${id}"]`);
        if(c) moveCard(c,positions[id].x,positions[id].y);
      }
    }
    function resetLayout(){
      document.querySelectorAll('.dashboard-card').forEach(c=>{
        c.style.gridColumnStart='';c.style.gridRowStart='';
      });
      positions={}; localStorage.removeItem('dashboardLayout');
      updateDebug();
    }
    function toggleDebug(){
      debug=!debug;
      document.getElementById('positionInfo').style.display=debug?'block':'none';
      updateDebug();
    }
    function updateDebug(){
      if(!debug) return;
      let out=''; for(let id in positions){
        let p=positions[id];
        out+=`Card ${id}: (${p.x},${p.y}) [${p.sz}]<br>`;
      }
      document.getElementById('positionData').innerHTML=out||'No positions';
    }
  </script>
</body>
</html>

<!--
Changelog:
- Full manual drag-and-drop implementation replacing SortableJS.
- 12×8 CSS grid with spans for small/medium/large/tall cards.
- Controls to save/reset layout in localStorage and toggle debug overlay.
- Changelog appended here at very end after </html>.
-->

<!--
Changelog:
- Changed `view-error-log` click handler: now toggles visibility of card with id="appLogCard".
- Removed window.open('/logs/debug.log') for the header error-log button.
- Consolidated and placed changelog at end, after </html>.
-->
<!--
Changelog:
- Added error handling, console logs, and dual event listeners in `initializeSortable()` to ensure SortableJS initializes correctly.
- Deferred SortableJS load with `defer` attribute.
- Enhanced CSS for `.card-wrapper` to support dragging (`cursor: grab`, `user-select: none`).
- Kept changelog at very end of file after </html> tag.
-->

<!--
Changelog:
- Moved SortableJS load to footer and added console logs to verify its presence.
- Added error checks for missing `#cardGrid` or SortableJS.
- Deferred drag-and-drop initialization until after SortableJS load.
- Kept changelog at very end of file after </html> tag.
-->

<!--
Changelog:
- Added inline <style> to override .card-grid for responsive auto-fill layout.
- Integrated SortableJS for drag-and-drop card reordering with localStorage persistence.
- Consolidated header button wiring under DOMContentLoaded.
- Restored card-settings modal HTML and behavior scripts.
- Set default dark mode via <html class="dark" data-theme="dark">.
- Placed changelog at end after closing </html> tag for future reference.
-->

  <!-- *
 * Changelog:
 * - Wrapped navigation and main in a `div.content-area` with `flex` to place them side-by-side.
 * - Changed body layout from `flex-col` to top-level header, then content-area, then footer.
 * - Ensured `.sidebar` fills vertical space and `.main` flexes to remaining width.
 * - Removed scripts earlier; now re-adding Feather initialization and header button wiring.
 * - Consolidated changelog entries into one section at top.
 * Changelog:
 * - Wrapped navigation and main in a `div.content-area` with `flex` to place them side-by-side.
 * - Changed body layout from `flex-col` to top-level header, then content-area, then footer.
 * - Ensured `.sidebar` fills vertical space and `.main` flexes to remaining width.
  * Changelog:
  * - Fixed stray closing </script> tag.
  * - Ensured modal is explicitly hidden on load via hideModal() before other actions.
  * - Consolidated changelog entries into one section at end.
  Changelog:
  - Changed <html> tag to default dark mode: added class="dark" and data-theme="dark".
  - Wrapped event listener attachments in null-safe checks (using `?.`) and conditional bindings to prevent JS errors.
  - Ensured modal remains hidden by default (`class="hidden"`).
  - Verified click-outside and inner-stopPropagation logic works reliably.
  - Appended detailed changelog entries for future reference.
  -->
  <!--
  Changelog:
  - Added id="cardSettingsContent" to inner modal div.
  - Added stopPropagation on inner content to prevent overlay click from firing when clicking inside.
  - Simplified overlay click listener to hideModal directly.
  - Verified Save/Cancel buttons have type="button" and hide modal on click.
  - Logged all changes for future reference.
  -->
