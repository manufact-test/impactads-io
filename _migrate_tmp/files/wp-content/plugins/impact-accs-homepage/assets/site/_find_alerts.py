import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

t = Path("_next/static/chunks/04bde6fc7adf5b08.js").read_text(encoding="utf-8")
# find alert data objects
for m in re.finditer(r'timestamp:"[^"]+"', t):
    print(t[m.start()-200:m.start()+800])
    print("---")
