import re
from pathlib import Path

t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
print("=== titles ===")
for m in re.finditer(r'title:"([^"]{2,100})"', t):
    print(m.group(1))
print("\n=== descriptions (first 25 unique) ===")
seen = set()
for m in re.finditer(r'description:"([^"]{15,300})"', t):
    s = m.group(1)
    if s not in seen:
        seen.add(s)
        print(repr(s))
