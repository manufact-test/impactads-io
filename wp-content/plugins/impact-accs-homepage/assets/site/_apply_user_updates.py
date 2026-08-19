"""Investor logos x4, Accounts nav, tower text-only overlays."""
from __future__ import annotations

import re
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parent
CHUNKS = ROOT / "_next/static/chunks"

LOGO_END = '}}];function i(){return(0,t.jsxs)("div",{className:"flex w-[60vw] flex-col items-center gap-2 md:w-[35vw]"'


def wordmark(name: str, label: str, width: int) -> str:
    return (
        f'{{name:"{name}",Logo:function({{className:e}})'
        f'{{return(0,t.jsx)("svg",{{className:e,viewBox:"0 0 {width} 20",fill:"currentColor",'
        f'xmlns:"http://www.w3.org/2000/svg",children:(0,t.jsx)("text",{{x:"0",y:"16",fontSize:"14",'
        f'fontWeight:"600",fontFamily:"ui-monospace,monospace",letterSpacing:"0.08em",children:"{label}"}})}})}}}}'
    )


LOGO_ARRAY_NEW = (
    "let n=["
    + wordmark("ScaleGrid", "SCALEGRID", 132)
    + ","
    + wordmark("LaunchDesk", "LAUNCHDESK", 142)
    + ","
    + wordmark("AdVolume", "ADVOLUME", 128)
    + ","
    + wordmark("TrafficLab", "TRAFFICLAB", 138)
    + "];function i(){return(0,t.jsxs)(\"div\",{className:\"flex w-[60vw] flex-col items-center gap-2 md:w-[35vw]\""
)


def patch_logos(text: str) -> str:
    start = text.find("let n=[")
    end = text.find(LOGO_END)
    if start < 0 or end <= start:
        raise RuntimeError("logo array not found in 827ff")
    return text[:start] + LOGO_ARRAY_NEW + text[end + len(LOGO_END) :]


REPLACEMENTS_827: list[tuple[str, str]] = []

REPLACEMENTS_1E7F: list[tuple[str, str]] = [
    ('href:"/features",label:"Access"', 'href:"/features",label:"Accounts"'),
    ('children:["FEATURES",', 'children:["ACCOUNTS",'),
    ('children:"Features"}),(0,t.jsx)("ul",{className:"font-misc flex w-full flex-col gap-8 text-3xl tracking-wider uppercase"',
     'children:"Accounts"}),(0,t.jsx)("ul",{className:"font-misc flex w-full flex-col gap-8 text-3xl tracking-wider uppercase"'),
]

REPLACEMENTS_5308: list[tuple[str, str]] = [
    # Tower overlays — text cards only (no chat/charts/avatars)
    (
        "children:!y&&(0,o.jsx)(ee.ErrorConversation,{errorChartVisible:w,inputActive:T,onSend:()=>{v(),b.current?.()},wrapper:K.RevealItem})",
        "children:null",
    ),
    (
        "children:!y&&(0,o.jsx)(ee.WarningConversation,{latencyChartVisible:M,inputActive:F,onSend:()=>{v(),j.current?.()},wrapper:K.RevealItem})",
        "children:null",
    ),
    (
        "children:!y&&(0,o.jsx)(ee.OperationalConversation,{uptimeChartVisible:E,inputActive:P,onSend:()=>{v(),G(2)},wrapper:K.RevealItem})",
        "children:null",
    ),
]

# Read exact em-dash strings from file at runtime for tower copy
TOWER_COPY: list[tuple[str, str]] = []


