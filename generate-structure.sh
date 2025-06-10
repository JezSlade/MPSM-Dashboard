#!/usr/bin/env bash
# generate-structure.sh
# ---------------------------------------------
# Scan your project tree and emit a JSON manifest
# of every file (path, size, mtime), plus Git commit
# and generation timestamp. Writes file-structure.json
# beside this script, no matter where you invoke it.
# ---------------------------------------------

set -euo pipefail

# Ensure dependencies
command -v jq >/dev/null 2>&1 || {
  echo "ERROR: jq is required. Install it (brew install jq or apt-get install jq)." >&2
  exit 1
}

# Resolve script directory (project root)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
OUTPUT="$SCRIPT_DIR/file-structure.json"
TMP="$OUTPUT.tmp"

echo "🔍 Generating file structure in: $OUTPUT"

# Get Git commit if available
if git -C "$SCRIPT_DIR" rev-parse --git-dir >/dev/null 2>&1; then
  GIT_COMMIT=$(git -C "$SCRIPT_DIR" rev-parse HEAD)
else
  GIT_COMMIT=null
fi

# Start JSON
{
  printf '{\n'
  printf '  "generated": %s,\n' "$(jq -R --arg now "$(date --iso-8601=seconds)" '$now')"
  printf '  "gitCommit": %s,\n' "$(jq -R --arg c "$GIT_COMMIT" '$c')"
  printf '  "files": [\n'

  first=true
  # Walk from SCRIPT_DIR
  while IFS= read -r -d '' file; do
    rel="${file#$SCRIPT_DIR/}"
    size=$(stat -c%s "$file")
    mtime=$(stat -c%Y "$file")
    if $first; then first=false; else printf ',\n'; fi
    printf '    { "path": %s, "size": %d, "mtime": %d }' \
      "$(jq -R --arg p "$rel" '$p')" \
      "$size" "$mtime"
  done < <(find "$SCRIPT_DIR" -type f ! -path "$SCRIPT_DIR/file-structure.json" -print0)

  printf '\n  ]\n}\n'
} > "$TMP"

# Move into place
mv "$TMP" "$OUTPUT"
echo "✅ Written $OUTPUT ($(wc -l < "$OUTPUT") lines)"
