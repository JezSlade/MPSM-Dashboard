"""
Fix all remaining hardcoded colors in card-management.css for proper theme support
"""
import re

# Read the CSS file
with open('cms/assets/css/card-management.css', 'r', encoding='utf-8') as f:
    css_content = f.read()

# Color replacements - specific to context
replacements = [
    # Border colors
    (r'border: 2px solid #ddd', 'border: 2px solid var(--border-color)'),
    (r'border: 1px solid #ddd', 'border: 1px solid var(--border-color)'),
    (r'border-bottom: 1px solid #ddd', 'border-bottom: 1px solid var(--border-color)'),
    (r'border-top: 1px dashed #ddd', 'border-top: 1px dashed var(--border-color)'),

    # Background gradients
    (r'background: linear-gradient\(135deg, #f8f9fa, #e9ecef\)',
     'background: linear-gradient(135deg, var(--bg-secondary), var(--border-color))'),
    (r'background: #e9ecef', 'background: var(--bg-secondary)'),
    (r'background: #f0fdf4', 'background: var(--bg-secondary)'),
    (r'background: #fef2f2', 'background: var(--bg-secondary)'),

    # Specific color values for states
    (r'#0854cc', 'var(--accent-hover)'),
    (r'#6c757d', 'var(--text-secondary)'),
    (r'#5a6268', 'var(--text-secondary)'),
    (r'#e0e0e0', 'var(--border-color)'),

    # Warning/Error/Success colors
    (r'background: var\(--bg-card\)3cd', 'background: rgba(255, 193, 7, 0.1)'),
    (r'border: 1px solid #ffc107', 'border: 1px solid var(--warning-color)'),
    (r'color: #856404', 'color: var(--text-primary)'),
    (r'background: #cfe2ff', 'background: rgba(13, 110, 253, 0.1)'),
    (r'border: 1px solid #0d6efd', 'border: 1px solid var(--accent-color)'),
    (r'color: #084298', 'color: var(--text-primary)'),
    (r'background: #f8d7da', 'background: rgba(220, 53, 69, 0.1)'),

    # Supply item colors
    (r'border-left: 4px solid #28a745', 'border-left: 4px solid var(--success-color)'),
    (r'border-left-color: #ffc107', 'border-left-color: var(--warning-color)'),
    (r'border-left-color: #dc3545', 'border-left-color: var(--danger-color)'),
    (r'border-left: 4px solid #6c757d', 'border-left: 4px solid var(--text-secondary)'),

    # Supply gradients
    (r'background: linear-gradient\(90deg, #28a745, #20c997\)',
     'background: linear-gradient(90deg, var(--success-color), #20c997)'),
    (r'background: linear-gradient\(90deg, #ffc107, #fd7e14\)',
     'background: linear-gradient(90deg, var(--warning-color), #fd7e14)'),
    (r'background: linear-gradient\(90deg, #dc3545, #c82333\)',
     'background: linear-gradient(90deg, var(--danger-color), #c82333)'),
    (r'background: linear-gradient\(90deg, #28a745, #34d058\)',
     'background: linear-gradient(90deg, var(--success-color), #34d058)'),

    # Status badge colors
    (r'background: #d4edda', 'background: rgba(40, 167, 69, 0.2)'),
    (r'color: #155724', 'color: var(--success-color)'),
    (r'color: #721c24', 'color: var(--danger-color)'),

    # Border colors for specific states
    (r'border-color: #28a745', 'border-color: var(--success-color)'),
    (r'background: var\(--bg-card\)bf0', 'background: rgba(255, 193, 7, 0.1)'),
]

# Apply all replacements
for pattern, replacement in replacements:
    css_content = re.sub(pattern, replacement, css_content)

# Fix the .loading class to not override the flex layout
loading_pattern = r'\/\* Card Content States \*\/\n\.loading \{[^}]+\}'
loading_replacement = '''/* Card Content States */
.card-body .loading {
    /* Inherits display: flex from styles.css for spinner positioning */
    text-align: center;
    padding: 2rem;
    color: var(--text-secondary);
    font-style: italic;
}'''
css_content = re.sub(loading_pattern, loading_replacement, css_content, flags=re.MULTILINE)

# Write back
with open('cms/assets/css/card-management.css', 'w', encoding='utf-8') as f:
    f.write(css_content)

print("[OK] Fixed all theme colors in card-management.css")
print("[OK] Fixed loading spinner positioning")
