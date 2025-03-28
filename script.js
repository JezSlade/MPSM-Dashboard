function loadAuthData() {
    fetch('api/auth')  // no leading slash
        .then(response => response.json())
        .then(data => {
            const pre = document.getElementById("auth-data");
            if (data.status === "success") {
                pre.textContent = data.auth.join("\n");
            } else {
                pre.textContent = "Error loading authentication data.";
            }
        })
        .catch(err => {
            document.getElementById("auth-data").textContent = "Network error loading auth data.";
        });
}

function loadMpsmData() {
    fetch('api/data')  // no leading slash
        .then(response => response.json())
        .then(data => {
            const pre = document.getElementById("mpsm-data");
            if (data.status === "success") {
                pre.textContent = data.data.join("\n");
            } else {
                pre.textContent = "Error loading MPSM data.";
            }
        })
        .catch(err => {
            document.getElementById("mpsm-data").textContent = "Network error loading MPSM data.";
        });
}
