import re
from pathlib import Path

# Find demo/alert strings in 04bde and 827ff with context
for fname in ["04bde6fc7adf5b08.js", "827ff3490ba1793e.js", "692acfebb5322696.js", "29c2c1c591d62005.js"]:
    p = Path(f"_next/static/chunks/{fname}")
    t = p.read_text(encoding="utf-8")
    print(f"\n=== {fname} ===")
    for pat in ["Sherwood", "latency", "Kinesis", "Impact APP", "Impact:", "Severity", "High", "Log-in", "when this became", "launch window", "Volume request", "Confirm availability", "View in Impact", "Start an Incident", "Contact team", "REQUEST ACCESS", "CONTACT TEAM", "AnomalyAlerts", "ConversationalDebug", "CodingAgents"]:
        for m in re.finditer(re.escape(pat) if pat[0].isupper() else pat, t, re.I):
            start = max(0, m.start()-80)
            end = min(len(t), m.end()+120)
            snippet = t[start:end].replace('\n',' ')
            print(f"  [{pat}] ...{snippet}...")
            break
