import re
from pathlib import Path

t = Path("_next/static/chunks/692acfebb5322696.js").read_text(encoding="utf-8")
# extract investor objects
start = t.find('{name:"Abhi Aiyer"')
if start < 0:
    start = t.find("profilePicture:")
end = t.find("],56253")  # guess
chunk = t[start:start+8000] if start >= 0 else t
for m in re.finditer(r'\{name:"([^"]+)",company:"([^"]+)",profilePicture:"([^"]+)",companyLogo:"([^"]+)"\}', t):
    print(f'{m.group(1)} | {m.group(2)} | {m.group(3)}')
