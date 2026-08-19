import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

h = Path("about.html").read_text(encoding="utf-8")

# find RSC about page component references
for pat in [r'AboutHero', r'AboutDescription', r'TeamSection', r'ValuesSection', r'81919', r'20416', r'77572', r'3927', r'3629']:
    matches = [(m.start(), h[max(0,m.start()-80):m.start()+120]) for m in re.finditer(pat, h)]
    if matches:
        print(f"\n{pat}: {len(matches)} hits")
        for pos, ctx in matches[:2]:
            print(f"  @{pos}: ...{ctx}...")

# find end of main content / footer in body HTML (not script)
body_end = h.rfind("</footer>")
if body_end >= 0:
    print("\n--- footer area ---")
    print(h[body_end-500:body_end+800])

# find chunk list in about vs index for 827ff
idx = h.find("827ff3490ba1793e")
print(f"\n827ff in about: {idx}")

# find about page function in RSC
m = re.search(r'"about"[^}]{0,500}', h)
if m:
    print("\nabout route:", m.group(0)[:300])
