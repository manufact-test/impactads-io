import sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

t = Path("_next/static/chunks/04bde6fc7adf5b08.js").read_text(encoding="utf-8")
idx = t.find('label:"View in Impact"')
print(t[idx-800:idx+2500])
