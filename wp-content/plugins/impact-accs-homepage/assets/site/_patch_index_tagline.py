from pathlib import Path

NEW = "Closed access infrastructure for media buying teams. Working resource — request, terms, delivery."
OLD_VARIANTS = [
    "The AI-native observability platform for fast-moving engineering teams. Ship with confidence.",
    "The AI-native observability platform for fast-moving engineering teams. Ship with confidence.\\",
]

for fname in ["index.html", "index492c.html", "index89bf.html"]:
    p = Path(fname)
    t = p.read_text(encoding="utf-8")
    raw = t
    for old in OLD_VARIANTS:
        t = t.replace(old, NEW)
    if t != raw:
        p.write_text(t, encoding="utf-8")
        print("patched", fname, "count", raw.count(OLD_VARIANTS[0]))
