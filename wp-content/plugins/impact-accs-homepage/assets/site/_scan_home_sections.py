import re
from pathlib import Path

for fname in ["827ff3490ba1793e.js", "d53e27b68750e6f9.js", "1e7f2c52e84d02fd.js", "692acfebb5322696.js", "0476edb0af9ab771.js"]:
    p = Path(f"_next/static/chunks/{fname}")
    if not p.exists():
        continue
    t = p.read_text(encoding="utf-8")
    print(f"\n=== {fname} ({len(t)} chars) ===")
    for pat in [
        "ACCESS IN MINUTES", "WORKS WITH", "BACKED BY", "BUILDERS",
        "Ship with confidence", "AI-native observability", "impact.corp",
        "ai-native", "Sazabi", "sazabi", "hameni", "Integrations",
        "About", "Blog", "Features", "Contact", "Sherwood", "Sequoia",
        "name:", "title:", "role:", "profilePicture",
    ]:
        c = t.count(pat)
        if c:
            print(f"  {pat!r}: {c}")

# extract quoted strings with investor-like content from 827ff
t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
print("\n--- 827ff names/roles ---")
for m in re.finditer(r'name:"([^"]+)"|role:"([^"]+)"|company:"([^"]+)"', t):
    print(m.group(0)[:120])

print("\n--- asset paths in 827ff ---")
for m in re.finditer(r'"/assets/[^"]+"|"/_next/static/media/[^"]+"', t):
    s = m.group(0)
    if "team" in s or "logo" in s or "investor" in s or "sazabi" in s.lower() or "hameni" in s.lower():
        print(s)
