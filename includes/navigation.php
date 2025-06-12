<nav>
    <ul>
        <?php foreach ($available_views as $slug => $title): ?>
            <li class="<?= ($slug == $current_view_slug) ? 'active' : '' ?>">
                <a href="?view=<?= urlencode($slug) ?>"><?= sanitize_html($title) ?></a>
            </li>
        <?php endforeach; ?>
        <li><a href="?logout=true">Logout</a></li>
    </ul>
</nav>
<nav class="main-navigation"> <ul>
        <?php foreach ($available_views as $slug => $title): ?>
            <li class="<?= ($slug == $current_view_slug) ? 'active' : '' ?>">
                <a href="?view=<?= urlencode($slug) ?>"><?= sanitize_html($title) ?></a>
            </li>
        <?php endforeach; ?>
        <li><a href="?logout=true">Logout</a></li>
    </ul>
</nav>
<main></main>
<main>