from pathlib import Path

t = Path("_next/static/chunks/1e7f2c52e84d02fd.js").read_text(encoding="utf-8")
i = t.find("Initializing")
print(t[i-600:i+400])
