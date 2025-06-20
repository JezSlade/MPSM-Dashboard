<?php declare(strict_types=1);
/**
 *  preferences.php
 *
 *  Utility functions for persisting and retrieving per-user settings
 *  (currently: which dashboard cards are visible).
 *
 *  STORAGE STRATEGY
 *  ────────────────
 *  • The front-end JavaScript writes the selected card filenames to
 *      localStorage['visibleCards']              ← primary store
 *    and also mirrors the same JSON array into
 *      document.cookie  (name = 'visibleCards')  ← server can read
 *
 *  • On the PHP side we CANNOT read localStorage, so we look only at
 *    the cookie.  If the cookie is missing or corrupt we fall back to
 *    showing all available cards.
 *
 *  • The function below keeps the order saved by the user but
 *    guarantees that every returned filename actually exists on disk.
 */

if (!function_exists('getVisibleCards')) {
    /**
     * @param string[] $allCards Filenames (basename only) of every card on disk
     * @return string[]          Filenames that should be rendered, in user order
     */
    function getVisibleCards(array $allCards): array
    {
        $cookie = $_COOKIE['visibleCards'] ?? null;
        if ($cookie === null) {
            // First-time visitor or prefs cleared → show everything
            return $allCards;
        }

        $saved = json_decode($cookie, true);
        if (!is_array($saved)) {
            // Malformed cookie (not JSON) → ignore and show everything
            return $allCards;
        }

        // Ensure we’re working with just the basenames
        $saved = array_map('basename', $saved);

        /** @var string[] $visible Keeps original user order */
        $visible = [];
        foreach ($saved as $file) {
            if (in_array($file, $allCards, true)) {
                $visible[] = $file;
            }
        }

        // If user selection is now empty (e.g. cards were deleted),
        // revert to default: show all available cards.
        return $visible !== [] ? $visible : $allCards;
    }
}
