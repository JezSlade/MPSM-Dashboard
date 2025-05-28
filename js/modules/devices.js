export function init(container) {
  container.innerHTML = "<h3>Devices Module</h3><p>Fetching data...</p>";

  fetch("api/token.php")
    .then(res => res.json())
    .then(token => {
      return fetch("https://api.abassetmanagement.com/api3/Device/List", {
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
