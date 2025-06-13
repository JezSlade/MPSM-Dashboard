window.addEventListener('DOMContentLoaded', () => {
  getToken();
});

function showTokenStatus(success) {
  const badge = document.createElement('div');
  badge.textContent = success ? 'Token OK' : 'Token Failed';
  badge.className = success ? 'token-ok' : 'token-failed';
  document.body.appendChild(badge);
  setTimeout(() => badge.remove(), 3000);
}

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

function populateCustomerDropdown() {
  const dropdown = document.getElementById('customerSelect');
  if (!dropdown) return console.error('#customerSelect not found.');

  dropdown.innerHTML = '<option disabled selected>Loading customers...</option>';

  fetch('/api/get_customer_list.php', {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      limit: 10,
      offset: 0
    })
  })
    .then(res => res.json().then(json => ({ status: res.status, body: json })))
    .then(({ status, body }) => {
      console.log('API response body:', body);

      if (
        status !== 200 ||
        body.status !== 'success' ||
        !body.data ||
        !Array.isArray(body.data.customers)
      ) {
        throw new Error('Invalid response');
      }

      dropdown.innerHTML = '<option value="">-- Select Customer --</option>';
      body.data.customers.forEach(c => {
        const option = document.createElement('option');
        option.value = c.customerId || c.id || '';
        option.textContent = c.customerDescription || c.name || 'Unnamed';
        dropdown.appendChild(option);
      });
    })
    .catch(err => {
      console.error('Failed to load customers:', err);
      dropdown.innerHTML = '<option disabled>Error loading customers</option>';
    });
}
