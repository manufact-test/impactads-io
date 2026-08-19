import re
import subprocess
from pathlib import Path

for fname in ["f7f1c59a71681025.js", "5308d2f8d20274da.js"]:
    p = Path(f"_next/static/chunks/{fname}")
    t = p.read_text(encoding="utf-8")
    r = subprocess.run(["node", "--check", str(p)], capture_output=True, text=True)
    print(fname, "syntax", "OK" if r.returncode == 0 else r.stderr)
    bad = ["p99", "PagerDuty", "on-call engineer", "Merrill", "Hadley", "ApiLatencyChart", "Spawning a fix agent", "latency for", "Monitoring services", "Root cause"]
    for b in bad:
        if b in t:
            print("  STILL:", b)
    good = ["Denis A.", "Elena M.", "Request status", "Volume terms", "Supply status", "ErrorConversation", "children:!y&&"]
    for g in good:
        if g in t:
            print("  OK:", g)
