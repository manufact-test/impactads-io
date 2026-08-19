import sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

t = Path("_next/static/chunks/0476edb0af9ab771.js").read_text(encoding="utf-8")
# find AboutDescription usage
for needle in ["AboutDescription", "function N(", "function y(", "20416,e"]:
    idx = 0
    n = 0
    while n < 2:
        j = t.find(needle, idx)
        if j < 0: break
        print(f"\n[{needle}@{j}]")
        print(t[j:j+300])
        idx = j + 1
        n += 1

# Home page footer usage in 827ff
h = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
idx = h.find("Footer")
while idx >= 0:
    print(f"\n827 Footer @{idx}:")
    print(h[idx-150:idx+500])
    idx = h.find("Footer", idx+1)
    if idx > 0 and h.find("Footer", idx) != idx:
        pass
    break  # first only

# find module 24490 import in 827ff
if "24490" in h:
    i = h.find("24490")
    print("\n24490 import:", h[i-80:i+120])
