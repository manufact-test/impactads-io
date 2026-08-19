import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

t = Path("_next/static/chunks/d53e27b68750e6f9.js").read_text(encoding="utf-8")
m = re.search(r'"features",0,\[(.*?)\],"getImagePath"', t)
if m:
    print(m.group(1)[:3000])
else:
    idx = t.find('shortTitle')
    print(t[idx-100:idx+2000])

# nav items
for pat in ['Manifesto', '/blog/manifesto', 'label:"Manifesto"']:
    if pat in t:
        i = t.find(pat)
        print(f"\nNAV {pat}:", t[i-80:i+120])
