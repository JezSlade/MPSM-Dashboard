"""
Remove all emojis from the CMS for a professional look
Replace with text or icons
"""
import re

files_to_process = [
    'cms/index.php',
    'cms/assets/js/app.js',
    'cms/assets/js/card-manager.js',
    'cms/assets/js/card-registry.js'
]

# Define replacements for each file
replacements = {
    'cms/index.php': [
        ('🔄 Refresh Stats', 'Refresh Stats'),
        ('🔥 Warm Cache', 'Warm Cache'),
        ('🗑️ Clear All Cache', 'Clear All Cache'),
        ('💾 Save', 'Save Changes'),
    ],
    'cms/assets/js/app.js': [
        ("icon.textContent = state.theme === 'light' ? '🌙' : '☀️';",
         "icon.textContent = state.theme === 'light' ? '◐' : '◑';"),
    ],
    'cms/assets/js/card-manager.js': [
        ('<div class="tile-drag">⋮⋮</div>',
         '<div class="tile-drag">≡</div>'),
        ("${isVisible ? '👁️' : '🚫'}",
         "${isVisible ? 'Hide' : 'Show'}"),
        ("💾 Save", "Save"),
        ("🔄 Reset", "Reset"),
    ],
    'cms/assets/js/card-registry.js': [
        ("icon: '⚠️'", "icon: '!'"),
        ("'🔴'", "'●'"),
        ("'⚠️'", "'▲'"),
        ("'ℹ️'", "'○'"),
    ]
}

for file_path in files_to_process:
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()

        if file_path in replacements:
            for old, new in replacements[file_path]:
                content = content.replace(old, new)

        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(content)

        print(f"[OK] Processed {file_path}")
    except Exception as e:
        print(f"[ERROR] Failed to process {file_path}: {e}")

print("\n[OK] All emojis removed for professional look")
