import re
from pathlib import Path

for fname in ["1e7f2c52e84d02fd.js", "d53e27b68750e6f9.js"]:
    t = Path(f"_next/static/chunks/{fname}").read_text(encoding="utf-8")
    print(f"\n=== {fname} ===")
    for m in re.finditer(r'children:"([A-Za-z][^"]{1,35})"', t):
        s = m.group(1)
        if s in ("About", "Blog", "Contact", "Features", "Manifesto", "Waitlist", "Agency Accounts", "Media Buying Access", "Team Supply", "Request access", "ACCESS", "Access"):
            print(s)

# footer strings
for fname in ["d53e27b68750e6f9.js", "1e7f2c52e84d02fd.js"]:
    t = Path(f"_next/static/chunks/{fname}").read_text(encoding="utf-8")
    i = t.find("impact.corp")
    print(f"\nfooter {fname}:", repr(t[i:i+120]))

# tagline
t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
for s in ["Ship with confidence", "AI-native observability", "fast-moving engineering"]:
    print(s, t.count(s))

t2 = Path("index.html").read_text(encoding="utf-8")
for s in ["Ship with confidence", "AI-native observability"]:
    print("index", s, t2.count(s))
