"""About page: hero image, copy rebrand, header parity, READY FOR ACTION footer block."""
from __future__ import annotations

import re
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parent
CHUNKS = ROOT / "_next/static/chunks"
ABOUT_JS = "0476edb0af9ab771.js"

_AP = "\u2019"
_EM = "\u2014"

HEADER_OLD = (
    'transition-[translate] translate-x-0 translate-y-0 duration-600 delay-100 '
    'ease-[cubic-bezier(0.5,0,0.75,0)]'
)
HEADER_NEW = (
    'transition-[translate] duration-400 ease-[cubic-bezier(0.33,1,0.68,1)] translate-x-0'
)

FEATURES_IMPORT = (
    '28:I[37131,["/_next/static/chunks/7a15b1faddf2401e.js",'
    '"/_next/static/chunks/e4d029f0a65d2836.js",'
    '"/_next/static/chunks/777ae40b3d9f0899.js",'
    '"/_next/static/chunks/fe86549c3883d530.js",'
    '"/_next/static/chunks/9279d163cbf075e3.js",'
    '"/_next/static/chunks/5de4205ab7076775.js",'
    '"/_next/static/chunks/8e1e9e7a85fc0466.js",'
    '"/_next/static/chunks/f439ea79198b32e7.js",'
    '"/_next/static/chunks/00f6275334388de2.js",'
    '"/_next/static/chunks/2620792f5abd3acd.js",'
    '"/_next/static/chunks/621fda41b5a90244.js",'
    '"/_next/static/chunks/694b218a672794b8.js",'
    '"/_next/static/chunks/29df6672875b8547.js",'
    '"/_next/static/chunks/1e7f2c52e84d02fd.js",'
    '"/_next/static/chunks/d53e27b68750e6f9.js",'
    '"/_next/static/chunks/692acfebb5322696.js",'
    '"/_next/static/chunks/827ff3490ba1793e.js",'
    '"/_next/static/chunks/f7f1c59a71681025.js"],"FeaturesAndBackedBy"]'
)

RSC_MAIN_OLD = (
    '"children":[[\\"$\\",\\"$L24\\",null,{}],[\\"$\\",\\"$L25\\",null,{}],'
    '[\\"$\\",\\"$L26\\",null,{}],[\\"$\\",\\"$L27\\",null,{}],\\"$L28\\"]'
)
RSC_MAIN_NEW = (
    '"children":[[\\"$\\",\\"$L24\\",null,{}],[\\"$\\",\\"$L25\\",null,{}],'
    '[\\"$\\",\\"$L26\\",null,{}],[\\"$\\",\\"$L27\\",null,{}],'
    '[\\"$\\",\\"$L28\\",null,{}],[\\"$\\",\\"div\\",null,'
    '{\\"className\\":\\"fade-to-t fade-from-background h-40 w-full\\"}]]'
)

RSC_SCRIPTS_OLD = (
    '[[\\"$\\",\\"script\\",\\"script-0\\",'
    '{\\"src\\":\\"/_next/static/chunks/0476edb0af9ab771.js\\",'
    '\\"async\\":true,\\"nonce\\":\\"$undefined\\"}]]'
)
RSC_SCRIPTS_NEW = (
    '[[\\"$\\",\\"script\\",\\"script-0\\",'
    '{\\"src\\":\\"/_next/static/chunks/0476edb0af9ab771.js\\",'
    '\\"async\\":true,\\"nonce\\":\\"$undefined\\"}],'
    '[\\"$\\",\\"script\\",\\"script-1\\",'
    '{\\"src\\":\\"/_next/static/chunks/692acfebb5322696.js\\",'
    '\\"async\\":true,\\"nonce\\":\\"$undefined\\"}],'
    '[\\"$\\",\\"script\\",\\"script-2\\",'
    '{\\"src\\":\\"/_next/static/chunks/827ff3490ba1793e.js\\",'
    '\\"async\\":true,\\"nonce\\":\\"$undefined\\"}],'
    '[\\"$\\",\\"script\\",\\"script-3\\",'
    '{\\"src\\":\\"/_next/static/chunks/f7f1c59a71681025.js\\",'
    '\\"async\\":true,\\"nonce\\":\\"$undefined\\"}]]'
)

