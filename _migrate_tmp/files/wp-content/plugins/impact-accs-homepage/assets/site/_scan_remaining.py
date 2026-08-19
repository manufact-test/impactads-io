import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent
patterns = [
    "observability",
    "Y Combinator",
    "Sherwood Callaway",
    "impact.ai",
    "San Francisco",
    "Kinesis",
    "Claude Code",
    "Autonomous Alerts",
    "Conversational Debugging",
    "Stop Wondering",
    "SAVE THE DAY",
    "OBSERVABILITY TO",
    "impact.accs a new",
    "impact.accs more than",
]

for pat in patterns:
    hits = []
    for p in ROOT.rglob("*"):
        if p.suffix not in (".html", ".js") or p.name.startswith("_"):
            continue
        if "node_modules" in str(p):
            continue
        t = p.read_text(encoding="utf-8", errors="ignore")
        if pat.lower() not in t.lower():
            continue
        if p.suffix == ".html":
            parts = re.split(r"(<script[\s\S]*?</script>)", t, flags=re.I)
            vis = "".join(parts[i] for i in range(0, len(parts), 2))
            if pat.lower() not in vis.lower():
                continue
            hits.append(str(p.relative_to(ROOT)))
        elif p.name in {
            "8e1e9e7a85fc0466.js",
            "fe86549c3883d530.js",
            "a77e907734f4b424.js",
            "694b218a672794b8.js",
            "a6dad97d9634a72d.js",
            "1e7f2c52e84d02fd.js",
        }:
            continue
        else:
            hits.append(str(p.relative_to(ROOT)))
    if hits:
        print(f"{pat}: {len(hits)}")
        for h in hits[:6]:
            print(f"  {h}")
