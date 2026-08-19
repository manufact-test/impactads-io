import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

for fname in ["about.html", "index.html"]:
    h = Path(fname).read_text(encoding="utf-8")
    m = re.search(r'<footer[^>]*>(.*?)</footer>', h, re.DOTALL)
    if m:
        footer = m.group(0)
        tail = footer[-1500:]
        print(f"\n=== {fname} footer tail ===")
        print(tail)
