import sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')
t = Path("_next/static/chunks/0476edb0af9ab771.js").read_text(encoding="utf-8")
i = t.find("That")
while i >= 0 and i < 20000:
    if "clicked" in t[i:i+80]:
        print(repr(t[i:i+100]))
        break
    i = t.find("That", i+1)
