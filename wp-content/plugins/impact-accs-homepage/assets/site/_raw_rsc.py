import re
from pathlib import Path

h = Path("about.html").read_text(encoding="utf-8")
idx = h.find("OPEN POSITIONS")
print("body idx", idx)
idx2 = h.find("careers")
print("careers count", h.count("careers"))
# find in scripts only
scripts = re.findall(r'<script[^>]*>(.*?)</script>', h, re.DOTALL)
for i, s in enumerate(scripts):
    if "OPEN POSITIONS" in s or ('28:' in s and 'section' in s):
        print(f"\nscript {i} len {len(s)}")
        j = s.find("OPEN POSITIONS")
        if j < 0:
            j = s.find('28:[')
        print(s[max(0,j-100):j+200])
