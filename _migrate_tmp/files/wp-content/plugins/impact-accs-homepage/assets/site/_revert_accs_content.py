"""Revert _apply_accs_content.py changes (swap new -> old)."""
from __future__ import annotations

import re
from pathlib import Path

# import replacement lists from apply script
import importlib.util

ROOT = Path(__file__).resolve().parent
spec = importlib.util.spec_from_file_location("apply", ROOT / "_apply_accs_content.py")
mod = importlib.util.module_from_spec(spec)
spec.loader.exec_module(mod)

REVERSE: list[tuple[str, str]] = []
for old, new in mod.REPLACEMENTS + mod.EXTRA_REPLACEMENTS:
    if old != new:
        REVERSE.append((new, old))
# longest first
REVERSE.sort(key=lambda x: len(x[0]), reverse=True)


def revert_text(text: str) -> str:
    for new, old in REVERSE:
        text = text.replace(new, old)
    # undo cleanup_remaining side effects on html
    text = re.sub(r"\baccess infrastructure\b", "observability", text, flags=re.IGNORECASE)
    text = text.replace("service data", "telemetry")
    return text


def main() -> None:
    changed = []
    for pattern in ("**/*.html", "**/*.js"):
        for path in ROOT.glob(pattern):
            if path.name.startswith("_"):
                continue
            try:
                original = path.read_text(encoding="utf-8")
            except Exception:
                continue
            updated = revert_text(original)
            if updated != original:
                path.write_text(updated, encoding="utf-8")
                changed.append(str(path.relative_to(ROOT)))
    print(f"reverted {len(changed)} files")
    for p in sorted(changed)[:40]:
        print(" ", p)
    if len(changed) > 40:
        print(f"  ... +{len(changed)-40} more")


if __name__ == "__main__":
    main()
