<?php
// /views/dashboard.php

// ... any header or setup code above ...

// Assume $cards is already populated via your usual API client logic
?>
<div class="controls" style="padding: 10px 20px;">
  <button id="sortTitle" class="card-sort">Sort by Title</button>
  <button id="sortDate"  class="card-sort">Sort by Date</button>
</div>

<div class="card-grid" id="cardGrid" style="display: grid; gap: 20px; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); padding: 20px;">
  <?php foreach ($cards as $card): ?>
    <div class="card" 
         data-id="<?= htmlspecialchars($card['id'],   ENT_QUOTES) ?>" 
         data-title="<?= htmlspecialchars($card['title'],ENT_QUOTES) ?>" 
         data-date="<?= htmlspecialchars($card['date'], ENT_QUOTES) ?>">
      
      <div class="card-snapshot">
        <h3><?= htmlspecialchars($card['title']) ?></h3>
        <p><?= htmlspecialchars($card['summary']) ?></p>
      </div>
      
      <div class="card-content">
        <button class="collapse-button">Close</button>
        <p><?= htmlspecialchars($card['description']) ?></p>
        <button class="drill-button" data-type="details">Details</button>
        <div class="drill-area"></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<script src="/public/js/card-interactions.js"></script>

<?php
// ... any footer code below ...
?>
