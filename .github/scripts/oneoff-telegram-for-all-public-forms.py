from pathlib import Path
import re

TG = 'https://t.me/founderads'

CTA = '''<a class="iac-telegram-cta" href="https://t.me/founderads" target="_blank" rel="noopener noreferrer" aria-label="Telegram @founderads">
<span class="iac-telegram-cta__signal" aria-hidden="true"><span></span></span>
<span class="iac-telegram-cta__copy">
<span class="iac-telegram-cta__status iac-tg-copy-en">ONLINE · 24/7</span><span class="iac-telegram-cta__status iac-tg-copy-ru">НА СВЯЗИ · 24/7</span>
<span class="iac-telegram-cta__title iac-tg-copy-en">MESSAGE ON TELEGRAM</span><span class="iac-telegram-cta__title iac-tg-copy-ru">НАПИСАТЬ В TELEGRAM</span>
</span>
<span class="iac-telegram-cta__arrow" aria-hidden="true">↗</span>
</a>'''


def write(path, text):
    Path(path).write_text(text, encoding='utf-8')

# 1) Application page: retain the visual shell, remove every form-only element.
application = f'''<section class="iac-application-page px-sides relative z-10 mx-auto w-full max-w-6xl py-10 md:py-14 lg:py-16">
<div class="iac-application-layout waitlist-modal-vars flex min-h-[min(620px,calc(100vh-160px))] flex-col overflow-hidden rounded-lg border border-border/40 bg-card shadow-[inset_0_-1px_4px_0_rgba(255,255,255,0.12)] lg:flex-row">
<div class="modal-image-notch relative hidden min-h-[280px] w-full overflow-hidden lg:block lg:min-h-0 lg:w-1/2">
<img alt="" class="absolute inset-0 h-full w-full object-cover object-center select-none" src="/assets/site/media/modal-hangar.png"/>
<div class="absolute inset-x-0 bottom-0 flex flex-col items-center gap-4 p-8 pb-10"><div class="flex flex-col items-center gap-2"><svg width="62" height="14" viewBox="0 0 62 14" fill="none" class="text-muted" aria-hidden="true"><path d="M30.7839 0L30.7841 12.1274" stroke="currentColor" stroke-width="2"/><path d="M61.5678 12.1279L9.43142e-05 12.1276" stroke="currentColor" stroke-width="2"/></svg><span class="font-misc text-muted text-base leading-[1.5] font-bold tracking-[0.2em] uppercase">impact.accs</span></div><h3 class="text-primary text-title text-h2 2xl:text-h1 text-center leading-[0.68] font-bold tracking-tight uppercase">Closed access<br/>infrastructure</h3></div>
</div>
<div class="iac-waitlist-side flex min-h-0 w-full min-w-0 flex-col lg:w-1/2">
<div class="iac-waitlist-tabbar flex shrink-0 items-end justify-between gap-3 border-b border-border/30 px-4 lg:px-6" style="height:var(--tab-bar-h,36px);min-height:36px"><div role="tablist" class="iac-waitlist-tablist font-misc flex min-w-0 items-end text-sm tracking-wider uppercase 2xl:text-base"><span role="tab" aria-selected="true" class="iac-waitlist-tab iac-waitlist-tab-active group relative z-10 shrink-0 p-0 text-foreground">Access</span><a href="/contact/" class="iac-waitlist-tab iac-waitlist-tab-contact font-misc relative z-[9] -ml-4 shrink-0 p-0 text-sm tracking-wider text-muted uppercase no-underline hover:text-foreground 2xl:-ml-6">Contact</a></div></div>
<div class="iac-waitlist-form-shell bg-card flex min-h-0 flex-1 flex-col overflow-hidden"><div class="iac-waitlist-panel flex min-h-0 flex-1 flex-col justify-center overflow-y-auto px-5 py-10 lg:px-10"><div class="flex flex-col gap-7"><span class="font-misc text-primary text-xs tracking-[.18em] uppercase">@founderads · 24/7</span><h1 id="iac-application-title" class="font-display text-card-foreground text-4xl leading-none font-semibold tracking-tight uppercase text-shadow-[0_0_10px_rgba(255,255,255,0.35)] sm:text-5xl 2xl:text-6xl">Request Access</h1><p class="text-muted text-sm leading-relaxed lg:text-base">Tell the owner what you need: spend, GEO, currency, vertical and volume. Get suitable accounts for verification before payment.</p><div class="iac-formless-telegram-wrap">{CTA}</div><p class="iac-formless-telegram-meta font-misc text-muted text-xs leading-relaxed tracking-wider uppercase">@founderads · direct owner contact · verification before payment · support 24/7</p></div></div></div>
</div>
</div>
</section>'''
write('wp-content/plugins/impact-accs-chrome/templates/application-page.html', application)

