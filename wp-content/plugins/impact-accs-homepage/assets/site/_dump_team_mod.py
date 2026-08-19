import sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

t = Path("_next/static/chunks/692acfebb5322696.js").read_text(encoding="utf-8")
idx = t.find("89626,e")
print(t[idx:idx+3500])
