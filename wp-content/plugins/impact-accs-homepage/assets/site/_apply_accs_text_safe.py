"""Safe impact.accs text — HTML (no scripts) + whitelisted JS string literals only."""
from __future__ import annotations

import re
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parent
CHUNKS_DIR = ROOT / "_next/static/chunks"

# Never modify branding / posthog / form validation chunks
JS_SKIP = {
    "8e1e9e7a85fc0466.js",
    "fe86549c3883d530.js",
    "a77e907734f4b424.js",
    "694b218a672794b8.js",
    "a6dad97d9634a72d.js",
}

JS_ALLOW = {
    "d53e27b68750e6f9.js",
    "827ff3490ba1793e.js",
    "0476edb0af9ab771.js",
    "692acfebb5322696.js",
    "f7f1c59a71681025.js",
    "29df6672875b8547.js",
    "9583d4a1bf83f1e7.js",
    "ba5d7afdb6dc00cc.js",
    "ac0d4738355e77b1.js",
    "1e7f2c52e84d02fd.js",
    "29c2c1c591d62005.js",
}

# Curly apostrophe as in exported Next.js strings
_AP = "\u2019"

REPLACEMENTS: list[tuple[str, str]] = [
    # Fix prior overly broad "Impact is" -> "impact.accs" replacements
    (
        "Autonomous alerts. Conversational debugging. Coding agent integrations. impact.accs a new, AI-first approach to observability. Built for teams who ship fast.",
        "Agency accounts. Media buying access. Team supply. Closed access infrastructure for teams who launch fast.",
    ),
    (
        "impact.accs more than a tool — it\'s a philosophy. We\'re taking a radically different approach to observability.",
        "impact.accs is more than a shop — it\'s infrastructure for teams that run traffic.",
    ),
    ("OBSERVABILITY TO, AND USE OF", "ACCESS TO, AND USE OF"),
    (
        "Observability that works where you do. Impact connects to your tools so incident response happens in the flow of work, not outside it.",
        "Access that works where you launch. impact.accs connects to your workflow so requests, terms, and delivery happen in one channel.",
    ),
    (
        "Observability platforms have accumulated hundreds of features over the past decade. None of it makes systems more reliable. If observability is about answering questions, the best interface is chat — not dashboards, not queries, not tickets.",
        "Account sellers have accumulated noise over the years. None of it makes launches faster. If access is infrastructure, the best interface is direct contact — not screenshots, not emojis, not random chats.",
    ),
    ("Intake: Kinesis Publish Timeout", "Launch: Account Request Pending"),
    ("View in Impact", "Request access"),
    ("Start an Incident", "Contact team"),
    (
        f"Don{_AP}t get lost in dashboards. Impact generates charts, tables, diagrams, and code blocks on the fly. Exactly what you need to see, and nothing more.",
        "No chaos in chats. You get what you asked for — access that matches the task, volume, and timeline.",
    ),
    ("Dynamic Visualizations", "Clear Delivery"),
    ("Less is more", "Resource over noise"),
    ('label:"Claude Code"', 'label:"Facebook"'),
    ('mention:"@Claude Code"', 'mention:"@Facebook"'),
    ('label:"Cursor"', 'label:"Google"'),
    ('mention:"@Cursor"', 'mention:"@Google"'),
    ('label:"Codex"', 'label:"TikTok"'),
    ('mention:"@Codex"', 'mention:"@TikTok"'),
    ("3. Monitoring Is Dead", "3. Chaos Is Optional"),
    ("Monitoring Is Dead", "Chaos Is Optional"),
    (
        "AI has changed what's possible in software systems. Observability hasn't caught up. This manifesto lays out a path forward.",
        "Media buying teams move faster than most suppliers can keep up. This manifesto lays out how impact.accs thinks about access.",
    ),
    (
        f"AI has changed what{_AP}s possible in software systems. Observability hasn{_AP}t caught up. This manifesto lays out a path forward.",
        f"Media buying teams move faster than most suppliers can keep up. This manifesto lays out how impact.accs thinks about access.",
    ),
    (">OBSERVABILITY</span>", ">ACCESS</span>"),
    (
        f"We{_AP}re taking a radically different approach to observability.",
        "We build closed access infrastructure for teams that run traffic.",
    ),
    (
        "impact.accs a new, AI-first approach to observability. Built for teams who ship fast.",
        "Closed access infrastructure for media buying teams. Built for teams who launch fast.",
    ),
    # Footer / global
    (
        "The AI-native observability platform for fast-moving engineering teams. Ship with confidence.",
        "Closed access infrastructure for media buying teams. Accounts and access for launch, tests, and scale.",
    ),
    ("Ship with confidence.", "Working resource. No noise."),
    ("SAVE THE DAY", "REQUEST ACCESS"),
    ("Stop Wondering", "Impact starts with access"),
    ("Get Access", "Request access"),
    ("Get Waitlist", "Request access"),
    ("Join the Waitlist", "Request access"),
    ("Join Waitlist", "Request access"),
    ("Sign up for early access to Impact.", "Request access to impact.accs."),
    ("Impact Waitlist Credential", "impact.accs access request"),
    ("We'll be in touch when it's your turn.", "We'll respond with terms and availability."),
    ("impact.corp", "impact.corp"),
    ("ai-native", "closed access"),
    # Meta / titles
    ("Impact | AI-Native Observability", "impact.accs | Closed Access Infrastructure"),
    ("Impact | About", "impact.accs | About"),
    ("Impact | Blog", "impact.accs | Blog"),
    ("Impact | Autonomous Alerts", "impact.accs | Agency Accounts"),
    ("Impact | Conversational Debugging", "impact.accs | Media Buying Access"),
    ("Impact | Coding Agents Welcome", "impact.accs | Team Supply"),
    (
        "Autonomous alerts. Conversational debugging. Coding agent integrations. Impact brings a new, AI-first approach to observability. Built for teams who ship fast.",
        "Agency accounts. Media buying access. Team supply. Closed access infrastructure for teams who launch fast.",
    ),
    (
        "Observability is broken. We're here to fix it. Learn about Impact's philosophy and meet the team.",
        "Closed account infrastructure for media buying teams. Learn how impact.accs helps teams launch faster.",
    ),
    (
        "Observability is broken. We&#x27;re here to fix it. Learn about Impact&#x27;s philosophy and meet the team.",
        "Closed account infrastructure for media buying teams. Learn how impact.accs helps teams launch faster.",
    ),
    (
        "Engineering insights, product updates, and deep dives from the Impact team.",
        "Notes on access, supply, and building infrastructure for media buying teams.",
    ),
    # Nav / features
    ("Autonomous Alerts", "Agency Accounts"),
    ("Conversational Debugging", "Media Buying Access"),
    ("Coding Agents Welcome", "Team Supply"),
    ("All agents supported", "All major platforms"),
    # Feature blurbs (footer + pages)
    (
        "Rich, actionable alerts that ",
        "Reliable account supply that ",
    ),
    (
        "Claude Code, Codex, Cursor — whatever. Impact integrates tightly with all major coding agents.",
        "Facebook, Google, TikTok — whatever platform you run. impact.accs supplies access under your team's workflow.",
    ),
    (
        "Interrogate your app in plain language. Stop digging through mountains of telemetry. Ask questions, get answers.",
        "Send a request in plain language. Get availability, terms, and delivery — not another sales pitch.",
    ),
    (
        "A persistent AI agent inside your comms channel. Ask what's failing. It responds with live system state and pinpoints anomalies before they become incidents.",
        "Agency accounts prepared for media buying workflows. Stable supply, clear terms, and replacement when the issue is on our side.",
    ),
    (
        "AI agents that understand your infrastructure. Adaptive UI that reconfigures per anomaly, surfacing only what matters, so your team can fix it faster.",
        "Access infrastructure built for teams that scale. Volume terms, fast contact, and supply that matches your launch tempo.",
    ),
    # Home carousel
    (
        "Choose your storage region and data center. Meet data sovereignty requirements and ensure your observability data stays exactly where you need it.",
        "Agency accounts, platform access, and resources matched to your geo, vertical, and launch requirements.",
    ),
    (
        f"Don{_AP}t get lost in dashboards. Impact generates charts, tables, diagrams, and code blocks on the fly. Exactly what you need to see, and nothing more.",
        "No chaos in chats. You get what you asked for — access that matches the task, volume, and timeline.",
    ),
    (
        "Enterprise-grade security built in, not bolted on. Impact's platform is compliant so your data stays protected at every layer.",
        "Closed infrastructure for teams that take access seriously. Structured process instead of random sellers.",
    ),
    (
        "Static monitors are broken. They're tedious to maintain, constantly misfire, and train teams to ignore real alerts. The future is autonomous alerts — AI that watches continuously and only escalates what actually matters.",
        "Random sellers are broken. Unstable supply and vague terms. The future is structured access — clear request, fast contact, working resource.",
    ),
    (
        "Traditional observability tools make you work for answers. Impact works for you — anticipating needs, surfacing insights, and eliminating busywork.",
        "Random account shops make you hunt for supply. impact.accs works as your resource layer — clear terms, fast contact, repeat orders.",
    ),
    (
        f"Impact is more than a tool — it\\'s a philosophy. We\\'re taking a radically different approach to observability.",
        "impact.accs is more than a shop — it\'s infrastructure for teams that run traffic.",
    ),
    ("Monitoring is dead", "Chaos is optional"),
    ("observability platform", "access infrastructure"),
    # About page
    ("ABOUT IMPACT", "ABOUT impact.accs"),
    (
        "Values shape culture. Culture shapes products. These six principles are the foundation of how we build Impact.",
        "Values shape culture. Culture shapes service. These six principles are the foundation of how we build impact.accs.",
    ),
    (
        "Our team founded the infrastructure and observability teams at",
        "Our team spent years in account supply and media buying before building",
    ),
    (
        "We fell in love with the promise of observability — the idea that you could truly understand and control complex systems. But we also witnessed firsthand the",
        "We saw how much launch speed depends on access — teams move fast when they have the right resource. But we also witnessed firsthand the",
    ),
    (
        "The dashboards. The alert fatigue. The 3am pages that could have been prevented.",
        "The chat hunting. The bad supply. The launches delayed by unreliable sellers.",
    ),
    (
        "In 2025, everything changed. We found ourselves building",
        "Over five years in the market, we built",
    ),
    ("mind-blowing agentic software products", "a reliable supply channel"),
    (
        "and using futuristic AI coding tools like Cursor and Claude Code. But observability still felt painfully manual, ",
        "for teams running traffic at scale. But account supply still felt painfully chaotic, ",
    ),
    ("stuck in the past", "stuck in Telegram noise"),
    (
        "We're building the observability platform we always wanted: conversational, intelligent, and built for teams who ship fast.",
        "We're building the access infrastructure we always wanted: closed, fast, and built for teams who launch.",
    ),
    (
        " for companies of all sizes during the AI era. Not by adding AI features to legacy tools, but by rethinking observability from first principles — conversational, intelligent, and built for teams who move fast.",
        " for media buying teams that need speed and stability. Structured access infrastructure — clear, fast, and built for teams who launch.",
    ),
    ("default observability solution", "default access channel"),
    (
        "impact.accs more than a tool — it&#x27;s a philosophy. We&#x27;re taking a radically different approach to observability.",
        "impact.accs is more than a shop — it&#x27;s infrastructure for teams that run traffic.",
    ),
    (
        "Observability platforms have accumulated hundreds of features over the past decade. None of it makes systems more reliable. If observability is about answering questions, the best interface is chat — not dashboards, not config files, not training manuals.",
        "Account sellers have accumulated noise over the years. None of it makes launches faster. If access is infrastructure, the best interface is direct contact — not screenshots, not emojis, not random chats.",
    ),
    (
        "impact.accs joining the market&#x27;s P26 batch as we continue building the future of AI-native observability.",
        "impact.accs has spent five years supplying accounts and access to media buying teams.",
    ),
    (
        "Impact is joining Y Combinator&#x27;s P26 batch as we continue building the future of AI-native observability.",
        "impact.accs has spent five years supplying accounts and access to media buying teams.",
    ),
    (
        "access infrastructure for AI, not just humans. Claude Code, Cursor, and every major coding agent can investigate production, trace errors, and debug live systems through native integrations.",
        "Closed access for teams, not random chats. Facebook, Google, TikTok, and every major platform your team runs — through one structured channel.",
    ),
    ("title=\"Claude Code\"", 'title="Facebook"'),
    ("@Claude Code", "@Facebook"),
    ("invisible\">@Claude Code", "invisible\">@Facebook"),
    (
        "Scale up the Kinesis stream shard count and add retry backoff to the intake publisher.",
        "Confirm availability, agree terms, and prepare access for delivery.",
    ),
    ("Sherwood Callaway", "Team Lead"),
    ("Brex", "the market"),
    # Demo chat (home)
    (
        "The intake service is experiencing high error rates while attempting to publish logs to Kinesis.",
        "A launch window is open and the team needs agency accounts before traffic goes live.",
    ),
    (
        "Kinesis shard throughput limit exceeded due to a spike in log volume from the intake service.",
        "Volume request received — matching availability and terms for the team.",
    ),
    ("@Impact Can we determine when this became a problem?", "@impact.accs Can we confirm availability for this geo?"),
    ("@Impact can you tell @Cursor to increase timeout on the ", "@impact.accs preparing replacement access for the "),
    ("sherwood-callaway", "impact-accs"),
    # Blog
    ("Impact's Journal", "impact.accs journal"),
    ("The Impact Manifesto", "The impact.accs Manifesto"),
    ("By Impact Team", "By impact.accs team"),
    ("Impact Team", "impact.accs team"),
    ("Impact Is Part of Y Combinator P26", "Five Years in the Market"),
    (
        "Impact is joining Y Combinator's P26 batch as we continue building the future of AI-native observability.",
        "impact.accs has spent five years supplying accounts and access to media buying teams — quietly, without the noise.",
    ),
    (
        "Impact is joining Y Combinator&#x27;s P26 batch as we continue building the future of AI-native observability.",
        "impact.accs has spent five years supplying accounts and access to media buying teams — quietly, without the noise.",
    ),
    (
        "Today we're announcing that Impact is part of Y Combinator's P26 batch. We're building AI-native observability for engineering teams who want answers, not more bells and whistles.",
        "impact.accs has been supplying accounts and access to media buying teams for five years. We work as a resource layer: no noise, clear communication, practical results.",
    ),
    (
        "Over the next few months we'll be heads down on the product, expanding our early customer base, and growing the team. If you're an engineer who cares about reliability, AI, and developer experience, we're hiring.",
        "We continue expanding supply and working with teams that need volume and speed. If your team runs traffic and needs a reliable access channel, get in touch.",
    ),
    (
        "This isn't our first time in the program. Impact founder Sherwood Callaway previously went through YC as a cofounder of Opkit in the S21 cohort, alongside Impact founding engineer Justin Ko.",
        "Our team has been in this market for years. We know what buyers need: speed, predictability, and a supplier who understands the work.",
    ),
    (
        "To our early users and design partners: thank you. We're building this for you.",
        "To the teams we work with: thank you. We build this for teams that move fast.",
    ),
    # Legal
    # Legal — do not touch footer entity line (handled in _fix_home_and_footer.py)
    ("legal@impact.ai", "contact@impact.accs"),
    ("privacy@impact.ai", "contact@impact.accs"),
    ("Copyright © 2026 Impact.", "Copyright © 2026 impact.accs."),
    (
        "means the observability and log management services Impact provides under the Principal Agreement;",
        "means the account and access infrastructure services impact.accs provides under the Principal Agreement;",
    ),
    (
        "Impact Processes Company Personal Data as necessary to provide the Services, including ingestion, storage, analysis, and display of log data and observability telemetry submitted by Company through Impact&#x27;s platform.",
        "impact.accs processes Company Personal Data as necessary to provide the Services, including account requests, contact details, delivery records, and related service data submitted through the platform.",
    ),
    ("Careers", "Contact"),
    ("Waitlist", "Access"),
    ("SAN FRANCISCO", "REMOTE"),
]
REPLACEMENTS.sort(key=lambda x: len(x[0]), reverse=True)

