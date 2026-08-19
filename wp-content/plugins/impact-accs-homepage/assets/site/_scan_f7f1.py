import re
from pathlib import Path

t = Path("_next/static/chunks/f7f1c59a71681025.js").read_text(encoding="utf-8")
# strings that look like UI copy (3+ words or tech terms)
for m in re.finditer(r'"([^"\\]{8,120})"', t):
    s = m.group(1)
    if any(k in s.lower() for k in ["error", "latency", "checkout", "kinesis", "deploy", "incident", "observ", "cursor", "health", "api/", "exception", "rollback", "spike", "log"]):
        print(repr(s))
