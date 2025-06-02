document.addEventListener('DOMContentLoaded', () => {
  const customerSelect = document.getElementById('customerSelect');
  const deviceStatus = document.getElementById('deviceStatus');
  const toggleDebug = document.getElementById('toggleDebug');
  const debugWindow = document.getElementById('debugWindow');
  const debugLogs = document.getElementById('debugLogs');

  // Toggle debug window
  toggleDebug.addEventListener('click', () => {
    debugWindow.classList.toggle('hidden');
  });

  // Log error to debug window
  function logError(message) {
    const timestamp = new Date().toLocaleString();
    const logEntry = document.createElement('p');
    logEntry.textContent = `[${timestamp}] ${message}`;
    debugLogs.appendChild(logEntry);
  }

  // Fetch customers
  async function loadCustomers() {
    try {
      const response = await fetch('customers.php');
      const data = await response.json();
      if (data.success && data.customers) {
        customerSelect.innerHTML = '<option value="">Select a customer</option>';
        data.customers.forEach(customer => {
          const option = document.createElement('option');
          option.value = customer.Id;
          option.textContent = customer.CustomerDescription || customer.Id;
          customerSelect.appendChild(option);
        });
      } else {
        deviceStatus.innerHTML = '<p class="text-red-400">No customers found</p>';
        logError(data.error || 'No customers found');
      }
    } catch (error) {
      deviceStatus.innerHTML = '<p class="text-red-400">Failed to load customers</p>';
      logError(`Failed to load customers: ${error.message}`);
    }
  }

  // Fetch device status
  async function loadDeviceStatus(customerId) {
    try {
      const response = await fetch(`device.php?customerId=${customerId}`);
      const data = await response.json();
      if (data.success && data.devices) {
        deviceStatus.innerHTML = '';
        data.devices.forEach(device => {
          const card = document.createElement('div');
          card.className = 'p-4 bg-gray-800 rounded-lg neumorphic device-card';
          card.innerHTML = `
            <h3 class="text-lg font-semibold">Device ID: ${device.Id}</h3>
            <p>Supply Type: ${device.SupplyType?.Description || 'N/A'}</p>
            <p>Color Type: ${device.ColorType?.Description || 'N/A'}</p>
            <p>Alert Status: Active</p>
          `;
          deviceStatus.appendChild(card);
        });
      } else {
        deviceStatus.innerHTML = '<p class="text-red-400">No device status available</p>';
        logError(data.error || 'No device status available');
      }
    } catch (error) {
      deviceStatus.innerHTML = '<p class="text-red-400">Failed to load device status</p>';
      logError(`Failed to load device status: ${error.message}`);
    }
  }

  // Customer selection event
  customerSelect.addEventListener('change', (event) => {
    const customerId = event.target.value;
    if (customerId) {
      loadDeviceStatus(customerId);
    } else {
      deviceStatus.innerHTML = '<p class="text-center">Select a customer to view device status</p>';
    }
  });

  // Initialize
  loadCustomers();
});