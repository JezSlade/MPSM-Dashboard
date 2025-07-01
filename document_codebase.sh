#!/bin/sh
# Script: document_codebase.sh
# Purpose: Document architecture and list contents of all PHP, HTML, CSS, JS, Markdown, .env, and similar files into a single text file.

# Set output file
OUTPUT_FILE="project_documentation_$(date +%Y%m%d_%H%M%S).txt"

# Set the root directory (default is current directory)
ROOT_DIR="${1:-.}"

# File types to include
FILE_TYPES="php html css js md env json xml yml yaml"

echo "Project Documentation - Generated on $(date)" > "$OUTPUT_FILE"
echo "Root directory: $ROOT_DIR" >> "$OUTPUT_FILE"
echo "------------------------------------------------------------" >> "$OUTPUT_FILE"

echo "\nARCHITECTURE OVERVIEW\n" >> "$OUTPUT_FILE"
echo "Directory Structure:" >> "$OUTPUT_FILE"
tree -a -I '.git|node_modules|vendor|.idea|.vscode' "$ROOT_DIR" >> "$OUTPUT_FILE" 2>/dev/null || find "$ROOT_DIR" >> "$OUTPUT_FILE"

echo "\n\nFILES DOCUMENTATION\n" >> "$OUTPUT_FILE"

# Loop through each file type and document its contents
for ext in $FILE_TYPES; do
    find "$ROOT_DIR" -type f -name "*.${ext}" | while read filepath; do
        echo "\n------------------------------------------------------------" >> "$OUTPUT_FILE"
        echo "File: $filepath" >> "$OUTPUT_FILE"
        echo "------------------------------------------------------------" >> "$OUTPUT_FILE"
        head -n 20 "$filepath" >> "$OUTPUT_FILE"
        echo "[...]" >> "$OUTPUT_FILE"
        echo "Full file contents below:" >> "$OUTPUT_FILE"
        echo "------------------------------------------------------------" >> "$OUTPUT_FILE"
        cat "$filepath" >> "$OUTPUT_FILE"
        echo "\n" >> "$OUTPUT_FILE"
    done
done

echo "Documentation written to: $OUTPUT_FILE"