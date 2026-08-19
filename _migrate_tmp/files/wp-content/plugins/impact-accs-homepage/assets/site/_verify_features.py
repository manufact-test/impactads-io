from pathlib import Path

ROOT = Path(__file__).resolve().parent
files = list(ROOT.rglob("*.html")) + list((ROOT / "_next/static/chunks").glob("*.js"))

def count(needle: str) -> int:
    return sum(1 for p in files if needle in p.read_text(encoding="utf-8", errors="replace"))

checks = [
    ("Manifesto nav removed", 'label:"Manifesto"', 0),
    ("Blog in nav JS", 'label:"Blog"', 1),
    ("Logo text centered", 'textAnchor:"middle"', 4),
    ("Old API alert gone", "API: 500 Error Rate Spike", 0),
    ("New launch alert", "Launch: Account Request Pending", 1),
    ("Sherwood removed", "Sherwood Callaway", 0),
    ("Denis A. present", "Denis A.", 3),
    ("View in Impact gone", "View in Impact", 0),
]

for name, needle, expect in checks:
    n = count(needle)
    status = "OK" if (expect == 0 and n == 0) or (expect > 0 and n >= expect) else f"FAIL (found {n}, want {expect})"
    print(f"{name}: {status}")
