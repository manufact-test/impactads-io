from pathlib import Path
t = Path("_next/static/chunks/f7f1c59a71681025.js").read_text(encoding="utf-8")
checks = [
    "#incidents", "PaymentService.ProcessCheckout", "null guard",
    "POST /api/search", "Payment App", "E-Commerce API", "@Sherwood",
    " Run ", "ErrorConversation", "ApiLatencyChart",
]
for s in checks:
    print(repr(s), t.count(s))
