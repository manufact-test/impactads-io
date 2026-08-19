import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

h = Path("about.html").read_text(encoding="utf-8")
chunks = re.findall(r'self\.__next_f\.push\(\[1,"([^"]*(?:\\.[^"]*)*)"\]\)', h)
full = "\n".join(c.encode().decode('unicode_escape') for c in chunks)

# find about page route definition - search for AboutHero as child
for pat in [r'\$L24', r'AboutHero', r'about/page', r'TeamSection']:
    matches = list(re.finditer(pat, full))
    print(f"{pat}: {len(matches)} matches")

# find chunk with page children array
idx = full.find("AboutHero")
while idx >= 0:
    print(f"\n--- AboutHero context ---")
    print(full[max(0,idx-500):idx+1500])
    idx = full.find("AboutHero", idx+1)
    if idx > 0:
        break

# find page segment 4: or similar
m = re.search(r'4:\["\$"[^\]]{0,8000}TeamSection[^\]]{0,2000}', full)
if m:
    print("\n=== PAGE ROUTE ===")
    print(m.group(0)[:4000])
