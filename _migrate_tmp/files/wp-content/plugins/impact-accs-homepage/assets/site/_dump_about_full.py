import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

# About chunk strings
t = Path("_next/static/chunks/0476edb0af9ab771.js").read_text(encoding="utf-8")
for s in sorted(set(re.findall(r'"([^"\\]{20,400})"', t))):
    if not s.startswith("/") and "className" not in s and "children:" not in s[:15]:
        if any(c.isalpha() for c in s):
            print(repr(s))

print("\n--- HTML about ---")
h = Path("about.html").read_text(encoding="utf-8")
for pat in ["ABOUT", "observability", "footer", "Footer", "RIGHTS", "founder", "Mission", "Vision", "Values", "src=", "webp", "jpg"]:
    if pat.lower() in h.lower():
        idx = h.lower().find(pat.lower())
        print(pat, ":", h[max(0,idx-40):idx+120][:160])
