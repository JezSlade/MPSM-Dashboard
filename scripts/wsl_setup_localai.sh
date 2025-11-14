#!/usr/bin/env bash
# Run inside WSL Ubuntu (as a normal user). This script installs tools and attempts to install LocalAI binary.
# Review before running. It uses sudo for system installs.

set -euo pipefail

echo "Updating apt and installing dependencies..."
sudo apt update -y
sudo apt install -y git build-essential wget curl python3 python3-pip unzip ca-certificates

echo "Creating model folder: ~/.localai/models"
mkdir -p "$HOME/.localai/models"

# Try to download LocalAI binary for Linux x86_64
LOCALAI_BIN_URL="https://github.com/go-skynet/LocalAI/releases/latest/download/localai-linux-amd64"
TMP_BIN="/tmp/localai"

echo "Downloading LocalAI binary to $TMP_BIN (will be moved to /usr/local/bin/localai)..."
curl -L "$LOCALAI_BIN_URL" -o "$TMP_BIN" || { echo "Failed to download LocalAI binary. You can install via Docker instead."; exit 1; }
chmod +x "$TMP_BIN"

echo "Moving LocalAI binary to /usr/local/bin (requires sudo)"
sudo mv "$TMP_BIN" /usr/local/bin/localai

echo "LocalAI binary installed to /usr/local/bin/localai"
localai_version=""
if command -v localai >/dev/null 2>&1; then
  localai_version=$(localai --version 2>/dev/null || true)
fi

if [ -n "$localai_version" ]; then
  echo "LocalAI appears installed: $localai_version"
else
  echo "LocalAI installed but 'localai --version' failed; verify manually with 'localai --help'"
fi

cat <<'EOF'
Next steps:
1) Download a ggml-quantized model file and place it in ~/.localai/models
   - Use Hugging Face CLI (pip install huggingface_hub) and login: `huggingface-cli login`.
   - Follow the model's page instructions to download the ggml (.bin) file.
   - Rename the file to a short name like 'mistral7b-q4.bin' and place it into ~/.localai/models
2) Start the server with: scripts/wsl_run_localai.sh
3) Test with scripts/test_localai.sh (provided)
EOF

exit 0
