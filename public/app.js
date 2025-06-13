window.addEventListener('DOMContentLoaded', () => {
  getToken();
});

/**
 * Visually display token status as a temporary badge.
 */
function showTokenStatus(success) {
  const badge = document.createElement('div');
  badge.textContent = success ? 'Token OK' : 'Token Failed';
  badge.className = success ? 'token-ok' : 'token-failed';
  document.body.appendChild(badge);
  setTimeout(() => badge.remove(), 3000);
}

/**
 * Retrieve an access token from the backend proxy.
 */
function getToken() {
  const payload = new URLSearchParams({
    grant_type: 'password',
    client_id: window.__ENV__.CLIENT_ID,
    client_secret: window.__ENV__.CLIENT_SECRET,
    username: window.__ENV__.USERNAME,
    password: window.__ENV__.PASSWORD,
    scope: window.__ENV__.SCOPE
  });

  fetch('/api/get_token.php', {
    method: 'POST',
    body: payload,
    headers: { 'Accept': 'application/json' }
  })
    .then(res => res.json())
    .then(data => {
      if (!data.access_token) throw new Error('No access_token in response');
      window.authToken = data.access_token;
      showTokenStatus(true);
      populateCustomerDropdown();
    })
    .catch(err => {
      showTokenStatus(false);
      console.error('Token request failed:', err);
    });
}

/**
 * Load customers from backend proxy and populate dropdown.
 */
function populateCustomerDropdown() {
  const dropdown = document.getElementById('customerSelect');
  if (!dropdown) return console.error('#customerSelect not found.');

  dropdown.innerHTML = '<option disabled selected>Loading customers...</option>';

  fetch('/api/get_customer_list.php', {
    method: 'GET',
    headers: {
      'Accept': 'application/json'
    }
  })
    .then(res => res.json())
    .then(body => {
      console.log('Customer list response:', body); // Debug line

      const customers = body?.data?.customers;
      if (!Array.isArray(customers)) {
        throw new Error('Invalid response format');
      }

      dropdown.innerHTML = '<option value="">-- Select Customer --</option>';
      customers.forEach(customer => {
        const option = document.createElement('option');
        option.value = customer.customerId || customer.id || '';
        option.textContent = customer.customerDescription || customer.name || 'Unnamed';
        dropdown.appendChild(option);
      });
    })
    .catch(err => {
      console.error('Failed to load customers:', err);
      dropdown.innerHTML = '<option disabled>Error loading customers</option>';
    });
}
