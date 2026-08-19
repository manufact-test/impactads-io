from pathlib import Path

CORP = "impact.corp\u00ae"
for fname in ["d53e27b68750e6f9.js", "1e7f2c52e84d02fd.js"]:
    t = Path(f"_next/static/chunks/{fname}").read_text(encoding="utf-8")
    print(f"\n=== {fname} ===")
    for s in [CORP, "RIGHTS RESERVED", "ai-native", "2026", "data-text", "data-scramble"]:
        print(f"  {s!r}: {t.count(s)}")
    i = t.find("data-text")
    if i >= 0:
        print("  ctx:", repr(t[i:i+350]))
