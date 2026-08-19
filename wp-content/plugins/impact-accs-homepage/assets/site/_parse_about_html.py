import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

h = Path("about.html").read_text(encoding="utf-8")

# all script src
scripts = re.findall(r'src="([^"]+\.js)"', h)
print("Scripts:", len(scripts))
for s in scripts:
    if "0476" in s or "827ff" in s or "page" in s.lower():
        print(" ", s)

# extract visible text snippets
texts = ["Meet Impact", "ABOUT", "impact.accs", "observability", "Footer", "READY", "Meet", "Denis", "Brex"]
for t in texts:
    count = h.count(t)
    if count:
        i = h.find(t)
        print(f"\n{t} x{count}: ...{h[max(0,i-50):i+150]}...")
