from pathlib import Path
import re


def load(path):
    return Path(path).read_text(encoding="utf-8")


def save(path, text):
    Path(path).write_text(text, encoding="utf-8")


# 1) Shared header: direct Contacts destination + direct homepage CTA fragment.
header_path = "wp-content/plugins/impact-accs-chrome/templates/header.html"
header = load(header_path)
old_header = header
header = header.replace(
    'href="/about#careers">Contact</a>',
    'href="https://impactads.io/contact/">Contact</a>',
    1,
)
if header == old_header:
    raise SystemExit("Shared header Contact link marker not found")

header, n = re.subn(
    r'<button(?P<attrs>[^>]*data-slot="button"[^>]*)>Request access</button>',
    r'<a href="/#iac-final-cta" data-iac-scroll-final="1"\g<attrs>>Request access</a>',
    header,
    count=1,
)
if n != 1:
    raise SystemExit(f"Expected one shared header CTA button, got {n}")
save(header_path, header)


# 2) Shared chrome click bridge: explicit final-CTA links must never be hijacked to /application/.
bridge_path = "wp-content/plugins/impact-accs-chrome/assets/js/bridge.js"
bridge = load(bridge_path)
needle = "function handleApplicationNav(event) {"
if needle not in bridge:
    raise SystemExit("handleApplicationNav marker not found")
if "directFinalCta" not in bridge:
    replacement = """function handleApplicationNav(event) {
            var directFinalCta = event.target && event.target.closest
                ? event.target.closest('a[data-iac-scroll-final]')
                : null;
            if (directFinalCta) {
                return;
            }"""
    bridge = bridge.replace(needle, replacement, 1)
save(bridge_path, bridge)


# 3) Homepage server guard: old Request access capture routes to the final CTA, not /application/.
homepage_class_path = "wp-content/plugins/impact-accs-homepage/includes/class-homepage.php"
homepage_class = load(homepage_class_path)
start = homepage_class.find("private static function waitlist_click_guard_script")
if start < 0:
    raise SystemExit("waitlist_click_guard_script not found")
end = homepage_class.find("private static function", start + 10)
if end < 0:
    end = len(homepage_class)
segment = homepage_class[start:end]
segment, n = re.subn(
    r"\$url\s*=\s*class_exists\(\s*'IAC_Application_Page'\s*\)\s*\?\s*IAC_Application_Page::url\(\)\s*:\s*home_url\(\s*'/application/'\s*\);",
    "$url = '#iac-final-cta';",
    segment,
    count=1,
)
if n != 1 and "$url = '#iac-final-cta';" not in segment:
    raise SystemExit("Homepage waitlist application URL marker not found")
homepage_class = homepage_class[:start] + segment + homepage_class[end:]
save(homepage_class_path, homepage_class)


# 4) Homepage static source: Contact is a real /contact/ URL, not a query switch.
index_path = "wp-content/plugins/impact-accs-homepage/content/index.html"
index = load(index_path)
index = index.replace("https://impactads.io/?contact=true", "https://impactads.io/contact/")
index = index.replace("/?contact=true", "/contact/")
index = index.replace('href="?contact=true"', 'href="/contact/"')
index = index.replace('href=\\"?contact=true\\"', 'href=\\"/contact/\\"')
save(index_path, index)


