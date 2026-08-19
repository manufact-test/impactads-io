from pathlib import Path
import re
t = Path("_next/static/chunks/f7f1c59a71681025.js").read_text(encoding="utf-8")
# find ErrorConversation or hero-related strings
keywords = [
    "NullReference", "CheckoutService", "Spike correlates", "Rolling back",
    "implement a fix", "Error Logs", "Impact APP", "342 health",
    "Payment App", "E-Commerce", "Kinesis", "intake service",
    "p99 latency", "Ticket INFRA", "Sherwood", "show me the",
]
for kw in keywords:
    if kw.lower() in t.lower():
        i = t.lower().find(kw.lower())
        # get surrounding context as readable snippet
        sn = t[max(0,i-30):i+len(kw)+80]
        sn = sn.replace("\n", " ")
        print(kw, "=>", repr(sn[:160]))
