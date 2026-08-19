import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

for fname in ["about.html", "index.html"]:
    h = Path(fname).read_text(encoding="utf-8")
    # find footer tag content
    m = re.search(r'<footer[^>]*>(.*?)</footer>', h, re.DOTALL)
    if m:
        footer = m.group(0)
        print(f"\n=== {fname} footer ({len(footer)} chars) ===")
        # strip long svg
        footer_short = re.sub(r'<svg.*?</svg>', '[SVG]', footer, flags=re.DOTALL)
        print(footer_short[:2000])
    else:
        print(f"{fname}: no footer tag")

    # page children in RSC - search for AboutHero through Footer sequence  
    m2 = re.search(r'AboutHero.*?Footer', h, re.DOTALL)
    if m2:
        print(f"\n{fname} page structure snippet:")
        print(m2.group(0)[:1500])
