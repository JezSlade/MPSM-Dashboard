"""
Generate a monolithic HTML catalog that shows sample data for every
discovered MPS Monitor endpoint based on the recorded API run data.

The script reads the consolidated dataset artifacts created by the
discovery tooling (`FINAL_complete_dataset.json` and
`complete_dataset_catalog.json`) and produces a single HTML document with
collapsible sections per endpoint, plus filtering/search utilities to
make the large file easier to browse.

Usage:
    python scripts/generate_endpoint_sample_catalog.py
"""

from __future__ import annotations

import json
from collections import Counter
from datetime import datetime
from html import escape as html_escape
from pathlib import Path
from typing import Any, Dict, List


PROJECT_ROOT = Path(__file__).resolve().parents[1]
DATASET_PATH = PROJECT_ROOT / "output" / "FINAL_complete_dataset.json"
CATALOG_PATH = PROJECT_ROOT / "output" / "complete_dataset_catalog.json"
REPAIRED_DATA_PATH = PROJECT_ROOT / "output" / "get_endpoint_data.repaired.json"
SWAGGER_PATH = PROJECT_ROOT / "Swagger.json"
OUTPUT_PATH = PROJECT_ROOT / "documentation" / "Endpoints" / "EndpointSampleCatalog.html"

# Limit the depth and breadth of the rendered payload so the HTML file
# remains navigable while still presenting representative samples.
MAX_DEPTH = 6
LIST_SAMPLE_LIMIT = 10


def load_json(path: Path) -> Any:
    with path.open("r", encoding="utf-8") as fh:
        return json.load(fh)


def load_swagger_descriptions() -> Dict[str, str]:
    """Load endpoint descriptions from Swagger documentation."""
    if not SWAGGER_PATH.exists():
        return {}

    swagger = load_json(SWAGGER_PATH)
    descriptions = {}

    for path, methods in swagger.get("paths", {}).items():
        action = path.lstrip("/")

        # Try to get description from GET or POST method
        for method in ["get", "post", "put", "delete"]:
            if method in methods:
                summary = methods[method].get("summary", "")
                description = methods[method].get("description", "")
                tags = methods[method].get("tags", [])

                # Create human-readable description
                desc_parts = []
                if summary:
                    desc_parts.append(summary)
                elif description:
                    desc_parts.append(description)

                if tags:
                    desc_parts.append(f"Category: {', '.join(tags)}")

                if desc_parts:
                    descriptions[action] = " | ".join(desc_parts)
                break

    return descriptions


def trim_value(value: Any, depth: int = 0) -> Any:
    """
    Recursively trim large lists/dicts to keep the rendered payloads readable.
    Adds a sentinel message when data is truncated so readers know more data
    exists in the raw capture.
    """
    if depth >= MAX_DEPTH:
        return value

    if isinstance(value, dict):
        return {key: trim_value(val, depth + 1) for key, val in value.items()}

    if isinstance(value, list):
        if not value:
            return []

        trimmed = [trim_value(item, depth + 1) for item in value[:LIST_SAMPLE_LIMIT]]
        if len(value) > LIST_SAMPLE_LIMIT:
            trimmed.append(f"... truncated {len(value) - LIST_SAMPLE_LIMIT} additional item(s) ...")
        return trimmed

    return value


def render_value_html(value: Any) -> str:
    """
    Convert a Python structure into nested HTML tables/lists for readability.
    Strings are HTML-escaped to prevent markup injection.
    """
    sampled = trim_value(value)
    return _render_value_html(sampled)


def _render_value_html(value: Any) -> str:
    if value is None:
        return "<span class=\"muted\">null</span>"

    if isinstance(value, bool):
        return f"<span class=\"bool {str(value).lower()}\">{str(value)}</span>"

    if isinstance(value, (int, float)):
        return f"<span class=\"number\">{value}</span>"

    if isinstance(value, str):
        text = html_escape(value)
        css_class = "text"
        if value.startswith("... truncated"):
            css_class = "muted truncated"
        return f"<span class=\"{css_class}\">{text}</span>"

    if isinstance(value, dict):
        rows = "".join(
            f"<tr><th>{html_escape(str(key))}</th><td>{_render_value_html(val)}</td></tr>"
            for key, val in value.items()
        )
        if not rows:
            rows = "<tr><td colspan=\"2\"><span class=\"muted\">empty object</span></td></tr>"
        return f"<table class=\"data-table\"><tbody>{rows}</tbody></table>"

    if isinstance(value, list):
        if not value:
            return "<div class=\"muted\">[]</div>"
        items = "".join(f"<li>{_render_value_html(item)}</li>" for item in value)
        return f"<ol class=\"data-list\">{items}</ol>"

    return f"<span class=\"text\">{html_escape(repr(value))}</span>"


