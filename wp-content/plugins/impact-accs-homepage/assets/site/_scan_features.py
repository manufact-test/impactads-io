import re
from pathlib import Path

for fname in ["692acfebb5322696.js", "29c2c1c591d62005.js", "0476edb0af9ab771.js", "04bde6fc7adf5b08.js"]:
    p = Path(f"_next/static/chunks/{fname}")
    if not p.exists():
        continue
    t = p.read_text(encoding="utf-8")
    print(f"\n=== {fname} ===")
    for s in sorted(set(re.findall(r'"([^"\\]{15,120})"', t))):
        if any(k in s.lower() for k in ["agency", "media", "team", "supply", "sherwood", "latency", "launch", "root", "impact", "alert", "chat", "agent", "facebook", "google", "tiktok", "sales", "replacement", "plain language"]):
            print(" ", repr(s))
