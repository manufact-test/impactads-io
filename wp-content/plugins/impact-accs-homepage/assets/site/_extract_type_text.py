import re
from pathlib import Path

t = Path("_next/static/chunks/f7f1c59a71681025.js").read_text(encoding="utf-8")
lines = []
idx = 0
while True:
    i = t.find('"data-type-text":!0,children:', idx)
    if i < 0:
        break
    lines.append(t[i : i + 500])
    idx = i + 1

Path("_type_text_out.txt").write_text("\n---\n".join(lines), encoding="utf-8")
print(len(lines))
