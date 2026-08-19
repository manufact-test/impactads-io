import re
from pathlib import Path
t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
for m in re.finditer(r'children:"Impact Inc[^"]+"', t):
    print("children", repr(m.group()))
for m in re.finditer(r'description:"[^"]*observability[^"]*"', t):
    print("desc", repr(m.group()))
for m in re.finditer(r'description:"[^"]*autonomous alerts[^"]*"', t, re.I):
    print("auto", repr(m.group()[:220]))
