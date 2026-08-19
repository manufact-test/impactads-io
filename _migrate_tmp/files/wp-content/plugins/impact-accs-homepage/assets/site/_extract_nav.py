import re
from pathlib import Path

for fname in ["1e7f2c52e84d02fd.js", "d53e27b68750e6f9.js"]:
    t = Path(f"_next/static/chunks/{fname}").read_text(encoding="utf-8")
    print(f"\n=== {fname} nav ===")
    for m in re.finditer(r'(?:label|title|children):"([^"]{2,40})"[^}]{0,80}href:"([^"]+)"', t):
        print(f"  {m.group(1)!r} -> {m.group(2)}")
    for m in re.finditer(r'href:"([^"]+)"[^}]{0,80}(?:label|children):"([^"]{2,40})"', t):
        print(f"  {m.group(2)!r} -> {m.group(1)}")
    # footer
    idx = t.find("impact.corp")
    if idx >= 0:
        print("footer ctx:", repr(t[idx-100:idx+200]))
