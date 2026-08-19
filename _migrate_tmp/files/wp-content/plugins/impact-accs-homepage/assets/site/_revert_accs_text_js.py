"""Revert JS text changes from _apply_accs_text_safe.py (keep HTML)."""
from __future__ import annotations

import importlib.util
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parent
spec = importlib.util.spec_from_file_location("safe", ROOT / "_apply_accs_text_safe.py")
mod = importlib.util.module_from_spec(spec)
spec.loader.exec_module(mod)

CHUNKS = ROOT / "_next/static/chunks"
REVERSE = sorted(
    [(new, old) for old, new in mod.REPLACEMENTS if old != new],
    key=lambda x: len(x[0]),
    reverse=True,
)


def main() -> None:
    for name in sorted(mod.JS_ALLOW):
        path = CHUNKS / name
        if not path.exists():
            continue
        raw = path.read_text(encoding="utf-8")
        new = raw
        for a, b in REVERSE:
            new = new.replace(a, b)
        if new == raw:
            print("skip", name)
            continue
        path.write_text(new, encoding="utf-8")
        r = subprocess.run(["node", "--check", str(path)], capture_output=True, text=True)
        if r.returncode != 0:
            path.write_text(raw, encoding="utf-8")
            raise RuntimeError(f"{name}: {r.stderr}")
        print("reverted", name)


if __name__ == "__main__":
    main()
