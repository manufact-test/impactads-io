from pathlib import Path
import re

t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
patterns = [
    "Stop Wondering", "SAVE THE DAY", "Rich, actionable", "Interrogate your",
    "Claude Code", "Traditional observability", "From alert to root cause",
    "Always watching", "One platform, dozens", "Observability that works",
    "Impact is more than", "impact.accs is more than", "Observability platforms",
    "Monitoring is dead", "Enterprise-grade", "Choose your storage",
    "Static monitors", "Fine-grained access", "READY FOR ACTION",
    "Code Search", "Perfect Memory", "Dynamic Visualizations",
    "Less is more", "Intake: Kinesis", "View in Impact",
]
for pat in patterns:
    i = t.find(pat)
    if i < 0:
        print("MISSING:", pat)
        continue
    # grab until next ",image: or ",children: or similar break
    end = i
    while end < min(i + 350, len(t)) and t[end] != '"' and (end - i < 5 or t[end-1] != '"'):
        end += 1
    # simpler: find closing quote after opening context
    snippet = t[i : i + 280]
    q = snippet.find('",')
    if q > 0:
        snippet = snippet[: q + 1]
    print("---", pat)
    print(repr(snippet))
