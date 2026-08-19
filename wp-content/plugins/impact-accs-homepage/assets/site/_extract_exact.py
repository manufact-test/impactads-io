from pathlib import Path

t = Path("_next/static/chunks/5308d2f8d20274da.js").read_text(encoding="utf-8")
for needle in ["children:!y&&", "ErrorConversation", "WarningConversation", "OperationalConversation"]:
    i = t.find(needle)
    print(needle, i)
    if i >= 0:
        print(repr(t[i:i+120]))

t2 = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
i = t2.find("let n=[")
print("\n827 n array end:", repr(t2[i:i+200]))

t3 = Path("_next/static/chunks/1e7f2c52e84d02fd.js").read_text(encoding="utf-8")
for s in ['label:"Access"', '["FEATURES"', 'children:"Features"']:
    print(s, t3.count(s), repr([t3.find(s), t3[t3.find(s)-20:t3.find(s)+40] if t3.find(s)>=0 else ""]))
