import re
from pathlib import Path

t = Path("_next/static/chunks/f7f1c59a71681025.js").read_text(encoding="utf-8")

# input components - search for data-type-text near You
for m in re.finditer(r'data-type-text[^}]{0,200}', t):
    print("type-text:", m.group(0)[:180])

# find function u({active
for fn in ["function u({active", "function f({active", "function j({active"]:
    i = t.find(fn)
    if i >= 0:
        Path(f"_f7f1_{fn[-1]}.txt").write_text(t[i:i+800], encoding="utf-8")

# chart labels in tspan
for m in re.finditer(r'children:"([^"]{3,40})"\}\)\}\)', t):
    s = m.group(1)
    if any(k in s.lower() for k in ["log", "latency", "uptime", "api", "error", "health"]):
        print("tspan?", repr(s))

# simpler - find Delivery Log and API Latency exact
for s in ["Delivery Log", "API Latency", "Service uptime", "Error rate", "Root cause", "Recommended action", "Spawning a fix agent", "PagerDuty", "Monitoring services", "Impact"]:
    print(s, t.count(s))
