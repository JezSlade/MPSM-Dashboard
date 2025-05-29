export function init(container, customerId) {
  container.innerHTML = "<h3>Debug Panel</h3><p>Loading...</p>";

  fetch("api/token.php")
    .then(res => res.json())
    .then(token => {
      container.innerHTML = `
        <div style="background:#111;padding:1em;border-radius:8px;">
          <h4>Token Info</h4>
          <pre>${JSON.stringify(token, null, 2)}</pre>
          <h4>Selected Customer ID</h4>
          <p>${customerId || "None"}</p>
        </div>
      `;
    })
    .catch(err => {
      container.innerHTML = `<p style="color:red;">Debug error: ${err}</p>`;
    });
}
