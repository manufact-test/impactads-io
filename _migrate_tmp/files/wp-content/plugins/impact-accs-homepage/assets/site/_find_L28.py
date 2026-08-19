import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

h = Path("about.html").read_text(encoding="utf-8")
chunks = re.findall(r'self\.__next_f\.push\(\[1,"([^"]*(?:\\.[^"]*)*)"\]\)', h)
full = "\n".join(c.encode().decode('unicode_escape') for c in chunks)

idx = full.find("$L28")
print(full[idx-200:idx+600])

# find definition of 28:
m = re.search(r'28:\[', full)
if m:
    print("\n28 definition:", full[m.start():m.start()+500])

# index page main children for comparison
h2 = Path("index.html").read_text(encoding="utf-8")
chunks2 = re.findall(r'self\.__next_f\.push\(\[1,"([^"]*(?:\\.[^"]*)*)"\]\)', h2)
full2 = "\n".join(c.encode().decode('unicode_escape') for c in chunks2)
m2 = re.search(r'\["\$","\$L\d+",null,\{\}\].*?\$L\d+.*?\]', full2[:50000])
# find main children
m3 = re.search(r'6:\["\$","\$1","c",\{"children":\[\["\$","main"', full2)
if m3:
    print("\nindex main:", full2[m3.start():m3.start()+1500])
