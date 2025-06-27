<?php
$_widget_config = [
    'name' => 'Quick Notes',
    'icon' => 'sticky-note',
    'width' => 1,
    'height' => 1
];
?>
<div class="note active">
    <div class="note-header">
        <h4>Meeting Notes</h4>
        <div class="note-date">Today, <?= date('g:i A') ?></div>
    </div>
    <div class="note-content">
        Discussed dashboard features and widget implementation.
        Decided to go with a dark glass theme with neomorphic elements.
        Need to finalize the settings panel by Friday.
    </div>
</div>
<div class="note">
    <div class="note-header">
        <h4>To-Do List</h4>
        <div class="note-date">Yesterday</div>
    </div>
    <div class="note-content">
        - Research neomorphism trends<br>
        - Create widget templates<br>
        - Implement drag and drop<br>
        - Test responsiveness
    </div>
</div>
<div class="new-note">
    <textarea placeholder="Start typing a new note..."></textarea>
    <button>Save Note</button>
</div>