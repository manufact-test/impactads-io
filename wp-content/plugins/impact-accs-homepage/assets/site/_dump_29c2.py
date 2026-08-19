import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

t = Path("_next/static/chunks/29c2c1c591d62005.js").read_text(encoding="utf-8")
# find message arrays
for pat in ["content:", "messages:", "when this", "Found an", "Can we", "log-in", "sherwood-callaway"]:
    idx = 0
    count = 0
    while count < 3:
        j = t.find(pat, idx)
        if j < 0: break
        print(f"\n[{pat}@{j}]")
        print(t[j-50:j+300])
        idx = j + 1
        count += 1
