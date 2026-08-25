from pathlib import Path
import re

TG = 'https://t.me/founderads'
CTA = '''<a class="iac-telegram-cta" href="https://t.me/founderads" target="_blank" rel="noopener noreferrer" aria-label="Telegram @founderads"><span class="iac-telegram-cta__signal" aria-hidden="true"><span></span></span><span class="iac-telegram-cta__copy"><span class="iac-telegram-cta__status iac-tg-copy-en">ONLINE · 24/7</span><span class="iac-telegram-cta__status iac-tg-copy-ru">НА СВЯЗИ · 24/7</span><span class="iac-telegram-cta__title iac-tg-copy-en">MESSAGE ON TELEGRAM</span><span class="iac-telegram-cta__title iac-tg-copy-ru">НАПИСАТЬ В TELEGRAM</span></span><span class="iac-telegram-cta__arrow" aria-hidden="true">↗</span></a>'''

def read(path): return Path(path).read_text(encoding='utf-8')
def write(path, text): Path(path).write_text(text, encoding='utf-8')

# Application landing: same site visual language, zero inputs / errors / success states / consent copy.
write('wp-content/plugins/impact-accs-chrome/templates/application-page.html', f'''<section class="iac-application-page px-sides relative z-10 mx-auto w-full max-w-6xl py-10 md:py-14 lg:py-16"><div class="iac-application-layout waitlist-modal-vars flex min-h-[min(620px,calc(100vh-160px))] flex-col overflow-hidden rounded-lg border border-border/40 bg-card shadow-[inset_0_-1px_4px_0_rgba(255,255,255,0.12)] lg:flex-row"><div class="modal-image-notch relative hidden min-h-[280px] w-full overflow-hidden lg:block lg:min-h-0 lg:w-1/2"><img alt="" class="absolute inset-0 h-full w-full object-cover object-center select-none" src="/assets/site/media/modal-hangar.png"/><div class="absolute inset-x-0 bottom-0 flex flex-col items-center gap-4 p-8 pb-10"><div class="flex flex-col items-center gap-2"><svg width="62" height="14" viewBox="0 0 62 14" fill="none" class="text-muted" aria-hidden="true"><path d="M30.7839 0L30.7841 12.1274" stroke="currentColor" stroke-width="2"/><path d="M61.5678 12.1279L9.43142e-05 12.1276" stroke="currentColor" stroke-width="2"/></svg><span class="font-misc text-muted text-base leading-[1.5] font-bold tracking-[0.2em] uppercase">impact.accs</span></div><h3 class="text-primary text-title text-h2 2xl:text-h1 text-center leading-[0.68] font-bold tracking-tight uppercase">Closed access<br/>infrastructure</h3></div></div><div class="iac-waitlist-side flex min-h-0 w-full min-w-0 flex-col lg:w-1/2"><div class="iac-waitlist-tabbar flex shrink-0 items-end justify-between gap-3 border-b border-border/30 px-4 lg:px-6" style="height:var(--tab-bar-h,36px);min-height:36px"><div role="tablist" class="iac-waitlist-tablist font-misc flex min-w-0 items-end text-sm tracking-wider uppercase 2xl:text-base"><span role="tab" aria-selected="true" class="iac-waitlist-tab iac-waitlist-tab-active text-foreground">Access</span><a href="/contact/" class="iac-waitlist-tab iac-waitlist-tab-contact font-misc ml-6 text-sm tracking-wider text-muted uppercase no-underline hover:text-foreground">Contact</a></div></div><div class="iac-waitlist-form-shell bg-card flex min-h-0 flex-1 flex-col overflow-hidden"><div class="iac-waitlist-panel flex min-h-0 flex-1 flex-col justify-center overflow-y-auto px-5 py-10 lg:px-10"><div class="flex flex-col gap-7"><span class="font-misc text-primary text-xs tracking-[.18em] uppercase">@founderads · 24/7</span><h1 id="iac-application-title" class="font-display text-card-foreground text-4xl leading-none font-semibold tracking-tight uppercase text-shadow-[0_0_10px_rgba(255,255,255,0.35)] sm:text-5xl 2xl:text-6xl">Request Access</h1><p class="text-muted text-sm leading-relaxed lg:text-base">Tell the owner what you need: spend, GEO, currency, vertical and volume. Get suitable accounts for verification before payment.</p><div class="iac-formless-telegram-wrap">{CTA}</div><p class="iac-formless-telegram-meta font-misc text-muted text-xs leading-relaxed tracking-wider uppercase">@founderads · direct owner contact · verification before payment · support 24/7</p></div></div></div></div></div></section>''')

