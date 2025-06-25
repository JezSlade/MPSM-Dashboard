<?php

/**
 * Search Helper with Session Storage
 * 
 * @param array $dataset Data to search (array of arrays/objects)
 * @param array $searchableFields Fields/keys to search within
 * @param string $sessionKey Session key to store the search term (default: 'search_term')
 * @return array Filtered results
 */
function searchDataset(array $dataset, array $searchableFields, string $sessionKey = 'search_term') {
    // Start session if not already active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Get search term from GET/POST
    $searchTerm = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';

    // Store in session
    $_SESSION[$sessionKey] = $searchTerm;

    // Early exit if no search term
    if (empty($searchTerm)) {
        return $dataset;
    }

    // Filter the dataset
    return array_filter($dataset, function($item) use ($searchTerm, $searchableFields) {
        foreach ($searchableFields as $field) {
            $value = is_array($item) 
                ? ($item[$field] ?? '') 
                : ($item->$field ?? '');

            if (stripos($value, $searchTerm) !== false) {
                return true;
            }
        }
        return false;
    });
}

/**
 * Renders the HTML search bar with the last-used search term.
 */
function renderSearchBar(string $placeholder = 'Search...', string $sessionKey = 'search_term') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $currentTerm = $_SESSION[$sessionKey] ?? '';

    // Precompute the clear button HTML
    $clearButton = $currentTerm 
        ? '<a href="?search=" class="clear-search">[X]</a>' 
        : '';

    return <<<HTML
    <form method="get" class="search-form">
        <input 
            type="text" 
            name="search" 
            placeholder="$placeholder" 
            value="$currentTerm"
            autocomplete="off"
        >
        <button type="submit">Search</button>
        $clearButton
    </form>
HTML;
}

?>