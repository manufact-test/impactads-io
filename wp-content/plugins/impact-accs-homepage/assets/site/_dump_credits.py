import re
from pathlib import Path

t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")

# find module 56253 or credits data
idx = t.find("colIndex:0")
print("credits section:", t[idx-2000:idx+4000][:6000])

# ImpactIcon usage
for m in re.finditer(r'.{0,80}ImpactIcon.{0,120}', t):
    print("ICON:", m.group(0)[:200])