# Legacy modal fallback, also formless.
write('wp-content/plugins/impact-accs-chrome/templates/waitlist-modal.html', f'''<div id="iac-waitlist-modal" class="iac-waitlist-modal" aria-hidden="true"><div class="iac-waitlist-overlay bg-background fixed inset-0 z-80" data-iac-waitlist-close="1"></div><div class="iac-waitlist-dialog waitlist-modal-vars fixed z-(--z-dialog) flex flex-row" role="dialog" aria-modal="true" aria-labelledby="iac-waitlist-title"><div class="modal-image-notch relative hidden min-h-0 w-1/2 overflow-hidden lg:block"><img alt="" class="absolute inset-0 h-full w-full object-cover object-center select-none" src="/assets/site/media/modal-hangar.png"/><div class="absolute inset-x-0 bottom-0 flex flex-col items-center gap-4 p-8 pb-10"><span class="font-misc text-muted text-base leading-[1.5] font-bold tracking-[0.2em] uppercase">impact.accs</span><h3 class="text-primary text-title text-h2 2xl:text-h1 text-center leading-[0.68] font-bold tracking-tight uppercase">Closed access<br/>infrastructure</h3></div></div><div class="iac-waitlist-side flex min-h-0 w-full min-w-0 flex-col lg:w-1/2"><div class="iac-waitlist-tabbar flex shrink-0 items-end justify-between gap-3" style="height:var(--tab-bar-h,36px);min-height:36px"><span class="font-misc text-foreground text-sm tracking-wider uppercase">Telegram</span><button type="button" class="iac-waitlist-close-btn font-misc text-foreground hover:text-primary mr-[var(--modal-inset,10px)] mb-1 cursor-pointer border-0 bg-transparent px-4 py-2 text-sm uppercase tracking-wider" data-iac-waitlist-close="1" aria-label="Close">Close</button></div><div class="iac-waitlist-form-shell bg-card flex min-h-0 flex-1 flex-col overflow-hidden" style="border-radius:var(--form-radius)"><div class="iac-waitlist-panel flex min-h-0 flex-1 flex-col justify-center overflow-y-auto px-5 py-10 lg:px-10"><div class="flex flex-col gap-7"><span class="font-misc text-primary text-xs tracking-[.18em] uppercase">@founderads · 24/7</span><h2 id="iac-waitlist-title" class="font-display text-card-foreground text-4xl leading-none font-semibold tracking-tight uppercase sm:text-5xl 2xl:text-6xl">Request Access</h2><p class="text-muted text-sm leading-relaxed lg:text-base">Send your request directly to the owner in Telegram. No forms, queues or tickets.</p><div class="iac-formless-telegram-wrap">{CTA}</div></div></div></div></div></div></div>''')

# Footer: surgically replace the complete old form zone and every related state/details block.
footer_path = Path('wp-content/plugins/impact-accs-chrome/templates/footer.html')
footer = read(footer_path)
start_marker = '<div class="flex flex-col items-center mt-8 w-full sm:mt-0">'
end_marker = '<div class="relative"><div class="px-sides absolute bottom-0'
start = footer.find(start_marker)
end = footer.find(end_marker, start)
if start < 0 or end < 0:
    raise SystemExit('Footer form zone markers not found')
footer_cta = f'''<div class="flex flex-col items-center mt-8 w-full sm:mt-0"><div class="relative w-full max-w-xl 2xl:max-w-2xl"><div class="iac-formless-telegram-wrap">{CTA}</div><p class="iac-formless-telegram-meta font-misc text-muted mt-3 text-center text-xs leading-relaxed tracking-wider uppercase">@founderads · direct owner contact · verification before payment · support 24/7</p></div></div></div></div>'''
write(footer_path, footer[:start] + footer_cta + footer[end:])

# Obsolete waitlist JS becomes a direct Telegram compatibility router.
write('wp-content/plugins/impact-accs-chrome/assets/js/waitlist-home.js', """(function(window,document){'use strict';var TG='https://t.me/founderads';document.addEventListener('click',function(e){var c=e.target&&e.target.closest?e.target.closest('[data-iac-waitlist-open],a[href*=\"waitlist=true\"]'):null;if(!c)return;e.preventDefault();e.stopPropagation();window.open(TG,'_blank','noopener,noreferrer');},true);function boot(){var p=new URLSearchParams(window.location.search||'');if(!p.has('waitlist'))return;p.delete('waitlist');try{window.history.replaceState({},'',window.location.pathname+(p.toString()?'?'+p.toString():'')+window.location.hash);}catch(e){}window.location.href=TG;}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot);else boot();})(window,document);\n""")

# Programmatic openWaitlist() calls also become Telegram calls.
bridge_path = Path('wp-content/plugins/impact-accs-chrome/assets/js/bridge.js')
bridge = read(bridge_path)
a = bridge.find('\tfunction openWaitlist() {')
b = bridge.find('\n\tfunction closeWaitlist(', a)
if a < 0 or b < 0: raise SystemExit('openWaitlist markers not found')
bridge = bridge[:a] + "\tfunction openWaitlist() {\n\t\twindow.open('https://t.me/founderads', '_blank', 'noopener,noreferrer');\n\t}\n" + bridge[b:]
write(bridge_path, bridge)

