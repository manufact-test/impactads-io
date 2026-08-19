"""Find user-facing English strings in key JS chunks."""
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent
CHUNKS = [
    "d53e27b68750e6f9.js",
    "827ff3490ba1793e.js",
    "0476edb0af9ab771.js",
    "692acfebb5322696.js",
    "f7f1c59a71681025.js",
    "694b218a672794b8.js",
    "29df6672875b8547.js",
    "9583d4a1bf83f1e7.js",
    "ba5d7afdb6dc00cc.js",
    "ac0d4738355e77b1.js",
]
KEYS = ["observ", "Impact", "Ship", "SAVE", "Wonder", "waitlist", "engineering", "debug", "agent", "alert", "telemetry", "log ", "monitor", "Sherwood", "Brex", "YC", "corp", "Get Access", "Join"]
lines = []
for name in CHUNKS:
    p = ROOT / "_next/static/chunks" / name
    if not p.exists():
        continue
    s = p.read_text(encoding="utf-8")
    hits = set()
    for m in re.finditer(r'"((?:[^"\\]|\\.){12,220})"', s):
        t = m.group(1)
        if any(k.lower() in t.lower() for k in KEYS):
            if "className" in t or t.startswith("/") or "http" in t:
                continue
            if t.startswith("M") and "H" in t[:20]:
                continue
            hits.add(t)
    if hits:
        lines.append(f"\n=== {name} ===")
        for h in sorted(hits):
            lines.append(h)
Path("_js_strings_to_replace.txt").write_text("\n".join(lines), encoding="utf-8")
print("written", len(lines))
