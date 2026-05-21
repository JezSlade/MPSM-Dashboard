#!/usr/bin/env python3
"""Shared FTP deployment helpers for MPSM Dashboard."""

from __future__ import annotations

import fnmatch
import ftplib
import os
import posixpath
from dataclasses import dataclass
from pathlib import Path
from typing import Iterable, Iterator


DEFAULT_HOST = "ftp.resolutionsbydesign.us"
DEFAULT_REMOTE_ROOT = "/"


DEPLOY_EXCLUDES = [
    ".git/**",
    ".git",
    ".env",
    ".env.*",
    ".archive/**",
    ".runtime/**",
    "reference/**",
    "backups/**",
    "cms/config.php",
    "cms/config.php.backup.*",
    "cms/api/tmp-secret-*.php",
    "**/tmp-secret-*.php",
    "cms/api/cache/**",
    "cms/data/**",
    "cms/locks/**",
    "cms/logs/**",
    "mps-api/.env",
    "mps-api/.env.*",
    "mps-api/cache/storage/**",
    "mps-api/logs/**",
    "tests/reports/**",
    "logs/**",
    "node_modules/**",
    "dist/**",
    "public/**",
    "__pycache__/**",
    "**/__pycache__/**",
    "*.pyc",
    "*.log",
    "error_log",
    "**/error_log",
    "*.zip",
    "*.dll",
    "*.pdf",
    "*.docx",
    "*.xlsx",
    "*.csv",
]


REMOTE_PRESERVE = [
    ".env",
    ".env.*",
    "cms/config.php",
    "cms/config.php.backup.*",
    "cms/api/cache/**",
    "cms/data/**",
    "cms/locks/**",
    "cms/logs/**",
    "mps-api/.env",
    "mps-api/.env.*",
    "mps-api/cache/storage/**",
    "mps-api/logs/**",
    "logs/**",
    "error_log",
    "**/error_log",
]


@dataclass(frozen=True)
class FtpConfig:
    host: str
    user: str
    password: str
    remote_root: str
    timeout: int = 30


def repo_root() -> Path:
    return Path(__file__).resolve().parents[1]


def load_env_file(path: Path) -> None:
    if not path.is_file():
        return
    for raw in path.read_text(errors="ignore").splitlines():
        line = raw.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        key = key.strip()
        value = value.strip().strip('"').strip("'")
        if key and key not in os.environ:
            os.environ[key] = value


def load_ftp_config() -> FtpConfig:
    root = repo_root()
    load_env_file(root / ".env")
    load_env_file(root / ".runtime" / "ftp.env")

    host = os.environ.get("MPSM_FTP_HOST") or os.environ.get("FTP_HOST") or DEFAULT_HOST
    user = os.environ.get("MPSM_FTP_USER") or os.environ.get("FTP_USER")
    password = os.environ.get("MPSM_FTP_PASSWORD") or os.environ.get("FTP_PASSWORD")
    remote_root = os.environ.get("MPSM_FTP_ROOT") or os.environ.get("FTP_ROOT") or DEFAULT_REMOTE_ROOT

    missing = []
    if not user:
        missing.append("MPSM_FTP_USER")
    if not password:
        missing.append("MPSM_FTP_PASSWORD")
    if missing:
        names = ", ".join(missing)
        raise SystemExit(f"Missing FTP credential environment variables: {names}")

    return FtpConfig(host=host, user=user, password=password, remote_root=clean_remote(remote_root))


def clean_remote(path: str) -> str:
    cleaned = posixpath.normpath("/" + path.strip("/"))
    return "/" if cleaned == "/." else cleaned


def remote_join(root: str, rel: str) -> str:
    rel = rel.replace("\\", "/").strip("/")
    if not rel:
        return clean_remote(root)
    return clean_remote(posixpath.join(root, rel))


def rel_from_remote(root: str, path: str) -> str:
    root = clean_remote(root)
    path = clean_remote(path)
    if root == "/":
        return path.strip("/")
    if path == root:
        return ""
    return path[len(root):].strip("/")


def match_any(rel: str, patterns: Iterable[str]) -> bool:
    rel = rel.replace("\\", "/").strip("/")
    for pattern in patterns:
        pattern = pattern.strip("/")
        if fnmatch.fnmatch(rel, pattern):
            return True
        if pattern.endswith("/**"):
            base = pattern[:-3].strip("/")
            if rel == base or rel.startswith(base + "/"):
                return True
        if "/" not in pattern and fnmatch.fnmatch(Path(rel).name, pattern):
            return True
    return False


def iter_local_files(root: Path, excludes: Iterable[str] = DEPLOY_EXCLUDES) -> Iterator[Path]:
    for path in sorted(root.rglob("*")):
        if not path.is_file():
            continue
        rel = path.relative_to(root).as_posix()
        if match_any(rel, excludes):
            continue
        yield path


def connect(config: FtpConfig) -> ftplib.FTP:
    ftp = ftplib.FTP()
    ftp.connect(config.host, 21, timeout=config.timeout)
    ftp.login(config.user, config.password)
    ftp.encoding = "utf-8"
    ftp.cwd(config.remote_root)
    return ftp


def ensure_remote_dir(ftp: ftplib.FTP, remote_dir: str) -> None:
    remote_dir = clean_remote(remote_dir)
    current = ftp.pwd()
    try:
        parts = [part for part in remote_dir.strip("/").split("/") if part]
        ftp.cwd("/")
        for part in parts:
            try:
                ftp.mkd(part)
            except ftplib.error_perm:
                pass
            ftp.cwd(part)
    finally:
        ftp.cwd(current)


def remote_entries(ftp: ftplib.FTP, remote_dir: str) -> list[tuple[str, str]]:
    """Return (name, type) where type is file, dir, or unknown."""
    entries: list[tuple[str, str]] = []
    try:
        for name, facts in ftp.mlsd(remote_dir):
            if name in {".", ".."}:
                continue
            entries.append((name, facts.get("type", "unknown")))
        return entries
    except Exception:
        pass

    current = ftp.pwd()
    try:
        ftp.cwd(remote_dir)
        names = ftp.nlst()
        for name in names:
            clean = name.rstrip("/").split("/")[-1]
            if clean in {".", ".."}:
                continue
            full = remote_join(remote_dir, clean)
            try:
                ftp.cwd(full)
                ftp.cwd(remote_dir)
                kind = "dir"
            except ftplib.error_perm:
                kind = "file"
            entries.append((clean, kind))
    finally:
        ftp.cwd(current)
    return entries


def walk_remote(ftp: ftplib.FTP, remote_dir: str) -> Iterator[tuple[str, str]]:
    for name, kind in remote_entries(ftp, remote_dir):
        full = remote_join(remote_dir, name)
        if kind == "dir":
            yield full, "dir"
            yield from walk_remote(ftp, full)
        else:
            yield full, "file"
