<?php
// index.php — Dashboard container with drag-and-drop logic and dynamic cards
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<main style="position: relative;">
    <div class="settings-menu" style="position: fixed; top: 10px; right: 10px; z-index: 1001; background: var(--bg-accent); padding: 12px; border-radius: 8px;">
        <h2 style="margin-top: 0;">Card Visibility</h2>
        <?php
        $cardFiles = glob(__DIR__ . '/cards/*.php');
        foreach ($cardFiles as $index => $cardPath) {
            $cardId = 'card' . $index;
            $cardName = basename($cardPath, '.php');
            echo "<label><input type='checkbox' id='{$cardId}-toggle'> {$cardName}</label><br>\n";
        }
        ?>
    </div>

    <div class="dashboard-container">
        <?php
        foreach ($cardFiles as $index => $cardPath) {
            $cardId = 'card' . $index;
            $title = basename($cardPath, '.php');
            $allowMinimize = true;
            $allowSettings = true;

            echo "<div class='card-wrapper' id='{$cardId}' style='display:none; left: 100px; top: " . ($index * 80 + 40) . "px;'>\n";
            include __DIR__ . '/includes/card_header.php';
            echo "<div class='card-content neumorphic glow'>\n";
            include $cardPath;
            echo "</div></div>\n";
        }
        ?>
    </div>
</main>
<script>
const checkboxes = document.querySelectorAll('.settings-menu input[type="checkbox"]');
checkboxes.forEach(cb => {
    cb.addEventListener('change', () => {
        const cardId = cb.id.replace('-toggle', '');
        const card = document.getElementById(cardId);
        if (cb.checked) {
            card.style.display = 'block';
            card.style.zIndex = 1;
            checkAndResolveCollisions(card);
        } else {
            card.style.display = 'none';
        }
    });
});

let dragTarget = null, offsetX = 0, offsetY = 0;
document.querySelectorAll('.card-wrapper').forEach(card => {
    const header = card.querySelector('.card-header');
    const minimizeBtn = card.querySelector('[data-action="minimize"]');
    const settingsBtn = card.querySelector('[data-action="settings"]');
    const content = card.querySelector('.card-content');

    if (header) {
        header.addEventListener('mousedown', e => {
            dragTarget = card;
            offsetX = e.clientX - dragTarget.offsetLeft;
            offsetY = e.clientY - dragTarget.offsetTop;
            dragTarget.classList.add('dragging');
        });
    }

    if (minimizeBtn && content) {
        minimizeBtn.addEventListener('click', () => {
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
        });
    }

    if (settingsBtn) {
        settingsBtn.addEventListener('click', () => {
            alert(`Settings for ${card.id}`);
        });
    }
});

document.addEventListener('mousemove', e => {
    if (dragTarget) {
        dragTarget.style.left = (e.clientX - offsetX) + 'px';
        dragTarget.style.top = (e.clientY - offsetY) + 'px';
    }
});

document.addEventListener('mouseup', () => {
    if (dragTarget) {
        dragTarget.classList.remove('dragging');
        checkAndResolveCollisions(dragTarget);
        dragTarget = null;
    }
});

function getCardRect(card) {
    const rect = card.getBoundingClientRect();
    return {
        left: rect.left,
        top: rect.top,
        width: rect.width,
        height: rect.height
    };
}

function isOverlapping(rect1, rect2) {
    return (
        rect1.left < rect2.left + rect2.width &&
        rect1.left + rect1.width > rect2.left &&
        rect1.top < rect2.top + rect2.height &&
        rect1.top + rect1.height > rect2.top
    );
}

function checkAndResolveCollisions(movedCard, iterations = 0) {
    if (iterations >= 100) return;

    let hasOverlaps = false, dx = 0, dy = 0;
    const movedRect = getCardRect(movedCard);
    const cards = Array.from(document.querySelectorAll('.card-wrapper')).filter(card => card !== movedCard && card.style.display !== 'none');

    for (let other of cards) {
        const otherRect = getCardRect(other);
        if (isOverlapping(movedRect, otherRect)) {
            hasOverlaps = true;
            const cx1 = movedRect.left + movedRect.width / 2;
            const cx2 = otherRect.left + otherRect.width / 2;
            const cy1 = movedRect.top + movedRect.height / 2;
            const cy2 = otherRect.top + otherRect.height / 2;
            const angle = Math.atan2(cy2 - cy1, cx2 - cx1);
            dx += -Math.cos(angle) * 5;
            dy += -Math.sin(angle) * 5;
        }
    }

    if (hasOverlaps) {
        let newLeft = parseInt(movedCard.style.left || '0') + dx;
        let newTop = parseInt(movedCard.style.top || '0') + dy;
        newLeft = Math.max(0, Math.min(newLeft, window.innerWidth - movedCard.offsetWidth));
        newTop = Math.max(0, Math.min(newTop, window.innerHeight - movedCard.offsetHeight));
        movedCard.style.left = `${newLeft}px`;
        movedCard.style.top = `${newTop}px`;
        setTimeout(() => checkAndResolveCollisions(movedCard, iterations + 1), 50);
    }
}
</script>
</body>
</html>