# 5) Homepage UI source bridge: direct slider CTAs, no reviews section, Telegram final CTA.
home_bridge_path = "wp-content/plugins/impact-accs-homepage/assets/js/wp-bridge.js"
home_bridge = load(home_bridge_path)
if "function patchManifestoDirectCtas()" not in home_bridge:
    marker = "    function applyRussianPatch() {"
    if marker not in home_bridge:
        raise SystemExit("applyRussianPatch marker not found")
    helpers = r'''
    function setHomepageActionLabel(element, label) {
        if (!element) {
            return;
        }
        var walker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT);
        var node;
        while ((node = walker.nextNode())) {
            if (normalize(node.nodeValue)) {
                node.nodeValue = label;
                return;
            }
        }
        element.appendChild(document.createTextNode(label));
    }

    function patchManifestoDirectCtas() {
        var section = closestSectionWithText('ПОЧЕМУ МЫ') || closestSectionWithText('WHY US') || closestSectionWithText('Why Us');
        if (!section) {
            return;
        }

        Array.prototype.forEach.call(section.querySelectorAll('a'), function (link) {
            var text = normalize(link.textContent).toLowerCase();
            var href = (link.getAttribute('href') || '').toLowerCase();
            var actionText = /read|learn|подробнее|связаться|подобрать|получить/.test(text);
            var actionHref = href.indexOf('/blog/manifesto') !== -1 || href.indexOf('/features/') !== -1 || href.indexOf('#iac-final-cta') !== -1;
            if (!actionText && !actionHref) {
                return;
            }

            link.setAttribute('href', '#iac-final-cta');
            link.setAttribute('data-iac-scroll-final', '1');
            setHomepageActionLabel(link, 'СВЯЗАТЬСЯ');
        });
    }

    function removeCustomerReviews() {
        var sections = document.querySelectorAll('main section, section');
        for (var i = 0; i < sections.length; i += 1) {
            var section = sections[i];
            var text = normalize(section.textContent).toUpperCase();
            if (!text || text.length > 9000) {
                continue;
            }
            var russian = text.indexOf('КЛИЕНТЫ') !== -1 && text.indexOf('ОТЗЫВЫ') !== -1;
            var english = text.indexOf('CUSTOMER') !== -1 && text.indexOf('REVIEWS') !== -1;
            if (russian || english) {
                section.remove();
                return;
            }
        }
    }

    function createTelegramCta() {
        var link = document.createElement('a');
        link.className = 'iac-telegram-cta';
        link.href = 'https://t.me/founderads';
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.setAttribute('aria-label', 'Связаться в Telegram');

        var signal = document.createElement('span');
        signal.className = 'iac-telegram-cta__signal';
        signal.setAttribute('aria-hidden', 'true');
        signal.appendChild(document.createElement('span'));

        var copy = document.createElement('span');
        copy.className = 'iac-telegram-cta__copy';

        var status = document.createElement('span');
        status.className = 'iac-telegram-cta__status';
        status.textContent = 'НА СВЯЗИ';

        var title = document.createElement('span');
        title.className = 'iac-telegram-cta__title';
        title.textContent = 'НАПИСАТЬ В TELEGRAM';

        var arrow = document.createElement('span');
        arrow.className = 'iac-telegram-cta__arrow';
        arrow.setAttribute('aria-hidden', 'true');
        arrow.textContent = '↗';

        copy.appendChild(status);
        copy.appendChild(title);
        link.appendChild(signal);
        link.appendChild(copy);
        link.appendChild(arrow);
        return link;
    }

    function patchTelegramFinalCta() {
        var section = document.getElementById('iac-final-cta') ||
            closestSectionWithText('ПОЛУЧИТЕ АККАУНТ. ПРОВЕРЬТЕ. ПОТОМ ПЛАТИТЕ.') ||
            closestSectionWithText('REQUEST ACCESS');

        if (!section) {
            var forms = document.querySelectorAll('main form, form');
            if (forms.length) {
                section = forms[forms.length - 1].closest('section');
            }
        }
        if (!section) {
            return;
        }

        section.id = 'iac-final-cta';
        var existing = section.querySelector('.iac-telegram-cta');
        var form = section.querySelector('form');
        if (existing) {
            if (form) {
                form.remove();
            }
            return;
        }
        if (form) {
            form.replaceWith(createTelegramCta());
        }
    }

'''
    home_bridge = home_bridge.replace(marker, helpers + marker, 1)

# Run the direct CTA patch after every existing manifesto/carousel patch, including delayed slide updates.
if "patchManifestoDirectCtas();" not in home_bridge.split("function applyRussianPatch()", 1)[1]:
    home_bridge = home_bridge.replace(
        "patchManifesto();",
        "patchManifesto();\n        patchManifestoDirectCtas();",
    )

# Replace the final form immediately after every existing final-CTA localization pass.
if "patchTelegramFinalCta();" not in home_bridge.split("function applyRussianPatch()", 1)[1]:
    home_bridge = home_bridge.replace(
        "patchFinalFormCta();",
        "patchFinalFormCta();\n        patchTelegramFinalCta();",
    )

