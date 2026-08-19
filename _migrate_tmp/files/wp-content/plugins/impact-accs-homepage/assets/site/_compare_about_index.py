import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

for fname in ["index.html", "about.html"]:
    h = Path(fname).read_text(encoding="utf-8")
    # nav links
    for m in re.finditer(r'>(Manifesto|Blog|About|Features|Request access|Accounts)[^<]*<', h):
        print(f"{fname} nav: {m.group(0)}")
    # header class
    m = re.search(r'transition-\[translate\][^"]*"', h)
    if m: print(f"{fname} header: {m.group(0)[:120]}")
    # footer
    for kw in ["READY FOR ACTION", "impact.corp", "RIGHTS RESERVED", "Footer"]:
        if kw in h:
            print(f"{fname} has {kw}")
        else:
            print(f"{fname} NO {kw}")
    # scripts
    chunks = re.findall(r'chunks/([a-f0-9]+)\.js', h)
    print(f"{fname} unique chunks: {len(set(chunks))}")
    print(f"  827ff: {'827ff3490ba1793e' in h}")
    print()
