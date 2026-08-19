import sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

t = Path("_next/static/chunks/0476edb0af9ab771.js").read_text(encoding="utf-8")

# dump values section and page root
for start in [13000, 14100, 14700, 17500]:
    print(f"\n=== @{start} ===")
    print(t[start:start+2500])

# find page component - search for TeamSection usage
idx = t.find("TeamSection")
while idx >= 0:
    print(f"\nTeamSection ref @{idx}: {t[idx-200:idx+300]}")
    idx = t.find("TeamSection", idx+1)
    break

# Footer in d53
d = Path("_next/static/chunks/d53e27b68750e6f9.js").read_text(encoding="utf-8")
fi = d.find('e.s(["Footer"')
print(f"\nFooter export @{fi}: {d[fi:fi+800]}")

# Homepage bottom in 827ff
h = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
for needle in ["READY FOR ACTION", "Footer"]:
    i = h.find(needle)
    if i >= 0:
        print(f"\n827 {needle} @{i}: {h[i-100:i+400]}")
