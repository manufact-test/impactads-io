from pathlib import Path

t = Path("_next/static/chunks/1e7f2c52e84d02fd.js").read_text(encoding="utf-8")
for s in ["impact.accs", "Initializing", "Impact System", "Impact is"]:
    i = t.find(s)
    print(s, i)
    if i >= 0:
        print(repr(t[i-80:i+120]))
