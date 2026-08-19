import re, json
from pathlib import Path

h = Path("about.html").read_text(encoding="utf-8")
scripts = re.findall(r'<script[^>]*>(.*?)</script>', h, re.DOTALL)
s = scripts[46]
print("starts:", s[:200])
print("ends:", s[-200:])

# decode push payload
m = re.search(r'self\.__next_f\.push\(\[1,"((?:[^"\\]|\\.)*)"\]\)', s)
if m:
    raw = m.group(1)
    decoded = raw.encode('utf-8').decode('unicode_escape')
    print("\ndecoded len", len(decoded))
    print("has segment 28", '28:["$","section"' in decoded)
    i = decoded.find('28:["$","section"')
    j = decoded.find('\n29:', i)
    print("segment 28 len", j-i if j>i else "no end")
    print("segment 6 snippet:", decoded[decoded.find('6:["$"'):decoded.find('6:["$"')+400])
