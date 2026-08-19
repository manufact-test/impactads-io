import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

h = Path("about.html").read_text(encoding="utf-8")
chunks = re.findall(r'self\.__next_f\.push\(\[1,"([^"]*(?:\\.[^"]*)*)"\]\)', h)
full = "\n".join(c.encode().decode('unicode_escape') for c in chunks)

# find segments referencing L24 (AboutHero)
for m in re.finditer(r'\d+:\[.*?\$L24.*?\]', full):
    s = m.group(0)
    if len(s) < 5000:
        print("SHORT:", s)
    else:
        print("LONG segment len", len(s))
        print(s[:2000])
        print("...")
        print(s[-1000:])

# broader search
idx = full.find("$L24")
count = 0
while idx >= 0 and count < 5:
    print(f"\n$L24 @{idx}: {full[idx:idx+800]}")
    idx = full.find("$L24", idx+1)
    count += 1