MANIFESTO = """<div class="prose prose-blog md:prose-lg container-prose w-full min-w-0"><p>We don't build a brand around noise. In performance, access, speed, and the ability to move without unnecessary losses matter.</p>
<p>Accounts are not a minor expense — they are infrastructure. For a team, they affect launch speed, test tempo, and the ability to scale.</p>
<p>impact.accs exists for teams that work with traffic seriously: they calculate fast, decide fast, and don't want to depend on random sellers.</p>
<h2 id="1-access-is-infrastructure"><a href="#1-access-is-infrastructure" class="group block no-underline">1. Access Is Infrastructure</a></h2>
<p>Most account suppliers are chaotic. Screenshots, emojis, vague terms, and endless chat hunting.</p>
<p>Structure beats noise. Teams need clear answers: launch accounts, volume, access type, delivery speed.</p>
<h2 id="2-resource-over-promises"><a href="#2-resource-over-promises" class="group block no-underline">2. Resource Over Promises</a></h2>
<p>Working resource is what you need — verified supply, clear terms, and replacement when the issue is on our side.</p>
<h2 id="3-trust-is-built-in-process"><a href="#3-trust-is-built-in-process" class="group block no-underline">3. Trust Is Built in Process</a></h2>
<p>Request → match → terms → access → repeat. Five years in the market. Fast contact. Clear process.</p>
<h2 id="introducing-impact-accs"><a href="#introducing-impact-accs" class="group block no-underline">Introducing: impact.accs</a></h2>
<p>impact.accs is closed account infrastructure — the first module of the impact. performance ecosystem.</p>
<p>If this way of working fits your team, request access.</p></div>"""

