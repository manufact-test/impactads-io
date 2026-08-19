from pathlib import Path
t = Path("features/conversational-debugging.html").read_text(encoding="utf-8")
idx = 0
while True:
    j = t.find("Sherwood", idx)
    if j < 0:
        break
    print(repr(t[j-30:j+80]))
    idx = j + 1

print("\nView in Impact:", t.count("View in Impact"))
print("Denis:", t.count("Denis A."))
