from pathlib import Path

t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
idx = t.find("MANIFESTO")
chunk = t[idx:idx+25000]
print("ImpactIcon in manifesto chunk:", chunk.count("ImpactIcon"))
print("viewBox 0 0 45 40 in manifesto:", chunk.count('viewBox:"0 0 45 40"'))
print("data-logo in manifesto:", chunk.count("data-logo"))

# find manifesto right column logos render
i = chunk.find("data-logo")
print("\nfirst data-logo ctx:", chunk[i-200:i+600])

# K component - manifesto card
i2 = chunk.find("3xl:pr-32")
print("\npr-32 ctx:", chunk[i2-400:i2+800])
