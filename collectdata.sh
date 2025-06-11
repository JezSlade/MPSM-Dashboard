#!/bin/bash

OUTPUT_FILE="app_core_files_$(date +'%Y%m%d_%H%M%S').json"
FULL_EXTENSIONS=("php" "html" "htm" "css" "js" "json" "xml" "md" "txt")
MAX_PREVIEW_SIZE=5000  # 5KB previews for other text files

echo "[" > "$OUTPUT_FILE"
first_entry=true

escape_json() {
    sed 's/\\/\\\\/g; s/"/\\"/g; s/\t/\\t/g; s/\r/\\r/g; s/\n/\\n/g'
}

find . -type f -print0 | while IFS= read -r -d '' file; do
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
        echo "," >> "$OUTPUT_FILE"
    else
        first_entry=false
    fi

    cat <<EOF >> "$OUTPUT_FILE"
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

echo "]" >> "$OUTPUT_FILE"

echo "Human-readable JSON with text extracted to $OUTPUT_FILE"
