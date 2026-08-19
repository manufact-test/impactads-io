"""Homepage sections: nav, footer, animations off, logos, builders, tagline."""
from __future__ import annotations

import re
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parent
CHUNKS = ROOT / "_next/static/chunks"
CORP = "impact.corp\u00ae"

INVESTOR_REPLACEMENTS = [
    ("Abhi Aiyer", "Viktor Kozlov"),
    ("Demetrios Brinkmann", "Elena Morozova"),
    ("Ivan Burazin", "Dmitry Volkov"),
    ("Harrison Chase", "Anna Petrova"),
    ("Sonny Gupta", "Maksim Orlov"),
    ("Jay Hack", "Irina Sokolova"),
    ("Mark Hillick", "Pavel Kuznetsov"),
    ("Jay Hackney", "Nikita Romanov"),
    ("Paul Klein IV", "Olga Nemova"),
    ("Matthew Lenhard", "Artem Vlasov"),
    ("Jordan Lewis", "Kira Melnik"),
    ("Merrill Lutsky", "Denis Arkhipov"),
    ("Lance Martin", "Maria Koch"),
    ("Yohei Nakajima", "Alex Stepanov"),
    ("Matt Palmer", "Yulia Fomina"),
    ("Andrew Qu", "Roman Sidorov"),
    ("Hunter Walk", "Daria Kuzmina"),
    ("Rotem Weiss", "Sergei Polov"),
    ("Mastra", "MediaBuy Lab"),
    ("MLOps Community", "Traffic Unit"),
    ("Daytona", "Launch Ops"),
    ("LangChain", "Buyer Desk"),
    ("Brex", "Scale Team"),
    ("Codegen", "Access Lead"),
    ("Fastino", "Geo Lead"),
    ("Browserbase", "Supply Hub"),
    ("Vercel", "Agency Desk"),
    ("Cockroach Labs", "Volume Ops"),
    ("Graphite", "Platform Access"),
    ("Anthropic", "Media Ops"),
    ("Untapped Capital", "Buyer Network"),
    ("Replit", "Launch Lead"),
    ("Homebrew", "Traffic Lead"),
    ("Tavily", "Access Ops"),
]

TEXT_REPLACEMENTS: list[tuple[str, str]] = [
    # Header nav (keep About)
    ('href:"/blog",label:"Blog"', 'href:"/blog/manifesto",label:"Manifesto"'),
    ('href:"/about#careers",label:"Contact"', 'href:"?contact=true",label:"Contact"'),
    ('href:"/features",label:"Features"', 'href:"/features",label:"Access"'),
    ('label:"Media Buying Access"', 'label:"Platform Access"'),
    # Tagline (index html + any chunk)
    (
        "The AI-native observability platform for fast-moving engineering teams. Ship with confidence.",
        "Closed access infrastructure for media buying teams. Working resource — request, terms, delivery.",
    ),
    (
        "Autonomous alerts. Conversational debugging. Coding agent integrations. Impact brings a new, AI-first approach to observability. Built for teams who ship fast.",
        "Closed access infrastructure for media buying teams. Agency accounts, platform access, and supply under volume.",
    ),
    # Remove integration animations (keep titles/descriptions)
    (
        'children:(0,t.jsx)(X,{integrations:Q,className:"w-[340px] sm:w-[380px] md:w-[400px] lg:w-[440px] xl:w-[460px]"})',
        "children:null",
    ),
    (
        'children:(0,t.jsx)(F,{variant:"rounded",className:"w-[340px] sm:w-[380px] md:w-[400px] lg:w-[420px] xl:w-[440px]"})',
        "children:null",
    ),
    # Credits grid — ia logo instead of Impact mark
    (
        '(0,t.jsx)(v.ImpactIcon,{className:"size-5"})',
        '(0,t.jsx)("img",{src:"/assets/ia-logo.svg",alt:"impact.accs",className:"size-5 object-contain"})',
    ),
    # Footer
    (f'"data-text":"{CORP}",children:"{CORP}"', '"data-text":"RIGHTS RESERVED",children:"RIGHTS RESERVED"'),
    (f'"data-scramble":"{CORP}"', '"data-scramble":"RIGHTS RESERVED"'),
    (f'children:"{CORP}"', 'children:"RIGHTS RESERVED"'),
    ('"data-text":"ai-native",className:"self-end",children:"ai-native"', '"data-text":"2026",className:"self-end",children:"2026"'),
    ('"data-scramble":"ai-native"', '"data-scramble":"2026"'),
    (',(0,t.jsx)("p",{"data-text":"2026",children:"2026"})', ""),
    ('children:"ai-native"', 'children:"2026"'),
    *INVESTOR_REPLACEMENTS,
]
TEXT_REPLACEMENTS.sort(key=lambda x: len(x[0]), reverse=True)

LOGO_ARRAY_START = 'let n=[{name:"Graphite",Logo:function'
LOGO_ARRAY_END = '}}];function i(){return(0,t.jsxs)("div",{className:"flex w-[60vw] flex-col items-center gap-2 md:w-[35vw]"'
LOGO_ARRAY_NEW = (
    'let n=[{name:"impact.accs",Logo:function({className:e})'
    '{return(0,t.jsx)("img",{src:"/assets/ia-logo.svg",alt:"impact.accs",className:e})}}];'
    "function i(){return(0,t.jsxs)(\"div\",{className:\"flex w-[60vw] flex-col items-center gap-2 md:w-[35vw]\""
)

HTML_FILES = ("index.html", "index492c.html", "index89bf.html")
JS_FILES = (
    "827ff3490ba1793e.js",
    "692acfebb5322696.js",
    "1e7f2c52e84d02fd.js",
    "d53e27b68750e6f9.js",
)


def apply(text: str, *, logo_array: bool = False) -> str:
    if logo_array and LOGO_ARRAY_START in text:
        start = text.find(LOGO_ARRAY_START)
        end = text.find(LOGO_ARRAY_END)
        if end > start:
            text = text[:start] + LOGO_ARRAY_NEW + text[end + len(LOGO_ARRAY_END) :]
    for old, new in TEXT_REPLACEMENTS:
        if old and old in text:
            text = text.replace(old, new)
    return text


def patch_js(name: str, *, logo_array: bool = False) -> bool:
    path = CHUNKS / name
    raw = path.read_text(encoding="utf-8")
    new = apply(raw, logo_array=logo_array)
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
    if patch_js("827ff3490ba1793e.js", logo_array=True):
        print("js  ", "827ff3490ba1793e.js")
    for name in JS_FILES[1:]:
        if patch_js(name):
            print("js  ", name)
    print("done")


if __name__ == "__main__":
    main()
