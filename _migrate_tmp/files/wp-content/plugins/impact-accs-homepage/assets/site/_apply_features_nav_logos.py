"""Feature page demos, Manifesto→Blog nav, investor logo centering."""
from __future__ import annotations

import re
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parent
CHUNKS = ROOT / "_next/static/chunks"

# --- shared text replacements (HTML outside script + JS) ---
REPLACEMENTS: list[tuple[str, str]] = [
    # Nav
    ('href:"/blog/manifesto",label:"Manifesto"', 'href:"/blog/manifesto",label:"Blog"'),
    ('label:"Manifesto",href:"/blog/manifesto"', 'label:"Blog",href:"/blog/manifesto"'),
    ('{label:"Manifesto",href:"/blog/manifesto"}', '{label:"Blog",href:"/blog/manifesto"}'),
    # Footer d53
    ('label:"Manifesto",href:"/blog/manifesto"}', 'label:"Blog",href:"/blog/manifesto"}'),
    # HTML header visible link text (SSR)
    (">Manifesto<", ">Blog<"),
    # AnomalyAlertsDemo component labels (04bde + hydration)
    ('{label:"View in Impact",variant:"primary"},{label:"Start an Incident"}',
     '{label:"Request access",variant:"primary"},{label:"Contact team"}'),
    ("View in Impact", "Request access"),
    ("Start an Incident", "Contact team"),
    ('children:["Impact",', 'children:["Request",'),
    ('"Root cause"', '"Terms"'),
    ('"Recommended action"', '"Next step"'),
    # impactUser bot name (shared chat modules)
    ('"impactUser",0,{name:"Impact",isBot:!0}', '"impactUser",0,{name:"impact.accs",isBot:!0}'),
    # Homepage features section chat (827ff)
    ("@Impact Can we determine when this became a problem?",
     "@impact.accs Can we confirm availability for this geo?"),
    ('Found an issue with the latency on ', 'Working resource matched for '),
    ('{children:"log-in"}', '{children:"EU launch"}'),
    ("@Impact When did it start?", "@impact.accs What's the delivery timeline?"),
    ("This started at 08:56 AM this morning",
     "Delivery window opens today at 14:00 — terms already locked."),
    ("Sherwood Callaway", "Denis A."),
    ('"name":"Impact","isBot":true', '"name":"impact.accs","isBot":true'),
    # 827ff alert overlay label (keep body — already themed)
    ('children:["Root cause",', 'children:["Terms",'),
    ('children:["Recommended action",', 'children:["Next step",'),
    # Conversational debug network graph (ba5d7)
    ("Why is the checkout API returning 500 errors?",
     "Do we have agency accounts ready for EU launch?"),
    ("Auth service not responding", "Terms still pending on volume request"),
    ("Deploy failed on staging", "Launch blocked — access not confirmed"),
    ("Latency spike on /api/payments", "Supply gap on TikTok batch"),
    ("Database connection pool exhausted", "Repeat order channel overloaded"),
    ("Memory leak in worker pods", "Random seller failed mid-delivery"),
    ("Rate limiter blocking valid requests", "GEO mismatch on last batch"),
    ("CI pipeline stuck for 20 minutes", "Team waiting on account handoff"),
    # Coding agents hero (9583d4)
    ("All agents supported", "All platforms covered"),
    ("@Claude Code", "@Facebook"),
    # Manifesto card CTA on homepage
    ('label:"Read Manifesto"', 'label:"Read Blog"'),
]

