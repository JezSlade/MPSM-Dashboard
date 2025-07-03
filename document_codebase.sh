#!/bin/bash

# Codebase compiler script
# Compiles all code files in the current directory and subdirectories into a single text file

# Output file name
OUTPUT_FILE="codebase_compilation.txt"

# Common code file extensions (modify as needed)
EXTENSIONS=(
    "*.py"     # Python
    "*.js"     # JavaScript
    "*.ts"     # TypeScript
    "*.jsx"    # React JSX
    "*.tsx"    # React TSX
    "*.java"   # Java
    "*.c"      # C
    "*.cpp"    # C++
    "*.cc"     # C++
    "*.cxx"    # C++
    "*.h"      # Header files
    "*.hpp"    # C++ headers
    "*.cs"     # C#
    "*.php"    # PHP
    "*.rb"     # Ruby
    "*.go"     # Go
    "*.rs"     # Rust
    "*.swift"  # Swift
    "*.kt"     # Kotlin
    "*.scala"  # Scala
    "*.sh"     # Shell scripts
    "*.bash"   # Bash scripts
    "*.zsh"    # Zsh scripts
    "*.fish"   # Fish scripts
    "*.ps1"    # PowerShell
    "*.sql"    # SQL
    "*.html"   # HTML
    "*.css"    # CSS
    "*.scss"   # SCSS
    "*.sass"   # Sass
    "*.less"   # Less
    "*.xml"    # XML
    "*.json"   # JSON
    "*.yaml"   # YAML
    "*.yml"    # YAML
    "*.toml"   # TOML
    "*.ini"    # INI
    "*.cfg"    # Config files
    "*.conf"   # Config files
    "*.md"     # Markdown
    "*.txt"    # Text files
    "*.dockerfile" # Dockerfile
    "Dockerfile"   # Dockerfile (no extension)
    "*.vim"    # Vim scripts
    "*.lua"    # Lua
    "*.r"      # R
    "*.R"      # R
    "*.m"      # MATLAB/Objective-C
    "*.pl"     # Perl
    "*.pm"     # Perl modules
    "*.tcl"    # Tcl
    "*.awk"    # AWK
    "*.sed"    # SED
    "*.makefile" # Makefile
    "Makefile"   # Makefile (no extension)
    "*.cmake"  # CMake
    "*.gradle" # Gradle
    "*.sbt"    # SBT
)

# Directories to exclude (modify as needed)
EXCLUDE_DIRS=(
    ".git"
    ".svn"
    "node_modules"
    ".venv"
    "venv"
    "__pycache__"
    ".pytest_cache"
    "target"
    "build"
    "dist"
    ".next"
    ".nuxt"
    "vendor"
    ".idea"
    ".vscode"
    ".DS_Store"
    "*.egg-info"
    ".mypy_cache"
    ".tox"
    "coverage"
    ".coverage"
    ".nyc_output"
    "logs"
    "*.log"
    "tmp"
    "temp"
)

# Function to check if a directory should be excluded
should_exclude_dir() {
    local dir="$1"
    local basename=$(basename "$dir")
    
    for exclude in "${EXCLUDE_DIRS[@]}"; do
        if [[ "$basename" == "$exclude" ]] || [[ "$basename" == ${exclude#*.} ]]; then
            return 0
        fi
    done
    return 1
}

# Function to build find command with exclusions
build_find_command() {
    local cmd="find ."
    
    # Add directory exclusions
    for exclude in "${EXCLUDE_DIRS[@]}"; do
        cmd="$cmd -name '$exclude' -prune -o"
    done
    
    # Add file type inclusions
    cmd="$cmd \\( "
    for i in "${!EXTENSIONS[@]}"; do
        if [ $i -eq 0 ]; then
            cmd="$cmd -name '${EXTENSIONS[$i]}'"
        else
            cmd="$cmd -o -name '${EXTENSIONS[$i]}'"
        fi
    done
    cmd="$cmd \\) -print"
    
    echo "$cmd"
}

# Clear the output file
> "$OUTPUT_FILE"

echo "Compiling codebase into $OUTPUT_FILE..."
echo "========================================"

# Get the find command
FIND_CMD=$(build_find_command)

# Write header to output file
cat << EOF >> "$OUTPUT_FILE"
================================================================================
CODEBASE COMPILATION
================================================================================
Generated on: $(date)
Directory: $(pwd)
================================================================================

EOF

# Counter for processed files
file_count=0

# Execute the find command and process each file
eval "$FIND_CMD" | sort | while read -r file; do
    if [[ -f "$file" ]] && [[ -r "$file" ]]; then
        # Get relative path
        rel_path="${file#./}"
        
        echo "Processing: $rel_path"
        
        # Write file separator and header
        cat << EOF >> "$OUTPUT_FILE"

================================================================================
FILE: $rel_path
================================================================================

EOF
        
        # Check if file is binary
        if file "$file" | grep -q "text"; then
            # Add file content
            cat "$file" >> "$OUTPUT_FILE"
        else
            echo "[BINARY FILE - Content not included]" >> "$OUTPUT_FILE"
        fi
        
        # Add some spacing
        echo -e "\n" >> "$OUTPUT_FILE"
        
        ((file_count++))
    fi
done

# Write footer
cat << EOF >> "$OUTPUT_FILE"

================================================================================
COMPILATION COMPLETE
================================================================================
Total files processed: $file_count
Generated on: $(date)
================================================================================
EOF

echo "========================================"
echo "Compilation complete!"
echo "Output file: $OUTPUT_FILE"
echo "Files processed: $file_count"
echo "========================================"