# 2) Waitlist modal remains only as a backwards-compatible fallback, but is now completely formless.
modal = f'''<div id="iac-waitlist-modal" class="iac-waitlist-modal" aria-hidden="true">
<div class="iac-waitlist-overlay bg-background fixed inset-0 z-80" data-iac-waitlist-close="1"></div>
<div class="iac-waitlist-dialog waitlist-modal-vars fixed z-(--z-dialog) flex flex-row" role="dialog" aria-modal="true" aria-labelledby="iac-waitlist-title">
<div class="modal-image-notch relative hidden min-h-0 w-1/2 overflow-hidden lg:block"><img alt="" class="absolute inset-0 h-full w-full object-cover object-center select-none" src="/assets/site/media/modal-hangar.png"/><div class="absolute inset-x-0 bottom-0 flex flex-col items-center gap-4 p-8 pb-10"><div class="flex flex-col items-center gap-2"><svg width="62" height="14" viewBox="0 0 62 14" fill="none" class="text-muted" aria-hidden="true"><path d="M30.7839 0L30.7841 12.1274" stroke="currentColor" stroke-width="2"/><path d="M61.5678 12.1279L9.43142e-05 12.1276" stroke="currentColor" stroke-width="2"/></svg><span class="font-misc text-muted text-base leading-[1.5] font-bold tracking-[0.2em] uppercase">impact.accs</span></div><h3 class="text-primary text-title text-h2 2xl:text-h1 text-center leading-[0.68] font-bold tracking-tight uppercase">Closed access<br/>infrastructure</h3></div></div>
<div class="iac-waitlist-side flex min-h-0 w-full min-w-0 flex-col lg:w-1/2"><div class="iac-waitlist-tabbar flex shrink-0 items-end justify-between gap-3" style="height:var(--tab-bar-h,36px);min-height:36px"><div class="font-misc flex min-w-0 items-end text-sm tracking-wider uppercase 2xl:text-base"><span class="text-foreground">Telegram</span><a href="/contact/" class="ml-6 text-muted hover:text-foreground">Contact</a></div><button type="button" class="iac-waitlist-close-btn font-misc text-foreground hover:text-primary relative z-20 mr-[var(--modal-inset,10px)] mb-1 flex shrink-0 cursor-pointer items-center justify-center self-stretch border-0 bg-transparent px-4 py-2 text-sm uppercase tracking-wider" data-iac-waitlist-close="1" aria-label="Close">Close</button></div><div class="iac-waitlist-form-shell bg-card flex min-h-0 flex-1 flex-col overflow-hidden shadow-[inset_0_-1px_4px_0_rgba(255,255,255,0.25)]" style="border-radius:var(--form-radius)"><div class="iac-waitlist-panel flex min-h-0 flex-1 flex-col justify-center overflow-y-auto px-5 py-10 lg:px-10"><div class="flex flex-col gap-7"><span class="font-misc text-primary text-xs tracking-[.18em] uppercase">@founderads · 24/7</span><h2 id="iac-waitlist-title" class="font-display text-card-foreground text-4xl leading-none font-semibold tracking-tight uppercase text-shadow-[0_0_10px_rgba(255,255,255,0.35)] sm:text-5xl 2xl:text-6xl">Request Access</h2><p class="text-muted text-sm leading-relaxed lg:text-base">Send your request directly to the owner in Telegram. No forms, queues or tickets.</p><div class="iac-formless-telegram-wrap">{CTA}</div></div></div></div></div>
</div>
</div>'''
write('wp-content/plugins/impact-accs-chrome/templates/waitlist-modal.html', modal)

# 3) Footer: replace the full form zone, including badge, success/error UI and consent line.
footer_path = Path('wp-content/plugins/impact-accs-chrome/templates/footer.html')
footer = footer_path.read_text(encoding='utf-8')
start_marker = '<div class="flex flex-col items-center mt-8 w-full sm:mt-0">'
end_marker = '<div class="relative"><div class="px-sides absolute bottom-0'
start = footer.find(start_marker)
end = footer.find(end_marker, start)
if start < 0 or end < 0 or end <= start:
    raise SystemExit('Could not locate the complete footer form zone')
