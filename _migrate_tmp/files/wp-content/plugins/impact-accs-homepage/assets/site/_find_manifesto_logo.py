from pathlib import Path
import re

t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")

# manifesto carousel area
idx = t.find("Resource over noise")
chunk = t[idx-5000:idx+15000]

print("ia-logo", chunk.count("ia-logo"))
print("ImpactIcon", chunk.count("ImpactIcon"))

# find red S svg paths in manifesto - viewBox 0 0 45 40
for pat in ['viewBox:"0 0 45 40"', "M6.44003", "function X(", "61254", "26044"]:
    print(pat, "in manifesto area:", pat in chunk, "full:", t.count(pat))

# search manifesto card right side - Badge ref c, pr-48
for m in re.finditer(r'pr-48|pr-64|absolute right|data-logo|Logo,', chunk):
    pass

# find module 61254 - ImpactIcon import
i = t.find("61254")
print("61254 ctx:", t[i:i+200] if i>=0 else "none")

# search all img src in manifesto section
for m in re.finditer(r'src:"[^"]+"|src:\`/[^`]+\`', chunk):
    print("src", m.group(0)[:80])

# find let n= impact.accs logo strip
i2 = t.find('let n=[{name:"impact.accs"')
print("\nlogo strip:", repr(t[i2:i2+400]) if i2>=0 else "not found")

# search for svg path that looks like S/chevron in manifesto function es
i3 = chunk.find("function es")
print("\nfunction es at", i3)
if i3 >= 0:
    print(chunk[i3:i3+3000][:3000])
