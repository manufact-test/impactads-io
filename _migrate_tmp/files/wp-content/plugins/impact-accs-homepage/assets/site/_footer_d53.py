import sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

d = Path("_next/static/chunks/d53e27b68750e6f9.js").read_text(encoding="utf-8")
# footer text strings
for kw in ["impact.corp", "RIGHTS RESERVED", "2026", "Impact Inc", "READY"]:
    i = d.find(kw)
    while i >= 0:
        print(f"{kw} @{i}: {d[max(0,i-40):i+120]}")
        i = d.find(kw, i+1)
        if i > 0 and d.find(kw, i) != i:
            break

# find about page route in 81919 chunk area - search about.html for page children order
h = Path("about.html").read_text(encoding="utf-8")
# find sequence around Footer in RSC
fi = h.find('"Footer"')
print("\n--- RSC around Footer ---")
print(h[fi-800:fi+1200])

# find ReadyForAction or similar in index RSC
idx = Path("index.html").read_text(encoding="utf-8")
for kw in ["ReadyForAction", "READY FOR ACTION", "81919", "29315"]:
    i = idx.find(kw)
    if i >= 0:
        print(f"\nindex {kw} @{i}: {idx[i-200:i+400]}")
