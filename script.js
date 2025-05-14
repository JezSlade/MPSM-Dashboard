document.addEventListener('DOMContentLoaded', function () {
  fetch('get_devices.php')
    .then(response => response.json())
    .then(data => {
      const tbody = document.querySelector('#deviceTable tbody');
      data.forEach(device => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${device.AssetNumber || ''}</td>
          <td>${device.IpAddress || ''}</td>
          <td>${device.Model || ''}</td>
        `;
        tbody.appendChild(row);
      });
    })
    .catch(error => console.error('Error fetching devices:', error));
});
