from pathlib import Path
import re

for fname in ["about.html", "index.html"]:
    t = Path(fname).read_text(encoding="utf-8")
    i = t.find('role="status"')
    if i < 0:
        i = t.find("Initializing")
    print(f"\n=== {fname} ===")
    print(t[i:i+800] if i >= 0 else "not found")
