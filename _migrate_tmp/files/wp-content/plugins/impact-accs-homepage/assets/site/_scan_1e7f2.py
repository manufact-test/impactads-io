import re
from pathlib import Path

t = Path("_next/static/chunks/1e7f2c52e84d02fd.js").read_text(encoding="utf-8")
for pat in ["Waitlist", "Careers", "Join", "Request access", "Autonomous", "Observability", "AI-Native"]:
    print(pat, t.count(pat))
