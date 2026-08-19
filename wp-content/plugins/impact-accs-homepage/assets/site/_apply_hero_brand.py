"""Hero section — impact.accs texts in 5308, f7f1, 1e7f2 (+ index HTML header)."""
from __future__ import annotations

import re
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parent
CHUNKS = ROOT / "_next/static/chunks"

HERO_JS = (
    "5308d2f8d20274da.js",
    "f7f1c59a71681025.js",
    "1e7f2c52e84d02fd.js",
)

REPLACEMENTS: list[tuple[str, str]] = [
    # --- 3D city floating alerts (5308) ---
    (
        "5XX error rate on /api/checkout",
        "Launch blocked — accounts needed",
    ),
    (
        "847 users couldn't complete checkout in the last 10m",
        "Team needs agency accounts before traffic goes live",
    ),
    ("Checkout error resolved", "Access confirmed"),
    ("Error rate back to normal", "Accounts delivered under agreed terms"),
    (
        "P99 latency spike on POST /api/search",
        "Volume request — GEO: EU",
    ),
    (
        "Response time jumped from 187ms to 4.1s",
        "50 agency accounts needed before 18:00",
    ),
    ("Latency stabilized", "Supply matched"),
    (
        "Response time back to normal",
        "Terms agreed — delivery in progress",
    ),
    ("All systems operational", "Supply stable"),
    (
        "No elevated error rates detected",
        "Repeat order channel active — terms unchanged",
    ),
    ("Health check confirmed", "Status confirmed"),
    (
        "All services running normally",
        "Working resource ready for next launch",
    ),
    # --- Hero chat / chart (f7f1) ---
    ("Error Logs", "Delivery Log"),
    ("NullReferenceException", "VolumeRequestPending"),
    ("CheckoutService", "AgencyAccounts"),
    ("Spike correlates with deploy at ", "Terms confirmed at "),
    ("Rolling back now.", "Preparing delivery now."),
    (" What's causing the spike?", " What's available for EU?"),
    (
        " and implement a fix for this issue",
        " availability and terms for this launch",
    ),
    (
        "342 health checks completed in the last hour",
        "5 years supplying access — repeat orders active",
    ),
    ('children:"@Impact"', 'children:"@impact.accs"'),
    ('children:"@Cursor"', 'children:"@supply"'),
    ('name:"Impact"', 'name:"impact.accs"'),
    ("Join the Waitlist", "Request access"),
    ("Start an Incident", "Contact team"),
    (
        "The intake service is experiencing high error rates while attempting to publish logs to Kinesis.",
        "A launch window is open — the team needs agency accounts before traffic goes live.",
    ),
    (
        " show me the p99 latency trend for this endpoint for the last month.",
        " confirm availability for EU and volume terms.",
    ),
    ("Ticket INFRA-4821 created and assigned to on-call.", "Request logged — matching supply and terms."),
    ("#incidents", "#requests"),
    ("PaymentService.ProcessCheckout()", "AgencyAccounts.RequestEU()"),
    (
        "It will add a null guard before the gateway call and open a PR.",
        "Terms confirmed. Delivery scheduled before the launch window.",
    ),
    ("POST /api/search", "EU · 50 accounts"),
    ("Payment App", "Media buy"),
    ("E-Commerce API", "Agency pool"),
    ("@Sherwood", "@team"),
    # --- Header nav (1e7f2 + d53 overlap via separate file) ---
    ("Autonomous Alerts", "Agency Accounts"),
    ("Conversational Debugging", "Media Buying Access"),
    ("Coding Agents Welcome", "Team Supply"),
    ("Careers", "Contact"),
    ("JOIN THE WAITLIST", "REQUEST ACCESS"),
    # --- HTML header / meta ---
    ("Impact | AI-Native Observability", "impact.accs | Closed Access Infrastructure"),
    ("Get Access", "Request access"),
    ("Join Waitlist", "Request access"),
]
# Remove noop
REPLACEMENTS = [(a, b) for a, b in REPLACEMENTS if a != b]
REPLACEMENTS.sort(key=lambda x: len(x[0]), reverse=True)

HTML_FILES = ("index.html", "index492c.html", "index89bf.html")


def apply(text: str) -> str:
    for old, new in REPLACEMENTS:
        if old in text:
            text = text.replace(old, new)
    return text


def patch_js(name: str) -> bool:
    path = CHUNKS / name
    raw = path.read_text(encoding="utf-8")
    new = apply(raw)
    if new == raw:
        return False
    path.write_text(new, encoding="utf-8")
    r = subprocess.run(["node", "--check", str(path)], capture_output=True, text=True)
    if r.returncode != 0:
        path.write_text(raw, encoding="utf-8")
        raise RuntimeError(f"{name}: {r.stderr.strip()}")
    return True


def patch_html(path: Path) -> bool:
    raw = path.read_text(encoding="utf-8")
    parts = re.split(r"(<script[\s\S]*?</script>)", raw, flags=re.IGNORECASE)
    out: list[str] = []
    changed = False
    for i, part in enumerate(parts):
        if i % 2 == 1:
            out.append(part)
            continue
        new = apply(part)
        if new != part:
            changed = True
        out.append(new)
    if changed:
        path.write_text("".join(out), encoding="utf-8")
    return changed


def main() -> None:
    for fname in HTML_FILES:
        p = ROOT / fname
        if p.exists() and patch_html(p):
            print("html", fname)
    for name in HERO_JS:
        if patch_js(name):
            print("js  ", name)
    # footer nav labels on homepage header chunk overlap
    if patch_js("d53e27b68750e6f9.js"):
        print("js  ", "d53e27b68750e6f9.js")
    print("done")


if __name__ == "__main__":
    main()
