window.addEventListener('DOMContentLoaded', () => {
  getToken();
});

/**
 * Show token status badge.
 */
function showTokenStatus(success) {
  const badge = document.createElement('div');
  badge.textContent = success ? 'Token OK' : 'Token Failed';
  badge.className = success ? 'token-ok' : 'token-failed';
  document.body.appendChild(badge);
  setTimeout(() => badge.remove(), 3000);
}

/**
 * Request OAuth2 token and initialize dashboard.
 */
function getToken() {
  const payload = {
    grant_type: 'password',
    client_id: window.__ENV__.CLIENT_ID,
    client_secret: window.__ENV__.CLIENT_SECRET,
    username: window.__ENV__.USERNAME,
    password: window.__ENV__.PASSWORD,
    scope: window.__ENV__.SCOPE
  };

  fetch('/api/get_token.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'Accept': 'application/json'
    },
    body: new URLSearchParams(payload)
  })
    .then(response => response.json())
    .then(data => {
      if (!data.access_token) throw new Error('Missing token');
      window.authToken = data.access_token;
      showTokenStatus(true);
      populateCustomerDropdown(); // ✅ Corrected from loadCustomers()
    })
    .catch(err => {
      showTokenStatus(false);
      console.error('Token request failed:', err);
    });
}

/**
 * Populate the customer <select> element with results from /Customer/List.
 */
function populateCustomerDropdown() {
  const dropdown = document.getElementById('customerSelect');
  const token = window.authToken;

  if (!dropdown) {
    console.error('Element with id="customerSelect" not found.');
    return;
  }

  if (!token) {
    dropdown.innerHTML = '<option value="">No API token</option>';
    return;
  }

  dropdown.innerHTML = '<option disabled selected>Loading customers...</option>';

  fetch('/api/get_customer_list.php', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  })
    .then(res => res.json().then(data => ({ status: res.status, data })))
    .then(({ status, data }) => {
      if (status !== 200 || !Array.isArray(data.result)) {
        throw new Error('Invalid response from server.');
      }

      dropdown.innerHTML = '<option value="">-- Select Customer --</option>';
      data.result.forEach(customer => {
        const option = document.createElement('option');
        option.value = customer.customerId || customer.id || '';
        option.textContent = customer.customerDescription || customer.name || 'Unnamed';
        dropdown.appendChild(option);
      });
    })
    .catch(error => {
      console.error('Failed to load customers:', error);
      dropdown.innerHTML = '<option disabled>Error loading customers</option>';
    });
}
