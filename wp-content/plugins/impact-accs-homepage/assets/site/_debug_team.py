import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

for fname in ["692acfebb5322696.js", "827ff3490ba1793e.js"]:
    t = Path(f"_next/static/chunks/{fname}").read_text(encoding="utf-8")
    print(f"\n=== {fname} ===")
    for m in re.finditer(r'TEAM_MEMBERS\["([^"]+)"\]', t):
        print("  ref:", m.group(1))
    # find TEAM_MEMBERS object definition
    m = re.search(r'TEAM_MEMBERS=\{([^}]+(?:\{[^}]*\}[^}]*)*)\}', t)
    if m:
        print("  keys:", re.findall(r'"([a-z-]+)"\s*:', m.group(1)))
    idx = t.find("TEAM_MEMBERS")
    if idx >= 0:
        print("  ctx:", t[idx:idx+1200][:1200])