YC_ARTICLE = """<p>impact.accs has been supplying accounts and access to media buying teams for five years. We work as a resource layer: no noise, clear communication, and focus on practical results.</p>
<p>We continue expanding supply, improving processes, and working with teams that need volume and speed. If your team runs traffic and needs a reliable access channel, get in touch.</p>
<p>Our team has been in this market for years. We know what buyers need: speed, predictability, and a supplier who understands the work.</p>
<p>To the teams we work with: thank you. We build this for teams that move fast.</p>
<p>The impact.accs team</p>"""


def apply_text(text: str) -> str:
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
        new = apply_text(part)
        if new != part:
            changed = True
        out.append(new)
    result = "".join(out)
    if "manifesto" in path.name:
        pat = re.compile(
            r'<div class="prose prose-blog md:prose-lg container-prose w-full min-w-0">.*?</div>(?=<div class="max-md:hidden">)',
            re.DOTALL,
        )
        if pat.search(result):
            result = pat.sub(MANIFESTO, result, count=1)
            changed = True
    if "yc-p26" in path.name:
        pat = re.compile(
            r'<div class="prose prose-blog md:prose-lg container-prose w-full min-w-0">.*?</div>(?=<div class="max-md:hidden">)',
            re.DOTALL,
        )
        if pat.search(result):
            result = pat.sub(
                f'<div class="prose prose-blog md:prose-lg container-prose w-full min-w-0">{YC_ARTICLE}</div>',
                result,
                count=1,
            )
            changed = True
    if changed:
        path.write_text(result, encoding="utf-8")
    return changed


def patch_js(path: Path) -> bool:
    """JS patching disabled — breaks runtime (React #306). HTML-only is safe."""
    _ = path
    return False


def main() -> None:
    html_n = js_n = 0
    for path in sorted(ROOT.glob("**/*.html")):
        if path.name.startswith("_"):
            continue
        if patch_html(path):
            html_n += 1
            print("html", path.relative_to(ROOT))
    for name in sorted(JS_ALLOW):
        p = CHUNKS_DIR / name
        if p.exists() and patch_js(p):
            js_n += 1
            print("js  ", name)
    print(f"done: {html_n} html (JS untouched — text in chunks stays original)")


if __name__ == "__main__":
    main()
