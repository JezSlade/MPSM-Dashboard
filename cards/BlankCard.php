<?php
// cards/BlankCard.php
// -------------------------------------------------------------------
// BLANK CARD TEMPLATE
// -------------------------------------------------------------------
//
// This file is a completely blank “card” that fits into the MPSM Dashboard.
// You can copy this as a starting point for any new card you wish to create.
// It demonstrates the required structure, includes, settings, and rendering flow.
// Everything is commented heavily—feel free to skim the comments if you’re an
// experienced dev, but they’re here to guide you step by step, line by line.
// -------------------------------------------------------------------

declare(strict_types=1); // Enforce strict typing throughout this file

// -------------------------------------------------------------------
// 1) LOAD ESSENTIAL HELPERS
// -------------------------------------------------------------------
// We need to load the environment parser so we can read .env values
// We need to load the auth helper to get our API token
// We need the api_client to make HTTP calls to the MPS Monitor API
require_once __DIR__ . '/../includes/env_parser.php';   // parse_env_file()
require_once __DIR__ . '/../includes/auth.php';         // get_token()
require_once __DIR__ . '/../includes/api_client.php';   // call_api()

// -------------------------------------------------------------------
// 2) DEFINE CARD IDENTIFIER & SETTINGS
// -------------------------------------------------------------------
// Each card has a unique key used for cookies & caching.
// This lets users configure each card’s caching and indicator settings
$cardKey = 'BlankCard'; // Unique identifier; change this for each new card

// Read from cookies whether caching is enabled for this card
$cacheEnabledFlag = isset($_COOKIE["{$cardKey}_cache_enabled"])
    ? (bool) $_COOKIE["{$cardKey}_cache_enabled"]
    : true; // default: caching allowed

// Read from cookies whether the “indicator” (updated timestamp) should show
$indicatorDisplayFlag = isset($_COOKIE["{$cardKey}_indicator_display"])
    ? (bool) $_COOKIE["{$cardKey}_indicator_display"]
    : true; // default: show indicator

// Read the time‐to‐live (TTL) for cache, in minutes, from cookies
$ttlMinutes = isset($_COOKIE["{$cardKey}_ttl_minutes"])
    ? max(1, (int) $_COOKIE["{$cardKey}_ttl_minutes"]) // minimum of 1 minute
    : 5; // default: 5 minutes

// Convert TTL from minutes to seconds for any caching logic you implement
$cacheTTL = $ttlMinutes * 60; // seconds

// -------------------------------------------------------------------
// 3) OPTIONAL: FETCH DATA FROM YOUR API
// -------------------------------------------------------------------
// Here’s an example of how you would call your endpoint:
//   $response = call_api('Your/Endpoint', [ /* parameters */ ]);
//   $items = $response['items'] ?? [];
//
// For this blank template, we won’t fetch anything. But here’s how:
// $response = call_api('Example/Endpoint', [
//     'DealerCode' => DEALER_CODE,
//     'PageNumber' => 1,
//     'PageRows'   => 50,
//     'SortColumn' => 'Name',
//     'SortOrder'  => 'Asc'
// ]);
// $items = $response['items'] ?? []; // ensure we have an array

// -------------------------------------------------------------------
// 4) RENDER CARD MARKUP
// -------------------------------------------------------------------
// Each card is wrapped in a <div> with glassmorphic styling and a unique ID.
// The ID helps with JS hooks if you want per-card behavior.
?>
<div
  id="<?= $cardKey ?>"  <!-- Unique ID for DOM selection -->
  class="glass-card p-4 rounded-lg bg-white/20 backdrop-blur-md border border-gray-600"
  data-card-key="<?= $cardKey ?>"  <!-- Custom attribute for future use -->
>
  <!-- CARD HEADER -->
  <header class="mb-3 flex items-center justify-between">
    <!-- Title of the card -->
    <h2 class="text-xl font-semibold">Blank Card Title</h2>
    <?php if ($indicatorDisplayFlag): // show the cache‐TTL indicator ?>
      <span class="text-sm text-gray-400">
        <?= $cacheEnabledFlag
              ? "{$ttlMinutes} min cache" // show TTL if caching on
              : 'No cache'               // show “No cache” if disabled
        ?>
      </span>
    <?php endif; ?>
  </header>

  <!-- CARD BODY -->
  <div class="card-body">
    <!--
      Put your content here!
      For example, if you fetched $items above, you might:
      <ul>
        <?php foreach ($items as $item): ?>
          <li><?= htmlspecialchars($item['Name'], ENT_QUOTES) ?></li>
        <?php endforeach; ?>
      </ul>
    -->
    <p class="text-gray-200">No data to display yet. Start by copying this card and filling in your logic.</p>
  </div>

  <!-- CARD FOOTER -->
  <footer class="mt-4 text-right">
    <?php if ($cacheEnabledFlag): // only show timestamp if caching is on ?>
      <small class="text-xs text-gray-500">
        Updated <?= date('Y-m-d H:i') ?>  <!-- current server time -->
      </small>
    <?php endif; ?>
  </footer>
</div>

<script>
// -------------------------------------------------------------------
// OPTIONAL PER-CARD JAVASCRIPT
// -------------------------------------------------------------------
// This script block runs when the page loads. It’s an example of how
// you can add card-specific behaviors. If you don’t need it, remove it.
//
// Wrap in DOMContentLoaded to ensure the element exists before running
document.addEventListener('DOMContentLoaded', () => {
  // Grab the card by its ID
  const card = document.getElementById('<?= $cardKey ?>');
  if (!card) {
    console.warn('<?= $cardKey ?> not found in DOM');
    return;
  }

  // EXAMPLE: Log click events on the card
  card.addEventListener('click', () => {
    console.log('Card <?= $cardKey ?> clicked');
  });

  // EXAMPLE: You could trigger an API call, open a modal, etc.
  // fetch('/api/YourDetailEndpoint.php?id=123')
  //   .then(res => res.json())
  //   .then(data => console.log('Detail data:', data))
  //   .catch(err => console.error(err));
});
</script>