# Autonomous alerts — escaped JSON inside HTML RSC payload
ALERT_REPLACEMENTS: list[tuple[str, str]] = [
    (
        r'API: 500 Error Rate Spike',
        r'Launch: Account Request Pending',
    ),
    (
        r'Error rate jumped from 0.1% to 12% starting 8 minutes ago. ~340 users affected across checkout and profile endpoints.',
        r'A launch window is open and the team needs agency accounts before traffic goes live.',
    ),
    (
        r'Correlated with deploy v2.4.1 pushed 12 minutes ago. New Redis connection pooling configuration suspected.',
        r'Volume request received — matching availability and terms for the team.',
    ),
    (
        r'Roll back to v2.4.0 or increase Redis connection pool size from 10 to 50.',
        r'Confirm availability, agree terms, and prepare access for delivery.',
    ),
    (
        r'PostgreSQL: Query Latency Degradation',
        r'Volume: EU Agency Batch',
    ),
    (
        r'p99 latency increased from 45ms to 2.3s on orders table. Affecting checkout flow for ~120 active sessions over 15 minutes.',
        r'50 agency accounts requested for EU geo. Team needs confirmation before 18:00.',
    ),
    (
        r'Missing index on orders.customer_id. Sequential scan triggered by new filter added in PR #892.',
        r'Buyer profile matched to stable supply tier. Terms draft ready for review.',
    ),
    (
        r'Add index: CREATE INDEX CONCURRENTLY idx_orders_customer_id ON orders(customer_id)',
        r'Lock terms, confirm volume, and schedule delivery.',
    ),
    (
        r'Deploy Failed: payments-service v3.1.0',
        r'Supply Hold: Platform Access',
    ),
    (
        r'Deployment rolled back after health checks failed. 3 of 8 instances returned HTTP 503 for 2 minutes. No customer impact.',
        r'Handoff paused until buyer confirms GEO and vertical. No accounts released yet.',
    ),
    (
        r'Missing environment variable STRIPE_WEBHOOK_SECRET in production configuration. Added in code but not in infrastructure.',
        r'Buyer requested TikTok access but terms for replacement policy were not confirmed.',
    ),
    (
        r'Add secret to AWS Secrets Manager and redeploy.',
        r'Confirm replacement terms and resume delivery.',
    ),
    (
        r'Payment Webhooks: Processing Stalled',
        r'Repeat Order: Terms Update',
    ),
    (
        r'No webhooks processed in 23 minutes. 47 Stripe events pending. 12 customers awaiting order confirmation.',
        r'Repeat buyer needs updated volume terms before the next batch ships.',
    ),
    (
        r'SQS consumer Lambda hit concurrency limit. Dead letter queue receiving messages.',
        r'Previous terms expired — new volume tier requested for the same desk.',
    ),
    (
        r'Increase Lambda reserved concurrency from 10 to 50. Replay DLQ messages.',
        r'Agree updated terms and release the next account batch.',
    ),
    (
        r'Checkout: User Frustration Detected',
        r'Launch Risk: Supply Gap',
    ),
    (
        r'14 users abandoned checkout in last 10 minutes after repeated submit attempts. Rage click pattern detected.',
        r'Media buyer flagged unstable supply from a random seller — launch window closes in 4 hours.',
    ),
    (
        r'Support ticket #4521 opened 3 minutes ago: \\\"payment button does nothing.\\\" Submit handler silently failing due to null cartId.',
        r'Team lead opened a request: need working agency accounts before traffic goes live.',
    ),
    (
        r'Investigate CartContext hydration race condition in CheckoutForm.tsx:142.',
        r'Route request to impact.accs desk — confirm terms and prepare delivery.',
    ),
    (
        r'AWS: Unusual S3 Egress Spike',
        r'Cost Alert: Random Seller Markup',
    ),
    (
        r'Egress costs up 340% ($127/hr vs $28/hr baseline). Started 45 minutes ago. Projected daily overage: $2,376.',
        r'Buyer overpaid a random seller by 340% vs agreed impact.accs terms. Repeat order recommended.',
    ),
    (
        r'CloudFront cache invalidation at 8:33 AM caused cache miss storm on media-prod bucket. All requests hitting origin.',
        r'Unverified supply failed mid-campaign — team needs structured replacement under known terms.',
    ),
    (
        r'Verify CloudFront cache is repopulating. Consider origin shield if pattern repeats.',
        r'Switch to impact.accs supply channel — clear terms, verified delivery.',
    ),
]

CONV_REPLACEMENTS: list[tuple[str, str]] = [
    ("What errors are users hitting on the payments page?",
     "@impact.accs Do we have 50 agency accounts for EU launch?"),
    ("Found 3 distinct error patterns in the last hour: 67% are CARD_DECLINED from Stripe API — expected behavior.",
     "Availability confirmed: EU agency batch ready. Terms locked for 50 accounts."),
    ("28% are INVALID_POSTAL_CODE in ",
     "Remaining slots: "),
    ("PaymentForm.tsx:89", "EU geo"),
    ("5% are NETWORK_ERROR timeouts to api.stripe.com in ",
     "Delivery ETA: handoff today via "),
    ("ap-southeast-1", "direct channel"),
    ('Ed Carrel', 'Elena M.'),
    ("What changed before latency spiked at 3pm?",
     "What's the timeline for the TikTok batch?"),
    ("Two changes correlate with the spike at 15:00 UTC:",
     "Two items block delivery today:"),
    ("order-service v1.8.3", "TikTok access"),
    ("/orders", "volume tier"),
    ("query count went from 3 to 47.",
     "terms need buyer sign-off before release."),
    ("new-inventory-check", "replacement-policy"),
    ("enabled for 100% of traffic, adding an external API call per checkout.",
     "must be confirmed before accounts ship."),
    ('Henry Ventura', 'Team Lead'),
    ("Why are webhook deliveries failing?",
     "When can we confirm terms for the volume request?"),
    ("Traced the failure across 4 services:",
     "Status across your request:"),
    ("webhook-worker", "availability"),
    ("outbound-proxy", "terms"),
]

