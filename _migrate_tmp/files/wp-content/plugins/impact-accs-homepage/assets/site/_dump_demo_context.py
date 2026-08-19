import sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

t = Path("_next/static/chunks/04bde6fc7adf5b08.js").read_text(encoding="utf-8")
idx = t.find("AnomalyAlertsDemo")
print("=== AnomalyAlertsDemo context ===")
print(t[idx-500:idx+3500])

print("\n\n=== ba5d7 conversational ===")
t2 = Path("_next/static/chunks/ba5d7afdb6dc00cc.js").read_text(encoding="utf-8")
idx2 = t2.find("ConversationalDebugDemo")
if idx2 < 0:
    idx2 = t2.find("sherwood")
print(t2[max(0,idx2-200):idx2+4000])

print("\n\n=== 9583d4 coding agents ===")
t3 = Path("_next/static/chunks/9583d4a1bf83f1e7.js").read_text(encoding="utf-8")
idx3 = t3.find("All agents supported")
print(t3[max(0,idx3-500):idx3+3500])
