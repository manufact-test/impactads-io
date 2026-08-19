import sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

t = Path("_next/static/chunks/0476edb0af9ab771.js").read_text(encoding="utf-8")
start = t.find('20416,e=>')
end = t.find('},49846,e=>', start)
if end < 0:
    end = start + 15000
chunk = t[start:end+20]
print(f"Module 20416 length: {len(chunk)}")
print(chunk[:4000])
print("\n--- MID ---\n")
print(chunk[4000:8000])
print("\n--- END ---\n")
print(chunk[-2000:])
