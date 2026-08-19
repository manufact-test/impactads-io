import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

h = Path("about.html").read_text(encoding="utf-8")
chunks = re.findall(r'self\.__next_f\.push\(\[1,"([^"]*(?:\\.[^"]*)*)"\]\)', h)
full = "\n".join(c.encode().decode('unicode_escape') for c in chunks)

# full L28 careers section
m = re.search(r'28:\["\$","section".*?(?=\n\d+:\[|\Z)', full, re.DOTALL)
if m:
    print(m.group(0)[:3000])
    print("\n...len:", len(m.group(0)))

# layout footer L20
idx = full.find('4:[')
print("\nlayout 4:", full[idx:idx+1200])
