import re
from pathlib import Path

CHUNKS = Path("_next/static/chunks")
files = ["5308d2f8d20274da.js", "f7f1c59a71681025.js", "1e7f2c52e84d02fd.js", "ba5d7afdb6dc00cc.js", "d53e27b68750e6f9.js"]
out = []
for fname in files:
    p = CHUNKS / fname
    if not p.exists():
        continue
    t = p.read_text(encoding="utf-8")
    out.append(f"\n# {fname}\n")
    seen = set()
    for m in re.finditer(r'(?:title|subtitle|label|message|heading|description|children|alt|text):"([^"]{4,200})"', t):
        v = m.group(1)
        if v in seen:
            continue
        seen.add(v)
        out.append(repr(v))
    # also single-quoted
    for m in re.finditer(r"(?:title|subtitle|label|message|heading|description):'([^']{4,200})'", t):
        v = m.group(1)
        if v in seen:
            continue
        seen.add(v)
        out.append("SINGLE:" + repr(v))

Path("_hero_strings_raw.txt").write_text("\n".join(out), encoding="utf-8")
print("written", len(out))
