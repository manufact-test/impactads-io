import re
from pathlib import Path

# investors strip
t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
i = t.find("let n=[")
print("n array:", repr(t[i:i+500]))

# Features label
t2 = Path("_next/static/chunks/1e7f2c52e84d02fd.js").read_text(encoding="utf-8")
for pat in ['label:"Features"', 'label:"Access"', 'children:"Features"']:
    print(pat, t2.count(pat))

# 5308 alerts / towers
t3 = Path("_next/static/chunks/5308d2f8d20274da.js").read_text(encoding="utf-8")
for m in re.finditer(r'alertTitle:"([^"]+)"|alertDescription:"([^"]+)"|resolvedTitle:"([^"]+)"|resolvedDescription:"([^"]+)"', t3):
    print("5308", m.group(0)[:100])

# tower ids / buildings
for pat in ["buildingId", "tower", "hotspot", "pinId", "landmark", "alertTitle"]:
    print("5308", pat, t3.count(pat))

# f7f1 tower related
t4 = Path("_next/static/chunks/f7f1c59a71681025.js").read_text(encoding="utf-8")
# extract UI strings
strings = set(re.findall(r'(?:children|title|description|alt|name):"([^"]{4,80})"', t4))
for s in sorted(strings):
    if any(k in s.lower() for k in ["launch", "delivery", "volume", "account", "team", "request", "supply", "eu", "log", "impact", "merrill", "avatar", "9:"]):
        print("f7f1:", s)
