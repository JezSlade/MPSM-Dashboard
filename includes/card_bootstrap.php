<?php declare(strict_types=1);
// /includes/card_bootstrap.php

// 1) Load shared API helpers & config
require_once __DIR__ . '/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// 2) Determine selected customer (URL → cookie → default)
if (isset($_GET['customer'])) {
    $customerCode = $_GET['customer'];
    setcookie('customer', $customerCode, time()+31536000, '/');
} elseif (!empty($_COOKIE['customer'])) {
    $customerCode = $_COOKIE['customer'];
} else {
    $customerCode = $config['DEALER_CODE'] ?? '';
}

// 3) Validate card metadata
if (empty($path) || empty($cardTitle) || !is_array($columns)) {
    echo "<p class='error'>Card not configured properly.</p>";
    return;
}

// 4) Ensure payload exists
$payload = $payload ?? [];

// 4a) Inject customerCode if needed
if (array_key_exists('CustomerCode', $payload) && !$payload['CustomerCode']) {
    $payload['CustomerCode'] = $customerCode;
}

// 4b) Populate any requiredFields from GET → cookie
$missing = [];
foreach ($requiredFields ?? [] as $field) {
    if (!empty($_GET[$field])) {
        $payload[$field] = $_GET[$field];
        setcookie($field, $_GET[$field], time()+31536000, '/');
    } elseif (empty($payload[$field]) && !empty($_COOKIE[$field])) {
        $payload[$field] = $_COOKIE[$field];
    }
    if (empty($payload[$field])) {
        $missing[] = $field;
    }
}

// 5) If any required fields still missing, render prompt with searchable dropdown for customerId
if (!empty($missing)) {
    echo "<div class='card'>";
    echo   "<div class='card-header'><h3>" . htmlspecialchars($cardTitle) . "</h3></div>";
    echo   "<div class='card-body'>";
    echo     "<form method='GET'>";
    // preserve existing params
    foreach ($_GET as $gk => $gv) {
        echo "<input type='hidden' name='" . htmlspecialchars($gk)
             . "' value='" . htmlspecialchars($gv) . "'>";
    }
    echo     "<p>Please enter:</p>";
    foreach ($missing as $field) {
        echo "<label for='{$field}'>" . htmlspecialchars($field) . ":</label><br>";
        if ($field === 'customerId') {
            // load customer list for dropdown
            $custResp = call_api($config, 'POST', 'Customer/GetCustomers', [
                'DealerCode' => $customerCode,
                'PageNumber' => 1,
                'PageRows'   => 2147483647,
                'SortColumn' => 'Description',
                'SortOrder'  => 'Asc',
            ]);
            $options = $custResp['Result'] ?? [];
            echo "<input type='text' id='{$field}-search' class='searchable-input' "
               . "placeholder='Search customers…'><br>";
            echo "<select id='{$field}' name='{$field}' class='searchable-select'>"
               . "<option value='' disabled selected>— choose —</option>";
            foreach ($options as $c) {
                $code = htmlspecialchars($c['Code'] ?? '');
                $name = htmlspecialchars($c['Description'] ?? $c['Name'] ?? $code);
                echo "<option value='{$code}'>{$name}</option>";
            }
            echo "</select><br><br>";
        } else {
            echo "<input type='text' id='{$field}' name='{$field}'><br><br>";
        }
    }
    echo     "<button type='submit' class='btn'>Load “"
             . htmlspecialchars($cardTitle)
             . "”</button>";
    echo   "</form>";
    echo "</div></div>";
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      var input  = document.getElementById('customerId-search');
      var select = document.getElementById('customerId');
      if (input && select) {
        input.addEventListener('input', function() {
          var filter = this.value.toLowerCase();
          Array.from(select.options).forEach(function(opt) {
            if (!opt.value) return;
            opt.style.display = opt.text.toLowerCase().includes(filter) ? '' : 'none';
          });
        });
      }
    });
    </script>
    <?php
    return;
}

// 6) Fetch data
try {
    $method = $method ?? 'POST';
    $resp   = call_api($config, $method, $path, $payload);

    // handle API errors
    if (!empty($resp['Errors']) && is_array($resp['Errors'])) {
        $first = $resp['Errors'][0];
        throw new \Exception($first['Description'] ?? 'API error');
    }
    $data = $resp['Result'] ?? [];

} catch (\Throwable $e) {
    echo "<p class='error'>Error fetching data: "
         . htmlspecialchars($e->getMessage())
         . "</p>";
    return;
}

// 7) Render the card
echo "<div class='card'>";
echo   "<div class='card-header'><h3>"
     . htmlspecialchars($cardTitle)
     . "</h3></div>";

// 8) Search box
if (!empty($enableSearch)) {
    echo "<div class='card-search'><input type='text' class='search-input' "
         . "placeholder='Search…' onkeyup='filterCard(this)'></div>";
}

// 9) Table
echo "<div class='card-table-container'>";
echo   "<table class='card-table' data-page-size='"
     . ($pageSize ?? 15)
     . "'><thead><tr>";
foreach ($columns as $key => $label) {
    echo "<th>" . htmlspecialchars($label) . "</th>";
}
echo   "</tr></thead><tbody>";
foreach ($data as $row) {
    echo "<tr>";
    foreach ($columns as $key => $_) {
        echo "<td>" . htmlspecialchars($row[$key] ?? '') . "</td>";
    }
    echo "</tr>";
}
echo   "</tbody></table></div>";

// 10) Pagination
if (!empty($enablePagination)) {
    echo "<div class='card-pagination'></div>";
}

echo "</div>";
?>

<script>
// client-side search
function filterCard(input) {
    var filter = input.value.toLowerCase();
    var rows   = input.closest('.card').querySelectorAll('tbody tr');
    rows.forEach(function(r) {
        r.style.display = r.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
}
</script>
