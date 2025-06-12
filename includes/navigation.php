<?php
/**
 * includes/navigation.php
 *
 * Main Application Navigation
 *
 * This file contains the primary navigation menu.
 */
// $available_views and $current_view_slug are passed from index.php
?>
<nav class="main-navigation">
    <ul>
        <?php if (!empty($available_views)): ?>
            <?php foreach ($available_views as $slug => $title):
                $active = ($slug === $current_view_slug) ? 'active' : '';
                $url    = BASE_URL . '?view=' . urlencode($slug);
            ?>
                <li>
                    <a
                        href="<?php echo sanitize_html($url); ?>"
                        class="<?php echo sanitize_html($active); ?>"
                    >
                        <?php echo sanitize_html($title); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li><span>No views available</span></li>
        <?php endif; ?>
        </ul>
</nav>
<main>