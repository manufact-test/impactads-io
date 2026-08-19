from pathlib import Path

t = Path("_next/static/chunks/1e7f2c52e84d02fd.js").read_text(encoding="utf-8")
for s in ["RIGHTS RESERVED", "2026", "impact.corp", "data-scramble"]:
    print(s, t.count(s))

# find footer scramble block
i = t.find("RIGHTS RESERVED")
print(repr(t[i-300:i+500]))
