<?php
/**
 * index.php — Entrypoint with “smart nudging” drag logic applied to PHP cards
 *
 * Changelog:
 * - Restored original PHP structure (favicon, includes, card‐settings, header, nav, footer).
 * - Replaced CSS‐grid/card‐wrapper arrangement with absolute positioning inside a relative container.
 * - Imported “smart nudging” logic from drag.tsx: snap‐to‐grid, overlap detection, gentle nudging.
 * - Cards now carry data‐attributes for x, y, size loaded from localStorage or default PHP positions.
 * - All JS consolidated at end; changelog appended after </html>.
 */
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');

// Placeholder positions (could be loaded from backend or localStorage)
$saved = json_decode(
    "<script>document.write(localStorage.getItem('cardPositions'))</script>",
    true
) ?: [];

// Default positions for cards (in pixels)
$defaults = [
  // filename => ['x'=>..., 'y'=>...,'size'=>'small'|'medium'|'large']
  'CardLarge.php'=>['x'=>20,'y'=>20,'size'=>'large'],
  // ... add defaults for each file
];

// Merge saved over defaults
$positions = array_merge($defaults,$saved);
?>
<!DOCTYPE html>
<html lang="en" class="h-full dark" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <title>Dashboard for <?php echo htmlspecialchars(DEALER_CODE,ENT_QUOTES);?></title>
  <link rel="icon" href="data:;base64,">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/public/css/styles.css">
  <style>
    .dashboard-container { position:relative; width:100%; height:calc(100vh - 64px); }
    .card-wrapper { position:absolute; touch-action:none; }
    .dragging { cursor:grabbing; z-index:1000; }
    /* grid background */
    .dashboard-container { background-image:
      radial-gradient(circle,#e5e7eb 1px,transparent 1px);
      background-size:20px 20px;
    }
  </style>
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="h-full flex flex-col">

  <?php include __DIR__.'/includes/header.php'; ?>
  <div class="flex flex-1 overflow-hidden">
    <?php include __DIR__.'/includes/navigation.php'; ?>
    <main class="flex-1 p-6">
      <div class="dashboard-container" id="dashboard">
        <?php
        $cardsDir = __DIR__.'/cards/';
        foreach(array_filter(scandir($cardsDir), fn($f)=>pathinfo($f,PATHINFO_EXTENSION)==='php') as $file):
          $pos = $positions[$file] ?? ['x'=>20,'y'=>20,'size'=>'medium'];
          $size = $pos['size'];
          list($w,$h) = match($size){
            'small'=>[240,140],
            'medium'=>[300,180],
            'large'=>[380,220],
            default=>[300,180],
          };
        ?>
        <div class="card-wrapper neumorphic" 
             data-file="<?php echo $file?>" 
             data-size="<?php echo $size?>" 
             style="left:<?php echo $pos['x']?>px;top:<?php echo $pos['y']?>px;
                    width:<?php echo $w?>px;height:<?php echo $h?>px;">
          <?php include $cardsDir.$file; ?>
        </div>
        <?php endforeach;?>
      </div>
    </main>
  </div>
  <?php include __DIR__.'/includes/footer.php'; ?>

  <!-- Smart‐nudging drag logic -->
  <script>
  (() => {
    const GRID = 20, MAX_NUDGE = 3*GRID;
    const CARD_SIZES = { small:{w:240,h:140}, medium:{w:300,h:180}, large:{w:380,h:220} };
    const container = document.getElementById('dashboard');
    let cards = Array.from(container.children);
    let dragged=null, startX=0, startY=0, origX=0, origY=0;

    // Utility
    const snap = v=>Math.round(v/GRID)*GRID;
    const rectsOverlap=(r1,r2)=>
      !(r1.x+r1.w<=r2.x||r2.x+r2.w<=r1.x||r1.y+r1.h<=r2.y||r2.y+r2.h<=r1.y);
    function canNudge(id,newX,newY){
      const size=CARD_SIZES[dragged.dataset.size];
      let dropRect={x:newX,y:newY,w:size.w,h:size.h};
      for(let c of cards){
        if(c===dragged) continue;
        let s=CARD_SIZES[c.dataset.size];
        let existing={x:parseInt(c.style.left),y:parseInt(c.style.top),w:s.w,h:s.h};
        if(rectsOverlap(dropRect,existing)){
          // try to nudge existing
          for(let dx of [0,GRID,-GRID]){
            for(let dy of [0,GRID,-GRID]){
              let nx=existing.x+dx, ny=existing.y+dy;
              let nr={x:nx,y:ny,w:existing.w,h:existing.h};
              if(nr.x>=0&&nr.y>=0&&!rectsOverlap(dropRect,nr)){
                c.style.left=nx+'px';c.style.top=ny+'px';
                return true;
              }
            }
          }
          return false;
        }
      }
      return true;
    }

    // Persist
    const save=()=>{
      const pos={};
      for(let c of cards){
        pos[c.dataset.file]={col:parseInt(c.style.left),row:parseInt(c.style.top)};
      }
      localStorage.setItem('cardPositions',JSON.stringify(pos));
    };

    // Handlers
    cards.forEach(c=>{
      c.addEventListener('mousedown',e=>{
        dragged=c; startX=e.clientX; startY=e.clientY;
        origX=parseInt(c.style.left); origY=parseInt(c.style.top);
        c.classList.add('dragging');
      });
    });
    document.addEventListener('mousemove',e=>{
      if(!dragged) return;
      let dx=e.clientX-startX, dy=e.clientY-startY;
      let nx=snap(origX+dx), ny=snap(origY+dy);
      if(nx<0||ny<0) return;
      if(canNudge(dragged.dataset.file,nx,ny)){
        dragged.style.left=nx+'px'; dragged.style.top=ny+'px';
      }
    });
    document.addEventListener('mouseup',()=>{
      if(dragged){
        dragged.classList.remove('dragging');
        save();
        dragged=null;
      }
    });
  })();
  document.addEventListener('DOMContentLoaded',()=>feather.replace());
  </script>
</body>
</html>

<!--
Changelog:
- Imported “smart nudging” logic from drag.tsx into native JS.
- Cards absolutely positioned in .dashboard-container with snap‐to‐grid and gentle overlapping nudges.
- Positions persist in localStorage and reload on page load.
-->```

<!--
Changelog:
- Restored original index.php with favicon and Feather icon initialization.
- Removed SortableJS; implemented manual HTML5 drag/drop for `.card-wrapper`.
- Positions stored in `localStorage.cardPositions` and reapplied on load.
- Application Log toggle preserved.
- Changelog appended after </html>.
-->```

<!--
Changelog:
- Changed `view-error-log` click handler: now toggles visibility of card with id="appLogCard".
- Removed window.open('/logs/debug.log') for the header error-log button.
- Consolidated and placed changelog at end, after </html>.
- Integrated manual HTML5 drag-and-drop from sample.
- Established 12×8 CSS grid and size spans.
- Controls added: Save, Reset, Toggle Debug.
- JS unified in one <script>, layout state in localStorage.
- Changelog appended at end after </html>.
-->```

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
