import re
from pathlib import Path

for name in ["5308d2f8d20274da.js", "f7f1c59a71681025.js"]:
    t = Path(f"_next/static/chunks/{name}").read_text(encoding="utf-8")
    print(f"\n=== {name} ===")
    # find alert config arrays
    for pat in [r'alertTitle:"[^"]+"', r'id:"[^"]+"', r'variant:"[^"]+"', r'name:"[^"]+"']:
        ms = re.findall(pat, t)
        if ms:
            print(pat, len(ms), ms[:20])

# extract full alert objects from 5308
t3 = Path("_next/static/chunks/5308d2f8d20274da.js").read_text(encoding="utf-8")
idx = t3.find("alertTitle:")
while idx != -1:
    print("\nCTX5308:", repr(t3[max(0,idx-200):idx+400]))
    idx = t3.find("alertTitle:", idx+1)
    if idx > 0 and t3.count("alertTitle:") > 4:
        break

# f7f1 tower conversation blocks
t4 = Path("_next/static/chunks/f7f1c59a71681025.js").read_text(encoding="utf-8")
for s in ["Merrill Lutsky", "Delivery Log", "VolumeRequestPending", "AgencyAccounts", "profilePicture", "What's available"]:
    i = t4.find(s)
    if i >= 0:
        print(f"\nF7F1 {s}:", repr(t4[max(0,i-150):i+250]))
