from pathlib import Path
import re


def load(path):
    return Path(path).read_text(encoding="utf-8")


def save(path, text):
    Path(path).write_text(text, encoding="utf-8")


# Shared header: Contacts goes directly to /contact/ on every page.
header_path = "wp-content/plugins/impact-accs-chrome/templates/header.html"
header = load(header_path)
header, contact_count = re.subn(
    r'href="(?:/about#careers|/\?contact=true|\?contact=true|/contact/?)">Contact</a>',
    'href="https://impactads.io/contact/">Contact</a>',
    header,
    count=1,
)
if contact_count != 1 and 'href="https://impactads.io/contact/">Contact</a>' not in header:
    raise SystemExit("Shared header Contact source was not found")

# Shared desktop CTA is a real anchor to the homepage final block.
if 'data-iac-scroll-final="1"' not in header:
    header, cta_count = re.subn(
        r'<button(?P<attrs>[^>]*data-slot="button"[^>]*)>Request access</button>',
        r'<a href="/#iac-final-cta" data-iac-scroll-final="1"\g<attrs>>Request access</a>',
        header,
        count=1,
    )
    if cta_count != 1:
        raise SystemExit("Shared header Request access source was not found")
save(header_path, header)


# Shared chrome bridge must respect explicit final-CTA anchors instead of hijacking them to /application/.
chrome_bridge_path = "wp-content/plugins/impact-accs-chrome/assets/js/bridge.js"
chrome_bridge = load(chrome_bridge_path)
if "directFinalCta" not in chrome_bridge:
    marker = "function handleApplicationNav(event) {"
    if marker not in chrome_bridge:
        raise SystemExit("handleApplicationNav source was not found")
    chrome_bridge = chrome_bridge.replace(
        marker,
        """function handleApplicationNav(event) {
            var directFinalCta = event.target && event.target.closest
                ? event.target.closest('a[data-iac-scroll-final]')
                : null;
            if (directFinalCta) {
                return;
            }""",
        1,
    )
save(chrome_bridge_path, chrome_bridge)


# Homepage server guard + mobile menu: request-access actions point to the final CTA, not the application form.
homepage_class_path = "wp-content/plugins/impact-accs-homepage/includes/class-homepage.php"
homepage_class = load(homepage_class_path)
start = homepage_class.find("function waitlist_click_guard_script")
if start < 0:
    raise SystemExit("waitlist_click_guard_script source was not found")
end = homepage_class.find("\n\tpublic static function", start + 10)
if end < 0:
    end = len(homepage_class)
segment = homepage_class[start:end]
segment, guard_count = re.subn(
    r"\$url\s*=\s*class_exists\(\s*'IAC_Application_Page'\s*\)\s*\?\s*IAC_Application_Page::url\(\)\s*:\s*home_url\(\s*'/application/'\s*\);",
    "$url = '#iac-final-cta';",
    segment,
    count=1,
)
if guard_count != 1 and "$url = '#iac-final-cta';" not in segment:
    raise SystemExit("Homepage header application guard source was not found")
homepage_class = homepage_class[:start] + segment + homepage_class[end:]

# Give the mobile menu its own final CTA URL while preserving genuine /application/ routes.
if "$cta      = esc_js( home_url( '/#iac-final-cta' ) );" not in homepage_class:
    app_line = re.search(r"(^\s*\$app\s*=.*?;\s*$)", homepage_class, flags=re.M)
    if app_line:
        insertion = app_line.group(1) + "\n\t\t$cta      = esc_js( home_url( '/#iac-final-cta' ) );"
        homepage_class = homepage_class[:app_line.start()] + insertion + homepage_class[app_line.end():]

for label in (
    "request access",
    "get access",
    "запросить доступ",
    "получить доступ",
    "связаться",
):
    old = "\\\"" + label + "\\\":\\\"' . $app . '\\\""
    new = "\\\"" + label + "\\\":\\\"' . $cta . '\\\""
    homepage_class = homepage_class.replace(old, new)

save(homepage_class_path, homepage_class)


# Homepage static Next mirror: direct Contact URL and no old waitlist query on source links.
index_path = "wp-content/plugins/impact-accs-homepage/content/index.html"
index = load(index_path)
index = index.replace("https://impactads.io/?contact=true", "https://impactads.io/contact/")
index = index.replace("/?contact=true", "/contact/")
index = index.replace("?contact=true", "/contact/")
index = index.replace("/?waitlist=true", "/#iac-final-cta")
index = index.replace("?waitlist=true", "#iac-final-cta")
save(index_path, index)


