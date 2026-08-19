import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

# Find feature page layout chunk - search all chunks for FeatureHero or similar
for p in Path("_next/static/chunks").glob("*.js"):
    t = p.read_text(encoding="utf-8", errors="replace")
    if "FeatureDetail" in t or "FeaturePage" in t or "features/autonomous" in t:
        print(p.name, "FeaturePage/Detail")
    if "alerts:[" in t or "impact:" in t and "rootCause" in t:
        print(p.name, "alert data")
    if "messages:[" in t and "Sherwood" in t:
        print(p.name, "chat messages")
