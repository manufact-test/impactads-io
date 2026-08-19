import re
from pathlib import Path

t = Path("_next/static/chunks/692acfebb5322696.js").read_text(encoding="utf-8")
idx = t.find("merrill-lutsky")
print(t[idx-300:idx+400])

# find all name/title near credits png
for pic in re.findall(r'/assets/credits/[^"]+\.png', t):
    i = t.find(pic)
    ctx = t[max(0,i-200):i+len(pic)+100]
    nm = re.search(r'name:"([^"]+)"', ctx)
    tt = re.search(r'title:"([^"]+)"', ctx)
    co = re.search(r'company:"([^"]+)"', ctx)
    print(pic.split("/")[-1], "|", nm.group(1) if nm else "?", "|", tt.group(1) if tt else "?", "|", co.group(1) if co else "?")
