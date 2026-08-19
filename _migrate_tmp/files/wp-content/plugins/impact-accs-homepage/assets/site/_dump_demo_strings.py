import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

for fname in ["ba5d7afdb6dc00cc.js", "29c2c1c591d62005.js", "692acfebb5322696.js", "04bde6fc7adf5b08.js", "9583d4a1bf83f1e7.js"]:
    p = Path(f"_next/static/chunks/{fname}")
    t = p.read_text(encoding="utf-8")
    print(f"\n=== {fname} ===")
    for s in sorted(set(re.findall(r'"([^"\\]{10,200})"', t))):
        if any(k in s for k in ["Can we", "issue", "request", "account", "launch", "team", "availability", "terms", "delivery", "Impact", "Sherwood", "Denis", "Elena", "checkout", "Auth service", "Deploy failed", "Latency spike", "Database", "Memory leak", "Rate limiter", "agents supported", "Claude", "Codex", "Cursor", "Kinesis", "Intake", "error rates", "shard"]):
            print(" ", repr(s))
