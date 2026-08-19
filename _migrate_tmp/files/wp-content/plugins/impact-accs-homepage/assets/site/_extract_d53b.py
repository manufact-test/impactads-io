import re
from pathlib import Path
t = Path("_next/static/chunks/d53e27b68750e6f9.js").read_text(encoding="utf-8")
Path("_d53_all.txt").write_text("\n".join(sorted(set(m.group(1) for m in re.finditer(r'description:"([^"]+)"', t)))), encoding="utf-8")
Path("_d53_labels.txt").write_text("\n".join(sorted(set(m.group(1) for m in re.finditer(r'label:"([^"]+)"', t)))), encoding="utf-8")
