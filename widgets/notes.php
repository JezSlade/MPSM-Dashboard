<?php
// widgets/notes.php

// Widget Name: Quick Notes
// Widget Icon: fas fa-sticky-note
// Widget Width: 1.0
// Widget Height: 1.0

// The $_widget_config array is no longer directly used for metadata extraction
// by discover_widgets(). It's kept here for backward compatibility or other
// internal widget logic if needed. The metadata is now parsed from comments.
$_widget_config = [
    'name' => 'Quick Notes',
    'icon' => 'sticky-note', // This 'sticky-note' will be overridden by the comment parsing
    'width' => 1,
    'height' => 1
];
?>
<div class="compact-content">
    <div class="note active">
        <div class="note-header">
            <h4>Meeting Notes</h4>
            <div class="note-date">Today, <?= date('g:i A') ?></div>
        </div>
        <div class="note-content">
            Discussed dashboard features and widget implementation.
        </div>
    </div>
</div>
<div class="expanded-content">
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
</div>