# Homepage bridge: replace the existing slider and final-form functions directly in source.
home_bridge_path = "wp-content/plugins/impact-accs-homepage/assets/js/wp-bridge.js"
home_bridge = load(home_bridge_path)
pattern = re.compile(
    r"\tfunction patchManifesto\(\) \{.*?\n\t\}\n\n\tfunction patchFinalFormCta\(\) \{.*?\n\t\}\n",
    flags=re.S,
)
replacement = r'''	function patchManifesto() {
		var section = closestSectionWithText('ПОЧЕМУ МЫ') || closestSectionWithText('WHY US') || closestSectionWithText('Why Us');
		if (!section) return;

		replaceTextNodes(section, {
			'Resource over noise': 'ТОЛЬКО СПЕНД. БЕЗ ПУСТОГО ФАРМА.',
			'Account sellers have accumulated noise over the years. None of it makes launches faster. If access is infrastructure, the best interface is direct contact — not screenshots, not emojis, not random chats.': 'Мы продаём Google Ads аккаунты с реальной историей открутки. Не автореги и не фарм без трат: у аккаунта уже есть спенд, а значит — накопленный траст, выше лимиты, мягче модерация и меньше проверок при первом заливе. Вы платите за готовый рабочий ресурс и экономите время на самостоятельном прогреве перед заливом.',
			'Working resource': 'СНАЧАЛА ПРОВЕРЯЕТЕ. ПОТОМ ПЛАТИТЕ.',
			'Random Telegram sellers used to be the norm. Structured supply is the strength — clear request, fast contact, working access under terms your team can trust.': 'Получаете аккаунт и самостоятельно сверяете заявленные параметры: спенд, гео и валюту. Всё совпадает и аккаунт работает — оплачиваете в USDT TRC20. Не хотите проводить крупную сделку напрямую — подключаем гаранта. Комиссию оплачивает покупатель.',
			'Chaos is optional': 'ПОСТАВЩИК, КОТОРОГО НЕ НУЖНО МЕНЯТЬ',
			'Random sellers are broken. Unstable supply and vague terms. The future is structured access — clear request, fast contact, working resource.': 'Аккаунт не заходит или не соответствует заявленному спенду, гео или валюте — заменяем, пока вы не внесли в него изменения. Без тикетов, мелкого шрифта и споров. По каждой покупке на связи лично владелец, поддержка работает 24/7. За impact. — 7 лет на рынке, 15 000 выданных аккаунтов и 100+ активных команд.'
		});

		Array.prototype.forEach.call(section.querySelectorAll('a'), function (link) {
			var text = normalize(link.textContent).toLowerCase();
			var href = (link.getAttribute('href') || '').toLowerCase();
			var isAction = /read|learn|подробнее|связаться|подобрать|получить/.test(text) ||
				href.indexOf('/blog/manifesto') !== -1 ||
				href.indexOf('/features/') !== -1 ||
				href.indexOf('#iac-final-cta') !== -1;
			if (!isAction) return;

			link.setAttribute('href', '#iac-final-cta');
			link.setAttribute('data-iac-scroll-final', '1');

			var walker = document.createTreeWalker(link, NodeFilter.SHOW_TEXT);
			var node;
			while ((node = walker.nextNode())) {
				if (!normalize(node.nodeValue)) continue;
				node.nodeValue = 'СВЯЗАТЬСЯ';
				break;
			}
		});
	}

	function patchFinalFormCta() {
		var section = closestSectionWithText('ПОЛУЧИТЕ АККАУНТ. ПРОВЕРЬТЕ. ПОТОМ ПЛАТИТЕ.') || document.querySelector('footer');
		if (!section) return;
		section.id = 'iac-final-cta';

		var sections = document.querySelectorAll('main section, section');
		for (var i = 0; i < sections.length; i += 1) {
			var reviewText = normalize(sections[i].textContent).toUpperCase();
			if (!reviewText || reviewText.length > 9000) continue;
			var ruReviews = reviewText.indexOf('КЛИЕНТЫ') !== -1 && reviewText.indexOf('ОТЗЫВЫ') !== -1;
			var enReviews = reviewText.indexOf('CUSTOMER') !== -1 && reviewText.indexOf('REVIEWS') !== -1;
			if (ruReviews || enReviews) {
				sections[i].remove();
				break;
			}
		}

		var form = section.querySelector('form');
		var existing = section.querySelector('.iac-telegram-cta');
		if (existing) {
			if (form) form.remove();
			return;
		}
		if (!form) return;

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
		copy.appendChild(status);
		copy.appendChild(title);

		var arrow = document.createElement('span');
		arrow.className = 'iac-telegram-cta__arrow';
		arrow.setAttribute('aria-hidden', 'true');
		arrow.textContent = '↗';

		link.appendChild(signal);
		link.appendChild(copy);
		link.appendChild(arrow);
		form.replaceWith(link);
	}
'''
home_bridge, replaced = pattern.subn(lambda m: replacement, home_bridge, count=1)
if replaced != 1:
    raise SystemExit(f"Expected one manifesto/final-form source pair, got {replaced}")
