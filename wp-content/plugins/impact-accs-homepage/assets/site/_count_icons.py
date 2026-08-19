from pathlib import Path

t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
print("ImpactIcon count:", t.count("ImpactIcon"))
print("children:(0,t.jsx)(X,", t.count("children:(0,t.jsx)(X,"))
print("children:(0,t.jsx)(F,", t.count("children:(0,t.jsx)(F,"))

idx = t.find('name:"Graphite",Logo:')
print("\nGraphite logos block:", t[idx:idx+2000][:2000])

# list company names in n array
import re
for m in re.finditer(r'name:"([^"]+)",Logo:function', t):
    print("company logo:", m.group(1))
