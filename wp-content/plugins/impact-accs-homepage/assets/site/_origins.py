import sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')
t = Path("_next/static/chunks/0476edb0af9ab771.js").read_text(encoding="utf-8")
for needle in ['children:"Brex"', "about-vision", "about-founder"]:
    i = t.find(needle)
    print(f"\n{needle} @{i}:")
    print(t[i:i+600] if i>=0 else "NOT FOUND")
