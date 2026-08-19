from pathlib import Path
import re

t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
idx = t.find('let n=[{name:"Graphite"')
print("n array end approx:", t[idx:idx+12000].find("}];function"))
chunk = t[idx:idx+15000]
# find where n.map is used
for m in re.finditer(r'n\.map|\.Logo', chunk):
    print("use at", m.start(), chunk[m.start()-80:m.start()+200][:280])

# search Logo, in manifesto card layout
for m in re.finditer(r'Logo,\{className:"([^"]+)"\}', t):
    print("Logo usage:", m.group(0), "count", t.count(m.group(0)))

# find all Logo usages
for m in re.finditer(r'\(0,t\.jsx\)\([a-zA-Z]+\.Logo', t):
    print("jsx Logo:", t[m.start():m.start()+120])
