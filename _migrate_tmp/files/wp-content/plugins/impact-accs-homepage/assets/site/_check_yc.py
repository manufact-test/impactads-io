from pathlib import Path
t = Path("blog/yc-p26.html").read_text(encoding="utf-8")
idx = 0
while True:
    j = t.find("Sherwood", idx)
    if j < 0:
        break
    print(repr(t[j:j+60]))
    idx = j + 1
