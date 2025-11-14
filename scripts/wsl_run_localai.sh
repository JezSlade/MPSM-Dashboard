#!/usr/bin/env bash
# Start LocalAI server (tries docker first, falls back to the binary if present).
# Edit MODEL_FILENAME to match the file you downloaded into ~/.localai/models

set -euo pipefail

MODEL_FILENAME="" # e.g. mistral7b-q4.bin
PORT=8080
MODELS_DIR="$HOME/.localai/models"

if [ ! -d "$MODELS_DIR" ]; then
  echo "Models dir not found: $MODELS_DIR"
  echo "Create it and drop your ggml model file there (see LOCAL_AI_SETUP.md)"
  exit 1
fi

# If docker is available, run LocalAI container mounting the models dir
if command -v docker >/dev/null 2>&1; then
  echo "Starting LocalAI via Docker on port $PORT (press Ctrl+C to stop)"
  echo "Mapped models dir: $MODELS_DIR"
  # this container image accepts environment or startup flags to point to /models
  docker run --rm -it -p ${PORT}:8080 -v "$MODELS_DIR":/models ghcr.io/go-skynet/localai:latest --models-dir /models
  exit 0
fi

# Otherwise try the local binary
if command -v localai >/dev/null 2>&1; then
  echo "Starting LocalAI binary on port $PORT (press Ctrl+C to stop)"
  # The CLI flags below are a common pattern; if they fail, run 'localai --help' and adapt.
  localai --listen 0.0.0.0:8080 --models-dir "$MODELS_DIR"
  exit 0
fi

echo "Neither docker nor localai binary found. Run scripts/wsl_setup_localai.sh to install the binary or install Docker." 
exit 2