# Remove testimonials as part of the normal homepage patch pass.
apply_marker = "    function applyRussianPatch() {"
apply_pos = home_bridge.find(apply_marker)
if apply_pos < 0:
    raise SystemExit("applyRussianPatch disappeared")
next_func = home_bridge.find("\n    function ", apply_pos + len(apply_marker))
if next_func < 0:
    next_func = len(home_bridge)
apply_segment = home_bridge[apply_pos:next_func]
if "removeCustomerReviews();" not in apply_segment:
    brace_pos = apply_segment.rfind("}")
    if brace_pos < 0:
        raise SystemExit("Could not find applyRussianPatch closing brace")
    apply_segment = (
        apply_segment[:brace_pos]
        + "        removeCustomerReviews();\n"
        + apply_segment[brace_pos:]
    )
    home_bridge = home_bridge[:apply_pos] + apply_segment + home_bridge[next_func:]

save(home_bridge_path, home_bridge)


# 6) Telegram CTA styling, scoped to the final homepage section.
css_path = "wp-content/plugins/impact-accs-chrome/assets/css/wp-overrides.css"
css = load(css_path)
css_marker = "/* IAC formless homepage Telegram CTA */"
if css_marker not in css:
    css += r'''

/* IAC formless homepage Telegram CTA */
#iac-final-cta .iac-telegram-cta {
    position: relative;
    display: flex;
    align-items: center;
    gap: 20px;
    width: 100%;
    min-height: 92px;
    padding: 20px 24px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.2);
    background: linear-gradient(112deg, rgba(244,11,50,.16), rgba(255,255,255,.035) 58%, rgba(244,11,50,.08));
    color: #fff;
    text-decoration: none;
    box-shadow: inset 0 0 0 1px rgba(244,11,50,.05);
    transition: transform .28s ease, border-color .28s ease, background .28s ease, box-shadow .28s ease;
    isolation: isolate;
}

#iac-final-cta .iac-telegram-cta::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: -1;
    background: linear-gradient(105deg, transparent 0 35%, rgba(255,255,255,.11) 48%, transparent 61% 100%);
    transform: translateX(-120%);
    animation: iac-telegram-sheen 4.4s cubic-bezier(.4,0,.2,1) infinite;
}

#iac-final-cta .iac-telegram-cta:hover {
    transform: translateY(-2px);
    border-color: rgba(244,11,50,.72);
    background: linear-gradient(112deg, rgba(244,11,50,.24), rgba(255,255,255,.055) 58%, rgba(244,11,50,.12));
    box-shadow: 0 16px 42px rgba(0,0,0,.24), inset 0 0 0 1px rgba(244,11,50,.12);
}

#iac-final-cta .iac-telegram-cta__signal {
    position: relative;
    flex: 0 0 46px;
    width: 46px;
    height: 46px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(244,11,50,.5);
    border-radius: 50%;
    background: rgba(244,11,50,.08);
}

#iac-final-cta .iac-telegram-cta__signal::before,
#iac-final-cta .iac-telegram-cta__signal::after {
    content: '';
    position: absolute;
    inset: 9px;
    border: 1px solid rgba(244,11,50,.45);
    border-radius: 50%;
    animation: iac-telegram-pulse 2s ease-out infinite;
}

#iac-final-cta .iac-telegram-cta__signal::after {
    animation-delay: 1s;
}

#iac-final-cta .iac-telegram-cta__signal > span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #f40b32;
    box-shadow: 0 0 16px rgba(244,11,50,.9);
}

#iac-final-cta .iac-telegram-cta__copy {
    min-width: 0;
    display: flex;
    flex: 1 1 auto;
    flex-direction: column;
    gap: 4px;
}

#iac-final-cta .iac-telegram-cta__status {
    font-size: 10px;
    line-height: 1.2;
    letter-spacing: .2em;
    color: rgba(255,255,255,.55);
}

#iac-final-cta .iac-telegram-cta__title {
    font-size: clamp(16px, 1.4vw, 22px);
    line-height: 1.1;
    font-weight: 600;
    letter-spacing: .02em;
    color: #fff;
}

#iac-final-cta .iac-telegram-cta__arrow {
    flex: 0 0 42px;
    width: 42px;
    height: 42px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 50%;
    font-size: 20px;
    transition: transform .28s ease, border-color .28s ease, background .28s ease;
}

#iac-final-cta .iac-telegram-cta:hover .iac-telegram-cta__arrow {
    transform: translate(2px,-2px);
    border-color: rgba(244,11,50,.65);
    background: rgba(244,11,50,.1);
}

@keyframes iac-telegram-pulse {
    0% { transform: scale(.65); opacity: .85; }
    80%, 100% { transform: scale(1.55); opacity: 0; }
}

@keyframes iac-telegram-sheen {
    0%, 54% { transform: translateX(-120%); }
    76%, 100% { transform: translateX(120%); }
}

@media (max-width: 640px) {
    #iac-final-cta .iac-telegram-cta {
        min-height: 82px;
        padding: 16px;
        gap: 14px;
    }
    #iac-final-cta .iac-telegram-cta__signal {
        flex-basis: 40px;
        width: 40px;
        height: 40px;
    }
    #iac-final-cta .iac-telegram-cta__arrow {
        flex-basis: 38px;
        width: 38px;
        height: 38px;
    }
}

@media (prefers-reduced-motion: reduce) {
    #iac-final-cta .iac-telegram-cta,
    #iac-final-cta .iac-telegram-cta::before,
    #iac-final-cta .iac-telegram-cta__signal::before,
    #iac-final-cta .iac-telegram-cta__signal::after,
    #iac-final-cta .iac-telegram-cta__arrow {
        animation: none !important;
        transition: none !important;
    }
}
'''
save(css_path, css)


