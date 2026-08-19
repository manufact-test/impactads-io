import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent
path = ROOT / "_next/static/chunks/8e1e9e7a85fc0466.js"
text = path.read_text(encoding="utf-8")

for pat in ["nulll", "PulseLine", 'logo"===m', "})}):null", "})}):nulll"]:
    print(pat, text.count(pat))

idx = text.find('e.s(["PulseLine"')
print("PulseLine idx", idx)
if idx >= 0:
    print(text[idx : idx + 4000])