def build_stats(results: List[Dict[str, Any]], catalog_map: Dict[str, Dict[str, Any]]) -> Dict[str, Any]:
    prefix_counter: Counter[str] = Counter()
    data_type_counter: Counter[str] = Counter()

    for entry in results:
        action = entry.get("action", "")
        prefix = action.split("/", 1)[0] if action else "Unknown"
        prefix_counter[prefix] += 1

        catalog_entry = catalog_map.get(action, {})
        data_type = catalog_entry.get("data_type") or "unknown"
        data_type_counter[data_type] += 1

    return {
        "by_prefix": prefix_counter.most_common(),
        "by_data_type": data_type_counter.most_common(),
    }


def build_endpoint_card(entry: Dict[str, Any], catalog_entry: Dict[str, Any], description: str = "") -> str:
    """Build a compact grid card for an endpoint"""
    action = entry.get("action", "Unknown/Action")
    normalized_action = html_escape(action)
    prefix = action.split("/", 1)[0] if action else "Unknown"

    success = entry.get("success", False)
    http_status = entry.get("http_status")
    duration_ms = entry.get("duration_ms")

    response = entry.get("response") or {}
    response_data = response.get("data")
    data_block = render_value_html(response_data)

    params_used = catalog_entry.get("params_used")
    params_block = render_value_html(params_used) if params_used else "<span class=\"muted\">None</span>"

    data_type = catalog_entry.get("data_type") or "unknown"
    data_count = catalog_entry.get("data_count")

    status_class = "success" if success else "failed"
    status_icon = "✓" if success else "✗"

    # Short description
    short_desc = description.split("|")[0] if description else ""
    if len(short_desc) > 60:
        short_desc = short_desc[:57] + "..."

    return f"""
    <div class="endpoint-grid-card {status_class}" data-action="{normalized_action.lower()}" data-prefix="{prefix.lower()}">
        <div class="card-compact-header">
            <div class="endpoint-status">{status_icon}</div>
            <div class="endpoint-name">{normalized_action}</div>
        </div>
        <div class="card-compact-body">
            {f'<div class="endpoint-desc">{html_escape(short_desc)}</div>' if short_desc else ''}
            <div class="endpoint-meta">
                <span class="meta-item">HTTP: {int(http_status) if http_status else 'N/A'}</span>
                <span class="meta-item">{duration_ms:.0f}ms</span>
                {f'<span class="meta-item">{data_count} items</span>' if data_count else ''}
            </div>
            <details class="endpoint-details">
                <summary>View Details</summary>
                <div class="details-modal">
                    <div class="modal-tabs">
                        <button class="modal-tab active" data-tab="response">Response Data</button>
                        <button class="modal-tab" data-tab="params">Parameters</button>
                    </div>
                    <div class="modal-tab-content active" data-content="response">
                        <div class="data-block-expanded">{data_block}</div>
                    </div>
                    <div class="modal-tab-content" data-content="params">
                        <div class="data-block-expanded">{params_block}</div>
                    </div>
                </div>
            </details>
        </div>
    </div>
    """


