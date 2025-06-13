<?php
// cards/sampleCard.php

function renderCard($customerId, $env) {
    return <<<HTML
    <div class="card">
        <h3>Welcome Card</h3>
        <p>Customer <strong>#{$customerId}</strong> loaded. Environment: <code>{$env['API_BASE_URL']}</code>.</p>
    </div>
    HTML;
}
