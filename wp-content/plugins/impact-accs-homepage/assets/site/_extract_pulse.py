from pathlib import Path

s = Path(r"c:\Users\olga-\OneDrive\Desktop\Это\public_html\_next\static\chunks\8e1e9e7a85fc0466.js").read_text(
    encoding="utf-8", errors="ignore"
)
for marker in ['"logo"===m', '"logo"===m?', "shield\"===m"]:
    i = s.find(marker)
    print("marker", marker, "at", i)
    if i >= 0:
        print(s[i : i + 4000])
        print("---")
