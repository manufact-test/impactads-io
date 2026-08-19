import re
from pathlib import Path

for fname in ["f7f1c59a71681025.js", "5308d2f8d20274da.js", "827ff3490ba1793e.js"]:
    t = Path(f"_next/static/chunks/{fname}").read_text(encoding="utf-8")
    print("===", fname)
    for m in re.finditer(r'(?:children:|title:|description:|alertTitle:|alertDescription:|resolvedTitle:|resolvedDescription:)"([^"]{4,100})"', t):
        s = m.group(1)
        if not s.startswith("/") and "jsx" not in s and "className" not in s:
            print(" ", s[:90])
