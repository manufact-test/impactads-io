import sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

t = Path("_next/static/chunks/0476edb0af9ab771.js").read_text(encoding="utf-8")
# Find AboutDescription function - search for export AboutDescription
idx = t.find('e.s(["AboutDescription",()=>p])')
# walk back to find function p=
start = t.rfind("function p(", 0, idx)
print("AboutDescription function start:", start)
print(t[start:start+500])
print("\n--- END ---")
# find closing of main return - search last sections before export
end = t.find('e.s(["AboutDescription",()=>p])')
print(t[end-800:end+100])
