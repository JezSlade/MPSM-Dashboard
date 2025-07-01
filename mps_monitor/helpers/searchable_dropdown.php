<?php declare(strict_types=1);
// /includes/searchable_dropdown.php

/**
 * Renders a compact, Tailwind-styled searchable dropdown.
 * This function generates the HTML for an input field with a datalist,
 * and includes JavaScript to fetch options from an API, manage selection
 * via cookies, and handle page reloads. It is a front-end component, not an API endpoint.
 *
 * @param string $id            HTML ID for the <input> element. Must be unique on the page.
 * @param string $datalistId    HTML ID for the <datalist> element. Must be unique on the page.
 * @param string $apiEndpoint   URL to fetch options. The API response is expected to be JSON
 * and contain an array of items at `resp.customers`, `resp.Result`, or directly as `resp`.
 * Each item should have a 'Code' property and a 'Description' or 'Name' property.
 * @param string $cookieName    The key under which the selected 'Code' will be stored in a browser cookie.
 * @param string $placeholder   Placeholder text to display in the input field when empty.
 * @param string $cssClasses    Optional: Additional Tailwind CSS classes for the <input> element.
 * Default classes provide a basic dark-themed, rounded input style.
 */
function renderSearchableDropdown(
    string $id,
    string $datalistId,
    string $apiEndpoint,
    string $cookieName,
    string $placeholder,
    string $cssClasses = 'w-full text-xs bg-gray-800 text-white border border-gray-600 rounded-md py-1 px-2 focus:outline-none focus:ring-1 focus:ring-cyan-500 focus:border-cyan-500'
): void {
    // Read the current selected code from the cookie, if it exists.
    // This ensures the dropdown retains its last selected value across page loads.
    $currCode = $_COOKIE[$cookieName] ?? '';
    ?>
<div class="relative z-10 flex-1 max-w-xs">
  <label for="<?= htmlspecialchars($id) ?>" class="sr-only"><?= htmlspecialchars($placeholder) ?></label>
  <input
    list="<?= htmlspecialchars($datalistId) ?>"
    id="<?= htmlspecialchars($id) ?>"
    class="<?= htmlspecialchars($cssClasses) ?>"
    placeholder="<?= htmlspecialchars($placeholder) ?>"
    value="" <!-- Value will be set by JavaScript after fetching options -->
  />
  <datalist id="<?= htmlspecialchars($datalistId) ?>"></datalist>
</div>
<script>
(function(){
  // --- DOM Elements and Configuration ---
  const inputElement   = document.getElementById('<?= htmlspecialchars($id) ?>');
  const datalistElement= document.getElementById('<?= htmlspecialchars($datalistId) ?>');
  const cookieKey      = '<?= htmlspecialchars($cookieName) ?>';
  const apiDataUrl     = '<?= htmlspecialchars($apiEndpoint) ?>';

  // --- Initial Load: Retrieve Current Selection from Cookie ---
  // Regular expression to find the cookie value.
  const cookieMatch = document.cookie.match(new RegExp('(?:^|; )' + cookieKey + '=([^;]+)'));
  // Decode the URI component to get the actual value.
  const currentSelectedCode = cookieMatch ? decodeURIComponent(cookieMatch[1]) : '';

  // --- Fetch Dropdown Options from API ---
  fetch(apiDataUrl)
    .then(response => {
      // Check if the response is OK (status 200-299).
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json(); // Parse the JSON response.
    })
    .then(data => {
      // Try to extract the list of items from various possible JSON response structures.
      // Prioritize 'customers', then 'Result', then assume the entire response is the list.
      const itemList = data.customers || data.Result || data || [];

      // Clear any existing options in the datalist.
      datalistElement.innerHTML = '';

      // Populate the datalist with options fetched from the API.
      itemList.forEach(item => {
        const optionElement = document.createElement('option');
        // Use 'Description', 'Name', or 'Code' as the display value for the option.
        optionElement.value        = item.Description || item.Name || item.Code || '';
        // Store the 'Code' in a dataset attribute for later retrieval (when an item is selected).
        optionElement.dataset.code = item.Code || '';
        datalistElement.appendChild(optionElement);
      });

      // --- Pre-select the input field if a value was found in the cookie ---
      if (currentSelectedCode) {
        const foundOption = Array.from(datalistElement.options)
                                 .find(option => option.dataset.code === currentSelectedCode);
        if (foundOption) {
          inputElement.value = foundOption.value; // Set the input field's value to the pre-selected option's display value.
        }
      }
    })
    .catch(error => {
      // Log any errors during the fetch operation to the console.
      console.error('Searchable dropdown load error:', error);
      // In a production environment, you might display a user-friendly error message.
    });

  // --- Event Listener for Input Change (User Selection) ---
  inputElement.addEventListener('change', () => {
    // Find the selected option in the datalist based on the input field's current value.
    const selectedOption = Array.from(datalistElement.options)
                                 .find(option => option.value === inputElement.value);

    // Get the 'Code' from the selected option's dataset.
    const selectedCode = selectedOption ? selectedOption.dataset.code : '';

    // If a code is selected, save it to a cookie and reload the page.
    // Reloading ensures that other parts of the application can react to the new selection.
    if (selectedCode) {
      // Set the cookie with the selected code, path for entire domain, and a long expiry (1 year).
      document.cookie = cookieKey + '=' + encodeURIComponent(selectedCode)
                      + ';path=/;max-age=' + (60 * 60 * 24 * 365);
      location.reload(); // Reload the page to apply the new selection.
    }
  });
})();
</script>
<?php
} // end function renderSearchableDropdown
