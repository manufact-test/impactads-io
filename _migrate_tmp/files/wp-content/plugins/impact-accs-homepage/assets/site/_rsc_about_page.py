import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

h = Path("about.html").read_text(encoding="utf-8")

# Find about page segment - search for AboutHero in RSC pushes
chunks = re.findall(r'self\.__next_f\.push\(\[1,"([^"]*(?:\\.[^"]*)*)"\]\)', h)
for i, c in enumerate(chunks):
    if 'AboutHero' in c or 'TeamSection' in c or 'READY' in c:
        decoded = c.encode().decode('unicode_escape')
        print(f"\n=== chunk {i} ===")
        print(decoded[:3000])

# Also search index for comparison - page children after hero sections
h2 = Path("index.html").read_text(encoding="utf-8")
chunks2 = re.findall(r'self\.__next_f\.push\(\[1,"([^"]*(?:\\.[^"]*)*)"\]\)', h2)
for i, c in enumerate(chunks2):
    if 'FeaturesAndBackedBy' in c or '29315' in c:
        decoded = c.encode().decode('unicode_escape')
        print(f"\n=== index chunk {i} ===")
        print(decoded[:2000])
