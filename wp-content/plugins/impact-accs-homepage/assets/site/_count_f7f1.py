from pathlib import Path
t = Path("_next/static/chunks/f7f1c59a71681025.js").read_text(encoding="utf-8")
checks = [
    "NullReferenceException", "CheckoutService", "Error Logs",
    " and implement a fix for this issue", " What's causing the spike?",
    "Spike correlates with deploy at ", "Rolling back now.",
    'children:"@Impact"', 'children:"@Cursor"',
    'name:"Impact"', "Join the Waitlist", "Start an Incident",
    "342 health checks", "P99 latency spike on POST /api/search",
    "The intake service is experiencing",
]
for s in checks:
    print(repr(s), t.count(s))
