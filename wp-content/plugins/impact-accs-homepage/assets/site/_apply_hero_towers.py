"""Apply impact.accs theme to hero building overlays (5308 + f7f1)."""
from __future__ import annotations

import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parent
CHUNKS = ROOT / "_next/static/chunks"

def text_card(title: str, body: str, sub: str) -> str:
    return (
        '(0,C.jsx)("div",{className:"shadow-card bg-border-dark rounded-lg border p-4",children:(0,C.jsxs)("div",{className:"flex flex-col gap-1.5 text-sm text-white/80",children:['
        f'(0,C.jsx)("p",{{className:"font-misc text-[10px] uppercase tracking-wider text-white/45",children:{title!r}}}),'
        f'(0,C.jsx)("p",{{children:{body!r}}}),'
        f'(0,C.jsx)("p",{{className:"text-white/55",children:{sub!r}}})'
        "]})})"
    )

F7F1_REPLACEMENTS: list[tuple[str, str]] = [
    # --- Tower 0: launch / access ---
    ('name:"Merrill Lutsky"', 'name:"Denis A."'),
    ('avatar:"/assets/credits/merrill-lutsky.png"', 'avatar:"/assets/ia-logo.svg"'),
    ('children:" What\'s available for EU?"', 'children:" Need EU accounts before launch."'),
    (
        'children:["Spawning a fix agent to patch"," ",',
        'children:["Matching supply for"," ",',
    ),
    (
        '"data-type-text":!0,children:[" ","Run ",(0,C.jsx)("span",{className:"text-primary font-bold",children:"@supply"})," availability and terms for this launch"]',
        '"data-type-text":!0,children:[" ","Request ",(0,C.jsx)("span",{className:"text-primary font-bold",children:"@impact.accs"})," terms and delivery for EU launch"]',
    ),
    (
        '(0,C.jsx)("div",{className:"shadow-card bg-border-dark rounded-lg border p-2",children:(0,C.jsx)(d,{visible:e})})',
        text_card(
            "Request status",
            "EU · 50 agency accounts — terms confirmed.",
            "Delivery scheduled before the launch window.",
        ),
    ),
    # --- Tower 1: volume / terms ---
    ('name:"Hadley Callaway"', 'name:"Elena M."'),
    ('avatar:"/assets/team/sherwood.webp"', 'avatar:"/assets/ia-logo.svg"'),
    (
        'children:["Here\'s the p99 latency for"," ",(0,C.jsx)("code",{className:"rounded bg-white/10 px-1 py-0.5 text-[12px]",children:"EU · 50 accounts"})," over the last 30 days."]',
        'children:"Terms draft ready for EU · 50 accounts. Volume and GEO locked."',
    ),
    (
        '(0,C.jsx)("div",{className:"shadow-card bg-border-dark w-full rounded-lg border p-2",children:(0,C.jsx)(c.ApiLatencyChart,{visible:e})})',
        text_card(
            "Volume terms",
            "50 accounts · EU · delivery before 18:00.",
            "Working resource — request logged, supply matching.",
        ),
    ),
    (
        '"data-type-text":!0,children:[" ","notify ",(0,C.jsx)("span",{className:"bg-slack-mention/15 text-slack-mention rounded px-0.5",children:"#requests"}),", open a ticket, and page the on-call engineer"]',
        '"data-type-text":!0,children:[" ",(0,C.jsx)("span",{className:"text-primary font-bold",children:"@impact.accs"})," lock terms and confirm delivery for this volume"]',
    ),
    ('children:["Impact",', 'children:["impact.accs",'),
    ('children:["Root cause",', 'children:["Request",'),
    ('children:"Recommended action"', 'children:"Next step"'),
    (
        'children:"A launch window is open — the team needs agency accounts before traffic goes live."',
        'children:"Buyer desk needs agency accounts before traffic goes live. GEO: EU."',
    ),
    (
        'children:["Posted to ",(0,C.jsx)("span",{className:"bg-slack-mention/15 text-slack-mention rounded px-0.5",children:"#requests"}),"."]',
        'children:["Posted to ",(0,C.jsx)("span",{className:"bg-slack-mention/15 text-slack-mention rounded px-0.5",children:"#requests"})," — volume logged."]',
    ),
    (
        'children:["Paged ",(0,C.jsx)("span",{className:"text-primary font-bold",children:"@team"})," via PagerDuty."]',
        'children:["Desk notified ",(0,C.jsx)("span",{className:"text-primary font-bold",children:"@team"}),". Delivery queued."]',
    ),
    # --- Tower 2: supply / repeat ---
    ('children:["Monitoring services: "', 'children:["Active channels: "'),
    (
        '"data-type-text":!0,children:["Thank you ",(0,C.jsx)("span",{className:"text-primary font-bold",children:"@impact.accs"}),"! Looks good to me"]',
        '"data-type-text":!0,children:["Repeat order confirmed — ",(0,C.jsx)("span",{className:"text-primary font-bold",children:"@impact.accs"})," supply stable"]',
    ),
    (
        '(0,C.jsx)("div",{className:"shadow-card bg-border-dark rounded-lg border p-2",children:(0,C.jsx)(x,{visible:e})})',
        text_card(
            "Supply status",
            "Repeat order channel active. Terms unchanged.",
            "Working resource ready for the next launch.",
        ),
    ),
    # --- misc leftovers ---
    ('children:"Media buy"', 'children:"EU desk"'),
]

