import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

h = Path("index.html").read_text(encoding="utf-8")
chunks = re.findall(r'self\.__next_f\.push\(\[1,"([^"]*(?:\\.[^"]*)*)"\]\)', h)
full = "\n".join(c.encode().decode('unicode_escape') for c in chunks)

for m in re.finditer(r'26:I\[37131,(\[[^\]]+\]),"FeaturesAndBackedBy"\]', full):
    print("FeaturesAndBackedBy import:", m.group(0)[:800])

for m in re.finditer(r'25:I\[', full):
    s = full[m.start():m.start()+500]
    if 'FeaturesTimeline' in s or '77914' in s:
        print("\nL25:", s[:500])
