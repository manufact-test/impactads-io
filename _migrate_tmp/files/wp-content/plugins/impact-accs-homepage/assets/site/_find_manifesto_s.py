from pathlib import Path

t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")

# find manifesto card K component - uses Badge ref c with label scramble
# find where red S appears - search for X( or ImpactIcon in manifesto render after function es
idx = t.find("function es()")
chunk = t[idx:idx+20000]

# find all jsx with svg or Icon in manifesto card K
for pat in ["ImpactIcon", "v.ImpactIcon", "function X(", "61254", "Badge,{children:(0,t.jsx)(\"span\",{ref:c})"]:
    i = chunk.find(pat)
    print(pat, i)
    if i >= 0:
        print(repr(chunk[i:i+400])[:400])

# K component - manifesto card wrapper
idx2 = t.find("function K({children:e,cta:r,arrows:a")
print("\nK component:", repr(t[idx2:idx2+2500])[:2500])