def build_html(results: List[Dict[str, Any]], catalog_map: Dict[str, Dict[str, Any]], generated_at: str, updated_at: str) -> str:
    successful_results = [item for item in results if item.get("success")]
    failed_results = [item for item in results if not item.get("success")]

    total = len(results)
    total_success = len(successful_results)
    total_failed = len(failed_results)
    stats = build_stats(results, catalog_map)

    generated_label = generated_at or updated_at
    if generated_label:
        try:
            generated_label = (
                datetime.fromisoformat(generated_label.replace("Z", "+00:00"))
                .astimezone()
                .strftime("%Y-%m-%d %H:%M:%S %Z")
            )
        except ValueError:
            pass

    prefix_stats_rows = "\n".join(
        f"<li><code>{html_escape(prefix)}</code>: {count}</li>"
        for prefix, count in stats["by_prefix"]
    )
    data_type_rows = "\n".join(
        f"<li><code>{html_escape(dtype)}</code>: {count}</li>"
        for dtype, count in stats["by_data_type"]
    )

    failed_note = ""
    if failed_results:
        failed_items = "".join(
            f"<li><code>{html_escape(item.get('action', 'Unknown'))}</code>"
            f"{' &mdash; ' + html_escape(str(item.get('error'))) if item.get('error') else ''}</li>"
            for item in failed_results
        )
        failed_note = f"""
        <section class="failed-note">
            <details>
                <summary>Failed Endpoints ({len(failed_results)}) are omitted from the catalog below.</summary>
                <ul>
                    {failed_items}
                </ul>
            </details>
        </section>
        """

    # Load swagger descriptions
    swagger_descriptions = load_swagger_descriptions()

    # Group endpoints by prefix
    grouped_endpoints = {}
    for entry in successful_results:
        action = entry.get("action", "")
        prefix = action.split("/", 1)[0] if action else "Unknown"
        if prefix not in grouped_endpoints:
            grouped_endpoints[prefix] = []
        grouped_endpoints[prefix].append(entry)

    # Build HTML by groups
    endpoint_html = ""
    for prefix in sorted(grouped_endpoints.keys()):
        entries = sorted(grouped_endpoints[prefix], key=lambda x: x.get("action", "").lower())
        group_count = len(entries)

        # Build cards for this group
        cards_html = []
        for entry in entries:
            action = entry.get("action")
            catalog_entry = catalog_map.get(action, {})
            description = swagger_descriptions.get(action, "")
            cards_html.append(build_endpoint_card(entry, catalog_entry, description))

        endpoint_html += f"""
        <div class="endpoint-group">
            <details class="group-details" open>
                <summary class="group-header">
                    <span class="group-name">{html_escape(prefix)}</span>
                    <span class="group-count">{group_count} endpoint{'s' if group_count != 1 else ''}</span>
                </summary>
                <div class="endpoint-grid">
                    {''.join(cards_html)}
                </div>
            </details>
        </div>
        """

    return f"""<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>MPS Monitor Endpoint Sample Catalog</title>
    <style>
        :root {{
            color-scheme: light dark;
            --bg: #f5f5f5;
            --bg-card: #ffffff;
            --border: #d0d7de;
            --text: #1f2328;
            --muted: #5c636a;
            --success: #146c43;
            --failed: #b42318;
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
        }}
        body {{
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 0 0 4rem;
        }}
        header.page-header {{
            padding: 2.5rem 1.5rem 1rem;
            background: linear-gradient(135deg, #004b91, #0a6cff);
            color: #ffffff;
        }}
        header.page-header h1 {{
            margin: 0;
            font-size: 2rem;
        }}
        header.page-header p {{
            margin: 0.35rem 0 0;
            max-width: 60rem;
        }}
        .toolbar {{
            padding: 1rem 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(4px);
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 1px solid var(--border);
        }}
        .toolbar label {{
            font-weight: 600;
        }}
        .toolbar input[type="search"] {{
            flex: 1 1 18rem;
            padding: 0.6rem 0.8rem;
            font-size: 1rem;
            border-radius: 999px;
            border: 1px solid var(--border);
        }}
        .toolbar button {{
            padding: 0.5rem 0.9rem;
            border: 1px solid var(--border);
            background: #ffffff;
            border-radius: 0.5rem;
            cursor: pointer;
        }}
        .stats {{
            padding: 1.5rem;
            display: grid;
            gap: 1.5rem;
        }}
        .stats details {{
            cursor: pointer;
        }}
        .stats details summary {{
            font-weight: 600;
            padding: 0.75rem 1rem;
            background: rgba(0, 75, 145, 0.08);
            border-radius: 0.5rem;
            list-style: none;
            user-select: none;
        }}
        .stats details summary::-webkit-details-marker {{
            display: none;
        }}
        .stats details summary::before {{
            content: '▶';
            display: inline-block;
            margin-right: 0.5rem;
            transition: transform 0.2s;
        }}
        .stats details[open] summary::before {{
            transform: rotate(90deg);
        }}
        .stats details[open] .stat-grid {{
            margin-top: 1rem;
        }}
        .stat-grid {{
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }}
        .stat {{
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 1rem 1.2rem;
        }}
        .stat strong {{
            display: block;
            font-size: 1.2rem;
            margin-bottom: 0.35rem;
        }}
        .stat ul {{
            margin: 0.5rem 0 0;
            padding-left: 1.2rem;
        }}
        .catalog {{
            padding: 0 1.5rem 3rem;
        }}
        .endpoint-group {{
            margin-bottom: 2rem;
        }}
        .group-details {{
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            overflow: hidden;
        }}
        .group-details[open] .group-header {{
            border-bottom: 1px solid var(--border);
        }}
        .group-header {{
            padding: 1rem 1.5rem;
            cursor: pointer;
            user-select: none;
            background: linear-gradient(135deg, rgba(0, 75, 145, 0.05), transparent);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
        }}
        .group-header::-webkit-details-marker {{
            display: none;
        }}
        .group-header::before {{
            content: '▶';
            margin-right: 0.5rem;
            transition: transform 0.2s;
        }}
        .group-details[open] .group-header::before {{
            transform: rotate(90deg);
        }}
        .group-name {{
            font-size: 1.2rem;
            color: var(--text);
        }}
        .group-count {{
            font-size: 0.9rem;
            color: var(--muted);
            font-weight: normal;
        }}
        .endpoint-grid {{
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1rem;
            padding: 1.5rem;
        }}
        .endpoint-grid-card {{
            background: var(--bg-card);
            border: 2px solid var(--border);
            border-radius: 0.5rem;
            overflow: hidden;
        }}
        .endpoint-grid-card:not(:has(.endpoint-details[open])):hover {{
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }}
        .endpoint-grid-card.success {{
            border-color: rgba(20, 108, 67, 0.4);
            border-left: 4px solid #146c43;
        }}
        .endpoint-grid-card.failed {{
            border-color: rgba(180, 35, 24, 0.4);
            border-left: 4px solid #b42318;
        }}
        .card-compact-header {{
            padding: 0.75rem;
            background: rgba(0, 0, 0, 0.02);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 1px solid var(--border);
        }}
        .endpoint-status {{
            font-size: 1rem;
            font-weight: bold;
        }}
        .endpoint-grid-card.success .endpoint-status {{
            color: #146c43;
        }}
        .endpoint-grid-card.failed .endpoint-status {{
            color: #b42318;
        }}
        .endpoint-name {{
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text);
            word-break: break-word;
        }}
        .card-compact-body {{
            padding: 0.75rem;
        }}
        .endpoint-desc {{
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 0.5rem;
            font-style: italic;
        }}
        .endpoint-meta {{
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: var(--muted);
            margin-bottom: 0.5rem;
        }}
        .meta-item {{
            background: rgba(0, 0, 0, 0.04);
            padding: 0.2rem 0.5rem;
            border-radius: 0.25rem;
        }}
        .endpoint-details {{
            margin-top: 0.5rem;
        }}
        .endpoint-details summary {{
            font-size: 0.85rem;
            font-weight: 600;
            color: #0a6cff;
            cursor: pointer;
            padding: 0.4rem 0.6rem;
            background: rgba(10, 108, 255, 0.08);
            border-radius: 4px;
            text-align: center;
        }}
        .endpoint-details summary:hover {{
            background: rgba(10, 108, 255, 0.15);
        }}
        .endpoint-details[open] {{
            position: fixed;
            top: 5vh;
            left: 5vw;
            right: 5vw;
            bottom: 5vh;
            z-index: 1000;
            background: var(--bg-card);
            border: 2px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            max-width: 900px;
            margin: 0 auto;
            overflow: hidden;
            transform: none;
        }}
        .endpoint-details[open] summary {{
            position: sticky;
            top: 0;
            z-index: 10;
            background: linear-gradient(135deg, #0a6cff, #004b91);
            color: white;
            margin: 0;
            padding: 1rem 1.5rem;
            border-radius: 0;
            font-size: 1rem;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }}
        .endpoint-details[open] summary::after {{
            content: '✕';
            font-size: 1.5rem;
            font-weight: normal;
        }}
        .endpoint-details[open] summary:hover {{
            background: linear-gradient(135deg, #0854cc, #003366);
        }}
        .details-modal {{
            display: flex;
            flex-direction: column;
            height: 100%;
        }}
        .modal-tabs {{
            display: flex;
            gap: 0.5rem;
            padding: 1rem 1.5rem 0;
            background: var(--bg);
            border-bottom: 2px solid var(--border);
        }}
        .modal-tab {{
            padding: 0.6rem 1.2rem;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: 600;
            color: var(--muted);
            transition: all 0.2s;
        }}
        .modal-tab:hover {{
            color: var(--text);
            background: rgba(0, 0, 0, 0.04);
        }}
        .modal-tab.active {{
            color: #0a6cff;
            border-bottom-color: #0a6cff;
        }}
        .modal-tab-content {{
            display: none;
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }}
        .modal-tab-content.active {{
            display: block;
        }}
        .data-block-expanded {{
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 1rem;
            font-size: 0.9rem;
            overflow-x: auto;
        }}
        .data-block-expanded .data-table {{
            font-size: 0.9rem;
        }}
        .data-block-expanded .data-table th,
        .data-block-expanded .data-table td {{
            padding: 0.6rem 0.8rem;
        }}
        .endpoint-details[open]::before {{
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
            backdrop-filter: blur(4px);
        }}
        .badge {{
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.85rem;
            border: 1px solid transparent;
        }}
        .badge.success {{
            background: rgba(20, 108, 67, 0.12);
            color: var(--success);
            border-color: rgba(20, 108, 67, 0.45);
        }}
        .badge.failed {{
            background: rgba(180, 35, 24, 0.12);
            color: var(--failed);
            border-color: rgba(180, 35, 24, 0.45);
        }}
        .meta-row {{
            color: var(--muted);
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }}
        details {{
            margin-top: 0.75rem;
        }}
        summary {{
            cursor: pointer;
            font-weight: 600;
        }}
        .data-block {{
            margin-top: 0.4rem;
            padding: 0.5rem 0.7rem;
            border: 1px solid var(--border);
            border-radius: 0.4rem;
            background: rgba(13, 17, 23, 0.04);
            max-height: 300px;
            overflow-y: auto;
            font-size: 0.85rem;
        }}
        .data-table {{
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }}
        .data-table th,
        .data-table td {{
            border-bottom: 1px solid var(--border);
            padding: 0.4rem 0.6rem;
            text-align: left;
            vertical-align: top;
        }}
        .data-table th {{
            width: 22%;
            font-weight: 600;
            color: var(--muted);
        }}
        .data-table tr:last-child th,
        .data-table tr:last-child td {{
            border-bottom: none;
        }}
        .data-list {{
            margin: 0.4rem 0 0.4rem 1.2rem;
            padding-left: 1rem;
        }}
        .data-list > li {{
            margin: 0.25rem 0;
        }}
        .muted {{
            color: var(--muted);
        }}
        .muted.truncated {{
            font-style: italic;
        }}
        .bool.true {{
            color: var(--success);
            font-weight: 600;
        }}
        .bool.false {{
            color: var(--failed);
            font-weight: 600;
        }}
        .number {{
            font-variant-numeric: tabular-nums;
        }}
        .text {{
            word-break: break-word;
        }}
        .error-box {{
            margin: 0.5rem 0 0.25rem;
            padding: 0.75rem 1rem;
            border-left: 4px solid var(--failed);
            background: rgba(180, 35, 24, 0.1);
            border-radius: 0.5rem;
        }}
        .failed-note {{
            background: rgba(180, 35, 24, 0.05);
            border: 1px solid rgba(180, 35, 24, 0.2);
            border-radius: 0.75rem;
            padding: 1rem 1.2rem;
        }}
        .failed-note summary {{
            color: var(--failed);
        }}
        footer.page-footer {{
            padding: 2rem 1.5rem 3rem;
            color: var(--muted);
            font-size: 0.9rem;
        }}
    </style>
</head>
<body>
    <header class="page-header">
        <h1>MPS Monitor Endpoint Sample Catalog</h1>
        <p>
            Unified reference of all discovered API endpoints with captured sample payloads.
            Arrays are trimmed to the first {LIST_SAMPLE_LIMIT} items to keep this document manageable.
        </p>
        <p>Generated: {html_escape(generated_label or "N/A")}</p>
    </header>
    <div class="toolbar">
        <label for="endpoint-search">Filter by endpoint action:</label>
        <input id="endpoint-search" type="search" placeholder="e.g. Device/List or AlertLimit" />
        <button type="button" id="expand-all">Expand All</button>
        <button type="button" id="collapse-all">Collapse All</button>
    </div>
    <section class="stats">
        <details>
            <summary>Statistics & Overview (Total: {total} | Success: {total_success} | Failed: {total_failed})</summary>
            <div class="stat-grid">
                <div class="stat">
                    <strong>Total Endpoints</strong>
                    <div>{total}</div>
                </div>
                <div class="stat">
                    <strong>Successful</strong>
                    <div>{total_success}</div>
                </div>
                <div class="stat">
                    <strong>Failed</strong>
                    <div>{total_failed}</div>
                </div>
                <div class="stat">
                    <strong>Endpoint Groups</strong>
                    <ul>
                        {prefix_stats_rows}
                    </ul>
                </div>
                <div class="stat">
                    <strong>Data Types</strong>
                    <ul>
                        {data_type_rows}
                    </ul>
                </div>
            </div>
        </details>
        {failed_note}
    </section>
    <main class="catalog" id="endpoint-catalog">
        {endpoint_html}
    </main>
    <footer class="page-footer">
        Endpoint data sourced from FINAL_complete_dataset.json and complete_dataset_catalog.json.
        Large collections are truncated for readability while keeping representative samples.
    </footer>
    <script>
        const searchInput = document.getElementById("endpoint-search");
        const cards = Array.from(document.querySelectorAll(".endpoint-card"));

        function applyFilter() {{
            const term = searchInput.value.trim().toLowerCase();
            cards.forEach(card => {{
                if (!term) {{
                    card.style.display = "";
                    return;
                }}
                const action = card.dataset.action;
                const prefix = card.dataset.prefix;
                card.style.display = (action.includes(term) || prefix.includes(term)) ? "" : "none";
            }});
        }}

        searchInput.addEventListener("input", applyFilter);

        const expandAllBtn = document.getElementById("expand-all");
        const collapseAllBtn = document.getElementById("collapse-all");

        expandAllBtn.addEventListener("click", () => {{
            document.querySelectorAll(".endpoint-card details").forEach(detail => detail.open = true);
        }});

        collapseAllBtn.addEventListener("click", () => {{
            document.querySelectorAll(".endpoint-card details").forEach(detail => detail.open = false);
        }});

        // Modal tabs functionality
        document.addEventListener('click', (e) => {{
            if (e.target.classList.contains('modal-tab')) {{
                const tabName = e.target.dataset.tab;
                const modalTabs = e.target.closest('.details-modal');

                // Update active tab
                modalTabs.querySelectorAll('.modal-tab').forEach(tab => {{
                    tab.classList.toggle('active', tab === e.target);
                }});

                // Update active content
                modalTabs.querySelectorAll('.modal-tab-content').forEach(content => {{
                    content.classList.toggle('active', content.dataset.content === tabName);
                }});
            }}
        }});

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {{
            if (e.key === 'Escape') {{
                document.querySelectorAll('.endpoint-details[open]').forEach(detail => {{
                    detail.open = false;
                }});
            }}
        }});

        // Prevent body scroll when modal is open
        document.addEventListener('toggle', (e) => {{
            if (e.target.classList.contains('endpoint-details')) {{
                if (e.target.open) {{
                    document.body.style.overflow = 'hidden';
                }} else {{
                    document.body.style.overflow = '';
                }}
            }}
        }}, true);
    </script>
</body>
</html>
"""


