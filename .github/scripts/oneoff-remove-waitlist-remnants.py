from pathlib import Path

bridge_path = Path('wp-content/plugins/impact-accs-homepage/assets/js/wp-bridge.js')
bridge = bridge_path.read_text(encoding='utf-8')

marker = "\t\tsection.id = 'iac-final-cta';\n"
if bridge.count(marker) != 1:
    raise SystemExit(f'Expected one final CTA section marker, found {bridge.count(marker)}')

cleanup = """\t\tsection.id = 'iac-final-cta';

\t\tvar waitlistError = section.querySelector('#waitlist-error');
\t\tif (waitlistError) {
\t\t\tvar waitlistErrorWrap = waitlistError.closest('div');
\t\t\tif (waitlistErrorWrap) waitlistErrorWrap.remove();
\t\t}

\t\tArray.prototype.forEach.call(section.querySelectorAll('p'), function (label) {
\t\t\tvar labelText = normalize(label.textContent).toLowerCase();
\t\t\tif (labelText !== 'access' && labelText !== 'waitlist' && labelText !== 'лист ожидания') return;
\t\t\tvar labelWrap = label.parentElement;
\t\t\tif (!labelWrap || !labelWrap.classList.contains('absolute') || !labelWrap.classList.contains('top-0') || !labelWrap.classList.contains('left-4')) return;
\t\t\tlabelWrap.remove();
\t\t});
"""
bridge = bridge.replace(marker, cleanup, 1)

old_style = ".iah-faq__cta-button{position:relative;isolation:isolate;display:inline-flex;align-items:center;justify-content:center;min-height:58px;padding:0 28px;border:1px solid rgba(255,255,255,.14);border-radius:999px;background:var(--iah-faq-red);"
new_style = ".iah-faq__cta-button{position:relative;isolation:isolate;display:inline-flex;align-items:center;justify-content:center;min-height:52px;padding:0 24px;border:1px solid rgba(255,255,255,.14);border-radius:6px;background:var(--iah-faq-red);"
if bridge.count(old_style) != 1:
    raise SystemExit(f'Expected one FAQ CTA style, found {bridge.count(old_style)}')
bridge = bridge.replace(old_style, new_style, 1)
bridge_path.write_text(bridge, encoding='utf-8')

plugin_path = Path('wp-content/plugins/impact-accs-homepage/impact-accs-homepage.php')
plugin = plugin_path.read_text(encoding='utf-8')
if plugin.count('Version:           1.5.51') != 1 or plugin.count("define( 'IAH_VERSION', '1.5.51' );") != 1:
    raise SystemExit('Unexpected homepage plugin version')
plugin = plugin.replace('Version:           1.5.51', 'Version:           1.5.52', 1)
plugin = plugin.replace("define( 'IAH_VERSION', '1.5.51' );", "define( 'IAH_VERSION', '1.5.52' );", 1)
plugin_path.write_text(plugin, encoding='utf-8')

print('Homepage cleanup patch applied successfully.')
