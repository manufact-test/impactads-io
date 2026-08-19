import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

h = Path("about.html").read_text(encoding="utf-8")
scripts = re.findall(r'<script[^>]*>(.*?)</script>', h, re.DOTALL)
for i, s in enumerate(scripts):
    if '6:["$","$1","c"' in s or '6:[\\"$\\",\\"$1\\",\\"c\\"' in s:
        print(f"segment 6 in script {i}, len {len(s)}")
        # find the main children part
        for pat in ['$L27', 'L28', '0476edb', '827ff']:
            if pat in s:
                print(f"  has {pat}")

# extract READY FOR ACTION section from index body
idx = Path("index.html").read_text(encoding="utf-8")
start = idx.find("READY FOR ACTION")
# go back to section start
sec_start = idx.rfind("<section", 0, start)
sec_end = idx.find("</section>", start) + len("</section>")
ready_section = idx[sec_start:sec_end]
print(f"\nREADY section len {len(ready_section)}")
print(ready_section[:300])

# about careers section bounds
start2 = h.find('id="careers"')
sec_start2 = h.rfind("<section", 0, start2)
sec_end2 = h.find("</section>", start2) + len("</section>")
print(f"\ncareers section len {sec_end2-sec_start2}")
