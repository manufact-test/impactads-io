"""Safe HTML-only text updates (no JS, no script tags)."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent

# Only user-visible strings — safe in HTML meta + body
SAFE: list[tuple[str, str]] = [
    (
        "The AI-native observability platform for fast-moving engineering teams. Ship with confidence.",
        "Closed access infrastructure for media buying teams. Accounts and access for launch, tests, and scale.",
    ),
    ("SAVE THE DAY", "REQUEST ACCESS"),
    ("Stop Wondering", "Impact starts with access"),
    ("Get Access", "Request access"),
    ("Join the Waitlist", "Request access"),
    ("impact.corp®", "impact.accs"),
    ("Impact is", "impact.accs"),
    ("Initializing", "Loading access"),
    (
        "Observability is broken. We&#x27;re here to fix it. Learn about Impact&#x27;s philosophy and meet the team.",
        "Closed account infrastructure for media buying teams. Learn how impact.accs helps teams launch faster.",
    ),
    ("Impact | AI-Native Observability", "impact.accs | Closed Access Infrastructure"),
    (
        "Autonomous alerts. Conversational debugging. Coding agent integrations. Impact brings a new, AI-first approach to observability. Built for teams who ship fast.",
        "Closed access infrastructure for media buying teams. Agency accounts, platform access, and supply under volume.",
    ),
    ("Impact's Journal", "impact.accs journal"),
    ("The Impact Manifesto", "The impact.accs Manifesto"),
    ("Impact Is Part of Y Combinator P26", "Five Years in the Market"),
    (
        "Impact is joining Y Combinator&#x27;s P26 batch as we continue building the future of AI-native observability.",
        "impact.accs has spent five years supplying accounts and access to media buying teams — quietly, without the noise.",
    ),
    ("Autonomous Alerts", "Agency Accounts"),
    ("Conversational Debugging", "Media Buying Access"),
    ("Coding Agents Welcome", "Team Supply"),
    ("Impact Inc., 2261 Market Street, STE 85391, San Francisco, California 94114", "impact.accs"),
    ("legal@impact.ai", "contact@impact.accs"),
    ("Copyright © 2026 Impact.", "Copyright © 2026 impact.accs."),
]
SAFE.sort(key=lambda x: len(x[0]), reverse=True)

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


def patch_html(path: Path) -> bool:
    text = path.read_text(encoding="utf-8")
    # split out script/style — only patch the rest
    parts = re.split(r"(<script[\s\S]*?</script>)", text, flags=re.IGNORECASE)
    out = []
    changed = False
    for i, part in enumerate(parts):
        if i % 2 == 1:  # script block — leave untouched
            out.append(part)
            continue
        new = part
        for old, rep in SAFE:
            if old in new:
                new = new.replace(old, rep)
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
    if changed:
        path.write_text(result, encoding="utf-8")
    return changed


def main() -> None:
    n = 0
    for path in ROOT.glob("**/*.html"):
        if path.name.startswith("_"):
            continue
        if patch_html(path):
            n += 1
            print(path.relative_to(ROOT))
    print(f"patched {n} html files (JS not touched)")


if __name__ == "__main__":
    main()
