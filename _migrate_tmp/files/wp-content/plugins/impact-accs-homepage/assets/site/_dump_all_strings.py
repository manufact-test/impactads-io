import re
from pathlib import Path

for fname in ["692acfebb5322696.js", "29c2c1c591d62005.js", "04bde6fc7adf5b08.js", "ac0d4738355e77b1.js", "d53e27b68750e6f9.js", "827ff3490ba1793e.js", "1e7f2c52e84d02fd.js"]:
    p = Path(f"_next/static/chunks/{fname}")
    if not p.exists():
        continue
    t = p.read_text(encoding="utf-8")
    print(f"\n=== {fname} ALL STRINGS ===")
    for s in sorted(set(re.findall(r'"([^"\\]{4,250})"', t))):
        if not s.startswith("/") and not s.startswith("data-") and not s.startswith("http") and "className" not in s and "children:" not in s[:20]:
            if any(c.isalpha() for c in s) and len(s) > 10:
                print(" ", repr(s))
