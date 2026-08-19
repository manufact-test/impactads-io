"""Fix footer (impact.corp® / RIGHTS RESERVED / 2026), preloader text, manifesto logo."""
from __future__ import annotations

import re
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parent
CHUNKS = ROOT / "_next/static/chunks"
CORP = "impact.corp\u00ae"

REPLACEMENTS: list[tuple[str, str]] = [
    # Preloader — first line was overwritten by footer patch
    (
        '(0,t.jsx)("span",{className:"block",children:"RIGHTS RESERVED"}),(0,t.jsx)("span",{className:"block",children:"Initializing"})',
        '(0,t.jsx)("span",{className:"block",children:"impact.accs"}),(0,t.jsx)("span",{className:"block",children:"Initializing"})',
    ),
    # Desktop footer (d53) — 3 lines
    (
        f'(0,t.jsx)("p",{{"data-text":"RIGHTS RESERVED",children:"RIGHTS RESERVED"}}),(0,t.jsx)("p",{{"data-text":"2026",className:"self-end",children:"2026"}})',
        f'(0,t.jsx)("p",{{"data-text":"{CORP}",children:"{CORP}"}}),(0,t.jsx)("p",{{"data-text":"RIGHTS RESERVED",className:"self-end",children:"RIGHTS RESERVED"}}),(0,t.jsx)("p",{{"data-text":"2026",children:"2026"}})',
    ),
    # Mobile menu footer — left column
    (
        f'(0,t.jsxs)("div",{{className:"flex flex-col",children:[(0,t.jsx)("p",{{"data-scramble":"RIGHTS RESERVED",className:"opacity-0",children:"RIGHTS RESERVED"}}),(0,t.jsx)("p",{{"data-scramble":"2026",className:"opacity-0",children:"2026"}})]}})',
        f'(0,t.jsxs)("div",{{className:"flex flex-col",children:[(0,t.jsx)("p",{{"data-scramble":"{CORP}",className:"opacity-0",children:"{CORP}"}}),(0,t.jsx)("p",{{"data-scramble":"RIGHTS RESERVED",className:"opacity-0",children:"RIGHTS RESERVED"}})]}})',
    ),
    # Manifesto card — red S logo top-right
    (
        '(0,t.jsx)("div",{className:"absolute top-0 right-0 p-8",children:(0,t.jsx)(X,{className:"text-primary w-12"})})',
        '(0,t.jsx)("div",{className:"absolute top-0 right-0 p-8",children:(0,t.jsx)("img",{src:"/assets/ia-logo.svg",alt:"impact.accs",className:"w-12 h-auto object-contain"})})',
    ),
]

HTML_FILES = ("index.html", "index492c.html", "index89bf.html")
JS_FILES = ("827ff3490ba1793e.js", "1e7f2c52e84d02fd.js", "d53e27b68750e6f9.js")


def patch_js(name: str) -> bool:
    path = CHUNKS / name
    raw = path.read_text(encoding="utf-8")
    new = raw
    for old, repl in REPLACEMENTS:
        if old in new:
            new = new.replace(old, repl)
    if new == raw:
        return False
    path.write_text(new, encoding="utf-8")
    r = subprocess.run(["node", "--check", str(path)], capture_output=True, text=True)
    if r.returncode != 0:
        path.write_text(raw, encoding="utf-8")
        raise RuntimeError(f"{name}: {r.stderr.strip()}")
    return True


def patch_html_preloader(path: Path) -> bool:
    raw = path.read_text(encoding="utf-8")
    new = raw
    # ensure preloader SSR matches JS
    new = new.replace(
        '<span class="block">RIGHTS RESERVED</span><span class="block">Initializing</span>',
        '<span class="block">impact.accs</span><span class="block">Initializing</span>',
    )
    if new == raw:
        return False
    path.write_text(new, encoding="utf-8")
    return True


def main() -> None:
    for fname in HTML_FILES:
        p = ROOT / fname
        if p.exists() and patch_html_preloader(p):
            print("html", fname)
    for name in JS_FILES:
        if patch_js(name):
            print("js  ", name)
    print("done")


if __name__ == "__main__":
    main()
