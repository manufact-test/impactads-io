from pathlib import Path

t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
idx = t.find('name:"Graphite",Logo:')
Path("_graphite_block.txt").write_text(t[idx:idx+25000], encoding="utf-8")
print("written", idx)

# ImpactIcon contexts
for i in range(t.count("ImpactIcon")):
    pos = t.find("ImpactIcon", t.find("ImpactIcon") + i if i else 0)
    if i == 0:
        pos = t.find("ImpactIcon")
    else:
        pos = t.find("ImpactIcon", pos + 1 if i else 0)
    
idxs = []
start = 0
while True:
    p = t.find("ImpactIcon", start)
    if p < 0: break
    idxs.append(p)
    start = p + 1
for p in idxs:
    print("\n--- ImpactIcon at", p)
    print(t[p-150:p+200])
