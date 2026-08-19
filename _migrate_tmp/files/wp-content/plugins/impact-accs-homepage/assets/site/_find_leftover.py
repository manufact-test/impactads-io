import re
from pathlib import Path
t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
for pat in ["observability", "Autonomous Alerts", "2261 Market", "Impact Inc"]:
    for m in re.finditer(pat, t, re.I):
        print(repr(t[max(0,m.start()-40):m.end()+80]))
        break
