document.addEventListener('DOMContentLoaded', async () => {
  const customerSelect = document.getElementById('customerSelect');
  const deviceStatus = document.getElementById('deviceStatus');
  const toggleDebug = document.getElementById('toggleDebug');
  const debugWindow = document.getElementById('debugWindow');

  // Toggle debug window
  toggleDebug.addEventListener('click', () => {
    debugWindow.classList.toggle('hidden');
  });

  // Fetch and populate customers
  async function loadCustomers() {
    try {
      const data = await Core.fetchWithAuth('Customer/GetCustomers', { method: 'GET' });
      if (data.IsValid && data.Result) {
        customerSelect.innerHTML = '<option value="">Select a customer</option>';
        data.Result.forEach(customer => {
          const option = document.createElement('option');
          option.value = customer.Id;
          option.textContent = customer.CustomerDescription || customer.Id;
          customerSelect.appendChild(option);
        });
      } else {
        Core.renderError('deviceStatus', 'No customers found');
      }
    } catch (error) {
      Core.renderError('deviceStatus', 'Failed to load customers');
    }
  }

  // Fetch and display device status
  async function loadDeviceStatus(customerId) {
    try {
      // Placeholder deviceId (replace with actual device ID from customer data in future)
      const deviceId = '12345';
      const data = await Core.fetchWithAuth(`AlertLimit2/Device/GetDefault?id=${deviceId}`, { method: 'GET' });
      if (data.IsValid && data.Result) {
        deviceStatus.innerHTML = '';
        data.Result.forEach(device => {
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
        Core.renderError('deviceStatus', 'No device status available');
      }
    } catch (error) {
      Core.renderError('deviceStatus', 'Failed to load device status');
    }
  }

  // Event listener for customer selection
  customerSelect.addEventListener('change', async (event) => {
    const customerId = event.target.value;
    if (customerId) {
      await loadDeviceStatus(customerId);
    } else {
      deviceStatus.innerHTML = '<p class="text-center">Select a customer to view device status</p>';
    }
  });

  // Initialize
  await loadCustomers();
});