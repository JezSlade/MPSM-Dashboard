#!/bin/bash

# Ensure 'stat' command is available and supports %w for birth time
# (Some older Linux systems or non-GNU stat might not have %w.
# If %w doesn't work, %W for epoch time might be an alternative.)

# Function to get file metadata and content
get_file_data() {
    local filepath="$1"
    local filename=$(basename "$filepath")
    local dirname=$(dirname "$filepath")

    # Get metadata using stat
    # %w: time of file birth (creation time), human-readable
    # %y: time of last data modification, human-readable
    # %s: total size, in bytes
    local creation_date=$(stat -c %w "$filepath" 2>/dev/null || echo "N/A")
    local modification_date=$(stat -c %y "$filepath" 2>/dev/null || echo "N/A")
    local file_size=$(stat -c %s "$filepath" 2>/dev/null || echo "N/A")

    # Read file content. Use 'cat' but handle potential errors or binary files
    # Using 'head -c 1024' to limit content to first 1KB to avoid excessive output for large files.
    # Adjust this limit or remove it based on your AI's needs.
    local file_content=$(head -c 1024 "$filepath" 2>/dev/null | tr -d '\n\r\t' | sed 's/"/\\"/g' | tr -c '[:print:]' ' ')

    # Escape JSON special characters in content.
    # Replace newlines, tabs, and backslashes with their JSON escape sequences.
    # Replace double quotes with escaped double quotes.
    # Replace other non-printable characters with spaces (for AI readability).
    file_content=$(echo "$file_content" | sed -E ':a;N;$!ba;s/\r/\n/g' | \
                   sed 's/\\/\\\\/g' | sed 's/"/\\"/g' | \
                   tr -d '\n\r\t' | tr -c '[:print:]' ' ')


    # Output data in JSON Lines format
    echo "{\"path\": \"$filepath\", \"directory\": \"$dirname\", \"name\": \"$filename\", \"creation_date\": \"$creation_date\", \"modification_date\": \"$modification_date\", \"size_bytes\": $file_size, \"content_preview\": \"$file_content\"}"
}

# Generate a timestamp for the output file
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
OUTPUT_FILE="file_data_${TIMESTAMP}.jsonl"

# Start JSON Lines output (each line is a JSON object)
# Iterate through all files and directories in the current directory and subdirectories
find . -type f -print0 | while IFS= read -r -d $'\0' file; do
    get_file_data "$file" >> "$OUTPUT_FILE"
done

echo "Data collected and saved to $OUTPUT_FILE"
