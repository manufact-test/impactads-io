import re
from pathlib import Path

t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")

# find component w and columns
for pat in ["colIndex", "Instrument", "Integration", "Hexagon", "Canvas", "R3F", "useFrame", "56253", "61545"]:
    print(pat, t.count(pat))

# extract e.i(56253) module - find chunk id
for m in re.finditer(r'e\.i\((\d+)\)', t):
    pass

# find investor objects with profilePicture
for m in re.finditer(r'profilePicture:"([^"]+)"[^}]{0,200}name:"([^"]+)"', t):
    print("inv", m.group(2), m.group(1))
for m in re.finditer(r'name:"([^"]+)"[^}]{0,200}profilePicture:"([^"]+)"', t):
    print("inv2", m.group(1), m.group(2))

# manifesto logo - search svg or image in 827ff
for m in re.finditer(r'src:"([^"]+)"', t):
    s = m.group(1)
    if "logo" in s or "assets" in s or "media" in s:
        print("src:", s)

# find children components in w function area
idx = t.find("ACCESS IN MINUTES")
print("\naccess context:", t[idx-500:idx+1500][:2000])
