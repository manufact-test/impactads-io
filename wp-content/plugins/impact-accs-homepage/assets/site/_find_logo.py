import re
from pathlib import Path

t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
idx = t.find("Resource over noise")
# manifesto module - search backwards for function
chunk = t[idx-3000:idx+8000]

for pat in ["ImpactIcon", "logo", "svg", "Image", "onsen", "companyLogo", "Sazabi", "/assets/ia"]:
    if pat.lower() in chunk.lower():
        for m in re.finditer(re.escape(pat) if pat.startswith("/") else pat, chunk, re.I):
            print(pat, ":", chunk[max(0,m.start()-60):m.start()+120][:180])

# F component - integrations rounded - find Slack github
idx2 = t.find('variant:"rounded"')
print("\nF component area:", t[idx2-500:idx2+800][:1300])

# X component - find ImpactIcon in integration hub
idx3 = t.find("function X")
if idx3 < 0:
    idx3 = t.find("ImpactIcon")
print("\nX/ImpactIcon:", t[idx3:idx3+1500][:1500])
