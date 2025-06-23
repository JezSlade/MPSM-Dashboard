<?php
declare(strict_types=1);
require_once __DIR__ . '/debug.php';     // central debug helper

$currentCustomer = $_SESSION['selectedCustomer'] ?? '';
?>
<nav class="main-nav" style="position:sticky;top:0;z-index:999;
     backdrop-filter:blur(10px);background:var(--bg-card,rgba(255,255,255,.06));width:100%;">
    <ul style="margin:0;padding:.75rem 1.5rem;display:flex;gap:2rem;list-style:none;align-items:center">
        <!-- single dashboard tab -->
        <li class="active"><a href="/index.php"
             style="text-decoration:none;font-weight:600;color:var(--text-dark,#f5f5f5)">Dashboard</a></li>

        <!-- customer search -->
        <li style="margin-left:auto">   <!-- pushes form to the right -->
            <form method="get" action="/index.php" style="display:flex;gap:.5rem">
                <input type="text" name="customer"
                       placeholder="Customer code"
                       value="<?= htmlspecialchars($currentCustomer); ?>"
                       style="padding:.4rem .6rem;border-radius:6px;border:1px solid rgba(255,255,255,.25);
                              background:rgba(0,0,0,.2);color:#fff;min-width:180px">
                <button type="submit"
                        style="padding:.4rem 1rem;border:none;border-radius:6px;
                               backdrop-filter:blur(6px);background:rgba(255,255,255,.15);
                               color:#fff;font-weight:600;cursor:pointer">
                    Go
                </button>
            </form>
        </li>
    </ul>
</nav>
