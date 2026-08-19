import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

def extract_alerts(html_path):
    t = html_path.read_text(encoding="utf-8")
    m = re.search(r'"alerts":\[(.*?)\],"tabIcons"', t)
    if not m:
        m = re.search(r'\\"alerts\\":\[(.*?)\]', t)
    if m:
        print("ALERTS found, len", len(m.group(1)))
        # print first 3000 chars
        print(m.group(1)[:3000])
    else:
        idx = t.find("recommendedAction")
        print("fallback at", idx)
        print(t[idx-500:idx+1500])

def extract_conversations(html_path):
    t = html_path.read_text(encoding="utf-8")
    m = re.search(r'"conversations":\[\[(.*?)\]\]', t)
    if m:
        print("CONV len", len(m.group(1)))
        print(m.group(1)[:4000])
    else:
        idx = t.find("Sherwood")
        print(t[idx-100:idx+2000])

for p in Path("features").glob("autonomous-alerts.html"):
    print("===", p, "===")
    extract_alerts(p)

for p in Path("features").glob("conversational-debugging.html"):
    print("\n===", p, "===")
    extract_conversations(p)

for p in Path("features").glob("coding-agents-welcome.html"):
    t = p.read_text(encoding="utf-8")
    print("\n===", p, "===")
    for pat in ["Sherwood", "Claude", "agents supported", "All agents", "conversations", "demos"]:
        if pat in t:
            i = t.find(pat)
            print(pat, ":", t[i-40:i+200])
