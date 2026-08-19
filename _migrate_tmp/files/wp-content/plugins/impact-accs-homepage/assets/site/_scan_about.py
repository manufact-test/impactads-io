import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

t = Path("_next/static/chunks/0476edb0af9ab771.js").read_text(encoding="utf-8")

# extract quoted strings with observability, Impact, ABOUT, etc
keywords = ['observ', 'ABOUT', 'Meet Impact', 'OUR VALUES', 'MISSION', 'VISION', 'Sherwood', 
            'Footer', '24490', 'AboutHero', 'AboutDescription', 'TeamSection', 'manifesto',
            'software heals', 'default observ', 'mind-blowing', '3am', 'alert', 'CRAFTSMAN',
            'founder', 'origins', 'SPARK', 'Cursor', 'Claude']

for kw in keywords:
    idx = 0
    found = 0
    while found < 3:
        j = t.find(kw, idx)
        if j < 0: break
        ctx = t[max(0,j-60):j+120]
        print(f"\n[{kw}] @{j}: ...{ctx}...")
        idx = j + len(kw)
        found += 1

# find page export
for pat in [r'e\.s\(\["About', r'AboutPage', r'function \w+\(\).*AboutHero']:
    for m in re.finditer(pat, t):
        print(f"\nPattern {pat} @{m.start()}: {t[m.start():m.start()+200]}")

# Check if 24490 is referenced
print("\n24490 count:", t.count("24490"))
print("827ff in about html?")
for f in ["about.html", "index.html"]:
    h = Path(f).read_text(encoding="utf-8")
    for chunk in ["0476edb", "827ff", "d53e27", "1e7f2c5"]:
        print(f"  {f} {chunk}: {chunk in h}")
