document.addEventListener('DOMContentLoaded', () => {
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Theme Toggle
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;
    const currentTheme = localStorage.getItem('theme');

    if (currentTheme) {
        body.classList.add(currentTheme);
    }

    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-theme');
        if (body.classList.contains('dark-theme')) {
            localStorage.setItem('theme', 'dark-theme');
        } else {
            localStorage.removeItem('theme'); // Or localStorage.setItem('theme', 'light-theme'); if you have a light-theme class
        }
    });

    // View Error Logs (Debug)
    const debugLogsButton = document.getElementById('debug-logs');
    if (debugLogsButton) {
        debugLogsButton.addEventListener('click', () => {
            alert('Viewing error logs (Debug functionality to be implemented)');
            // You can replace the alert with actual functionality to view error logs.
            // This might involve fetching logs from a server or opening a debug panel.
        });
    }

    // Clear Session Cookies
    const clearSessionButton = document.getElementById('clear-session');
    if (clearSessionButton) {
        clearSessionButton.addEventListener('click', () => {
            // Clear session storage
            sessionStorage.clear();

            // Clear all cookies for the current domain
            document.cookie.split(";").forEach(function(c) {
                document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
            });

            alert('Session cookies cleared!');
        });
    }

    // Hard Refresh
    const refreshAllButton = document.getElementById('refresh-all');
    if (refreshAllButton) {
        refreshAllButton.addEventListener('click', () => {
            // Force reload the page, bypassing cache
            window.location.reload(true);
        });
    }
});