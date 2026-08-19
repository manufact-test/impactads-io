import re
from pathlib import Path

root = Path(r"c:\Users\olga-\OneDrive\Desktop\Это\public_html")
chunks = root / "_next" / "static" / "chunks"

markers = {
    "ImpactIcon export": 'e.s(["ImpactIcon"',
    "48x44 chevron (M6.92931)": "M6.92931 24.0949",
    "20x20 hex icon (M3.5625)": "M3.5625 5.85156",
    "PulseLine logo": '"logo"===m?',
    "full IMPACT wordmark SVG": 'viewBox:"0 -1000 5310 1000"',
    "ia logo viewBox 100": 'viewBox:"0 0 100 100"',
    "top frame logo 324x52": 'viewBox="0 0 324 52"',
    "group/logo in html": "group/logo",
}

print("=== JS chunks ===")
for f in sorted(chunks.glob("*.js")):
    text = f.read_text(encoding="utf-8", errors="ignore")
    hits = [name for name, m in markers.items() if m in text]
    if hits:
        print(f"{f.name}: {', '.join(hits)}")

print("\n=== HTML files ===")
html_hits = {}
for f in root.rglob("*.html"):
    if "_next" in str(f):
        continue
    text = f.read_text(encoding="utf-8", errors="ignore")
    hits = [name for name, m in markers.items() if m in text]
    if hits:
        html_hits[str(f.relative_to(root))] = hits

for path, hits in sorted(html_hits.items()):
    print(f"{path}: {', '.join(hits)}")

# find component names importing ImpactIcon
print("\n=== ImpactIcon usage patterns in JS ===")
for f in chunks.glob("*.js"):
    text = f.read_text(encoding="utf-8", errors="ignore")
    if "ImpactIcon" not in text:
        continue
    for m in re.finditer(r'.{0,40}ImpactIcon.{0,60}', text):
        s = m.group(0).replace("\n", " ")
        if "e.s([" not in s or "ImpactIcon" in s and "e.s" not in s[:20]:
            print(f"{f.name}: ...{s}...")
