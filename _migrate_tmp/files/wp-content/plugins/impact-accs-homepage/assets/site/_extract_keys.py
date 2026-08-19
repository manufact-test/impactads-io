import re
from pathlib import Path

KEYS = [
    "Sherwood", "latency", "Launch:", "Root cause", "Recommended", "Impact APP",
    "Impact:", "CONTACT", "REQUEST", "when this became", "Found an issue",
    "Agency", "Media Buying", "Team Supply", "Manifesto", "SCALEGRID", "textAnchor",
    "x:\"0\"", "shortTitle", "ALERTS", "CHAT", "AGENTS", "Severity", "Open",
    "Kinesis", "View in", "Start an", "Contact team", "contact team"
]

for fname in ["827ff3490ba1793e.js", "04bde6fc7adf5b08.js", "692acfebb5322696.js", "29c2c1c591d62005.js", "ba5d7afdb6dc00cc.js", "1e7f2c52e84d02fd.js", "d53e27b68750e6f9.js"]:
    p = Path(f"_next/static/chunks/{fname}")
    if not p.exists():
        continue
    t = p.read_text(encoding="utf-8")
    print(f"\n{'='*60}\n{fname}\n{'='*60}")
    for s in sorted(set(re.findall(r'"([^"\\]{4,300})"', t))):
        if any(k.lower() in s.lower() for k in KEYS):
            print(repr(s))
