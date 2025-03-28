// Function to fetch and display auth data
function loadAuthData() {
    fetch('/api/auth')
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

// Function to fetch and display MPSM data
function loadMpsmData() {
    fetch('/api/data')
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

window.onload = function() {
    loadAuthData();
    loadMpsmData();
};
