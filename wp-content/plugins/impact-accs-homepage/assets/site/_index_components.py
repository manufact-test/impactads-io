import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

h = Path("index.html").read_text(encoding="utf-8")
chunks = re.findall(r'self\.__next_f\.push\(\[1,"([^"]*(?:\\.[^"]*)*)"\]\)', h)
full = "\n".join(c.encode().decode('unicode_escape') for c in chunks)

# map I[ module imports for homepage
for m in re.finditer(r'(\d+):I\[(\d+),[^\]]+\],"([^"]+)"\]', full):
    num, mod, name = m.groups()
    if int(num) >= 20 and int(num) <= 35:
        print(f"L{num} = {name} (module {mod})")

# find L28, L29, L2a on index
for label in ["28:", "29:", "2a:"]:
    idx = full.find(label)
    if idx >= 0:
        print(f"\n{label} {full[idx:idx+400]}")
