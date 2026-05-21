#!/usr/bin/env python3
"""Run live smoke checks against the deployed site."""

from __future__ import annotations

import argparse
import base64
import json
import os
import sys
import urllib.error
import urllib.request


DEFAULT_CHECKS = [
    ("/cms/login.html", 200, "html"),
    ("/cms/", None, "html"),
    ("/cms/api/cache-status-report.php", 200, "text"),
    ("/cms/api/v1/health", None, "json"),
    ("/robots.txt", 200, "text"),
]


def request(base_url: str, path: str, timeout: int) -> tuple[int, bytes, str]:
    url = base_url.rstrip("/") + path
    req = urllib.request.Request(url, headers={"User-Agent": "mpsm-live-smoke/1.0"})
    user = os.environ.get("MPSM_BASIC_AUTH_USER")
    password = os.environ.get("MPSM_BASIC_AUTH_PASSWORD")
    if user and password:
        token = base64.b64encode(f"{user}:{password}".encode()).decode()
        req.add_header("Authorization", f"Basic {token}")

    try:
        with urllib.request.urlopen(req, timeout=timeout) as response:
            return response.status, response.read(1024 * 1024), response.geturl()
    except urllib.error.HTTPError as exc:
        return exc.code, exc.read(1024 * 1024), url


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--base-url", default=os.environ.get("MPSM_LIVE_BASE_URL", "https://mpsm.resolutionsbydesign.us"))
    parser.add_argument("--timeout", type=int, default=20)
    args = parser.parse_args()

    failures = 0
    for path, expected, expected_type in DEFAULT_CHECKS:
        try:
            status, body, final_url = request(args.base_url, path, args.timeout)
            ok = status < 500 and (expected is None or status == expected)
            if expected_type == "json" and status == 200:
                try:
                    json.loads(body.decode("utf-8", errors="ignore"))
                except json.JSONDecodeError:
                    ok = False
            print(f"{'PASS' if ok else 'FAIL'} {path} status={status} bytes={len(body)} url={final_url}")
            if not ok:
                failures += 1
        except Exception as exc:
            failures += 1
            print(f"FAIL {path} error={exc}")

    return 0 if failures == 0 else 1


if __name__ == "__main__":
    raise SystemExit(main())
