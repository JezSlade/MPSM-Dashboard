export function init(container, customerId) {
  container.innerHTML = "<h3>Devices Module</h3><p>Fetching data...</p>";

  if (!customerId) {
    container.innerHTML = "<p>Please select a customer to load device data.</p>";
    return;
  }

  fetch("api/token.php")
    .then(res => res.json())
    .then(token => {
      return fetch(`https://api.abassetmanagement.com/api3/CustomerDashboard/Devices?customerId=${customerId}`, {
        headers: {
          Authorization: `Bearer ${token.access_token}`
        }
      });
    })
    .then(res => res.json())
    .then(data => {
      container.innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
    })
    .catch(err => {
      container.innerHTML = `<p style="color:red;">Error: ${err}</p>`;
    });
}
