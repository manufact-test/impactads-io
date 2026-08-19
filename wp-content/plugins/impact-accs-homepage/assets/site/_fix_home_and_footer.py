"""Fix footer/preloader + homepage HTML texts from brand brief. JS: only 2 footer chunks, exact strings."""
from __future__ import annotations

import re
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parent
CHUNKS = ROOT / "_next/static/chunks"

# --- Footer / preloader: restore impact.corp® (not SF legal entity) ---
FOOTER_JS = [
    (
        "Impact Inc., 2261 Market Street, STE 85391, San Francisco, California 94114",
        "impact.corp\u00ae",
    ),
]

# --- Homepage-only HTML replacements (skip scripts) ---
HOME_REPLACEMENTS: list[tuple[str, str]] = [
    # Revert broken preloader HTML from prior script
    ("Loading access", "Initializing"),
    ("impact.accs system", "Impact System"),
    # Meta / title
    ("Impact | AI-Native Observability", "impact.accs | Closed Access Infrastructure"),
    ("Impact | About", "impact.accs | About"),
    ("Impact | Blog", "impact.accs | Blog"),
    (
        "Autonomous alerts. Conversational debugging. Coding agent integrations. impact.corp\u00ae a new, AI-first approach to observability. Built for teams who ship fast.",
        "Closed access infrastructure for media buying teams. Agency accounts, platform access, and supply under volume.",
    ),
    (
        "Autonomous alerts. Conversational debugging. Coding agent integrations. Impact brings a new, AI-first approach to observability. Built for teams who ship fast.",
        "Closed access infrastructure for media buying teams. Agency accounts, platform access, and supply under volume.",
    ),
    (
        "impact.corp\u00ae a new, AI-first approach to observability. Built for teams who ship fast.",
        "Closed access infrastructure for media buying teams. Built for teams who launch fast.",
    ),
    # Hero / nav labels in SSR HTML
    # Hero (brand brief block 1)
    (
        "The AI-native observability platform for fast-moving engineering teams.",
        "Closed access infrastructure for media buying teams.",
    ),
    (
        "Ship with confidence.",
        "Accounts and access for launch, tests, and scale.",
    ),
    ("Impact starts with access", "Impact starts with access"),
    ("SAVE THE DAY", "REQUEST ACCESS"),
    ("Get Access", "Request access"),
    ("Join the Waitlist", "Request access"),
    ("Autonomous Alerts", "Agency Accounts"),
    ("Conversational Debugging", "Media Buying Access"),
    ("Coding Agents Welcome", "Team Supply"),
    # Feature cards (homepage SSR)
    (
        "Rich, actionable alerts that &quot;just work&quot;. No setup required. Get notified, but only when it matters.",
        "Agency accounts prepared for launch. Stable supply, clear terms, replacement when the issue is on our side.",
    ),
    (
        "Interrogate your app in plain language. Stop digging through mountains of telemetry. Ask questions, get answers.",
        "Send a request in plain language. Get availability, terms, and delivery — not another sales pitch.",
    ),
    (
        "Claude Code, Codex, Cursor — whatever. Impact integrates tightly with all major coding agents.",
        "Facebook, Google, TikTok — whatever platform you run. impact.accs supplies access under your team\u2019s workflow.",
    ),
    # READY FOR ACTION section
    (
        "Traditional observability tools make you work for answers. Impact works for you — anticipating needs, surfacing insights, and eliminating busywork.",
        "Random account shops make you hunt for supply. impact.accs works as your resource layer — clear terms, fast contact, repeat orders.",
    ),
    # Feature blocks
    (
        "From alert to root cause in seconds. Impact pinpoints the exact file, commit, or line of code responsible for any issue. No guessing, no git blame deep-dives.",
        "From request to delivery in one channel. Clear terms, fast contact, working resource — no hunting through random chats.",
    ),
    ("Code Search", "Agency Accounts"),
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
        "Impact is more than a tool — it&#x27;s a philosophy. We&#x27;re taking a radically different approach to observability.",
        "impact.accs is more than a shop — it&#x27;s infrastructure for teams that run traffic.",
    ),
    (
        "impact.accs is more than a shop — it&#x27;s infrastructure for teams that run traffic.",
        "impact.accs is more than a shop — it&#x27;s infrastructure for teams that run traffic.",
    ),
    (
        "Observability platforms have accumulated hundreds of features over the past decade. None of it makes systems more reliable. If observability is about answering questions, the best interface is chat — not dashboards, not config files, not training manuals.",
        "Account sellers have accumulated noise over the years. None of it makes launches faster. If access is infrastructure, the best interface is direct contact — not screenshots, not emojis, not random chats.",
    ),
    ("Monitoring is dead", "Chaos is optional"),
    ("Less is more", "Resource over noise"),
    (
        "Enterprise-grade security built in, not bolted on. Impact&#x27;s platform is compliant so your data stays protected at every layer.",
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
    # Footer HTML (not JS)
    ("Copyright \u00a9 2026 Impact.", "Copyright \u00a9 2026 impact.accs."),
    ("Copyright \u00a9 2026 impact.accs.", "Copyright \u00a9 2026 impact.accs."),
    ("legal@impact.ai", "contact@impact.accs"),
    ("privacy@impact.ai", "contact@impact.accs"),
    ("2261 Market Street, STE 85391, San Francisco, California 94114", "impact.corp\u00ae"),
    ("Impact Inc., 2261 Market Street, STE 85391, San Francisco, California 94114", "impact.corp\u00ae"),
    ("Careers", "Contact"),
    ("Waitlist", "Access"),
    ("SAN FRANCISCO", "REMOTE"),
    # Demo chat in HTML SSR
    ("Intake: Kinesis Publish Timeout", "Launch: Account Request Pending"),
    (
        "Scale up the Kinesis stream shard count and add retry backoff to the intake publisher.",
        "Confirm availability, agree terms, and prepare access for delivery.",
    ),
    (
        "The intake service is experiencing high error rates while attempting to publish logs to Kinesis.",
        "A launch window is open and the team needs agency accounts before traffic goes live.",
    ),
]
HOME_REPLACEMENTS.sort(key=lambda x: len(x[0]), reverse=True)