footer_cta = f'''<div class="flex flex-col items-center mt-8 w-full sm:mt-0"><div class="relative w-full max-w-xl 2xl:max-w-2xl"><div class="iac-formless-telegram-wrap">{CTA}</div><p class="iac-formless-telegram-meta font-misc text-muted mt-3 text-center text-xs leading-relaxed tracking-wider uppercase">@founderads · direct owner contact · verification before payment · support 24/7</p></div></div></div></div>'''
footer = footer[:start] + footer_cta + footer[end:]
footer_path.write_text(footer, encoding='utf-8')

# 4) Old waitlist routing: direct Telegram, no validation, no form state machine.
waitlist_js = '''(function (window, document) {\n\t'use strict';\n\tvar TELEGRAM_URL = 'https://t.me/founderads';\n\n\tfunction goTelegram(event) {\n\t\tif (event) {\n\t\t\tevent.preventDefault();\n\t\t\tevent.stopPropagation();\n\t\t}\n\t\twindow.open(TELEGRAM_URL, '_blank', 'noopener,noreferrer');\n\t}\n\n\tdocument.addEventListener('click', function (event) {\n\t\tvar control = event.target && event.target.closest ? event.target.closest('[data-iac-waitlist-open], a[href*="waitlist=true"]') : null;\n\t\tif (!control) return;\n\t\tgoTelegram(event);\n\t}, true);\n\n\tfunction boot() {\n\t\tvar params = new URLSearchParams(window.location.search || '');\n\t\tif (!params.has('waitlist')) return;\n\t\tparams.delete('waitlist');\n\t\tvar clean = window.location.pathname + (params.toString() ? '?' + params.toString() : '') + window.location.hash;\n\t\ttry { window.history.replaceState({}, '', clean); } catch (e) {}\n\t\twindow.location.href = TELEGRAM_URL;\n\t}\n\n\tif (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);\n\telse boot();\n})(window, document);\n'''
write('wp-content/plugins/impact-accs-chrome/assets/js/waitlist-home.js', waitlist_js)

# 5) Shared bridge: any legacy programmatic openWaitlist call now opens Telegram.
bridge_path = Path('wp-content/plugins/impact-accs-chrome/assets/js/bridge.js')
bridge = bridge_path.read_text(encoding='utf-8')
open_start = bridge.find('\tfunction openWaitlist() {')
close_start = bridge.find('\n\tfunction closeWaitlist(', open_start)
if open_start < 0 or close_start < 0:
    raise SystemExit('Could not locate openWaitlist() in shared bridge')
replacement = "\tfunction openWaitlist() {\n\t\twindow.open('https://t.me/founderads', '_blank', 'noopener,noreferrer');\n\t}\n"
bridge = bridge[:open_start] + replacement + bridge[close_start:]
bridge_path.write_text(bridge, encoding='utf-8')

# 6) Make the homepage Telegram component CSS reusable across all chrome pages.
css_path = Path('wp-content/plugins/impact-accs-chrome/assets/css/wp-overrides.css')
css = css_path.read_text(encoding='utf-8')
comment = '/* IAC formless homepage Telegram CTA */'
pos = css.find(comment)
if pos < 0:
    raise SystemExit('Telegram CTA CSS block not found')
head, block = css[:pos], css[pos:]
block = block.replace('/* IAC formless homepage Telegram CTA */', '/* IAC reusable formless Telegram CTA */', 1)
block = block.replace('#iac-final-cta ', '')
extra = '''\n.iac-formless-telegram-wrap{width:100%;max-width:760px;}\n.iac-formless-telegram-meta{opacity:.72;}\n.iac-tg-copy-ru{display:none!important;}\nhtml[lang^="ru"] .iac-tg-copy-en{display:none!important;}\nhtml[lang^="ru"] .iac-tg-copy-ru{display:block!important;}\nbody.iac-locale-ru .iac-tg-copy-en{display:none!important;}\nbody.iac-locale-ru .iac-tg-copy-ru{display:block!important;}\n'''
css_path.write_text(head + block + extra, encoding='utf-8')

