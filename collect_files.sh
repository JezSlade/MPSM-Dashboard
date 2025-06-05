#!/bin/bash

# Smart PHP Project File Collector
OUTPUT_FILE="php_project_contents_$(date +'%Y%m%d_%H%M%S').txt"

# Configure exclusions (add more as needed)
EXCLUDE_DIRS=("vendor" "node_modules" ".git" ".idea" "build" "dist")
EXCLUDE_EXT=("png" "jpg" "jpeg" "gif" "svg" "ico" "woff" "woff2" "ttf" "eot" "pdf" "zip" "tar.gz")

# Better text file detection
is_text_file() {
    file -b --mime-encoding "$1" | grep -qvi 'binary'
}

# Start output
{
    echo "PHP PROJECT CONTENT COLLECTION"
    echo "Generated: $(date)"
    echo "----------------------------------------"
    echo ""
    
    find . -type f | while read -r file; do
        # Skip excluded directories
        for dir in "${EXCLUDE_DIRS[@]}"; do
            if [[ "$file" == *"/$dir/"* ]]; then
                continue 2
            fi
        done
        
        # Skip excluded extensions
        extension="${file##*.}"
        for ext in "${EXCLUDE_EXT[@]}"; do
            if [[ "${extension,,}" == "${ext,,}" ]]; then
                continue 2
            fi
        done
        
        # Special handling for PHP files - always include
        if [[ "${file##*.}" == "php" ]]; then
            echo "==== PHP FILE: $file ===="
            echo "Size: $(stat -c%s "$file") bytes"
            echo "Last Modified: $(date -r "$file")"
            echo "----------------------------------------"
            cat "$file"
            echo -e "\n\n"
            continue
        fi
        
        # For other files, check if they're text
        if is_text_file "$file"; then
            echo "==== TEXT FILE: $file ===="
            echo "Size: $(stat -c%s "$file") bytes"
            echo "Last Modified: $(date -r "$file")"
            echo "----------------------------------------"
            head -c 100000 "$file"  # Show first 100KB
            echo -e "\n\n"
        else
            echo "==== BINARY FILE: $file ===="
            echo "Size: $(stat -c%s "$file") bytes"
            echo "Last Modified: $(date -r "$file")"
            echo "[Binary content not displayed]"
            echo -e "\n"
        fi
    done
} > "$OUTPUT_FILE"

echo "Collection complete! Output saved to $OUTPUT_FILE"