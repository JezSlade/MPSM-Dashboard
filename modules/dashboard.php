<?php
// modules/dashboard.php
// This module displays the main dashboard content.
?>
<div class="glass p-6 rounded-lg shadow-xl mb-6">
    <h1 class="text-3xl text-cyan-neon font-bold mb-4">Welcome to Your Dashboard!</h1>
    <p class="text-default text-lg mb-4">This is your central hub for managing the system.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="p-4 rounded-lg bg-black-smoke border border-gray-700 shadow-inner-custom">
            <h3 class="text-xl text-yellow-neon mb-2">System Overview</h3>
            <p class="text-sm text-gray-300">Quick stats and important alerts will appear here.</p>
        </div>
        <div class="p-4 rounded-lg bg-black-smoke border border-gray-700 shadow-inner-custom">
            <h3 class="text-xl text-yellow-neon mb-2">Recent Activity</h3>
            <p class="text-sm text-gray-300">See the latest actions taken by users.</p>
        </div>
        <div class="p-4 rounded-lg bg-black-smoke border border-gray-700 shadow-inner-custom">
            <h3 class="text-xl text-yellow-neon mb-2">Module Access</h3>
            <p class="text-sm text-gray-300">You have access to <?php echo count($accessible_modules ?? []); ?> modules.</p>
        </div>
    </div>
</div>