CODING_REPLACEMENTS: list[tuple[str, str]] = [
    ("Claude Code, Cursor, and every major coding agent can investigate production, trace errors, and debug live systems through native integrations.",
     "Facebook, Google, TikTok, and every major platform your team runs — through one structured channel."),
    ('impact messages send \\"Why are my checkout API calls timing out?\\" --wait',
     'impact.accs request \\"50 EU agency accounts for launch\\" --wait'),
    ("Analyzing your observability", "Matching availability and terms"),
    ("checkout API calls timing out", "agency accounts for EU launch"),
]

REPLACEMENTS.sort(key=lambda x: len(x[0]), reverse=True)


def center_logos(text: str) -> str:
    pairs = [
        ('viewBox:"0 0 132 20"', 'viewBox:"0 0 132 20"', "66"),
        ('viewBox:"0 0 142 20"', "71"),
        ('viewBox:"0 0 128 20"', "64"),
        ('viewBox:"0 0 138 20"', "69"),
    ]
    # Replace each logo text element: x:"0" → x:"{half}", add textAnchor
    specs = [
        (132, "66", "SCALEGRID"),
        (142, "71", "LAUNCHDESK"),
        (128, "64", "ADVOLUME"),
        (138, "69", "TRAFFICLAB"),
    ]
    for width, cx, label in specs:
        old = (
            f'viewBox:"0 0 {width} 20",fill:"currentColor",xmlns:"http://www.w3.org/2000/svg",'
            f'children:(0,t.jsx)("text",{{x:"0",y:"16",fontSize:"14",fontWeight:"600",'
            f'fontFamily:"ui-monospace,monospace",letterSpacing:"0.08em",children:"{label}"}})'
        )
        new = (
            f'viewBox:"0 0 {width} 20",fill:"currentColor",xmlns:"http://www.w3.org/2000/svg",'
            f'children:(0,t.jsx)("text",{{x:"{cx}",y:"16",textAnchor:"middle",fontSize:"14",fontWeight:"600",'
            f'fontFamily:"ui-monospace,monospace",letterSpacing:"0.08em",children:"{label}"}})'
        )
        if old in text:
            text = text.replace(old, new)
        else:
            print(f"  warn: logo snippet missing for {label}")
    return text


def apply_list(text: str, reps: list[tuple[str, str]]) -> str:
    for old, new in reps:
        if old != new and old in text:
            text = text.replace(old, new)
    return text


def patch_html_file(path: Path, extra: list[tuple[str, str]] | None = None, *, full_file: bool = False) -> bool:
    raw = path.read_text(encoding="utf-8")
    if full_file and extra:
        new = apply_list(apply_list(raw, REPLACEMENTS), extra)
        if new != raw:
            path.write_text(new, encoding="utf-8")
            return True
        return False
    parts = re.split(r"(<script[\s\S]*?</script>)", raw, flags=re.IGNORECASE)
    out: list[str] = []
    changed = False
    for i, part in enumerate(parts):
        if i % 2 == 1:
            out.append(part)
            continue
        new = apply_list(part, REPLACEMENTS)
        if extra:
            new = apply_list(new, extra)
        if new != part:
            changed = True
        out.append(new)
    if changed:
        path.write_text("".join(out), encoding="utf-8")
    return changed


def patch_js(name: str, *, logos: bool = False, extra: list[tuple[str, str]] | None = None) -> None:
    path = CHUNKS / name
    if not path.exists():
        print(f"skip {name} (missing)")
        return
    raw = path.read_text(encoding="utf-8")
    new = center_logos(raw) if logos else raw
    new = apply_list(new, REPLACEMENTS)
    if extra:
        new = apply_list(new, extra)
    if new == raw:
        print(f"skip {name}")
        return
    path.write_text(new, encoding="utf-8")
    r = subprocess.run(["node", "--check", str(path)], capture_output=True, text=True)
    if r.returncode != 0:
        path.write_text(raw, encoding="utf-8")
        raise RuntimeError(f"{name}: {r.stderr.strip()}")
    print(f"ok   {name}")


def main() -> None:
    html_changed = 0
    for html in ROOT.rglob("*.html"):
        extra = None
        if "autonomous-alerts" in html.name:
            extra = ALERT_REPLACEMENTS
        elif "conversational-debugging" in html.name:
            extra = CONV_REPLACEMENTS
        elif "coding-agents-welcome" in html.name:
            extra = CODING_REPLACEMENTS
        if patch_html_file(html, extra, full_file=extra is not None):
            html_changed += 1
            print(f"ok   {html.relative_to(ROOT)}")

    patch_js("1e7f2c52e84d02fd.js")
    patch_js("d53e27b68750e6f9.js")
    patch_js("827ff3490ba1793e.js", logos=True)
    patch_js("04bde6fc7adf5b08.js")
    patch_js("ba5d7afdb6dc00cc.js")
    patch_js("9583d4a1bf83f1e7.js")
    patch_js("29c2c1c591d62005.js")
    patch_js("692acfebb5322696.js")
    patch_js("0476edb0af9ab771.js")

    print(f"done ({html_changed} html files)")


if __name__ == "__main__":
    main()
