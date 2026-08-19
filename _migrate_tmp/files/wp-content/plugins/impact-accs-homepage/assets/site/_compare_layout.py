import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

for name in ["index.html", "about.html"]:
    h = Path(name).read_text(encoding="utf-8")
    print(f"\n=== {name} ===")
    for pat in ["footer", "Footer", "READY FOR ACTION", "impact.corp", "RIGHTS RESERVED", "duration-400", "duration-600", "827ff", "0476edb"]:
        print(f"  {pat}: {h.count(pat)}")

# About page end structure in 0476
t = Path("_next/static/chunks/0476edb0af9ab771.js").read_text(encoding="utf-8")
idx = t.find("AboutPage")
if idx < 0:
    idx = t.find("function v(")
print("\nAbout export:", t[idx:idx+500] if idx>=0 else "not found")

# Find if Footer imported in about chunk or layout
for chunk in ["0476edb0af9ab771.js", "1e7f2c52e84d02fd.js", "d53e27b68750e6f9.js"]:
    c = Path(f"_next/static/chunks/{chunk}").read_text(encoding="utf-8")
    if "Footer" in c or "footer" in c.lower():
        print(f"{chunk} has footer mention")