JS_REPLACEMENTS: list[tuple[str, str]] = [
    ('e.v("/assets/about-hero.png")', 'e.v("assets/about-hero.png")'),
    (
        'N?(0,t.jsx)("div",{className:"relative h-lvh w-full",children:(0,t.jsx)(f.default,{src:h,alt:"Impact about page",priority:!0,className:"h-full w-full object-cover object-top"})}):(0,t.jsx)(x.TunnelIn,{children:(0,t.jsx)(v,{ref:b,visible:y})})',
        '(0,t.jsx)("div",{className:"relative h-lvh w-full",children:(0,t.jsx)(f.default,{src:h,alt:"impact.accs about page",priority:!0,className:"h-full w-full object-cover object-center"})})',
    ),
    ("Meet Impact", "Meet impact.accs"),
    (
        "We're building the observability platform we always wanted: conversational, intelligent, and built for teams who ship fast.",
        "Closed access infrastructure for teams that run traffic. Agency accounts, clear terms, delivery that matches your launch tempo.",
    ),
    ("ABOUT IMPACT", "ABOUT IMPACT.ACCS"),
    (
        "Values shape culture. Culture shapes products. These six principles are the foundation of how we build Impact.",
        "Values shape culture. Culture shapes service. These six principles guide how we supply access.",
    ),
    (
        f'Do the right thing, always. By our customers, Denis A., our investors. Honesty isn{_AP}t optional.',
        "Do the right thing by customers and partners. Clear terms, no shortcuts — honesty is not optional.",
    ),
    (
        "Move fast. Be impatient. Never settle for complacency or comfort. Speed is a feature.",
        "Move fast on requests. Launches do not wait — neither do we. Speed is part of the service.",
    ),
    (
        "Take pride in your work. Care about design. Care about quality. Never ship garbage.",
        "Take pride in supply quality. Accounts prepared, verified, and ready for the task.",
    ),
    (
        "Go above and beyond for customers. Extend that same care to teammates and partners. Service is everything.",
        "Go above and beyond for teams. Repeat orders, volume terms, and fast contact when it matters.",
    ),
    (
        "Be kind to everyone: teammates, customers, even competitors. Be direct, but always mindful. We're all in this together.",
        f"Be direct and respectful with operators and partners. We{_AP}re in this together — no noise, no games.",
    ),
    (
        "Enjoy the work. Enjoy each other. Do things because they're cool. Smile through the pain.",
        f"Enjoy the work. Teams that launch together stay together. Smile through the volume.",
    ),
    (
        'children:["Our team founded the infrastructure and observability teams at"," ",(0,t.jsx)("strong",{className:"text-card-foreground text-glow font-medium",children:"Brex"}),". We fell in love with the promise of observability — the idea that you could truly understand and control complex systems. But we also witnessed firsthand the"," ",(0,t.jsx)("strong",{className:"text-card-foreground text-glow font-medium",children:"challenges and complexity"}),". The dashboards. The alert fatigue. The 3am pages that could have been prevented."]',
        'children:["Our team spent years inside high-volume media buying — launching campaigns, rebuilding supply chains, and scaling spend under pressure. We learned what works:"," ",(0,t.jsx)("strong",{className:"text-card-foreground text-glow font-medium",children:"closed access, clear terms, operators who deliver"}),". We also saw firsthand the"," ",(0,t.jsx)("strong",{className:"text-card-foreground text-glow font-medium",children:"chaos of bad supply"}),". Random chats. Unstable sellers. Launches killed by accounts that never should have shipped."]',
    ),
    (
        'children:["In 2025, everything changed. We found ourselves building"," ",(0,t.jsx)("strong",{className:"text-card-foreground text-glow font-medium",children:"mind-blowing agentic software products"})," ","and using futuristic AI coding tools like Cursor and Claude Code. But observability still felt painfully manual, ",(0,t.jsx)("strong",{className:"text-card-foreground text-glow font-medium",children:"stuck in the past"}),". That\'s when it clicked: if AI can write code, why can\'t it understand production?"]',
        'children:["In 2025, the gap became obvious. Teams were scaling spend across Facebook, Google, and TikTok — but access was still a gamble. We kept seeing"," ",(0,t.jsx)("strong",{className:"text-card-foreground text-glow font-medium",children:"launch windows slip"})," ","while sellers disappeared mid-deal and terms changed overnight. Account access still felt like a treasure hunt, ",(0,t.jsx)("strong",{className:"text-card-foreground text-glow font-medium",children:"not infrastructure"}),". That\'s when impact.accs clicked: if you\'re running traffic at volume, access should work like infrastructure — not a gamble."]',
    ),
    (
        "default observability solution",
        "default resource layer for media buying teams",
    ),
    (
        f" for companies of all sizes during the AI era. Not by adding AI features to legacy tools, but by rethinking observability from first principles {_EM} conversational, intelligent, and built for teams who move fast.",
        f" during the launch era. Not by reselling random accounts, but by building closed access infrastructure {_EM} clear request, fast contact, working supply under volume.",
    ),
    (
        "software heals and improves itself",
        "teams launch without hunting for access",
    ),
    (
        f"without human intervention or direction. Where systems understand their own health, diagnose their own issues, and fix their own bugs. Where engineers spend their time building, not firefighting.",
        f". Where supply matches the task, terms are clear, and operators spend time scaling — not firefighting account chaos.",
    ),
    ('alt:"Impact about page"', 'alt:"impact.accs about page"'),
]
JS_REPLACEMENTS.sort(key=lambda x: len(x[0]), reverse=True)

