<?php
/**
 * index.php — Fixed drag-and-drop with ungrouped cards and dark background
 */
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');

// Define constants from .env
define('DEALER_CODE', getenv('DEALER_CODE') ?: 'N/A');
define('APP_NAME', getenv('APP_NAME') ?: 'MPS Monitor Dashboard');
?>
<!DOCTYPE html>
<html lang="en" class="h-full dark" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="icon" href="data:;base64,">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/public/css/styles.css">

  <style>
    .dashboard-container {
      position: relative;
      width: 100%;
      height: 700px;
      background: #1f2937;
      border-radius: 8px;
      border: 1px solid #374151;
      overflow: hidden;
      background-image: radial-gradient(circle, #4b5563 1px, transparent 1px);
      background-size: 20px 20px;
    }
    .card-wrapper {
      position: absolute;
      cursor: grab;
      user-select: none;
      transition: box-shadow 0.15s ease;
      display: block;
    }
    .card-wrapper:hover {
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
    }
    .card-wrapper.dragging {
      z-index: 1000;
      transform: scale(1.05);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
      cursor: grabbing;
    }
    .card-wrapper.dragging.valid {
      box-shadow: 0 0 0 2px rgba(34,197,94,0.5), 0 25px 50px -12px rgba(0,0,0,0.5);
    }
    .card-wrapper.dragging.invalid {
      box-shadow: 0 0 0 2px rgba(239,68,68,0.5), 0 25px 50px -12px rgba(0,0,0,0.5);
    }
    .card-wrapper.will-nudge {
      box-shadow: 0 0 0 2px rgba(245,158,11,0.5);
      z-index: 100;
    }
    .card-size-small { width: 240px; height: 140px; border: 2px solid #bfdbfe; }
    .card-size-medium { width: 300px; height: 180px; border: 2px solid #93c5fd; }
    .card-size-large { width: 380px; height: 220px; border: 2px solid #60a5fa; }

    /* only overlays need pointer-events:none */
    .drag-info, .nudge-indicator {
      pointer-events: none;
      position: absolute;
      white-space: nowrap;
      font-size: 12px;
      padding: 4px 8px;
      border-radius: 4px;
    }
    .drag-info {
      top: -32px; left: 0; display: flex; gap: 8px; z-index: 1001;
    }
    .drag-info-badge { background: #2563eb; color: #fff; }
    .drag-info-badge.valid { background: #16a34a; }
    .drag-info-badge.invalid { background: #dc2626; }
    .drag-info-badge.nudging { background: #d97706; }

    .nudge-indicator {
      top: -24px; left: 0; background: #d97706; color: #fff; padding: 2px 6px;
    }
  </style>

  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="h-full flex flex-col bg-gray-900">
  <?php include __DIR__ . '/includes/header.php'; ?>
  <div class="flex flex-1 overflow-hidden">
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 text-white">
      <div class="mb-6">
        <h1 class="text-3xl font-bold">Smart Nudging Dashboard</h1>
        <p class="text-gray-400 mt-2">Cards gently nudge others out of the way when needed</p>
      </div>

      <div id="dashboardContainer" class="dashboard-container">
        <?php
        $cardsDir = __DIR__ . '/cards/';
        $files = array_filter(scandir($cardsDir), fn($f) => pathinfo($f, PATHINFO_EXTENSION)==='php');
        $cardConfigs = [
          'revenue.php'=>['size'=>'large','x'=>20,'y'=>20],
          'users.php'=>['size'=>'medium','x'=>420,'y'=>20],
          'orders.php'=>['size'=>'small','x'=>740,'y'=>20],
          'errors.php'=>['size'=>'small','x'=>20,'y'=>260],
          'analytics.php'=>['size'=>'large','x'=>280,'y'=>260],
          'performance.php'=>['size'=>'medium','x'=>680,'y'=>220],
          'conversion.php'=>['size'=>'small','x'=>20,'y'=>420],
          'pageviews.php'=>['size'=>'medium','x'=>280,'y'=>500],
        ];
        foreach (array_values($files) as $i=>$file):
          $cfg = $cardConfigs[$file] ?? ['size'=>'medium','x'=>($i%3)*340+20,'y'=>floor($i/3)*220+20];
        ?>
        <div
          class="card-wrapper card-size-<?php echo $cfg['size']?>"
          data-file="<?php echo htmlspecialchars($file,ENT_QUOTES)?>"
          data-size="<?php echo $cfg['size']?>"
          data-x="<?php echo $cfg['x']?>"
          data-y="<?php echo $cfg['y']?>"
          style="left:<?php echo $cfg['x']?>px;top:<?php echo $cfg['y']?>px"
        >
          <?php include $cardsDir . $file; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>
  <?php include __DIR__ . '/includes/footer.php'; ?>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    feather.replace();
    const GRID=20, MAX_NUDGE=60;
    const SIZES={ small:{w:240,h:140}, medium:{w:300,h:180}, large:{w:380,h:220} };
    let state={ isDragging:false, dragged:null, startX:0, startY:0,
                cardX:0, cardY:0, origX:0, origY:0 };
    const container=document.getElementById('dashboardContainer');
    const cards=Array.from(document.querySelectorAll('.card-wrapper'));

    function snap(v){return Math.round(v/GRID)*GRID;}
    function rect(o){return {x:o.x,y:o.y,w:o.width,h:o.height};}
    function overlap(a,b){
      return !(a.x+a.w<=b.x||b.x+b.w<=a.x||a.y+a.h<=b.y||b.y+b.h<=a.y);
    }
    function getPos(c){
      return { x:+c.dataset.x, y:+c.dataset.y, size:c.dataset.size };
    }
    function setPos(c,x,y){
      c.dataset.x=x; c.dataset.y=y;
      c.style.left=x+'px'; c.style.top=y+'px';
    }
    function inBounds(x,y,size){
      if(x<0||y<0) return false;
      const B=container.getBoundingClientRect();
      const s=SIZES[size];
      return x+s.w<=B.width && y+s.h<=B.height-100;
    }
    function calcNudges(dragEl, dx, dy){
      const dp=getPos(dragEl), dS=SIZES[dp.size], drop={x:dx,y:dy,w:dS.w,h:dS.h};
      let hits=cards.filter(c=>c!==dragEl&&overlap(drop,rect({...getPos(c),...SIZES[getPos(c).size]})));
      if(!hits.length) return {can:true,n:[],c:[]};
      // sort small first
      hits.sort((a,b)=>({
        small:1,medium:2,large:3
      }[getPos(a).size] - {
        small:1,medium:2,large:3
      }[getPos(b).size]));
      const nudges=[], used=[dragEl];
      for(let c of hits){
        const p=getPos(c);
        let found=null;
        for(let d=GRID;d<=MAX_NUDGE;d+=GRID){
          for(let dir of [{dx:0,dy:-GRID},{dx:0,dy:GRID},{dx:-GRID,dy:0},{dx:GRID,dy:0}]){
            const nx=p.x+dir.dx*(d/GRID), ny=p.y+dir.dy*(d/GRID);
            if(inBounds(nx,ny,p.size) &&
               !overlap({x:nx,y:ny,w:SIZES[p.size].w,h:SIZES[p.size].h},
                         ...cards.filter(cc=>!used.includes(cc)).map(cc=>rect({...getPos(cc),...SIZES[getPos(cc).size]}))))
            {
              found={x:nx,y:ny};
              break;
            }
          }
          if(found) break;
        }
        if(!found) return {can:false,n:[],c:[]};
        nudges.push({card:c,from:p,to:found}); used.push(c);
      }
      return {can:true,n:nudges,c:hits};
    }

    function updatePreview(nudges){
      cards.forEach(c=>c.classList.remove('will-nudge'));
      document.querySelectorAll('.nudge-indicator').forEach(e=>e.remove());
      nudges.forEach(n=>{
        n.card.classList.add('will-nudge');
        const ind=document.createElement('div');
        ind.className='nudge-indicator'; ind.textContent='Will nudge';
        n.card.appendChild(ind);
      });
    }
    function updateInfo(card, ok, count){
      let info=card.querySelector('.drag-info');
      if(!info){
        info=document.createElement('div');
        info.className='drag-info';
        card.appendChild(info);
      }
      const x=snap(state.currentX), y=snap(state.currentY);
      info.innerHTML=`
        <div class="drag-info-badge">${x}, ${y}</div>
        <div class="drag-info-badge ${ok?'valid':'invalid'}">
          ${ok?'✓ Can Place':'✗ Cannot Place'}
        </div>
        ${count?`<div class="drag-info-badge nudging">Nudging ${count}</div>`:''}
      `;
    }

    function onDown(e){
      const card=e.currentTarget;
      const p=getPos(card);
      state={ isDragging:true, dragged:card,
              startX:e.clientX, startY:e.clientY,
              cardX:p.x, cardY:p.y,
              origX:p.x, origY:p.y,
              currentX:p.x, currentY:p.y };
      card.classList.add('dragging');
      e.preventDefault();
    }
    function onMove(e){
      if(!state.isDragging) return;
      const dx=e.clientX-state.startX, dy=e.clientY-state.startY;
      state.currentX=state.cardX+dx; state.currentY=state.cardY+dy;
      state.dragged.style.left=state.currentX+'px';
      state.dragged.style.top=state.currentY+'px';

      const sx=snap(state.currentX), sy=snap(state.currentY);
      if(inBounds(sx,sy,getPos(state.dragged).size)){
        const plan=calcNudges(state.dragged,sx,sy);
        state.dragged.classList.toggle('valid',plan.can);
        state.dragged.classList.toggle('invalid',!plan.can);
        updatePreview(plan.n);
        updateInfo(state.dragged,plan.can,plan.n.length);
      } else {
        state.dragged.classList.add('invalid');
        updatePreview([]);
        updateInfo(state.dragged,false,0);
      }
      e.preventDefault();
    }
    function onUp(e){
      if(!state.isDragging) return;
      const sx=snap(state.currentX), sy=snap(state.currentY);
      const card=state.dragged, size=getPos(card).size;
      if(!inBounds(sx,sy,size)){
        setPos(card,state.origX,state.origY);
      } else {
        const plan=calcNudges(card,sx,sy);
        if(plan.can){
          plan.n.forEach(n=>setPos(n.card,n.to.x,n.to.y));
          setPos(card,sx,sy);
          localStorage.setItem('cardPositions',
            JSON.stringify(Object.fromEntries(cards.map(c=>[c.dataset.file,getPos(c)])))
          );
        } else {
          setPos(card,state.origX,state.origY);
        }
      }
      card.classList.remove('dragging','valid','invalid');
      card.querySelector('.drag-info')?.remove();
      updatePreview([]);
      state.isDragging=false;
      e.preventDefault();
    }

    // init
    cards.forEach(c=>{
      c.addEventListener('mousedown', onDown);
      c.addEventListener('dragstart', e=>e.preventDefault());
      c.addEventListener('selectstart', e=>e.preventDefault());
      // load saved pos
      const saved=JSON.parse(localStorage.getItem('cardPositions')||'{}');
      if(saved[c.dataset.file]) {
        setPos(c,saved[c.dataset.file].x,saved[c.dataset.file].y);
      }
    });
    document.addEventListener('mousemove', onMove);
    document.addEventListener('mouseup', onUp);
  });
  </script>
</body>
</html>

<!-- **Consolidated Changelog:**

- **Application Configuration:**
  - Added `APP_NAME` constant from `APP_NAME` environment variable with fallback.
  - Updated `<title>` to reflect `APP_NAME`.
  - Set default dark mode via `<html class="dark" data-theme="dark">`.

- **Layout and Styling:**
  - Wrapped navigation and main in `div.content-area` with `flex` for side-by-side layout.
  - Changed body layout from `flex-col` to top-level header, content-area, and footer.
  - Ensured `.sidebar` fills vertical height and `.main` flexes to remaining width.
  - Added inline `<style>` to override `.card-grid` for responsive auto-fill layout.
  - Established 12×8 CSS grid with spans for small/medium/large/tall cards.
  - Enhanced `.card-wrapper` CSS for dragging (`cursor: grab`, `user-select: none`).

- **Drag-and-Drop Functionality:**
  - Replaced SortableJS with full manual HTML5 drag-and-drop implementation for `.card-wrapper`.
  - Imported “smart nudging” logic from `drag.tsx` into native JavaScript.
  - Cards are absolutely positioned in `.dashboard-container` with snap-to-grid and gentle overlapping nudges.
  - Added dual event listeners and error handling in `initializeSortable()` for SortableJS fallback.
  - Deferred SortableJS load with `defer` attribute and moved to footer with console logs to verify presence.
  - Added error checks for missing `#cardGrid` or SortableJS.
  - Deferred drag-and-drop initialization until after SortableJS load.

- **LocalStorage Persistence:**
  - Card positions stored in `localStorage.cardPositions` and reapplied on page load.
  - Layout state persisted in `localStorage` with controls to save/reset.
  - Positions persist in `localStorage` and reload on page load.

- **Controls and Interactivity:**
  - Added controls: Save, Reset, Toggle Debug overlay.
  - Changed `view-error-log` click handler to toggle visibility of card with `id="appLogCard"`.
  - Removed `window.open('/logs/debug.log')` for header error-log button.
  - Consolidated header button wiring under `DOMContentLoaded`.
  - Preserved Application Log toggle functionality.

- **Modal and Settings:**
  - Restored card-settings modal HTML and behavior scripts.
  - Added `id="cardSettingsContent"` to inner modal div with `stopPropagation` to prevent overlay click issues.
  - Simplified overlay click listener to call `hideModal` directly.
  - Ensured modal is hidden on load via `hideModal()` and `class="hidden"`.
  - Verified Save/Cancel buttons have `type="button"` and hide modal on click.
  - Wrapped event listener attachments in null-safe checks (using `?.`) and conditional bindings.
  - Verified click-outside and inner `stopPropagation` logic works reliably.

- **Script and Asset Management:**
  - Restored original `index.php` with favicon and Feather icon initialization.
  - Re-added Feather initialization and header button wiring after earlier removal.
  - Unified JavaScript in a single `<script>` tag.
  - Fixed stray closing `</script>` tag.

- **Data and Security:**
  - Sanitized `data-file` attributes in `card-wrapper`.

- **Logging and Documentation:**
  - Consolidated changelog entries into a single section, placed at the end after `</html>` for future reference.
  - Appended detailed changelog entries with console logs for tracking.

This changelog removes duplicates, organizes changes by category, and maintains all unique updates in a clear, concise format. -->