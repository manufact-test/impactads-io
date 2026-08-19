import sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

for fname in ["1e7f2c52e84d02fd.js", "827ff3490ba1793e.js"]:
    t = Path(f"_next/static/chunks/{fname}").read_text(encoding="utf-8")
    for pat in ["Manifesto", "blog/manifesto", "let n=[", "SCALEGRID", 'x:"0"', "textAnchor"]:
        if pat in t:
            i = t.find(pat)
            print(f"\n{fname} [{pat}]")
            print(t[i-60:i+400])
