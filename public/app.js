// public/app.js — Token Fetching, Header Display Control

window.authToken = null;
window.selectedCustomer = null;

const header = document.querySelector('header');
header.style.display = 'none'; // Hide header until authenticated

/**
 * Fetch access token using OAuth 2.0 password grant.
 */
function getToken() {
    const tokenUrl = window.__ENV__.TOKEN_URL;
    const formBody = new URLSearchParams({
        grant_type: 'password',
        client_id: window.__ENV__.CLIENT_ID,
        client_secret: window.__ENV__.CLIENT_SECRET,
        username: window.__ENV__.USERNAME,
        password: window.__ENV__.PASSWORD,
        scope: window.__ENV__.SCOPE
    });

    return fetch(tokenUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
        },
        body: formBody
    })
    .then(res => {
        if (!res.ok) throw new Error('Failed to retrieve token');
        return res.json();
    })
    .then(data => {
        window.authToken = data.access_token;
        console.log('Token retrieved');
        header.style.display = 'flex'; // Show header after token
        loadCustomers();
    })
    .catch(err => {
        console.error('Auth error:', err);
        document.getElementById('dashboard').innerHTML = '<p>Authentication failed. Check .env values.</p>';
    });
}

/**
 * Load customers into the dropdown.
 */
function loadCustomers() {
    const select = document.getElementById('customerSelect');
    const url = `${window.__ENV__.BASE_URL}/Customer/GetCustomers`;

    fetch(url, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${window.authToken}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            DealerCode: window.__ENV__.DEALER_CODE,
            DealerId: window.__ENV__.DEALER_ID
        })
    })
    .then(async res => {
        const contentType = res.headers.get("content-type") || "";
        const text = await res.text();

        if (!res.ok) {
            throw new Error(`HTTP ${res.status}: ${text}`);
        }

        if (!contentType.includes("application/json")) {
            throw new Error("Expected JSON response but received:\n" + text);
        }

        return JSON.parse(text);
    })
    .then(response => {
        const customers = response.Result || [];
        select.innerHTML = '<option disabled selected value="">-- Select Customer --</option>';
        customers.forEach(c => {
            const option = document.createElement('option');
            option.value = c.Code;
            option.textContent = c.Description;
            select.appendChild(option);
        });
    })
    .catch(err => {
        console.error('Failed to load customers:', err);
        select.innerHTML = '<option disabled>Error loading customers</option>';
    });
}


/**
 * Handle dropdown selection.
 */
function onCustomerSelected(event) {
    window.selectedCustomer = event.target.value;
    console.log(`Selected customer: ${window.selectedCustomer}`);
    // In production, update dashboard via fetch — here static PHP renders cards
    window.location.href = `?customer=${encodeURIComponent(window.selectedCustomer)}`;
}

// === Initialize ===
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('customerSelect').addEventListener('change', onCustomerSelected);
    getToken();
});
