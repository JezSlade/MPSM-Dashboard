<?php
class CodeEditorWidget {
    const MAX_EXECUTION_TIME = 5;
    const MEMORY_LIMIT = '64M';

    public static function render(array $config): string {
        $code = $config['code'] ?? '<?php\n// Write your widget code here';
        $preview = self::getPreview($code);
        $widgetId = htmlspecialchars($config['id'] ?? uniqid(), ENT_QUOTES);
        $title = htmlspecialchars($config['title'] ?? 'Code Editor', ENT_QUOTES);

        return <<<HTML
<div class="code-editor-widget" data-id="{$widgetId}">
    <div class="editor-header">
        <h3>{$title}</h3>
        <div class="editor-actions">
            <button class="edit-title-btn" title="Edit title">✏️</button>
            <button class="run-btn" title="Run code">▶ Run</button>
            <button class="save-btn" title="Save changes">💾 Save</button>
        </div>
    </div>
    <div class="editor-container">
        <div class="code-editor" id="editor-{$widgetId}">{$code}</div>
        <div class="preview-pane">
            <div class="preview-header">Live Preview</div>
            <div class="preview-content">{$preview}</div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.min.js"></script>
<script>
    (function() {
        const widget = document.querySelector('.code-editor-widget[data-id="{$widgetId}"]');
        const titleElement = widget.querySelector('h3');
        const editBtn = widget.querySelector('.edit-title-btn');
        
        editBtn.addEventListener('click', () => {
            const currentTitle = titleElement.textContent;
            const newTitle = prompt('Edit widget title:', currentTitle);
            if (newTitle !== null && newTitle.trim() !== '') {
                fetch('/update_widget_title.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        id: '{$widgetId}',
                        title: newTitle.trim()
                    })
                }).then(r => r.json()).then(data => {
                    if (data.success) titleElement.textContent = newTitle.trim();
                }).catch(err => console.error('Title update failed:', err));
            }
        });

        const editor = ace.edit("editor-{$widgetId}");
        editor.setTheme("ace/theme/monokai");
        editor.session.setMode("ace/mode/php");
        editor.setOptions({
            fontSize: "14px",
            enableBasicAutocompletion: true,
            enableLiveAutocompletion: true,
            maxLines: 30
        });

        widget.querySelector('.run-btn').addEventListener('click', () => {
            const preview = widget.querySelector('.preview-content');
            preview.innerHTML = '<div class="loading">Executing...</div>';
            
            fetch('/render_code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    code: editor.getValue(),
                    widgetId: '{$widgetId}'
                })
            }).then(r => {
                if (!r.ok) throw new Error('Execution failed');
                return r.text();
            }).then(html => {
                preview.innerHTML = html;
            }).catch(err => {
                preview.innerHTML = `<div class="error">${err.message}</div>`;
            });
        });
    })();
</script>
HTML;
    }

    private static function getPreview(string $code): string {
        $oldTimeLimit = ini_get('max_execution_time');
        $oldMemoryLimit = ini_get('memory_limit');
        
        set_time_limit(self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', self::MEMORY_LIMIT);
        
        ob_start();
        try {
            $sanitizedCode = preg_replace('/\?>\s*$/', '', $code);
            eval('?>' . $sanitizedCode);
            $output = ob_get_clean();
            
            if (empty(trim($output))) {
                return '<div class="no-output">No output generated</div>';
            }
            return $output;
        } catch (ParseError $e) {
            ob_end_clean();
            return '<div class="error">Syntax Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        } catch (Throwable $e) {
            ob_end_clean();
            return '<div class="error">Runtime Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        } finally {
            set_time_limit($oldTimeLimit);
            ini_set('memory_limit', $oldMemoryLimit);
        }
    }
}