from pathlib import Path
import re

t = Path("index.html").read_text(encoding="utf-8")
for pat in ["Initializing", "Impact System", "Loading access", "impact.accs system", "Loading", "preloader", "data-preloader"]:
    print(pat, t.count(pat))

for m in re.finditer(r'.{0,30}(Initializing|Impact System|Loading access|impact\.accs system).{0,30}', t):
    print(repr(m.group(0)))

# check 1e7f2 preloader
t2 = Path("_next/static/chunks/1e7f2c52e84d02fd.js").read_text(encoding="utf-8")
for pat in ["Initializing", "Impact System", "Loading access", "impact.accs system"]:
    print("1e7f2", pat, t2.count(pat))

# fe865 preloader
for fname in ["fe86549c3883d530.js", "8e1e9e7a85fc0466.js"]:
    p = Path(f"_next/static/chunks/{fname}")
    if p.exists():
        t3 = p.read_text(encoding="utf-8")
        for pat in ["Initializing", "Impact System", "Loading", "PulseLine", "preloader"]:
            c = t3.count(pat)
            if c:
                print(fname, pat, c)
