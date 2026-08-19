import re
from pathlib import Path

for fname in ["827ff3490ba1793e.js", "692acfebb5322696.js", "0476edb0af9ab771.js", "1e7f2c52e84d02fd.js"]:
    t = Path(f"_next/static/chunks/{fname}").read_text(encoding="utf-8")
    print(f"\n=== {fname} ===")
    # people-like strings
    for m in re.finditer(r'\{name:"([^"]+)",[^}]{0,300}?(?:title|role|company):"([^"]+)"', t):
        print(f"  {m.group(1)} | {m.group(2)}")
    for m in re.finditer(r'firstName:"([^"]+)"|lastName:"([^"]+)"|fullName:"([^"]+)"', t):
        pass
    # investor array
    idx = t.find("BACKED BY")
    if idx >= 0:
        print("  context:", repr(t[idx:idx+800])[:800])

# hameni / logo paths
for fname in Path("_next/static/chunks").glob("*.js"):
    t = fname.read_text(encoding="utf-8", errors="ignore")
    if "hameni" in t.lower() or "sotheby" in t.lower():
        print("logo in", fname.name)
        for m in re.finditer(r'[^"\']*(?:hameni|sotheby)[^"\']*', t, re.I):
            print(" ", m.group(0)[:100])

# Ship with confidence still present?
for fname in ["827ff3490ba1793e.js", "d53e27b68750e6f9.js", "1e7f2c52e84d02fd.js", "index.html"]:
    p = Path(fname) if fname.endswith('.html') else Path(f"_next/static/chunks/{fname}")
    t = p.read_text(encoding="utf-8")
    for s in ["Ship with confidence", "AI-native observability", "impact.corp", "ai-native"]:
        if s in t:
            print(f"STILL {s!r} in {fname}: {t.count(s)}")
