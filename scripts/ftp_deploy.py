#!/usr/bin/env python3
"""Deploy the repository to the live FTP site."""

from __future__ import annotations

import argparse
import ftplib
from pathlib import Path

from ftp_common import (
    DEPLOY_EXCLUDES,
    REMOTE_PRESERVE,
    clean_remote,
    connect,
    ensure_remote_dir,
    iter_local_files,
    load_ftp_config,
    match_any,
    rel_from_remote,
    remote_join,
    repo_root,
    walk_remote,
)


def upload_file(ftp: ftplib.FTP, local_path: Path, remote_path: str) -> None:
    ensure_remote_dir(ftp, str(Path(remote_path).parent).replace("\\", "/"))
    with local_path.open("rb") as handle:
        ftp.storbinary(f"STOR {remote_path}", handle)


def delete_remote_stale(ftp: ftplib.FTP, remote_root: str, keep: set[str], dry_run: bool) -> tuple[int, int]:
    deleted_files = 0
    deleted_dirs = 0
    dirs: list[str] = []
    for remote_path, kind in walk_remote(ftp, remote_root):
        rel = rel_from_remote(remote_root, remote_path)
        if not rel or match_any(rel, REMOTE_PRESERVE):
            continue
        if kind == "dir":
            dirs.append(remote_path)
            continue
        if rel not in keep:
            print(f"DELETE file {rel}")
            if not dry_run:
                try:
                    ftp.delete(remote_path)
                except ftplib.error_perm as exc:
                    print(f"WARN could not delete {rel}: {exc}")
                    continue
            deleted_files += 1

    for remote_path in sorted(dirs, key=lambda p: p.count("/"), reverse=True):
        rel = rel_from_remote(remote_root, remote_path)
        if not rel or match_any(rel, REMOTE_PRESERVE):
            continue
        if any(item == rel or item.startswith(rel + "/") for item in keep):
            continue
        print(f"DELETE dir  {rel}")
        if not dry_run:
            try:
                ftp.rmd(remote_path)
            except ftplib.error_perm:
                continue
        deleted_dirs += 1

    return deleted_files, deleted_dirs


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--remote-root", help="Override FTP remote root for this run.")
    parser.add_argument("--delete", action="store_true", help="Delete remote files not present locally, except preserved runtime/config paths.")
    parser.add_argument("--dry-run", action="store_true", help="Plan the deployment without uploading or deleting.")
    args = parser.parse_args()

    config = load_ftp_config()
    if args.remote_root:
        config = config.__class__(config.host, config.user, config.password, clean_remote(args.remote_root), config.timeout)

    root = repo_root()
    local_files = list(iter_local_files(root, DEPLOY_EXCLUDES))
    keep = {path.relative_to(root).as_posix() for path in local_files}

    uploaded = 0
    errors = 0
    with connect(config) as ftp:
        print(f"Connected to {config.host}; deploying {len(local_files)} files to {config.remote_root}")
        if args.delete:
            deleted_files, deleted_dirs = delete_remote_stale(ftp, config.remote_root, keep, args.dry_run)
            print(f"Remote cleanup: files={deleted_files}, dirs={deleted_dirs}, dry_run={args.dry_run}")

        for path in local_files:
            rel = path.relative_to(root).as_posix()
            remote_path = remote_join(config.remote_root, rel)
            print(f"{'PLAN' if args.dry_run else 'PUT '} {rel}")
            if args.dry_run:
                continue
            try:
                upload_file(ftp, path, remote_path)
                uploaded += 1
            except Exception as exc:
                errors += 1
                print(f"ERROR upload {rel}: {exc}")

    print(f"Deploy complete: uploaded={uploaded}, errors={errors}, dry_run={args.dry_run}")
    return 0 if errors == 0 else 1


if __name__ == "__main__":
    raise SystemExit(main())
