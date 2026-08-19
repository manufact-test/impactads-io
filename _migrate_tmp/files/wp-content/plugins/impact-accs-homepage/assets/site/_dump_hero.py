from pathlib import Path
import re

def dump(fname, patterns):
    t = Path("_next/static/chunks") / fname
    s = t.read_text(encoding="utf-8")
    print(f"\n=== {fname} ({len(s)} chars) ===")
    for pat in patterns:
        for m in re.finditer(re.escape(pat) + r".{0,100}", s):
            print(repr(m.group()[:140]))
            break

dump("5308d2f8d20274da.js", [
    "5XX error rate", "847 users", "All systems operational",
    "P99 latency", "187ms", "4.1s", "/api/checkout", "/api/search",
    "No elevated error",
])
dump("f7f1c59a71681025.js", [
    "NullReferenceException", "CheckoutService", "Error Logs",
    "Spike correlates", "Rolling back", "Impact APP", "@Impact",
    "@Cursor", "Run @", "implement a fix", "9:11 AM", "9:17 AM",
    "5XX error",
])
dump("1e7f2c52e84d02fd.js", [
    "Join the Waitlist", "JOIN THE", "Careers", "AI-Native",
    "Request access", "Get Access",
])
dump("ba5d7afdb6dc00cc.js", ["Error Logs", "checkout", "5XX"])
dump("d53e27b68750e6f9.js", ["Join the Waitlist", "Careers", "Waitlist", "AI-Native"])