# 7) Every legacy waitlist href in public Impact templates goes straight to Telegram.
for plugin in Path('wp-content/plugins').glob('impact-*'):
    if not plugin.is_dir():
        continue
    for path in plugin.rglob('*'):
        if not path.is_file() or path.suffix.lower() not in {'.html', '.php', '.js'}:
            continue
        # Do not mutate the mirrored homepage static HTML before React hydration.
        if 'impact-accs-homepage/content/' in path.as_posix():
            continue
        text = path.read_text(encoding='utf-8', errors='ignore')
        updated = re.sub(r'href=(['\"])([^'\"]*\?waitlist=true[^'\"]*)\1', lambda m: f'href={m.group(1)}{TG}{m.group(1)}', text, flags=re.I)
        if updated != text:
            path.write_text(updated, encoding='utf-8')

# 8) Internal FAQ CTAs should no longer lead through the former application form.
faq_path = Path('wp-content/plugins/impact-accs-chrome/includes/class-page-faq.php')
faq = faq_path.read_text(encoding='utf-8')
faq = faq.replace("home_url( '/application/' )", "'https://t.me/founderads'", 1)
faq_path.write_text(faq, encoding='utf-8')

# 9) Homepage final form cleanup for both languages, safely after the site's loader.
home_bridge_path = Path('wp-content/plugins/impact-accs-homepage/assets/js/wp-bridge.js')
home_bridge = home_bridge_path.read_text(encoding='utf-8')
home_bridge = home_bridge.replace("\t\tlink.setAttribute('aria-label', 'Связаться в Telegram');", "\t\tlink.setAttribute('aria-label', isRu() ? 'Связаться в Telegram' : 'Contact on Telegram');", 1)
home_bridge = home_bridge.replace("\t\tstatus.textContent = 'НА СВЯЗИ';", "\t\tstatus.textContent = isRu() ? 'НА СВЯЗИ' : 'ONLINE · 24/7';", 1)
home_bridge = home_bridge.replace("\t\ttitle.textContent = 'НАПИСАТЬ В TELEGRAM';", "\t\ttitle.textContent = isRu() ? 'НАПИСАТЬ В TELEGRAM' : 'MESSAGE ON TELEGRAM';", 1)
old_boot = """\t\t\tif (isRu()) {\n\t\t\t\t// Structural CTA/form cleanup is safe immediately after the site's own loader completes.\n\t\t\t\twindow.setTimeout(function () {\n\t\t\t\t\tpatchHeaderCta();\n\t\t\t\t\tpatchManifesto();\n\t\t\t\t\tpatchFinalFormCta();\n\t\t\t\t}, 120);\n\t\t\t\twindow.setTimeout(ensureFaqSection, 1400);\n\t\t\t}\n"""
new_boot = """\t\t\t// Structural form removal is safe only after the site's own loader/hydration boundary.\n\t\t\twindow.setTimeout(function () {\n\t\t\t\tpatchFinalFormCta();\n\t\t\t}, 120);\n\t\t\tif (isRu()) {\n\t\t\t\twindow.setTimeout(function () {\n\t\t\t\t\tpatchHeaderCta();\n\t\t\t\t\tpatchManifesto();\n\t\t\t\t}, 120);\n\t\t\t\twindow.setTimeout(ensureFaqSection, 1400);\n\t\t\t}\n"""
if old_boot not in home_bridge:
    raise SystemExit('Homepage post-loader block not found')
home_bridge = home_bridge.replace(old_boot, new_boot, 1)
home_bridge_path.write_text(home_bridge, encoding='utf-8')

# 10) Verification: no actual form tags remain in shared public chrome templates.
forms_left = []
for path in Path('wp-content/plugins/impact-accs-chrome/templates').glob('*.html'):
    text = path.read_text(encoding='utf-8', errors='ignore')
    if re.search(r'<form\\b', text, re.I):
        forms_left.append(path.as_posix())
if forms_left:
    raise SystemExit('Public chrome forms remain: ' + ', '.join(forms_left))

# Obvious form-only remnants must be absent from the three former form templates.
for rel in [
    'wp-content/plugins/impact-accs-chrome/templates/application-page.html',
    'wp-content/plugins/impact-accs-chrome/templates/waitlist-modal.html',
    'wp-content/plugins/impact-accs-chrome/templates/footer.html',
]:
    text = Path(rel).read_text(encoding='utf-8', errors='ignore').lower()
    for forbidden in ['type="submit"', 'waitlist-error', 'application-error', 'by signing up']:
        if forbidden in text:
            raise SystemExit(f'{forbidden!r} still present in {rel}')

print('All public chrome forms replaced with Telegram CTA successfully.')
