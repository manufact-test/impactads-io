import re
from pathlib import Path

for fname in ["5308d2f8d20274da.js", "f7f1c59a71681025.js", "1e7f2c52e84d02fd.js"]:
    t = Path("_next/static/chunks") / fname
    s = t.read_text(encoding="utf-8")
    print(f"\n=== {fname} ===")
    found = set()
    for m in re.finditer(r'"([^"]{12,140})"', s):
        v = m.group(1)
        if any(k in v for k in ["5XX", "checkout", "NullReference", "systems operational", "P99", "Error Log", "JOIN THE", "Careers", "847 users", "187ms", "Spike correlates", "Rolling back", "@Impact", "@Cursor", "deploy", "api/search"]):
            if v not in found:
                found.add(v)
                print("-", v[:130])
