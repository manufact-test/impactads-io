import re
from pathlib import Path

for fname in ["d53e27b68750e6f9.js", "827ff3490ba1793e.js"]:
    t = Path("_next/static/chunks") / fname
    s = t.read_text(encoding="utf-8")
    out = Path(f"_{fname[:8]}_strings.txt")
    lines = []
    for m in re.finditer(r'(?:title|description|label|children):"([^"]{4,350})"', s):
        val = m.group(1)
        if any(k in val.lower() for k in ["impact", "observ", "alert", "claude", "cursor", "log", "monitor", "engineer", "agent", "kinesis", "wonder", "save", "rich", "interrogate", "telemetry", "ship", "access", "account"]):
            lines.append(val)
    out.write_text("\n---\n".join(dict.fromkeys(lines)), encoding="utf-8")
    print(fname, len(lines))