HTML_REPLACEMENTS: list[tuple[str, str]] = [
    (HEADER_OLD, HEADER_NEW),
    ("Meet Impact", "Meet impact.accs"),
    (
        "We&#x27;re building the observability platform we always wanted: conversational, intelligent, and built for teams who ship fast.",
        "Closed access infrastructure for teams that run traffic. Agency accounts, clear terms, delivery that matches your launch tempo.",
    ),
    ("ABOUT IMPACT", "ABOUT IMPACT.ACCS"),
    (
        "Our team founded the infrastructure and observability teams at <strong class=\"text-card-foreground text-glow font-medium\">Brex</strong>. We fell in love with the promise of observability — the idea that you could truly understand and control complex systems. But we also witnessed firsthand the <strong class=\"text-card-foreground text-glow font-medium\">challenges and complexity</strong>. The dashboards. The alert fatigue. The 3am pages that could have been prevented.",
        "Our team spent years inside high-volume media buying — launching campaigns, rebuilding supply chains, and scaling spend under pressure. We learned what works: <strong class=\"text-card-foreground text-glow font-medium\">closed access, clear terms, operators who deliver</strong>. We also saw firsthand the <strong class=\"text-card-foreground text-glow font-medium\">chaos of bad supply</strong>. Random chats. Unstable sellers. Launches killed by accounts that never should have shipped.",
    ),
    (
        "default observability solution",
        "default resource layer for media buying teams",
    ),
    (
        "software heals and improves itself",
        "teams launch without hunting for access",
    ),
    (
        "The AI-native observability platform for fast-moving engineering teams. Ship with confidence.",
        "Closed access infrastructure for media buying teams. Accounts and access for launch, tests, and scale.",
    ),
]
HTML_REPLACEMENTS.sort(key=lambda x: len(x[0]), reverse=True)

SCRIPT_REPLACEMENTS = [
    (
        "The AI-native observability platform for fast-moving engineering teams. Ship with confidence.",
        "Closed access infrastructure for media buying teams. Accounts and access for launch, tests, and scale.",
    ),
]


def apply_pairs(text: str, pairs: list[tuple[str, str]]) -> str:
    for old, new in pairs:
        if old != new and old in text:
            text = text.replace(old, new)
    return text


def extract_ready_section() -> str:
    idx_html = ROOT / "index.html"
    raw = idx_html.read_text(encoding="utf-8")
    start = raw.find("READY FOR ACTION")
    if start < 0:
        raise RuntimeError("READY FOR ACTION not found in index.html")
    sec_start = raw.rfind("<section", 0, start)
    sec_end = raw.find("</section>", start) + len("</section>")
    section = raw[sec_start:sec_end]
    section = section.replace('href="/features/', 'href="features/')
    section = section.replace('href="/blog"', 'href="blog.html"')
    return section