def main() -> None:
    dataset = load_json(DATASET_PATH)
    catalog = load_json(CATALOG_PATH)

    results = dataset.get("results", [])

    if REPAIRED_DATA_PATH.exists():
        repaired_results = load_json(REPAIRED_DATA_PATH).get("results", [])
        merged: Dict[str, Dict[str, Any]] = {}

        for entry in results:
            action = entry.get("action")
            if not action:
                continue
            merged.setdefault(action, entry)
            if entry.get("success"):
                merged[action] = entry

        for entry in repaired_results:
            action = entry.get("action")
            if not action:
                continue
            existing = merged.get(action)
            if existing is None or (not existing.get("success") and entry.get("success")):
                merged[action] = entry
        results = list(merged.values())

    success_count = sum(1 for item in results if item.get("success"))
    catalog_map = {entry["action"]: entry for entry in catalog.get("endpoints", [])}

    html_content = build_html(
        results=results,
        catalog_map=catalog_map,
        generated_at=dataset.get("generated_at", ""),
        updated_at=dataset.get("updated_at", ""),
    )

    OUTPUT_PATH.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_PATH.write_text(html_content, encoding="utf-8")

    print(f"Generated catalog for {len(results)} endpoints ({success_count} successes) at {OUTPUT_PATH}")


if __name__ == "__main__":
    main()
