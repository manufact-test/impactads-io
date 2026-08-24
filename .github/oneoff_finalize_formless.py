from pathlib import Path
import re


def load(path):
    return Path(path).read_text(encoding="utf-8")


def save(path, text):
    Path(path).write_text(text, encoding="utf-8")


# Fix mobile homepage menu labels that still routed Request access to /application/.
class_path = "wp-content/plugins/impact-accs-homepage/includes/class-homepage.php"
text = load(class_path)
changed = 0
for label in (
    "request access",
    "get access",
    "запросить доступ",
    "получить доступ",
    "связаться",
):
    old = "\\\"" + label + "\\\":\\\"' . $app . '\\\""
    new = "\\\"" + label + "\\\":\\\"' . $cta . '\\\""
    count = text.count(old)
    if count:
        text = text.replace(old, new)
        changed += count

# Some source versions contain a single slash before the escaped quotes after PHP parsing.
if changed == 0:
    for label in (
        "request access",
        "get access",
        "запросить доступ",
        "получить доступ",
        "связаться",
    ):
        old = "\\\"" + label + "\\\":\\\"' . $app . '\\\""
        new = "\\\"" + label + "\\\":\\\"' . $cta . '\\\""
        text = text.replace(old, new)

# Regex fallback tied only to the L={...} label map.
for label in (
    "request access",
    "get access",
    "запросить доступ",
    "получить доступ",
    "связаться",
):
    pattern = re.compile(
        r'(\\"' + re.escape(label) + r'\\":\\")\' \. \$app \. \'(\\")'
    )
    text = pattern.sub(r"\1' . $cta . '\2", text)

if "$cta      = esc_js( home_url( '/#iac-final-cta' ) );" not in text:
    raise SystemExit("Final CTA URL variable missing from homepage mobile source")

save(class_path, text)


# Fix the RU header capture listener and make the structural removal happen immediately after the safe loader boundary.
bridge_path = "wp-content/plugins/impact-accs-homepage/assets/js/wp-bridge.js"
bridge = load(bridge_path)

old_header_tail = """\t\twindow.location.href = applicationUrl();
\t}, true);

\tdocument.addEventListener('click', function (event) {
\t\tif (!isRu()) return;
\t\tvar control = event.target && event.target.closest ? event.target.closest('button, a') : null;"""
new_header_tail = """\t\tvar target = document.getElementById('iac-final-cta');
\t\tif (target) {
\t\t\ttarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
\t\t\treturn;
\t\t}
\t\twindow.location.href = '#iac-final-cta';
\t}, true);

\tdocument.addEventListener('click', function (event) {
\t\tif (!isRu()) return;
\t\tvar control = event.target && event.target.closest ? event.target.closest('button, a') : null;"""
if old_header_tail not in bridge:
    raise SystemExit("RU header application redirect source was not found")
bridge = bridge.replace(old_header_tail, new_header_tail, 1)

old_loader = """\t\t\tif (isRu()) {
\t\t\t\twindow.setTimeout(ensureFaqSection, 1400);
\t\t\t}
\t\t\t// The rest of the long page keeps the conservative post-loader delay.
\t\t\twindow.setTimeout(queueRussianPatch, 5000);"""
new_loader = """\t\t\tif (isRu()) {
\t\t\t\t// Structural CTA/form cleanup is safe immediately after the site's own loader completes.
\t\t\t\twindow.setTimeout(function () {
\t\t\t\t\tpatchHeaderCta();
\t\t\t\t\tpatchManifesto();
\t\t\t\t\tpatchFinalFormCta();
\t\t\t\t}, 120);
\t\t\t\twindow.setTimeout(ensureFaqSection, 1400);
\t\t\t}
\t\t\t// Keep the conservative full-page localization pass for the remaining long-page copy.
\t\t\twindow.setTimeout(queueRussianPatch, 5000);"""
if old_loader not in bridge:
    raise SystemExit("Homepage loader scheduling source was not found")
bridge = bridge.replace(old_loader, new_loader, 1)

# FAQ fallback should never reopen the old application form.
faq_old = """\t\t\twindow.location.href = applicationUrl();
\t\t});

\t\tcta.appendChild(ctaCopy);"""
faq_new = """\t\t\twindow.location.href = 'https://t.me/founderads';
\t\t});

\t\tcta.appendChild(ctaCopy);"""
if faq_old in bridge:
    bridge = bridge.replace(faq_old, faq_new, 1)

save(bridge_path, bridge)


# Bump homepage plugin cache version.
plugin_path = "wp-content/plugins/impact-accs-homepage/impact-accs-homepage.php"
plugin = load(plugin_path)
m = re.search(r"Version:\s*(\d+)\.(\d+)\.(\d+)", plugin)
if not m:
    raise SystemExit("Homepage plugin version missing")
old_version = ".".join(m.groups())
new_version = m.group(1) + "." + m.group(2) + "." + str(int(m.group(3)) + 1)
plugin = plugin.replace("Version: " + old_version, "Version: " + new_version, 1)
plugin = plugin.replace("'" + old_version + "'", "'" + new_version + "'")
save(plugin_path, plugin)


# Assertions against the stale routes we just removed.
bridge = load(bridge_path)
if "window.location.href = '#iac-final-cta';" not in bridge:
    raise SystemExit("Header final CTA fallback missing")
if "patchFinalFormCta();\n\t\t\t\t}, 120);" not in bridge:
    raise SystemExit("Early safe structural patch missing")
if "window.location.href = 'https://t.me/founderads';" not in bridge:
    raise SystemExit("FAQ Telegram fallback missing")

class_text = load(class_path)
label_map_start = class_text.find('var L={')
label_map_end = class_text.find('};function norm', label_map_start)
label_map = class_text[label_map_start:label_map_end] if label_map_start >= 0 and label_map_end > label_map_start else ""
if "$app" in label_map and any(label in label_map for label in ("request access", "get access", "связаться")):
    # Exact check: these CTA labels must not reference $app anymore.
    for label in ("request access", "get access", "запросить доступ", "получить доступ", "связаться"):
        if re.search(r'\\"' + re.escape(label) + r'\\":\\"\' \. \$app', label_map):
            raise SystemExit("Stale mobile CTA label route remains for " + label)

print("Final formless routing validation passed; homepage version", old_version, "->", new_version)

# Remove technical files from final tree.
for path in (
    ".github/workflows/oneoff-finalize-formless.yml",
    ".github/oneoff_finalize_formless.py",
):
    p = Path(path)
    if p.exists():
        p.unlink()
