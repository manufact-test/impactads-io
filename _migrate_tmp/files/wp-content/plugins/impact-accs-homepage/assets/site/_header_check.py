import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

h = Path("about.html").read_text(encoding="utf-8")
for pat in [r'duration-600[^"]*', r'translate-y-0', r'OPEN POSITIONS', r'Meet Impact']:
    for m in re.finditer(pat, h):
        print(f"{pat}: count, first at {m.start()}")
        break

# mobile header
idx = h.find("duration-600")
print("\nAll duration-600:", h.count("duration-600"))
print("Sample:", h[idx:idx+200] if idx>=0 else "none")
