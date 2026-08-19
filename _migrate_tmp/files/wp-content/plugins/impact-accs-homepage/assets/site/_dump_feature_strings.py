import re
from pathlib import Path

chunks = Path("_next/static/chunks")
for p in sorted(chunks.glob("*.js")):
    t = p.read_text(encoding="utf-8", errors="replace")
    hits = []
    for s in re.findall(r'"([^"\\]{8,200})"', t):
        sl = s.lower()
        if any(k in sl for k in [
            "sherwood", "latency", "launch:", "root cause", "recommended action",
            "agency account", "media buying", "team supply", "contact team",
            "manifesto", "autonomous alert", "conversational", "coding agent",
            "kinesis", "pagerduty", "when this became", "impact app",
            "scalegrid", "launchdesk", "advolume", "trafficlab"
        ]):
            hits.append(s)
    if hits:
        print(f"\n=== {p.name} ({len(hits)} hits) ===")
        for s in sorted(set(hits)):
            print(" ", repr(s))
