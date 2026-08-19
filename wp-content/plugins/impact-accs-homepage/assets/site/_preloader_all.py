from pathlib import Path
import re

t = Path("index.html").read_text(encoding="utf-8")
# find preloader block
i = t.find("Initializing")
print("HTML preloader:", repr(t[i-200:i+300]))

t2 = Path("_next/static/chunks/1e7f2c52e84d02fd.js").read_text(encoding="utf-8")
for m in re.finditer(r'.{0,40}(Initializing|Impact System|Loading access|impact\.accs).{0,40}', t2):
    print("1e7f2:", repr(m.group(0)))

# search all chunks for Loading access
for p in Path("_next/static/chunks").glob("*.js"):
    c = p.read_text(encoding="utf-8", errors="ignore")
    if "Loading access" in c or "impact.accs system" in c:
        print(p.name, "Loading access", c.count("Loading access"), "impact.accs system", c.count("impact.accs system"))