# Reuse the exact homepage Telegram visual component everywhere.
css_path = Path('wp-content/plugins/impact-accs-chrome/assets/css/wp-overrides.css')
css = read(css_path)
marker = '/* IAC formless homepage Telegram CTA */'
pos = css.find(marker)
if pos < 0: raise SystemExit('Telegram CTA CSS block not found')
head, block = css[:pos], css[pos:]
block = block.replace(marker, '/* IAC reusable formless Telegram CTA */', 1).replace('#iac-final-cta ', '')
block += '\n.iac-formless-telegram-wrap{width:100%;max-width:760px}.iac-formless-telegram-meta{opacity:.72}.iac-tg-copy-ru{display:none!important}html[lang^="ru"] .iac-tg-copy-en,body.iac-locale-ru .iac-tg-copy-en{display:none!important}html[lang^="ru"] .iac-tg-copy-ru,body.iac-locale-ru .iac-tg-copy-ru{display:block!important}\n'
write(css_path, head + block)

# Remove all public legacy waitlist URLs, without touching mirrored homepage React source.
waitlist_href = re.compile(r"href=([\"'])([^\"']*\?waitlist=true[^\"']*)\1", re.I)
for plugin in Path('wp-content/plugins').glob('impact-*'):
    for path in plugin.rglob('*') if plugin.is_dir() else []:
        if not path.is_file() or path.suffix.lower() not in {'.html','.php','.js'}: continue
        if 'impact-accs-homepage/content/' in path.as_posix(): continue
        text = path.read_text(encoding='utf-8', errors='ignore')
        updated = waitlist_href.sub(lambda m: f'href={m.group(1)}{TG}{m.group(1)}', text)
        if updated != text: write(path, updated)

# FAQ CTA is a direct Telegram action now.
faq_path = Path('wp-content/plugins/impact-accs-chrome/includes/class-page-faq.php')
faq = read(faq_path)
faq = faq.replace("home_url( '/application/' )", "'https://t.me/founderads'", 1)
# External CTA should open safely in a new tab.
faq = faq.replace('class="iac-page-faq__cta-button" href="<?php echo esc_url( $application_url ); ?>"', 'class="iac-page-faq__cta-button" href="<?php echo esc_url( $application_url ); ?>" target="_blank" rel="noopener noreferrer"', 1)
write(faq_path, faq)

# Homepage: remove its mirrored form after the loader for EN as well as RU; no pre-hydration mutation.
hp = Path('wp-content/plugins/impact-accs-homepage/assets/js/wp-bridge.js')
home = read(hp)
home = home.replace("link.setAttribute('aria-label', 'Связаться в Telegram');", "link.setAttribute('aria-label', isRu() ? 'Связаться в Telegram' : 'Contact on Telegram');", 1)
home = home.replace("status.textContent = 'НА СВЯЗИ';", "status.textContent = isRu() ? 'НА СВЯЗИ' : 'ONLINE · 24/7';", 1)
home = home.replace("title.textContent = 'НАПИСАТЬ В TELEGRAM';", "title.textContent = isRu() ? 'НАПИСАТЬ В TELEGRAM' : 'MESSAGE ON TELEGRAM';", 1)
old = """\t\t\tif (isRu()) {\n\t\t\t\t// Structural CTA/form cleanup is safe immediately after the site's own loader completes.\n\t\t\t\twindow.setTimeout(function () {\n\t\t\t\t\tpatchHeaderCta();\n\t\t\t\t\tpatchManifesto();\n\t\t\t\t\tpatchFinalFormCta();\n\t\t\t\t}, 120);\n\t\t\t\twindow.setTimeout(ensureFaqSection, 1400);\n\t\t\t}\n"""
new = """\t\t\t// Structural form removal is safe only after the site's own loader/hydration boundary.\n\t\t\twindow.setTimeout(function () { patchFinalFormCta(); }, 120);\n\t\t\tif (isRu()) {\n\t\t\t\twindow.setTimeout(function () { patchHeaderCta(); patchManifesto(); }, 120);\n\t\t\t\twindow.setTimeout(ensureFaqSection, 1400);\n\t\t\t}\n"""
if old not in home: raise SystemExit('Homepage loader-safe patch block not found')
write(hp, home.replace(old, new, 1))

# Verify no form elements or old form-only remnants remain in shared public templates.
for path in Path('wp-content/plugins/impact-accs-chrome/templates').glob('*.html'):
    text = path.read_text(encoding='utf-8', errors='ignore')
    if re.search(r'<form\b', text, re.I): raise SystemExit('Form remains: ' + path.as_posix())
for rel in ['application-page.html','waitlist-modal.html','footer.html']:
    text = read(Path('wp-content/plugins/impact-accs-chrome/templates') / rel).lower()
    for forbidden in ['type="submit"','waitlist-error','application-error','by signing up']:
        if forbidden in text: raise SystemExit(f'{forbidden} remains in {rel}')
print('Public form replacement complete.')
