import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

for fname in ["04bde6fc7adf5b08.js", "29c2c1c591d62005.js", "692acfebb5322696.js", "ba5d7afdb6dc00cc.js", "9583d4a1bf83f1e7.js"]:
    p = Path(f"_next/static/chunks/{fname}")
    if not p.exists():
        continue
    t = p.read_text(encoding="utf-8")
    print(f"\n=== {fname} ===")
    for s in sorted(set(re.findall(r'"([^"\\]{4,300})"', t))):
        sl = s.lower()
        if any(k in sl for k in ["impact", "sherwood", "latency", "kinesis", "incident", "root", "recommended", "alert", "launch", "severity", "contact", "request", "agency", "media", "team supply", "facebook", "chat", "agent", "cursor", "codex", "claude", "log-in", "when this"]):
            if not s.startswith("/") and "className" not in s and "children:" not in s[:15]:
                print(repr(s))
