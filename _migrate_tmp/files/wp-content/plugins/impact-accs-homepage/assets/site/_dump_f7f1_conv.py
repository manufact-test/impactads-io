import re
from pathlib import Path

t = Path("_next/static/chunks/f7f1c59a71681025.js").read_text(encoding="utf-8")
out = []

for marker in ["function y({errorChartVisible", "function b({latencyChartVisible", "function x({uptimeChartVisible", "uptimeChartVisible"]:
    i = t.find(marker)
    if i >= 0:
        out.append(f"\n=== {marker} at {i} ===\n")
        out.append(t[i:i+3000])

Path("_f7f1_conv_dump.txt").write_text("\n".join(out), encoding="utf-8")
print("written", len(out))
