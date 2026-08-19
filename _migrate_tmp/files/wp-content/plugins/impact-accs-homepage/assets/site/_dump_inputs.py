from pathlib import Path
t = Path("_next/static/chunks/f7f1c59a71681025.js").read_text(encoding="utf-8")
parts = []
for i in [123030, 127928, 130527, 133253, 135563]:
    parts.append(f"\n--- {i} ---\n{t[i:i+700]}\n")
Path("_f7f1_inputs.txt").write_text("".join(parts), encoding="utf-8")
