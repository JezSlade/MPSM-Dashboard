// public/app.js — Token Fetching, Header Display Control

window.authToken = null;
window.selectedCustomer = null;

const header = document.querySelector('header');
header.style.display = 'none'; // Hide header until authenticated

/**
 * Fetch access token using OAuth 2.0 password grant.
 */
function updateTokenBanner(status) {
    const banner = document.getElementById('tokenStatusBanner');
    banner.style.display = 'block';

    banner.classList.remove('status-success', 'status-fail', 'status-pending');

    if (status === 'success') {
        banner.classList.add('status-success');
        banner.textContent = '✅ Token retrieved successfully.';
    } else if (status === 'fail') {
        banner.classList.add('status-fail');
        banner.textContent = '❌ Failed to retrieve token.';
    } else {
        banner.classList.add('status-pending');
        banner.textContent = '⏳ Requesting token...';
    }
}

function getToken() {
    window.authToken = null;
    window.tokenStatus = 'pending';
    updateTokenBanner('pending');

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
    .then(async res => {
        const contentType = res.headers.get("content-type") || "";
        const text = await res.text();

        if (!res.ok) {
            window.tokenStatus = 'fail';
            updateTokenBanner('fail');
            throw new Error(`HTTP ${res.status}: ${text}`);
        }

        if (!contentType.includes("application/json")) {
            window.tokenStatus = 'fail';
            updateTokenBanner('fail');
            throw new Error("Expected JSON but got:\n" + text);
        }

        const json = JSON.parse(text);
        window.authToken = json.access_token;
        window.tokenStatus = 'success';
        updateTokenBanner('success');

        console.log('[Token] ✅ Token received');
        document.querySelector('header').style.display = 'flex';
        loadCustomers();
    })
    .catch(err => {
        window.tokenStatus = 'fail';
        updateTokenBanner('fail');
        console.error('[Token] ❌ Failed:', err);
        document.getElementById('dashboard').innerHTML = `<p class="card">Token request failed: ${err.message}</p>`;
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
