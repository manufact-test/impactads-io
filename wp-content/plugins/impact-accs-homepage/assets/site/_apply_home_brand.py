"""Homepage brand texts — HTML + exact strings in d53 + 827ff only."""
from __future__ import annotations

import re
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parent
CHUNKS = ROOT / "_next/static/chunks"

# Curly apostrophe / em dash as in source files
_AP = "\u2019"
_EM = "\u2014"

HOME_JS_CHUNKS = ("d53e27b68750e6f9.js", "827ff3490ba1793e.js")

# Shared replacements (HTML + JS) — longest first
REPLACEMENTS: list[tuple[str, str]] = [
    # Corrupted manifesto line (prior bad replace)
    (
        f"Impact Inc., 2261 Market Street, STE 85391, San Francisco, California 94114 more than a tool {_EM} it\\'s a philosophy. We\\'re taking a radically different approach to observability.",
        f"impact.accs is more than a shop {_EM} it\\'s infrastructure for teams that run traffic.",
    ),
    (
        'children:"Impact Inc., 2261 Market Street, STE 85391, San Francisco, California 94114 more than a tool — it\'s a philosophy. We\'re taking a radically different approach to observability."',
        'children:"impact.accs is more than a shop — it\'s infrastructure for teams that run traffic."',
    ),
    (
        f"Impact Inc., 2261 Market Street, STE 85391, San Francisco, California 94114 more than a tool {_EM} it{_AP}s a philosophy. We{_AP}re taking a radically different approach to observability.",
        f"impact.accs is more than a shop {_EM} it{_AP}s infrastructure for teams that run traffic.",
    ),
    # Hero / meta
    (
        "Autonomous alerts. Conversational debugging. Coding agent integrations. Impact brings a new, AI-first approach to observability. Built for teams who ship fast.",
        "Closed access infrastructure for media buying teams. Working resource under volume. Built for teams who launch fast.",
    ),
    (
        f"Autonomous alerts. Conversational debugging. Coding agent integrations. impact.corp{_EM} a new, AI-first approach to observability. Built for teams who ship fast.",
        "Closed access infrastructure for media buying teams. Working resource under volume. Built for teams who launch fast.",
    ),
    (
        "The AI-native observability platform for fast-moving engineering teams. Ship with confidence.",
        "Closed access infrastructure for media buying teams. Accounts and access for launch, tests, and scale.",
    ),
    ("Stop Wondering", "Impact starts with access"),
    ("SAVE THE DAY", "REQUEST ACCESS"),
    ("Get Access", "Request access"),
    ("Join the Waitlist", "Request access"),
    # Top 3 feature cards (d53 + html)
    ("Autonomous Alerts", "Agency Accounts"),
    ("Conversational Debugging", "Media Buying Access"),
    ("Coding Agents Welcome", "Team Supply"),
    (
        'Rich, actionable alerts that "just work". No setup required. Get notified, but only when it matters.',
        "Agency accounts prepared for launch. Stable supply, clear terms. Replacement when the issue is on our side.",
    ),
    (
        "Interrogate your app in plain language. Stop digging through mountains of telemetry. Ask questions, get answers.",
        "Send a request in plain language. Get availability, terms, and delivery — not another sales pitch.",
    ),
    (
        "Claude Code, Codex, Cursor — whatever. Impact integrates tightly with all major coding agents.",
        "Facebook, Google, TikTok — whatever platform you run. impact.accs supplies access under your workflow.",
    ),
    # READY FOR ACTION
    (
        "Traditional observability tools make you work for answers. Impact works for you — anticipating needs, surfacing insights, and eliminating busywork.",
        "Random account shops make you hunt for supply. impact.accs is your resource layer — clear terms, fast contact, repeat orders.",
    ),
    (
        "From alert to root cause in seconds. Impact pinpoints the exact file, commit, or line of code responsible for any issue. No guessing, no git blame deep-dives.",
        "From request to delivery in one channel. Clear terms, fast contact, working resource — no hunting through random chats.",
    ),
    ("Code Search", "Agency Accounts"),
    (
        f"Don{_AP}t get lost in dashboards. Impact generates charts, tables, diagrams, and code blocks on the fly. Exactly what you need to see, and nothing more.",
        "No chaos in chats. You get what you asked for — access that matches the task, volume, and timeline.",
    ),
    ("Dynamic Visualizations", "Clear Delivery"),
    (
        "Always watching. Always learning. Past incidents and traffic patterns become institutional memory, with no manual work required.",
        "Five years on the market. Repeat orders, volume terms, and supply that matches your launch tempo.",
    ),
    ("Perfect Memory", "Team Supply"),
    (
        "One platform, dozens of data sources. Send logs in any format from any cloud or technology. Instrumentation is effortless, not invasive.",
        "One contact, multiple access categories. Agency accounts, platform access, and supply under volume.",
    ),
    ("INSTRUMENT IN MINUTES", "ACCESS IN MINUTES"),
    (
        "Observability that works where you do. Impact connects to your tools so incident response happens in the flow of work, not outside it.",
        "Access that works where you launch. impact.accs connects to your workflow — request, terms, delivery in one channel.",
    ),
    ("WORKS WITH YOUR APPS", "WORKS WITH YOUR TEAMS"),
    (
        f"Impact is more than a tool {_EM} it\\'s a philosophy. We\\'re taking a radically different approach to observability.",
        f"impact.accs is more than a shop {_EM} it\\'s infrastructure for teams that run traffic.",
    ),
    (
        f"Observability platforms have accumulated hundreds of features over the past decade. None of it makes systems more reliable. If observability is about answering questions, the best interface is chat {_EM} not dashboards, not config files, not training manuals.",
        "Account sellers have accumulated noise over the years. None of it makes launches faster. If access is infrastructure, the best interface is direct contact — not screenshots, not emojis, not random chats.",
    ),
    ("Monitoring is dead", "Chaos is optional"),
    ("Less is more", "Resource over noise"),
    (
        "Logs are all you need",
        "Working resource",
    ),
    (
        "Unstructured logs used to be a weakness. Now they're a strength. AI can process natural language at scale — finding patterns in logs that structured metrics could never capture.",
        "Random Telegram sellers used to be the norm. Structured supply is the strength — clear request, fast contact, working access under terms your team can trust.",
    ),
    (
        f"Static monitors are broken. They're tedious to maintain, constantly misfire, and train teams to ignore real alerts. The future is autonomous alerts {_EM} AI that watches continuously and only escalates what actually matters.",
        f"Random sellers are broken. Unstable supply and vague terms. The future is structured access {_EM} clear request, fast contact, working resource.",
    ),
    (
        "Enterprise-grade security built in, not bolted on. Impact's platform is compliant so your data stays protected at every layer.",
        "Closed infrastructure for teams that take access seriously. Structured process instead of random sellers.",
    ),
    (
        "Choose your storage region and data center. Meet data sovereignty requirements and ensure your observability data stays exactly where you need it.",
        "Agency accounts, platform access, and resources matched to your geo, vertical, and launch requirements.",
    ),
    (
        "Fine-grained access control for every team member. Set permissions, track access, maintain complete audit trails.",
        "Clear terms for every team member. Track requests, delivery, and repeat orders under one process.",
    ),
    ("BULLETPROOF SECURITY", "CLOSED INFRASTRUCTURE"),
    ("Data residency controls", "Geo & vertical match"),
    ("End-to-end encryption", "Verified supply"),
    ("RBAC", "Volume terms"),
    # Demo chat
    ("Intake: Kinesis Publish Timeout", "Launch: Account Request Pending"),
    (
        "The intake service is experiencing high error rates while attempting to publish logs to Kinesis.",
        "A launch window is open and the team needs agency accounts before traffic goes live.",
    ),
    (
        "Kinesis shard throughput limit exceeded due to a spike in log volume from the intake service.",
        "Volume request received — matching availability and terms for the team.",
    ),
    (
        "Scale up the Kinesis stream shard count and add retry backoff to the intake publisher.",
        "Confirm availability, agree terms, and prepare access for delivery.",
    ),
    ("View in Impact", "Request access"),
    ("Start an Incident", "Contact team"),
    # HTML entities
    (
        f"Impact is more than a tool {_EM} it&#x27;s a philosophy. We&#x27;re taking a radically different approach to observability.",
        f"impact.accs is more than a shop {_EM} it&#x27;s infrastructure for teams that run traffic.",
    ),
    (
        "Observability platforms have accumulated hundreds of features over the past decade. None of it makes systems more reliable. If observability is about answering questions, the best interface is chat — not dashboards, not config files, not training manuals.",
        "Account sellers have accumulated noise over the years. None of it makes launches faster. If access is infrastructure, the best interface is direct contact — not screenshots, not emojis, not random chats.",
    ),
    (
        "Ship with confidence.",
        "Accounts and access for launch, tests, and scale.",
    ),
    ('alt:"Impact observability dashboard"', 'alt:"impact.accs access panel"'),
    ("Impact | AI-Native Observability", "impact.accs | Closed Access Infrastructure"),
]
REPLACEMENTS.sort(key=lambda x: len(x[0]), reverse=True)


def apply(text: str) -> str:
    for old, new in REPLACEMENTS:
        if old != new and old in text:
            text = text.replace(old, new)
    return text


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
        raise RuntimeError(f"{name} syntax error: {r.stderr.strip()}")
    return True


def main() -> None:
    for fname in ("index.html", "index492c.html", "index89bf.html"):
        p = ROOT / fname
        if p.exists() and patch_html(p):
            print("html", fname)
    for name in HOME_JS_CHUNKS:
        if patch_js(name):
            print("js  ", name)
    print("done")


if __name__ == "__main__":
    main()
