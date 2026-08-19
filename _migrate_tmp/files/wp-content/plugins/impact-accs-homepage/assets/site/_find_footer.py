import sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

for chunk, needle in [
    ("d53e27b68750e6f9.js", "Footer"),
    ("0476edb0af9ab771.js", "Footer"),
    ("827ff3490ba1793e.js", "READY FOR ACTION"),
    ("0476edb0af9ab771.js", "AboutPage"),
    ("0476edb0af9ab771.js", "export default"),
]:
    t = Path(f"_next/static/chunks/{chunk}").read_text(encoding="utf-8")
    idx = t.find(needle)
    while idx >= 0:
        print(f"\n[{chunk}] {needle} @{idx}")
        print(t[max(0,idx-100):idx+400])
        idx = t.find(needle, idx+1)
        if needle != "Footer":
            break

# end of about page component
t = Path("_next/static/chunks/0476edb0af9ab771.js").read_text(encoding="utf-8")
for m in ["AboutPage", "function N(", "e.s([\"About"]:
    i = t.find(m)
    if i >= 0:
        print(f"\n--- {m} ---")
        print(t[i:i+800])
