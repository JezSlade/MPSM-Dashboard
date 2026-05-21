#!/usr/bin/env python3
"""Portable local verification for MPSM Dashboard."""

from __future__ import annotations

import argparse
import json
import os
import re
import shutil
import subprocess
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
PHP_EXT = ROOT / ".runtime/php-ext/extracted/usr/lib/php/20230831"


PHP_EXCLUDES = [
    ".git",
    ".archive",
    ".runtime",
    "reference",
    "backups",
    "cms/api/cache",
    "cms/config.php.backup.",
    "mps-api/cache/storage",
    "scripts/__pycache__",
    "scripts/utils/__pycache__",
]


PURE_PHP_TESTS = [
    "tests/php/test-pattern-matching.php",
    "tests/php/test-pattern-fix.php",
    "tests/php/test-hp-decode.php",
    "tests/php/test-hp-decode-v2.php",
]


KNOWN_SECRET_PATTERNS = [
    r"Deploy123",
    r"mpsm%40mpsm\.resolutionsbydesign\.us:",
    r"mpsm@mpsm\.resolutionsbydesign\.us",
    r"G0bYZyS9",
    r"wFFXo9",
    r"connect\.RBD",
    r"!C@S@",
    r"9AT9j4",
    r"9gTbAK",
    r"d@\$hpa",
    r"4Zx7m9kP2qL5wN8tY1cV3bR6dF",
    r"T3!-@D47XN=b",
]


def run(cmd: list[str], *, check: bool = True) -> subprocess.CompletedProcess[str]:
    print("+ " + " ".join(cmd))
    result = subprocess.run(cmd, cwd=ROOT, text=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
    if result.stdout:
        print(result.stdout.rstrip())
    if check and result.returncode != 0:
        raise SystemExit(result.returncode)
    return result


def php_cmd() -> list[str]:
    configured = os.environ.get("MPSM_PHP")
    if configured:
        cmd = configured.split()
    elif shutil.which("php"):
        cmd = ["php"]
    elif shutil.which("flatpak-spawn"):
        cmd = ["flatpak-spawn", "--host", "php"]
    else:
        raise SystemExit("php not found. Install PHP CLI or set MPSM_PHP.")

    if PHP_EXT.exists():
        for ext in ["mysqlnd.so", "mysqli.so", "pdo_mysql.so"]:
            path = PHP_EXT / ext
            if path.exists():
                cmd.extend(["-d", f"extension={path}"])
    return cmd


def iter_files(suffix: str) -> list[Path]:
    files = []
    for path in ROOT.rglob(f"*{suffix}"):
        rel = path.relative_to(ROOT).as_posix()
        if any(rel == item or rel.startswith(item + "/") for item in PHP_EXCLUDES):
            continue
        if rel.startswith("cms/config.php.backup."):
            continue
        if path.is_file():
            files.append(path)
    return sorted(files)


def php_lint() -> None:
    php = php_cmd()
    for path in iter_files(".php"):
        run(php + ["-l", str(path.relative_to(ROOT))])


def pure_php_tests() -> None:
    php = php_cmd()
    for test in PURE_PHP_TESTS:
        run(php + [test])


def python_compile() -> None:
    run([sys.executable, "-m", "compileall", "-q", "scripts"])


def shell_syntax() -> None:
    bash = shutil.which("bash")
    if not bash:
        print("SKIP shell syntax: bash not found")
        return
    scripts = [str(p.relative_to(ROOT)) for p in sorted((ROOT / "scripts/shell").glob("*.sh"))]
    scripts.extend(str(p.relative_to(ROOT)) for p in sorted((ROOT / "tests/shell").glob("*.sh")))
    if scripts:
        run([bash, "-n", *scripts])


def swagger_json() -> None:
    with (ROOT / "Swagger.json").open("r", encoding="utf-8") as handle:
        json.load(handle)
    print("Swagger.json ok")


def doc_links() -> None:
    docs = [
        ROOT / "README.md",
        ROOT / "agents.md",
        ROOT / "cms/README.md",
        ROOT / "context/README.md",
        ROOT / "docs/INDEX.md",
        ROOT / "docs/REPOSITORY_AUDIT.md",
        ROOT / "docs/CONSTITUTION.md",
    ]
    link_re = re.compile(r"\[[^\]]+\]\(([^)]+)\)")
    missing = []
    for doc in docs:
        text = doc.read_text(errors="ignore")
        for match in link_re.finditer(text):
            href = match.group(1).split("#", 1)[0]
            if not href or re.match(r"^[a-z][a-z0-9+.-]*:", href):
                continue
            if not (doc.parent / href).resolve().exists():
                line = text[: match.start()].count("\n") + 1
                missing.append(f"{doc.relative_to(ROOT)}:{line}: {href}")
    if missing:
        print("\n".join(missing))
        raise SystemExit(1)
    print("active doc links ok")


def secret_scan() -> None:
    combined = re.compile("|".join(KNOWN_SECRET_PATTERNS))
    ignored_roots = {
        ".git",
        ".archive",
        ".runtime",
        "reference",
        "backups",
        "mps-api/cache/storage",
        "scripts/__pycache__",
        "scripts/utils/__pycache__",
    }
    ignored_files = {".env", "cms/config.php", "mps-api/.env", "scripts/run_checks.py"}
    hits = []
    for path in ROOT.rglob("*"):
        rel = path.relative_to(ROOT).as_posix()
        if not path.is_file():
            continue
        if rel in ignored_files or any(rel == item or rel.startswith(item + "/") for item in ignored_roots):
            continue
        if rel.startswith("cms/config.php.backup."):
            continue
        if path.name.startswith("tmp-secret-"):
            hits.append(rel)
            continue
        try:
            text = path.read_text(errors="ignore")
        except UnicodeDecodeError:
            continue
        if combined.search(text):
            hits.append(rel)
    if hits:
        print("Known secret strings found:")
        print("\n".join(hits))
        raise SystemExit(1)
    print("known secret scan ok")


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--skip-php-lint", action="store_true")
    parser.add_argument("--skip-pure-php-tests", action="store_true")
    args = parser.parse_args()

    python_compile()
    shell_syntax()
    swagger_json()
    doc_links()
    secret_scan()
    if not args.skip_php_lint:
        php_lint()
    if not args.skip_pure_php_tests:
        pure_php_tests()
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