# Do NOT touch these in _apply_accs_text_safe global replacements for footer/preloader
SKIP_GLOBAL_IN_HOME = {
    "Initializing",
    "Impact System",
    "impact.corp\u00ae",
    "impact.corp",
}


def patch_footer_js(name: str) -> bool:
    path = CHUNKS / name
    raw = path.read_text(encoding="utf-8")
    new = raw
    for old, repl in FOOTER_JS:
        new = new.replace(old, repl)
    if new == raw:
        return False
    path.write_text(new, encoding="utf-8")
    r = subprocess.run(["node", "--check", str(path)], capture_output=True, text=True)
    if r.returncode != 0:
        path.write_text(raw, encoding="utf-8")
        raise RuntimeError(f"{name}: {r.stderr.strip()}")
    return True


def patch_home_html(path: Path) -> bool:
    raw = path.read_text(encoding="utf-8")
    parts = re.split(r"(<script[\s\S]*?</script>)", raw, flags=re.IGNORECASE)
    out: list[str] = []
    changed = False
    for i, part in enumerate(parts):
        if i % 2 == 1:
            out.append(part)
            continue
        new = part
        for old, repl in HOME_REPLACEMENTS:
            if old in new:
                new = new.replace(old, repl)
                changed = True
        out.append(new)
    if changed:
        path.write_text("".join(out), encoding="utf-8")
    return changed


def patch_all_html_footer() -> None:
    """Fix SF legal address in footer across all pages."""
    repl = [
        (
            "Impact Inc., 2261 Market Street, STE 85391, San Francisco, California 94114",
            "impact.corp\u00ae",
        ),
        (
            "2261 Market Street, STE 85391, San Francisco, California 94114",
            "impact.corp\u00ae",
        ),
        ("Loading access", "Initializing"),
        ("impact.accs system", "Impact System"),
    ]
    for path in sorted(ROOT.glob("**/*.html")):
        if path.name.startswith("_"):
            continue
        raw = path.read_text(encoding="utf-8")
        parts = re.split(r"(<script[\s\S]*?</script>)", raw, flags=re.IGNORECASE)
        out, changed = [], False
        for i, part in enumerate(parts):
            if i % 2 == 1:
                out.append(part)
                continue
            new = part
            for old, r in repl:
                if old in new:
                    new = new.replace(old, r)
                    changed = True
            out.append(new)
        if changed:
            path.write_text("".join(out), encoding="utf-8")
            print("footer", path.relative_to(ROOT))


def main() -> None:
    patch_all_html_footer()
    for name in ("d53e27b68750e6f9.js", "1e7f2c52e84d02fd.js"):
        if patch_footer_js(name):
            print("js ", name)
    for fname in ("index.html", "index492c.html", "index89bf.html"):
        p = ROOT / fname
        if p.exists() and patch_home_html(p):
            print("html", fname)


if __name__ == "__main__":
    main()
