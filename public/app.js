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

function updateTokenBanner(status) {
    const banner = document.getElementById('tokenStatusBanner');
    banner.style.display = 'block';
    banner.classList.remove('status-success', 'status-fail', 'status-pending');

    if (status === 'success') {
        banner.classList.add('status-success');
        banner.textContent = '✅ Token retrieved successfully.';
        setTimeout(() => banner.style.display = 'none', 3000);
    } else if (status === 'fail') {
        banner.classList.add('status-fail');
        banner.textContent = '❌ Failed to retrieve token.';
    } else {
        banner.classList.add('status-pending');
        banner.textContent = '⏳ Requesting token...';
    }
}

function scheduleTokenRefresh(secondsUntilExpiry) {
    const refreshIn = Math.max(0, (secondsUntilExpiry - 60)) * 1000;
    console.log(`[Token] 🔁 Refresh scheduled in ${refreshIn / 1000}s`);
    setTimeout(getToken, refreshIn);
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

        const expiresIn = parseInt(json.expires_in, 10) || 3600;
        scheduleTokenRefresh(expiresIn);

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
 * Load customers into the dropdown via local PHP proxy.
 */
/**
 * Populate the customer dropdown using data from the API proxy.
 */
function populateCustomerDropdown() {
  const dropdown = document.getElementById('customerDropdown');
  const token = window.authToken;
  const dealerCode = window.__ENV__?.DEALER_CODE;

  if (!dropdown) {
    console.error('Dropdown element with id="customerDropdown" not found.');
    return;
  }

  if (!token) {
    dropdown.innerHTML = '<option value="">No API token</option>';
    return;
  }

  // Optional: clear and show loading indicator
  dropdown.innerHTML = '<option disabled selected>Loading customers...</option>';

  fetch('/api/get_customer_list.php', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
      if (status !== 200 || !data.result || !Array.isArray(data.result)) {
        throw new Error('Invalid or missing result array in response.');
      }

      dropdown.innerHTML = '<option value="">Select a customer</option>';

      data.result.forEach(customer => {
        const option = document.createElement('option');
        option.value = customer.customerId || customer.id || '';
        option.textContent = customer.customerDescription || customer.name || 'Unnamed';
        dropdown.appendChild(option);
      });
    })
    .catch(error => {
      console.error('Failed to load customers:', error);
      dropdown.innerHTML = '<option value="">Error loading customers</option>';
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
