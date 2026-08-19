from pathlib import Path
import re

t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
i = t.find("let n=[")
print("logos:", t[i:i+400])
print("count:", t[i:t.find("];function i")].count("name:"))

t2 = Path("_next/static/chunks/1e7f2c52e84d02fd.js").read_text(encoding="utf-8")
print("Access left:", t2.count('label:"Access"'))
print("Accounts:", t2.count('label:"Accounts"'))
print("FEATURES btn:", "FEATURES" in t2 and 'children:["ACCOUNTS"' in t2)

t3 = Path("_next/static/chunks/5308d2f8d20274da.js").read_text(encoding="utf-8")
print("ErrorConversation:", "ErrorConversation" in t3)
print("children:null count:", t3.count("children:null"))
for m in re.finditer(r'alertTitle:"([^"]+)"', t3):
    print(" alert:", m.group(1))
