import re
from pathlib import Path

for fname in ["index.html", "index492c.html", "index89bf.html"]:
    t = Path(fname).read_text(encoding="utf-8")
    for pat in ["AI-Native", "Observability", "Waitlist", "Careers", "Join", "impact.accs", "Request access", "Launch blocked"]:
        if pat.lower() in t.lower():
            print(fname, pat, "YES")

# extract title from index
t = Path("index.html").read_text(encoding="utf-8")
m = re.search(r"<title>([^<]+)</title>", t)
print("title:", m.group(1) if m else "?")
