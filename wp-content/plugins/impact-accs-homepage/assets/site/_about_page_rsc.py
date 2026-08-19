import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

h = Path("about.html").read_text(encoding="utf-8")
# find RSC page component children array
for m in re.finditer(r'\["\$","[^"]+",null,\{[^}]*\}\]', h):
    pass

# simpler: find chunk after TeamSection
idx = h.find("TeamSection")
print(h[idx:idx+2500])