def replace_careers_with_ready(html: str, ready: str) -> str:
    marker = 'id="careers"'
    if marker not in html:
        return html
    sec_start = html.rfind("<section", 0, html.find(marker))
    sec_end = html.find("</section>", html.find(marker)) + len("</section>")
    return html[:sec_start] + ready + html[sec_end:]


def patch_rsc(html: str) -> str:
    if RSC_MAIN_OLD not in html:
        raise RuntimeError("RSC main children pattern not found in about HTML")
    html = html.replace(RSC_MAIN_OLD, RSC_MAIN_NEW)
    html = html.replace(RSC_SCRIPTS_OLD, RSC_SCRIPTS_NEW)

    scripts = re.findall(r"<script[^>]*>(.*?)</script>", html, re.DOTALL)
    for s in scripts:
        if '28:[\\"$\\",\\"section\\"' in s or '28:["$","section"' in s:
            escaped = FEATURES_IMPORT.replace("\\", "\\\\").replace('"', '\\"')
            new_script = f'self.__next_f.push([1,"{escaped}\\n"])'
            html = html.replace(f"<script>{s}</script>", f"<script>{new_script}</script>", 1)
            break
    else:
        raise RuntimeError("Careers RSC segment 28 script not found")
    return html


def patch_html(path: Path, ready: str) -> bool:
    raw = path.read_text(encoding="utf-8")
    parts = re.split(r"(<script[\s\S]*?</script>)", raw, flags=re.IGNORECASE)
    out: list[str] = []
    changed = False
    for i, part in enumerate(parts):
        if i % 2 == 1:
            new = part
            if RSC_MAIN_OLD in part:
                new = new.replace(RSC_MAIN_OLD, RSC_MAIN_NEW)
                new = new.replace(RSC_SCRIPTS_OLD, RSC_SCRIPTS_NEW)
                changed = True
            if '28:[\\"$\\",\\"section\\"' in part or '28:["$","section"' in part:
                escaped = FEATURES_IMPORT.replace("\\", "\\\\").replace('"', '\\"')
                new = f'<script>self.__next_f.push([1,"{escaped}\\n"])</script>'
                changed = True
            new = apply_pairs(new, SCRIPT_REPLACEMENTS)
            if new != part:
                changed = True
            out.append(new)
            continue
        new = apply_pairs(part, HTML_REPLACEMENTS)
        if "id=\"careers\"" in new:
            new = replace_careers_with_ready(new, ready)
            changed = True
        if HEADER_OLD in new:
            changed = True
        if 'rel="preload" as="image" href="_next/static/media/city-outline' in new and "about-hero" not in new:
            new = new.replace(
                'rel="preload" as="image" href="_next/static/media/city-outline.abe9c9cc.png"',
                'rel="preload" as="image" href="assets/about-hero.png"/>\n<link rel="preload" as="image" href="_next/static/media/city-outline.abe9c9cc.png"',
                1,
            )
            changed = True
        if new != part:
            changed = True
        out.append(new)
    if changed:
        path.write_text("".join(out), encoding="utf-8")
    return changed


def patch_about_js() -> bool:
    path = CHUNKS / ABOUT_JS
    raw = path.read_text(encoding="utf-8")
    new = apply_pairs(raw, JS_REPLACEMENTS)
    if new == raw:
        return False
    path.write_text(new, encoding="utf-8")
    r = subprocess.run(["node", "--check", str(path)], capture_output=True, text=True)
    if r.returncode != 0:
        path.write_text(raw, encoding="utf-8")
        raise RuntimeError(f"{ABOUT_JS} syntax error: {r.stderr.strip()}")
    return True


def main() -> None:
    src = ROOT / "assets" / "about-hero.png"
    if not src.exists():
        raise RuntimeError("assets/about-hero.png missing — copy user hero image first")

    ready = extract_ready_section()
    for fname in ("about.html", "about492c.html", "about89bf.html"):
        p = ROOT / fname
        if p.exists() and patch_html(p, ready):
            print("html", fname)

    if patch_about_js():
        print("js  ", ABOUT_JS)
    print("done")


if __name__ == "__main__":
    main()
