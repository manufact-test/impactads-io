import re
from pathlib import Path

t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")

# investors for credits
for m in re.finditer(r'\{id:"[^"]+",name:"([^"]+)",title:"([^"]+)"(?:,company:"([^"]+)")?,profilePicture:"([^"]+)"\}', t):
    print(m.groups())

# simpler
idx = t.find("BACKED BY")
chunk = t[idx:idx+5000]
for m in re.finditer(r'name:"([^"]+)"', chunk):
    print("name in backed:", m.group(1))
for m in re.finditer(r'profilePicture:"([^"]+)"', chunk):
    print("pic:", m.group(1))

# search sazabi / sotheby / impact logo in integrations Q array
idx2 = t.find("integrations:Q")
print("Q def:", t.find("let Q="), t.find("Q=["))
qstart = t.find("Q=[")
if qstart >= 0:
    print(t[qstart:qstart+800])

# manifesto investor sidebar - search for svg S or logo component near manifesto
for pat in ["Sazabi", "sotheby", "hameni", "ia-logo", "ImpactIcon", "profilePicture"]:
    i = t.find(pat)
    if i >= 0:
        print(pat, "at", i, repr(t[i:i+150]))
