import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

for fname in ["index.html", "about.html"]:
    h = Path(fname).read_text(encoding="utf-8")
    chunks = re.findall(r'self\.__next_f\.push\(\[1,"([^"]*(?:\\.[^"]*)*)"\]\)', h)
    full = "\n".join(c.encode().decode('unicode_escape') for c in chunks)
    idx = full.find('6:["$","$1","c"')
    print(f"\n=== {fname} ===")
    print(full[idx:idx+900])