# Sort longest first
F7F1_REPLACEMENTS.sort(key=lambda x: len(x[0]), reverse=True)

RESTORE_CONVERSATIONS: list[tuple[str, str]] = [
    (
        "onAllRevealed:()=>G(0),children:null}),(0,o.jsx)(J,{index:1",
        "onAllRevealed:()=>G(0),children:!y&&(0,o.jsx)(ee.ErrorConversation,{errorChartVisible:w,inputActive:T,onSend:()=>{v(),b.current?.()},wrapper:K.RevealItem})}),(0,o.jsx)(J,{index:1",
    ),
    (
        "onAllRevealed:()=>G(1),children:null}),(0,o.jsx)(J,{index:2",
        "onAllRevealed:()=>G(1),children:!y&&(0,o.jsx)(ee.WarningConversation,{latencyChartVisible:M,inputActive:F,onSend:()=>{v(),j.current?.()},wrapper:K.RevealItem})}),(0,o.jsx)(J,{index:2",
    ),
    (
        "onItemReveal:e=>{1===e&&k(!0),2===e&&I(!0)},children:null})]})}let el=",
        "onItemReveal:e=>{1===e&&k(!0),2===e&&I(!0)},children:!y&&(0,o.jsx)(ee.OperationalConversation,{uptimeChartVisible:E,inputActive:P,onSend:()=>{v(),G(2)},wrapper:K.RevealItem})})]})}let el=",
    ),
]

# Align top alert cards with overlay copy
S308_REPLACEMENTS: list[tuple[str, str]] = [
    (
        'alertTitle:"Launch blocked — access needed",alertDescription:"Agency accounts required before traffic goes live.",resolved:O,resolvedTitle:"Access confirmed",resolvedDescription:"Working accounts delivered on agreed terms."',
        'alertTitle:"Launch blocked — access needed",alertDescription:"Buyer desk needs agency accounts before traffic goes live.",resolved:O,resolvedTitle:"Access confirmed",resolvedDescription:"Accounts delivered on agreed terms. Launch window open."',
    ),
    (
        'alertTitle:"Volume request — EU",alertDescription:"50 accounts requested. Terms needed before 18:00.",resolved:D,resolvedTitle:"Supply matched",resolvedDescription:"Terms locked. Delivery in progress."',
        'alertTitle:"Volume request — EU",alertDescription:"50 accounts · GEO locked. Terms needed before 18:00.",resolved:D,resolvedTitle:"Supply matched",resolvedDescription:"Terms confirmed. Delivery in progress."',
    ),
    (
        'alertTitle:"Supply stable",alertDescription:"Repeat order channel active. Same terms, same desk.",resolved:B,resolvedTitle:"Ready for launch",resolvedDescription:"Working resource on standby for the next push."',
        'alertTitle:"Supply stable",alertDescription:"Repeat order channel active — terms unchanged.",resolved:B,resolvedTitle:"Supply confirmed",resolvedDescription:"Working resource ready for the next launch."',
    ),
]


def patch(path: Path, reps: list[tuple[str, str]]) -> bool:
    raw = path.read_text(encoding="utf-8")
    new = raw
    for old, repl in reps:
        if old in new:
            new = new.replace(old, repl)
    if new == raw:
        return False
    path.write_text(new, encoding="utf-8")
    r = subprocess.run(["node", "--check", str(path)], capture_output=True, text=True)
    if r.returncode != 0:
        path.write_text(raw, encoding="utf-8")
        raise RuntimeError(f"{path.name}: {r.stderr.strip()}")
    return True


def main() -> None:
    f7 = CHUNKS / "f7f1c59a71681025.js"
    s8 = CHUNKS / "5308d2f8d20274da.js"
    if patch(f7, F7F1_REPLACEMENTS):
        print("ok f7f1")
    else:
        print("skip f7f1")
    if patch(s8, RESTORE_CONVERSATIONS + S308_REPLACEMENTS):
        print("ok 5308")
    else:
        print("skip 5308")
    print("done")


if __name__ == "__main__":
    main()
