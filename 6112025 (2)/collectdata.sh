#!/bin/bash

# Combined Web Application Code Collector
# Outputs both JSON structure and readable text content

# Configuration
JSON_OUTPUT="app_structure_$(date +'%Y%m%d_%H%M%S').json"
TEXT_OUTPUT="app_contents_$(date +'%Y%m%d_%H%M%S').txt"

# Files to include full content for
FULL_EXTENSIONS=("php" "html" "htm" "css" "js" "json" "xml" "md" "txt" "env" "gitignore" "htaccess" "sh")
MAX_PREVIEW_SIZE=5000  # 5KB previews for other text files

# Directories to exclude
EXCLUDE_DIRS=("vendor" "node_modules" ".git" ".idea" "build" "dist" "cache" "logs")

# Binary extensions to skip content for
EXCLUDE_EXT=("png" "jpg" "jpeg" "gif" "svg" "ico" "woff" "woff2" "ttf" "eot" "pdf" "zip" "tar.gz" "mp3" "mp4" "avi" "mov")

# Helper functions
escape_json() {
    sed 's/\\/\\\\/g; s/"/\\"/g; s/\t/\\t/g; s/\r/\\r/g; s/\n/\\n/g'
}

is_text_file() {
    file -b --mime-encoding "$1" | grep -qvi 'binary'
}

should_exclude() {
    local file="$1"
    
    # Check excluded directories
    for dir in "${EXCLUDE_DIRS[@]}"; do
        if [[ "$file" == *"/$dir/"* ]]; then
            return 0
        fi
    done
    
    # Check excluded extensions
    local extension="${file##*.}"
    for ext in "${EXCLUDE_EXT[@]}"; do
        if [[ "${extension,,}" == "${ext,,}" ]]; then
            return 0
        fi
    done
    
    return 1
}

# Generate JSON structure
echo "Generating JSON structure..."
echo "[" > "$JSON_OUTPUT"
first_entry=true

find . -type f -print0 | while IFS= read -r -d '' file; do
    if should_exclude "$file"; then
        continue
    fi

    FILE_SIZE=$(stat -f '%z' "$file" 2>/dev/null || stat -c '%s' "$file")

    CREATION_DATE=$(stat -f '%B' "$file" 2>/dev/null || stat -c '%W' "$file" 2>/dev/null)
    if [[ "$CREATION_DATE" -le 0 || -z "$CREATION_DATE" ]]; then
        CREATION_DATE="N/A"
    else
        CREATION_DATE=$(date -r "$CREATION_DATE" '+%Y-%m-%dT%H:%M:%S' 2>/dev/null || date -d @"$CREATION_DATE" '+%Y-%m-%dT%H:%M:%S')
    fi

    MOD_DATE=$(stat -f '%Sm' -t '%Y-%m-%dT%H:%M:%S' "$file" 2>/dev/null || stat -c '%y' "$file" | cut -d'.' -f1)

    MIME_TYPE=$(file --mime-type -b "$file")
    FILE_TYPE=$(echo "$MIME_TYPE" | grep -qi 'text\|json\|xml\|script' && echo "Text" || echo "Binary")

    EXT="${file##*.}"
    INCLUDE_FULL=false
    for ext in "${FULL_EXTENSIONS[@]}"; do
        if [[ "${EXT,,}" == "$ext" ]]; then
            INCLUDE_FULL=true
            break
        fi
    done

    CONTENT=""
    if [[ "$FILE_TYPE" == "Text" ]]; then
        if [[ "$INCLUDE_FULL" == true ]]; then
            CONTENT=$(cat "$file" | escape_json)
        else
            CONTENT=$(head -c $MAX_PREVIEW_SIZE "$file" | escape_json)
        fi
    else
        CONTENT="[Binary file - content skipped]"
    fi

    if [[ "$first_entry" == false ]]; then
        echo "," >> "$JSON_OUTPUT"
    else
        first_entry=false
    fi

    cat <<EOF >> "$JSON_OUTPUT"
{
    "path": "$(echo "${file#./}" | escape_json)",
    "name": "$(basename "$file" | escape_json)",
    "directory": "$(dirname "${file#./}" | escape_json)",
    "creation_date": "$CREATION_DATE",
    "modification_date": "$MOD_DATE",
    "size_bytes": $FILE_SIZE,
    "mime_type": "$MIME_TYPE",
    "file_type": "$FILE_TYPE",
    "content": "$CONTENT"
}
EOF
done

echo "]" >> "$JSON_OUTPUT"

# Generate readable text output
echo "Generating readable text output..."
{
    echo "WEB APPLICATION CONTENT COLLECTION"
    echo "Generated: $(date)"
    echo "----------------------------------------"
    echo ""
    
    find . -type f | while read -r file; do
        if should_exclude "$file"; then
            continue
        fi
        
        EXT="${file##*.}"
        
        # Special handling for important files
        if [[ "${EXT,,}" == "php" ]]; then
            echo "==== PHP FILE: $file ===="
            echo "Size: $(stat -c%s "$file") bytes"
            echo "Last Modified: $(date -r "$file")"
            echo "----------------------------------------"
            cat "$file"
            echo -e "\n\n"
            continue
        fi
        
        if [[ "${EXT,,}" == "js" || "${EXT,,}" == "css" || "${EXT,,}" == "html" || "${EXT,,}" == "htm" ]]; then
            echo "==== ${EXT^^} FILE: $file ===="
            echo "Size: $(stat -c%s "$file") bytes"
            echo "Last Modified: $(date -r "$file")"
            echo "----------------------------------------"
            cat "$file"
            echo -e "\n\n"
            continue
        fi
        
        if [[ "${EXT,,}" == "env" || "${EXT,,}" == "gitignore" || "${EXT,,}" == "htaccess" ]]; then
            echo "==== CONFIG FILE: $file ===="
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
} > "$TEXT_OUTPUT"

echo "Collection complete!"
echo "1. JSON structure saved to: $JSON_OUTPUT"
echo "2. Readable text content saved to: $TEXT_OUTPUT"