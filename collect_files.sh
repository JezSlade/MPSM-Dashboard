#!/bin/bash

# Define the project directory (adjust if needed)
PROJECT_DIR="/home/resolut7/public_html/mpsm.resolutionsbydesign.us/mpsm"
OUTPUT_FILE="project_source_$(date +%Y%m%d_%H%M%S).txt"

# Create or overwrite the output file
echo "Collecting source files from $PROJECT_DIR at $(date)" > "$OUTPUT_FILE"
echo "----------------------------------------" >> "$OUTPUT_FILE"

# Find and process PHP, HTML, and CSS files, excluding .env
find "$PROJECT_DIR" -type f \( -name "*.php" -o -name "*.html" -o -name "*.css" \) ! -name ".env" | while read -r file; do
    echo "// File: $file" >> "$OUTPUT_FILE"
    echo "----------------------------------------" >> "$OUTPUT_FILE"
    cat "$file" >> "$OUTPUT_FILE"
    echo "" >> "$OUTPUT_FILE"  # Add a blank line between files
done

echo "Source files collected in $OUTPUT_FILE. Copy the contents and share with your assistant!"