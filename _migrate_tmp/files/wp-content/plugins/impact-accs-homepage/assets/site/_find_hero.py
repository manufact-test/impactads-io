import re
from pathlib import Path

CHUNKS = Path("_next/static/chunks")
needles = [
    "5XX", "checkout", "NullReference", "systems operational", "P99 latency",
    "Error Logs", "JOIN THE WAITLIST", "Join the Waitlist", "Careers",
    "Run @Cursor", "@Impact Run", "Spike correlates", "Rolling back",
    "AI-Native", "847 users", "187ms",
]
for p in sorted(CHUNKS.glob("*.js")):
    t = p.read_text(encoding="utf-8", errors="ignore")
    hits = [n for n in needles if n.lower() in t.lower()]
    if hits:
        print(p.name, "=>", hits)

print("\n--- hero strings in 5308 ---")
t = (CHUNKS / "5308d2f8d20274da.js").read_text(encoding="utf-8")
for m in re.finditer(r'(?:title|subtitle|label|message|text):"([^"]{8,120})"', t):
    s = m.group(1)
    if any(k in s.lower() for k in ["error", "checkout", "latency", "system", "log", "api", "user", "deploy", "null"]):
        print(repr(s))
