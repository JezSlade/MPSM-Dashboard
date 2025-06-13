// app.js — Vanilla JavaScript Dashboard Logic

window.selectedCustomer = null;

/**
 * Fetch customers and populate dropdown.
 */
function loadCustomers() {
    const select = document.getElementById('customerSelect');
    fetch(`${window.__ENV__.API_BASE_URL}/customers`, {
        headers: {
            'Authorization': `Bearer ${window.__ENV__.API_KEY}`
        }
    })
    .then(res => res.json())
    .then(customers => {
        select.innerHTML = '<option disabled selected value="">-- Select Customer --</option>';
        customers.forEach(customer => {
            const option = document.createElement('option');
            option.value = customer.id;
            option.textContent = customer.name;
            select.appendChild(option);
        });
    })
    .catch(err => {
        console.error('Failed to load customers:', err);
        select.innerHTML = '<option disabled>Error loading customers</option>';
    });
}

/**
 * Handle customer selection.
 */
function onCustomerSelected(event) {
    window.selectedCustomer = event.target.value;
    console.log(`Selected customer: ${window.selectedCustomer}`);
    loadDashboardForCustomer();
}

/**
 * Fetch and render dashboard data.
 */
function loadDashboardForCustomer() {
    const dashboard = document.getElementById('dashboard');
    dashboard.innerHTML = 'Loading dashboard...';

    fetch(`${window.__ENV__.API_BASE_URL}/customers/${window.selectedCustomer}/dashboard`, {
        headers: {
            'Authorization': `Bearer ${window.__ENV__.API_KEY}`
        }
    })
    .then(res => res.json())
    .then(data => {
        renderDashboard(data);
    })
    .catch(err => {
        console.error('Dashboard fetch error:', err);
        dashboard.innerHTML = 'Failed to load dashboard.';
    });
}

/**
 * Render dashboard cards from API data.
 */
function renderDashboard(data) {
    const dashboard = document.getElementById('dashboard');
    dashboard.innerHTML = ''; // Clear

    data.cards.forEach(card => {
        const div = document.createElement('div');
        div.className = 'card';
        div.innerHTML = `
            <h3>${card.title}</h3>
            <p>${card.body}</p>
        `;
        dashboard.appendChild(div);
    });
}

// === Init ===
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('customerSelect').addEventListener('change', onCustomerSelected);
    loadCustomers();
});
