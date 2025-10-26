#!/usr/bin/env python3
"""
Exercise the MPS query engine as if invoked via the ChatGPT Action.
Iterates through the catalogued endpoints and records pass/fail outcomes.
"""

from __future__ import annotations

import json
import time
from dataclasses import dataclass
from pathlib import Path
from typing import Any, Dict, List, Optional

import requests

CATALOG_PATH = Path("output/endpoint_test_results.json")
ENGINE_URL = "https://mpsm.resolutionsbydesign.us/mps-api/query"
REQUEST_TIMEOUT = 45
PAUSE_SECONDS = 0.25


@dataclass
class EndpointTestResult:
    action: str
    passed: bool
    status: str
    duration_ms: Optional[float]
    data_type: Optional[str]
    sample: Optional[Any]
    error: Optional[str]
    http_status: Optional[int]

    def to_dict(self) -> Dict[str, Any]:
        return {
            "action": self.action,
            "passed": self.passed,
            "status": self.status,
            "duration_ms": self.duration_ms,
            "data_type": self.data_type,
            "sample": self.sample,
            "error": self.error,
            "http_status": self.http_status,
        }


def load_catalog_actions() -> List[str]:
    catalog = json.loads(CATALOG_PATH.read_text(encoding="utf-8"))
    actions = sorted({entry["action"] for entry in catalog["results"]})
    return actions


def looks_like_real_data(payload: Any) -> bool:
    if payload is None:
        return False
    if isinstance(payload, list):
        return len(payload) > 0
    if isinstance(payload, dict):
        return len(payload) > 0
    if isinstance(payload, (int, float, bool)):
        return True
    if isinstance(payload, str):
        return payload.strip() != ""
    return False


def exercise_action(action: str) -> EndpointTestResult:
    try:
        response = requests.post(
            ENGINE_URL,
            json={"action": action, "params": {}},
            timeout=REQUEST_TIMEOUT,
        )
        http_status = response.status_code
        payload = response.json()
    except requests.RequestException as exc:
        return EndpointTestResult(
            action=action,
            passed=False,
            status="network_error",
            duration_ms=None,
            data_type=None,
            sample=None,
            error=str(exc),
            http_status=None,
        )
    except ValueError as exc:
        return EndpointTestResult(
            action=action,
            passed=False,
            status="invalid_json",
            duration_ms=None,
            data_type=None,
            sample=None,
            error=str(exc),
            http_status=response.status_code if "response" in locals() else None,
        )

    duration_ms = payload.get("duration_ms")
    success = payload.get("success")
    data = payload.get("data")
    error = payload.get("error")

    passed = bool(success) and looks_like_real_data(data)
    status = "pass" if passed else "fail"

    sample_data = None
    if isinstance(data, list) and data:
        sample_data = data[0]
    elif isinstance(data, dict):
        sample_data = {k: data[k] for k in list(data)[:5]}
    else:
        sample_data = data

    return EndpointTestResult(
        action=action,
        passed=passed,
        status=status,
        duration_ms=duration_ms,
        data_type=type(data).__name__ if data is not None else None,
        sample=sample_data,
        error=None if passed else error,
        http_status=http_status,
    )


def main() -> None:
    actions = load_catalog_actions()
    print(f"Testing {len(actions)} catalogued actions via {ENGINE_URL}")

    results: List[EndpointTestResult] = []
    for idx, action in enumerate(actions, start=1):
        print(f"[{idx:03}/{len(actions):03}] {action}")
        result = exercise_action(action)
        results.append(result)
        marker = "PASS" if result.passed else "FAIL"
        detail = f"{marker} http={result.http_status} duration={result.duration_ms}"
        if result.error:
            detail += f" error={result.error}"
        print(f"    -> {detail}")
        time.sleep(PAUSE_SECONDS)

    passes = sum(1 for r in results if r.passed)
    failures = len(results) - passes

    print("\nSummary")
    print("-------")
    print(f"Passes : {passes}")
    print(f"Fails  : {failures}")

    report_path = Path("output/catalog_test_run.json")
    report_path.write_text(
        json.dumps([r.to_dict() for r in results], indent=2),
        encoding="utf-8",
    )
    print(f"\nDetailed report written to {report_path}")


if __name__ == "__main__":
    main()
