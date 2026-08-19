import sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

t = Path("_next/static/chunks/0476edb0af9ab771.js").read_text(encoding="utf-8")
for needle in ["about-founder", "ABOUT IMPACT", "about-mission", "about-vision", "about-history", "src:", "image:", "webp", "jpg", "png", "hero", "founder", "Footer", "SiteFooter"]:
    idx = 0
    n = 0
    while n < 3:
        j = t.find(needle, idx)
        if j < 0: break
        print(f"\n[{needle}@{j}]")
        print(t[j-60:j+200])
        idx = j + 1
        n += 1
