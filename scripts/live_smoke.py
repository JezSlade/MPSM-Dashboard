#!/usr/bin/env python3
"""Run live smoke checks against the deployed site."""

from __future__ import annotations

import argparse
import base64
import http.cookiejar
import json
import os
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


def request_with_opener(
    opener: urllib.request.OpenerDirector,
    base_url: str,
    path: str,
    timeout: int,
) -> tuple[int, bytes, str]:
    url = base_url.rstrip("/") + path
    req = urllib.request.Request(url, headers={"User-Agent": "mpsm-live-smoke/1.0"})
    try:
        with opener.open(req, timeout=timeout) as response:
            return response.status, response.read(1024 * 1024), response.geturl()
    except urllib.error.HTTPError as exc:
        return exc.code, exc.read(1024 * 1024), url


def run_authenticated_checks(base_url: str, timeout: int, username: str, password: str) -> int:
    failures = 0
    jar = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))

    login_url = base_url.rstrip("/") + "/cms/api/login.php"
    login_payload = json.dumps({"username": username, "password": password}).encode("utf-8")
    login_req = urllib.request.Request(
        login_url,
        data=login_payload,
        headers={
            "Content-Type": "application/json",
            "Accept": "application/json",
            "User-Agent": "mpsm-live-smoke/1.0",
        },
        method="POST",
    )

    try:
        with opener.open(login_req, timeout=timeout) as response:
            body = response.read(1024 * 1024)
            if response.status != 200:
                print(f"FAIL /cms/api/login.php status={response.status}")
                return 1
            parsed = json.loads(body.decode("utf-8", errors="ignore"))
            if not parsed.get("success"):
                print("FAIL /cms/api/login.php success=false")
                return 1
            print("PASS /cms/api/login.php authenticated login")
    except Exception as exc:
        print(f"FAIL /cms/api/login.php error={exc}")
        return 1

    auth_checks = [
        ("/cms/index.php?forceDesktop=1", "html", ["data-tab=\"alerts\"", "id=\"alerts-tab\""]),
        ("/cms/command-center.php", "html", ["id=\"alert-center-root\"", "data-alert-center-standalone=\"1\""]),
    ]

    for path, expected_type, markers in auth_checks:
        try:
            status, body, final_url = request_with_opener(opener, base_url, path, timeout)
            ok = status == 200
            text = body.decode("utf-8", errors="ignore") if body else ""
            if expected_type == "html":
                for marker in markers:
                    if marker not in text:
                        ok = False
                        print(f"FAIL {path} missing marker={marker} status={status} bytes={len(body)} url={final_url}")
                        break
            if ok:
                print(f"PASS {path} status={status} bytes={len(body)} url={final_url}")
            else:
                failures += 1
        except Exception as exc:
            failures += 1
            print(f"FAIL {path} error={exc}")

    return failures


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--base-url", default=os.environ.get("MPSM_LIVE_BASE_URL", "https://mpsm.resolutionsbydesign.us"))
    parser.add_argument("--timeout", type=int, default=20)
    parser.add_argument("--auth-user", default=os.environ.get("MPSM_SMOKE_USER"))
    parser.add_argument("--auth-pass", default=os.environ.get("MPSM_SMOKE_PASSWORD"))
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

    if args.auth_user and args.auth_pass:
        failures += run_authenticated_checks(args.base_url, args.timeout, args.auth_user, args.auth_pass)
    else:
        print("INFO authenticated checks skipped (set --auth-user/--auth-pass or MPSM_SMOKE_USER/MPSM_SMOKE_PASSWORD)")

    return 0 if failures == 0 else 1


if __name__ == "__main__":
    raise SystemExit(main())
