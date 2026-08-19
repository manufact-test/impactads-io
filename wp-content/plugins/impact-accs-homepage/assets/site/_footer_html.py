import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

for fname in ["index.html", "about.html"]:
    h = Path(fname).read_text(encoding="utf-8")
    # find footer area
    for kw in ["impact.corp", "RIGHTS RESERVED", "READY FOR ACTION", "Meet Impact", "OUR VALUES"]:
        i = h.find(kw)
        if i >= 0:
            print(f"\n{fname} [{kw}] @{i}:")
            print(h[max(0,i-100):i+200])
        else:
            print(f"{fname}: no {kw}")
