import re
from pathlib import Path

t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")

for pat in [
    'children:(0,t.jsx)(X,{integrations:Q,className:"w-[340px] sm:w-[380px] md:w-[400px] lg:w-[440px] xl:w-[460px]"})',
    'children:(0,t.jsx)(F,{variant:"rounded",className:"w-[340px] sm:w-[380px] md:w-[400px] lg:w-[420px] xl:w-[440px]"})',
]:
    print(pat[:60], "...", t.count(pat))

idx = t.find("Resource over noise")
print("\nmanifesto:", repr(t[idx-800:idx+1200])[:2000])

# investor in manifesto
for m in re.finditer(r'investor:\{[^}]+\}', t):
    print("inv obj", m.group(0)[:200])

# search logo svg paths - hameni, sazabi-like
for m in re.finditer(r'viewBox:"[^"]*"[^}]{0,500}path', t[:50000]):
    pass

# all /assets/ paths
assets = set(re.findall(r'"/assets/[^"]+"', t))
for a in sorted(assets):
    print(a)
