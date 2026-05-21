#!/usr/bin/env python3
"""Back up the live FTP site to a local folder."""

from __future__ import annotations

import argparse
import ftplib
from datetime import datetime
from pathlib import Path

from ftp_common import connect, load_ftp_config, rel_from_remote, repo_root, walk_remote


def download_file(ftp: ftplib.FTP, remote_path: str, local_path: Path) -> None:
    local_path.parent.mkdir(parents=True, exist_ok=True)
    with local_path.open("wb") as handle:
        ftp.retrbinary(f"RETR {remote_path}", handle.write)


def is_transient_missing(exc: Exception) -> bool:
    message = str(exc)
    return "550" in message and "No such file" in message


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--output", help="Backup output folder. Defaults to backups/live-site-<timestamp>.")
    parser.add_argument("--remote-root", help="Override FTP remote root for this run.")
    args = parser.parse_args()

    config = load_ftp_config()
    if args.remote_root:
        config = config.__class__(config.host, config.user, config.password, args.remote_root, config.timeout)

    timestamp = datetime.now().strftime("%Y%m%d-%H%M%S")
    out_dir = Path(args.output) if args.output else repo_root() / "backups" / f"live-site-{timestamp}"
    out_dir.mkdir(parents=True, exist_ok=True)

    files = 0
    errors = 0
    with connect(config) as ftp:
        print(f"Connected to {config.host}; backing up {config.remote_root} -> {out_dir}")
        for remote_path, kind in walk_remote(ftp, config.remote_root):
            if kind != "file":
                continue
            rel = rel_from_remote(config.remote_root, remote_path)
            try:
                download_file(ftp, remote_path, out_dir / rel)
                files += 1
                if files % 100 == 0:
                    print(f"Downloaded {files} files...")
            except Exception as exc:
                if is_transient_missing(exc):
                    print(f"SKIP vanished during backup {remote_path}: {exc}")
                    continue
                errors += 1
                print(f"ERROR backup {remote_path}: {exc}")

    print(f"Backup complete: {files} files, {errors} errors, output={out_dir}")
    return 0 if errors == 0 else 1


if __name__ == "__main__":
    raise SystemExit(main())
