import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

def find_all(text, needle):
    i = 0
    while True:
        j = text.find(needle, i)
        if j < 0:
            break
        print(f"  @{j}: ...{text[max(0,j-100):j+200]}...")
        i = j + 1

for fname in ["04bde6fc7adf5b08.js", "29c2c1c591d62005.js", "692acfebb5322696.js", "ba5d7afdb6dc00cc.js", "9583d4a1bf83f1e7.js", "827ff3490ba1793e.js"]:
    p = Path(f"_next/static/chunks/{fname}")
    t = p.read_text(encoding="utf-8")
    needles = ["Sherwood", "latency", "Kinesis", "View in Impact", "Start an Incident", "Root cause", "Recommended action", "Launch:", "when this became", "Found an issue", "Impact APP", "impactUser", "alertTitle", "shortTitle", "AGENCY", "MEDIA BUYING", "Manifesto", "SCALEGRID", 'x:"0"']
    hits = [n for n in needles if n in t]
    if hits:
        print(f"\n=== {fname} hits: {hits} ===")
        for n in hits[:8]:
            find_all(t, n)
