from pathlib import Path
import re

t = Path("index.html").read_text(encoding="utf-8")
for pat in ["Ship with confidence", "AI-native", "Closed access", "fast-moving"]:
    i = t.find(pat)
    print(pat, i, repr(t[i-50:i+120]) if i >= 0 else "NOT FOUND")

# check if inside script
for m in re.finditer(r"Ship with confidence", t):
    pos = m.start()
    before = t[:pos]
    in_script = before.rfind("<script") > before.rfind("</script")
    print("in_script?", in_script)
