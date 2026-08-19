from pathlib import Path

t = Path("_next/static/chunks/d53e27b68750e6f9.js").read_text(encoding="utf-8")
idx = t.find("data-text")
print(t[idx-200:idx+400])

t2 = Path("_next/static/chunks/1e7f2c52e84d02fd.js").read_text(encoding="utf-8")
idx2 = t2.find("data-scramble")
print("\n1e7f2:", t2[idx2-200:idx2+400])
