Local AI (LocalAI) setup — CPU-only (WSL) — Quick Start

Goal
- Run an OpenAI-compatible local LLM server on your machine (WSL Ubuntu). This lets VS Code and other tools call the server via a local URL (no remote API limits).

Summary of your system (relevant parts)
- CPU: AMD Ryzen 5 5625U (6 cores / 12 threads)
- RAM: 8 GB physical
- GPU: AMD integrated (no NVIDIA CUDA). We'll use CPU inference (ggml quantized models). Expect slower inference than GPUs; smaller models are recommended for responsive code completion.

Recommendation (choice rationale)
- Server: LocalAI (small, actively maintained, OpenAI-compatible endpoints). Works with ggml quantized models and runs on CPU inside WSL.
- Model options (pick one based on responsiveness vs capability):
  1) Small / fastest (best for 8GB RAM): gpt4all-j or a compact 3B ggml model (gpt4all variants). Good for short code completions, fast.
  2) Balanced (7B quantized q4_*): "Mistral 7B" or "Vicuna 7B" ggml-q4_0 files — more capable, but on 8GB RAM this may need swap and will be slower.
  3) If you upgrade RAM or use a remote machine later, you can use 13B or 70B quantized models.

Important: Model licensing and download
- Most high-quality ggml model files live on Hugging Face or the model's release page and require acceptance of terms or an account.
- I cannot download or ship the model files for you. The steps below show exactly how to download (Hugging Face CLI or direct link) and where to place the file.

What I'll provide here
- A WSL shell script that installs required packages and the LocalAI binary.
- A run script that starts LocalAI pointing at a models folder.
- VS Code guidance to point a Code/Chat extension at the local server.

High-level steps you'll run (copy/paste into WSL/Ubuntu):
1) Install OS deps and LocalAI binary (script provided: scripts/wsl_setup_localai.sh)
2) Create models directory: ~/.localai/models
3) Download a ggml model into ~/.localai/models (instructions below)
4) Start LocalAI (script: scripts/wsl_run_localai.sh) — listens on port 8080 by default
5) Configure VS Code extension to use http://localhost:8080 (OpenAI-compatible)

Model download examples (pick one)
- Example: Mistral 7B ggml q4_0 (if available on Hugging Face):
  - Install Hugging Face CLI in WSL: pip install huggingface_hub
  - On your browser, accept the model's license on Hugging Face and create an access token.
  - In WSL: run: huggingface-cli login
  - Use huggingface-hub or wget to download the ggml bin (follow the model's instructions). Place the bin in ~/.localai/models and rename it to a simple name like "mistral7b-q4.bin".

Notes on model sizing vs memory (rough guide)
- 3B (quantized q4_0): comfortably fits in 8GB with swapping; fastest.
- 7B (q4_0): borderline on 8 GB; expect high memory/swap usage and slower inference.
- If you get OOM, switch to a smaller model.

VS Code integration
- Extensions that can talk to a local OpenAI-compatible server:
  - "Local AI" extension (if available) — configure server URL.
  - Or any extension that supports custom OpenAI base URL: set openai.apiBaseUrl to your local server (e.g., http://localhost:8080/v1).

Example VS Code settings you can add to workspace settings (replace if your extension differs):
{
  "openai.apiBaseUrl": "http://localhost:8080/v1",
  "openai.apiKey": "",
}

Notes and troubleshooting
- If LocalAI won't start or the model isn't loaded: check that the model filename is supported by LocalAI and that the model lives in the directory you passed.
- For speed: prefer q4_* quantized files; q4_0 is fast and smaller.
- For stability on low RAM: use smaller models (3B) for day-to-day code completion.

What's next (I can do for you in this repo)
- Provide the WSL install and run scripts (done below).
- Optionally add a small test script that sends a sample prompt to the local server (curl) so you can verify it's working.

Security
- Keep your Hugging Face token private.
- Local server runs on localhost by default. If you bind to 0.0.0.0 to access across devices, secure it (firewall, auth).

References
- LocalAI: https://github.com/go-skynet/LocalAI
- HuggingFace: https://huggingface.co

CHANGELOG
2025-11-13 Jez
- Created Local AI setup instructions and scripts for CPU-only WSL usage.