# 7) Bump plugin patch versions so WordPress cache-busted assets update immediately.
def bump_patch(path, expected=None):
    text = load(path)
    match = re.search(r"Version:\s*(\d+)\.(\d+)\.(\d+)", text)
    if not match:
        raise SystemExit(f"No plugin version in {path}")
    old = match.group(0).split(":", 1)[1].strip()
    if expected and old != expected:
        print(f"{path}: current version {old}, expected {expected}; bumping current patch")
    major, minor, patch = map(int, old.split("."))
    new = f"{major}.{minor}.{patch + 1}"
    text = text.replace(f"Version: {old}", f"Version: {new}", 1)
    text = text.replace("'" + old + "'", "'" + new + "'")
    text = text.replace('"' + old + '"', '"' + new + '"')
    save(path, text)
    print(f"{path}: {old} -> {new}")


bump_patch("wp-content/plugins/impact-accs-homepage/impact-accs-homepage.php", "1.5.48")
bump_patch("wp-content/plugins/impact-accs-chrome/impact-accs-chrome.php", "2.8.19")


# 8) Validate all requested behavior exists in source before committing.
checks = {
    "wp-content/plugins/impact-accs-chrome/templates/header.html": [
        "https://impactads.io/contact/",
        'data-iac-scroll-final="1"',
        "/#iac-final-cta",
    ],
    "wp-content/plugins/impact-accs-homepage/includes/class-homepage.php": [
        "$url = '#iac-final-cta';",
    ],
    "wp-content/plugins/impact-accs-homepage/assets/js/wp-bridge.js": [
        "setHomepageActionLabel(link, 'СВЯЗАТЬСЯ')",
        "function removeCustomerReviews()",
        "link.href = 'https://t.me/founderads'",
        "function patchTelegramFinalCta()",
    ],
    "wp-content/plugins/impact-accs-chrome/assets/css/wp-overrides.css": [
        "IAC formless homepage Telegram CTA",
        "@keyframes iac-telegram-pulse",
    ],
}
for path, needles in checks.items():
    text = load(path)
    for needle in needles:
        if needle not in text:
            raise SystemExit(f"Missing {needle!r} in {path}")

print("Source validation passed")

# Self-clean technical files so the final commit contains only product changes.
for path in (
    ".github/workflows/oneoff-formless-homepage.yml",
    ".github/oneoff_formless_patch.py",
):
    target = Path(path)
    if target.exists():
        target.unlink()
