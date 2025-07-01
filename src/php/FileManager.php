<?php
// src/php/FileManager.php

class FileManager {
    private $appRoot;

    public function __construct($appRoot) {
        $this->appRoot = $appRoot;
    }

    /**
     * Validates and normalizes a given file path to prevent directory traversal.
     * Ensures the path stays within the APP_ROOT.
     *
     * @param string $path The user-provided path.
     * @return string|false The normalized real path within APP_ROOT, or false if invalid/outside root.
     */
    private function validatePath($path) {
        $full_path = realpath($this->appRoot . '/' . $path);

        // Ensure the path is within the APP_ROOT and is not pointing to a device/symlink outside.
        if ($full_path && str_starts_with($full_path, $this->appRoot . DIRECTORY_SEPARATOR)) {
            return $full_path;
        }
        // Handle the APP_ROOT itself (e.g., if path is '.' or '')
        if ($full_path === $this->appRoot) {
            return $full_path;
        }

        return false; // Path is invalid or outside APP_ROOT
    }


    /**
     * Lists files and directories within a given path, restricted to APP_ROOT.
     * @param string $path Relative path from APP_ROOT.
     * @return array|false List of files/dirs (name, type), or false on error/invalid path.
     */
    public function listFiles($path) {
        $absolute_path = $this->validatePath($path);
        if ($absolute_path === false || !is_dir($absolute_path)) {
            error_log("IDE: listFiles - Invalid or non-directory path: " . $path);
            return false;
        }

        $items = scandir($absolute_path);
        if ($items === false) {
            error_log("IDE: listFiles - Failed to scan directory: " . $absolute_path);
            return false;
        }

        $file_list = [];
        foreach ($items as $item) {
            if ($item === '.' || ($item === '..' && $absolute_path === $this->appRoot)) {
                // '.' is always useful. '..' only if not at root.
                continue;
            }

            $item_full_path = $absolute_path . DIRECTORY_SEPARATOR . $item;
            $relative_path = str_replace($this->appRoot . DIRECTORY_SEPARATOR, '', $item_full_path);

            $file_list[] = [
                'name' => $item,
                'path' => $relative_path,
                'type' => is_dir($item_full_path) ? 'dir' : 'file',
                'is_writable' => is_writable($item_full_path)
            ];
        }

        // Sort directories first, then files, both alphabetically
        usort($file_list, function($a, $b) {
            if ($a['type'] === 'dir' && $b['type'] === 'file') return -1;
            if ($a['type'] === 'file' && $b['type'] === 'dir') return 1;
            return strcmp($a['name'], $b['name']);
        });

        // Add '..' entry if not at the root
        if ($absolute_path !== $this->appRoot) {
            $parent_path_relative = str_replace($this->appRoot . DIRECTORY_SEPARATOR, '', dirname($absolute_path));
            // Special case for root-level folders: if parent path becomes just '.' after stripping APP_ROOT, make it '' for consistency.
            if ($parent_path_relative === '.') $parent_path_relative = '';

            array_unshift($file_list, [
                'name' => '..',
                'path' => $parent_path_relative,
                'type' => 'dir',
                'is_writable' => true // Parent is always conceptually writable to navigate back
            ]);
        }
        
        return $file_list;
    }

    /**
     * Reads content of a file, restricted to APP_ROOT.
     * @param string $path Relative path from APP_ROOT.
     * @return string|false File content, or false on error/invalid path.
     */
    public function readFile($path) {
        $absolute_path = $this->validatePath($path);
        if ($absolute_path === false || !is_file($absolute_path)) {
            error_log("IDE: readFile - Invalid or non-file path: " . $path);
            return false;
        }
        $content = file_get_contents($absolute_path);
        if ($content === false) {
            error_log("IDE: readFile - Failed to read file: " . $absolute_path);
        }
        return $content;
    }

    /**
     * Saves content to a file, restricted to APP_ROOT.
     * @param string $path Relative path from APP_ROOT.
     * @param string $content Content to write.
     * @return bool True on success, false on failure.
     */
    public function saveFile($path, $content) {
        $absolute_path = $this->validatePath($path);
        if ($absolute_path === false) {
            error_log("IDE: saveFile - Invalid path: " . $path);
            return false;
        }
        // Check if the file exists and is writable, or if its directory is writable for new files
        if (file_exists($absolute_path) && !is_writable($absolute_path)) {
            error_log("IDE: saveFile - File exists but not writable: " . $absolute_path);
            return false;
        }
        if (!file_exists($absolute_path) && !is_writable(dirname($absolute_path))) {
            error_log("IDE: saveFile - Directory not writable for new file: " . dirname($absolute_path));
            return false;
        }

        $result = file_put_contents($absolute_path, $content);
        if ($result === false) {
            error_log("IDE: saveFile - Failed to write content to file: " . $absolute_path);
        }
        return $result !== false;
    }

    /**
     * Creates a new widget template file.
     * @param string $widget_id The ID for the new widget.
     * @param string $widget_name The display name for the new widget.
     * @param string $widget_icon The Font Awesome icon for the new widget.
     * @param float $widget_width The default width for the new widget.
     * @param float $widget_height The default height for the new widget.
     * @return bool True on success, false on failure.
     */
    public function createWidgetTemplate($widget_id, $widget_name, $widget_icon, $widget_width, $widget_height) {
        $widget_file_path = APP_ROOT . '/widgets/' . $widget_id . '.php';
        if (file_exists($widget_file_path)) {
            return false; // Widget ID already exists
        }

        // Clamp dimensions
        $widget_width = max(0.5, min(3.0, (float)$widget_width));
        $widget_height = max(0.5, min(4.0, (float)$widget_height));

        // Generate widget file content
        $template_content = <<<PHP
<?php
// widgets/{$widget_id}.php

// Widget configuration
\$_widget_config = [
    'name' => '{$widget_name}',
    'icon' => '{$widget_icon}',
    'width' => {$widget_width},
    'height' => {$widget_height}
];
?>
<div class="compact-content">
    <div style="text-align: center; padding: 20px;">
        <p style="font-size: 20px; font-weight: bold; color: var(--accent);">
            <i class="fas fa-{$widget_icon}"></i> {$widget_name}
        </p>
        <p style="font-size: 14px; color: var(--text-secondary);">
            This is your new widget!
        </p>
    </div>
</div>
<div class="expanded-content">
    <h4 style="color: var(--accent); margin-bottom: 15px;">Expanded View for {$widget_name}</h4>
    <p>Add more detailed content or functionality here.</p>
    <p>You can edit this file in the IDE widget: <code>widgets/{$widget_id}.php</code></p>
</div>
PHP;

        return file_put_contents($widget_file_path, $template_content) !== false;
    }
}
