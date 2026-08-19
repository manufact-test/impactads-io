from pathlib import Path

checks = {
    "827ff": [
        "children:null",
        "integrations:Q",
        "ia-logo.svg",
        "Graphite,Logo:function",
        "ImpactIcon",
        "ACCESS IN MINUTES",
    ],
    "692": ["Merrill Lutsky", "Viktor Kozlov", "Graphite", "Platform Access"],
    "1e7f2": ["label:\"Manifesto\"", "label:\"Access\"", "RIGHTS RESERVED", "impact.corp", "ai-native"],
    "d53": ["RIGHTS RESERVED", "impact.corp", "ai-native", 'data-text":"2026"'],
    "index.html": ["Ship with confidence", "AI-native observability", "Closed access infrastructure", "RIGHTS RESERVED"],
}

for fname, pats in checks.items():
    if fname == "index.html":
        t = Path(fname).read_text(encoding="utf-8")
    else:
        chunk = {"827ff": "827ff3490ba1793e.js", "692": "692acfebb5322696.js", "1e7f2": "1e7f2c52e84d02fd.js", "d53": "d53e27b68750e6f9.js"}[fname]
        t = Path(f"_next/static/chunks/{chunk}").read_text(encoding="utf-8")
    print(f"\n{fname}:")
    for p in pats:
        print(f"  {p!r}: {t.count(p)}")
