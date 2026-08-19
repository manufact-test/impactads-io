import re
from pathlib import Path
t = Path("_next/static/chunks/d53e27b68750e6f9.js").read_text(encoding="utf-8")
for m in re.finditer(r'"(Rich[^"]{0,200}|SAVE THE DAY|Stop Wondering|Autonomous Alerts|Conversational|Coding Agents|just work[^"]{0,120})"', t):
    print(repr(m.group(1)))