def load_tower_copy() -> None:
    t = (CHUNKS / "5308d2f8d20274da.js").read_text(encoding="utf-8")
    pairs = [
        (
            'alertTitle:"Launch blocked',
            'alertTitle:"Launch blocked — access needed",alertDescription:"Agency accounts required before traffic goes live.",resolved:O,resolvedTitle:"Access confirmed",resolvedDescription:"Working accounts delivered on agreed terms."',
        ),
        (
            'alertTitle:"Volume request',
            'alertTitle:"Volume request — EU",alertDescription:"50 accounts requested. Terms needed before 18:00.",resolved:D,resolvedTitle:"Supply matched",resolvedDescription:"Terms locked. Delivery in progress."',
        ),
        (
            'alertTitle:"Supply stable"',
            'alertTitle:"Supply stable",alertDescription:"Repeat order channel active. Same terms, same desk.",resolved:B,resolvedTitle:"Ready for launch",resolvedDescription:"Working resource on standby for the next push."',
        ),
    ]
    for prefix, _ in pairs:
        if prefix not in t:
            raise RuntimeError(f"tower prefix missing: {prefix[:40]}")
    # Replace full tower alert blocks by regex
    global REPLACEMENTS_5308
    REPLACEMENTS_5308 = REPLACEMENTS_5308 + [
        (
            re.search(
                r'index:0,visible:n,immediate:u,layerActive:d,position:er\[0\],variant:"destructive",side:"left",alertTitle:"[^"]+",alertDescription:"[^"]+",resolved:O,resolvedTitle:"[^"]+",resolvedDescription:"[^"]+"',
                t,
            ).group(0),
            'index:0,visible:n,immediate:u,layerActive:d,position:er[0],variant:"destructive",side:"left",alertTitle:"Launch blocked — access needed",alertDescription:"Agency accounts required before traffic goes live.",resolved:O,resolvedTitle:"Access confirmed",resolvedDescription:"Working accounts delivered on agreed terms."',
        ),
        (
            re.search(
                r'index:1,visible:n,immediate:u,layerActive:d,position:er\[1\],variant:"warning",side:"right",alertTitle:"[^"]+",alertDescription:"[^"]+",resolved:D,resolvedTitle:"[^"]+",resolvedDescription:"[^"]+"',
                t,
            ).group(0),
            'index:1,visible:n,immediate:u,layerActive:d,position:er[1],variant:"warning",side:"right",alertTitle:"Volume request — EU",alertDescription:"50 accounts requested. Terms needed before 18:00.",resolved:D,resolvedTitle:"Supply matched",resolvedDescription:"Terms locked. Delivery in progress."',
        ),
        (
            re.search(
                r'index:2,visible:n,immediate:u,layerActive:d,position:er\[2\],variant:"monitoring",side:"right",alertTitle:"[^"]+",alertDescription:"[^"]+",resolved:B,resolvedTitle:"[^"]+",resolvedDescription:"[^"]+"',
                t,
            ).group(0),
            'index:2,visible:n,immediate:u,layerActive:d,position:er[2],variant:"monitoring",side:"right",alertTitle:"Supply stable",alertDescription:"Repeat order channel active. Same terms, same desk.",resolved:B,resolvedTitle:"Ready for launch",resolvedDescription:"Working resource on standby for the next push."',
        ),
    ]


def apply_list(text: str, reps: list[tuple[str, str]]) -> tuple[str, list[str]]:
    changed: list[str] = []
    for old, new in reps:
        if old in text:
            text = text.replace(old, new)
            changed.append(old[:60])
    return text, changed


def patch_js(name: str, reps: list[tuple[str, str]], *, logo: bool = False) -> None:
    path = CHUNKS / name
    raw = path.read_text(encoding="utf-8")
    new = patch_logos(raw) if logo else raw
    new, changed = apply_list(new, reps)
    if new == raw:
        print(f"skip {name} (no changes)")
        return
    path.write_text(new, encoding="utf-8")
    r = subprocess.run(["node", "--check", str(path)], capture_output=True, text=True)
    if r.returncode != 0:
        path.write_text(raw, encoding="utf-8")
        raise RuntimeError(f"{name}: {r.stderr.strip()}")
    print(f"ok   {name} ({len(changed)} replacements)")


def main() -> None:
    load_tower_copy()
    patch_js("827ff3490ba1793e.js", REPLACEMENTS_827, logo=True)
    patch_js("1e7f2c52e84d02fd.js", REPLACEMENTS_1E7F)
    patch_js("5308d2f8d20274da.js", REPLACEMENTS_5308)
    print("done")


if __name__ == "__main__":
    main()
