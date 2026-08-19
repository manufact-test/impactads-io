import re
from pathlib import Path

t = Path("_next/static/chunks/1e7f2c52e84d02fd.js").read_text(encoding="utf-8")
idx = t.find("/about")
print("nav configs:")
for m in re.finditer(r'\{[^}]{0,120}href:"/[^"]+"[^}]{0,120}\}', t):
    s = m.group(0)
    if any(x in s for x in ["about", "blog", "features", "contact", "waitlist"]):
        print(s[:200])

# simpler - find label fields near about
for m in re.finditer(r'label:"([^"]+)"[^}]{0,100}href:"([^"]+)"', t):
    print("label href:", m.group(1), m.group(2))

for m in re.finditer(r'href:"([^"]+)"[^}]{0,100}label:"([^"]+)"', t):
    print("href label:", m.group(2), m.group(1))
