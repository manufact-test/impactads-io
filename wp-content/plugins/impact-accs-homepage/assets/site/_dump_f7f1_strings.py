import re
from pathlib import Path

t = Path("_next/static/chunks/f7f1c59a71681025.js").read_text(encoding="utf-8")

# find conversation exports
for name in ["ErrorConversation", "WarningConversation", "OperationalConversation", "function ErrorConversation", "ErrorConversation=function"]:
    print(name, t.find(name))

# all unique string literals > 10 chars
all_s = re.findall(r'"([^"\\]{10,200})"', t)
seen = set()
for s in all_s:
    if s in seen:
        continue
    seen.add(s)
    low = s.lower()
    if any(k in low for k in ["latency", "error", "log", "impact", "cursor", "kinesis", "checkout", "incident", "ticket", "on-call", "p99", "api", "notify", "engineer", "merrill", "hadley", "health", "deploy", "spike", "endpoint", "payment", "search", "operational", "uptime", "available", "request", "account", "supply", "launch", "volume", "terms", "delivery", "agency", "eu"]):
        print(repr(s))

# API Latency in svg tspan
for pat in ["API Latency", "p99", "Here's the", "notify", "on-call", "page the", "Run @", "show me"]:
    i = t.find(pat)
    if i >= 0:
        print(f"\n--- {pat} ---")
        print(repr(t[i-80:i+200]))
