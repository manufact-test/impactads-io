import re
from pathlib import Path

for fname in ["f7f1c59a71681025.js", "5308d2f8d20274da.js"]:
    t = Path(f"_next/static/chunks/{fname}").read_text(encoding="utf-8")
    print(f"\n=== {fname} ===")
    strings = set(re.findall(r'(?:children|title|alertTitle|alertDescription|resolvedTitle|resolvedDescription|name|placeholder|label):"([^"]{3,120})"', t))
    keywords = ["latency", "error", "log", "request", "account", "supply", "launch", "volume", "eu", "impact", "notify", "ticket", "engineer", "p99", "api", "delivery", "terms", "agency", "merrill", "hadley", "send", "you", "on-call", "chart", "pending"]
    for s in sorted(strings):
        if any(k in s.lower() for k in keywords):
            print(" ", repr(s))
    print("ErrorConversation", "ErrorConversation" in t)
    print("children:null", t.count("children:null"))
    print("children:!y&&", t.count("children:!y&&"))