save(home_bridge_path, home_bridge)


# Static stylesheet for the animated Telegram CTA.
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
#iac-final-cta .iac-telegram-cta__signal::after { animation-delay: 1s; }
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
    font-size: clamp(16px,1.4vw,22px);
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
    80%,100% { transform: scale(1.55); opacity: 0; }
}
@keyframes iac-telegram-sheen {
    0%,54% { transform: translateX(-120%); }
    76%,100% { transform: translateX(120%); }
}
@media (max-width:640px) {
    #iac-final-cta .iac-telegram-cta { min-height:82px; padding:16px; gap:14px; }
    #iac-final-cta .iac-telegram-cta__signal { flex-basis:40px; width:40px; height:40px; }
    #iac-final-cta .iac-telegram-cta__arrow { flex-basis:38px; width:38px; height:38px; }
}
@media (prefers-reduced-motion:reduce) {
    #iac-final-cta .iac-telegram-cta,
    #iac-final-cta .iac-telegram-cta::before,
    #iac-final-cta .iac-telegram-cta__signal::before,
    #iac-final-cta .iac-telegram-cta__signal::after,
    #iac-final-cta .iac-telegram-cta__arrow { animation:none !important; transition:none !important; }
}
'''
save(css_path, css)


# Cache-bust both plugins after source changes.
def bump_patch(path):
    text = load(path)
    match = re.search(r"Version:\s*(\d+)\.(\d+)\.(\d+)", text)
    if not match:
        raise SystemExit(f"No Version header in {path}")
    old = match.group(1) + "." + match.group(2) + "." + match.group(3)
    new = match.group(1) + "." + match.group(2) + "." + str(int(match.group(3)) + 1)
    text = text.replace("Version: " + old, "Version: " + new, 1)
    text = text.replace("'" + old + "'", "'" + new + "'")
    text = text.replace('"' + old + '"', '"' + new + '"')
    save(path, text)
    print(path + ": " + old + " -> " + new)


bump_patch("wp-content/plugins/impact-accs-homepage/impact-accs-homepage.php")
bump_patch("wp-content/plugins/impact-accs-chrome/impact-accs-chrome.php")


# Validate requested product behavior in the resulting sources.
validation = {
    header_path: ["https://impactads.io/contact/", "/#iac-final-cta", "data-iac-scroll-final"],
    homepage_class_path: ["$url = '#iac-final-cta';", "$cta      = esc_js( home_url( '/#iac-final-cta' ) );"],
    home_bridge_path: ["node.nodeValue = 'СВЯЗАТЬСЯ'", "https://t.me/founderads", "ruReviews", "form.replaceWith(link)"],
    css_path: [css_marker, "@keyframes iac-telegram-pulse"],
}
for path, needles in validation.items():
    text = load(path)
    for needle in needles:
        if needle not in text:
            raise SystemExit(f"Validation failed: {needle!r} missing from {path}")

print("Product source validation passed")

# Technical runner files must not remain in final main.
for path in (
    ".github/workflows/oneoff-formless-homepage.yml",
    ".github/oneoff_formless_patch.py",
    ".github/oneoff_formless_patch_v2.py",
):
    p = Path(path)
    if p.exists():
        p.unlink()
