from pathlib import Path
t = Path("_next/static/chunks/692acfebb5322696.js").read_text(encoding="utf-8")
for pat in ["Kinesis", "@Impact", "@Cursor", "Sherwood", "intake service", "error rates"]:
    i = t.find(pat)
    if i >= 0:
        print(pat, repr(t[i-20:i+120]))
