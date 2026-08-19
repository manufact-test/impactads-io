from pathlib import Path
t = Path("_next/static/chunks/d53e27b68750e6f9.js").read_text(encoding="utf-8")
i = t.find('"features",0,[')
Path("_d53_features.txt").write_text(t[i : i + 1500], encoding="utf-8")
