#!/usr/bin/env python3
"""Collect GET endpoint datasets and store quick lookup reference."""
import json
import time
from datetime import datetime
from pathlib import Path

import requests

API_URL = "https://mpsm.resolutionsbydesign.us/mps-api/query"
BASE_DIR = Path(__file__).resolve().parent.parent
ACTIONS_FILE = BASE_DIR / "output" / "working_actions_list.txt"
OUTPUT_FILE = BASE_DIR / "output" / "get_endpoint_data.json"

ACTIONS = [line.strip() for line in ACTIONS_FILE.read_text().splitlines() if line.strip()]

results = []

print(f"Collecting datasets for {len(ACTIONS)} GET actions...")
for idx, action in enumerate(ACTIONS, 1):
    payload = {"action": action, "params": {}}
    print(f"[{idx}/{len(ACTIONS)}] {action}")
    start = time.time()
    try:
        resp = requests.post(API_URL, json=payload, timeout=30)
        duration_ms = round((time.time() - start) * 1000, 2)
        content_type = resp.headers.get("content-type", "")
        if content_type.startswith("application/json"):
            body = resp.json()
        else:
            body = resp.text
        results.append({
            "action": action,
            "timestamp": datetime.utcnow().isoformat() + "Z",
            "http_status": resp.status_code,
            "success": bool(body.get("success")) if isinstance(body, dict) else False,
            "error": body.get("error") if isinstance(body, dict) else None,
            "request_id": body.get("request_id") if isinstance(body, dict) else None,
            "duration_ms": duration_ms,
            "response": body,
        })
    except Exception as exc:
        results.append({
            "action": action,
            "timestamp": datetime.utcnow().isoformat() + "Z",
            "http_status": None,
            "success": False,
            "error": f"Exception: {exc}",
            "request_id": None,
            "duration_ms": round((time.time() - start) * 1000, 2),
            "response": None,
        })
    time.sleep(0.4)

with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
    json.dump({
        "generated_at": datetime.utcnow().isoformat() + "Z",
        "endpoint_count": len(results),
        "results": results,
    }, f, indent=2, ensure_ascii=False)

print(f"Dataset written to {OUTPUT_FILE}")
