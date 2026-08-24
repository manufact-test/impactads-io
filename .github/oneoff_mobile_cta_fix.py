from pathlib import Path
import re

path = Path('wp-content/plugins/impact-accs-homepage/includes/class-homepage.php')
text = path.read_text(encoding='utf-8')

labels = [
    'request access',
    'get access',
    'запросить доступ',
    'получить доступ',
    'связаться',
    '?waitlist=true',
]

for label in labels:
    pattern = re.compile(r'(' + re.escape(label) + r'.{0,48}?)\$app')
    text, count = pattern.subn(lambda m: m.group(1) + '$cta', text, count=1)
    if count != 1:
        raise SystemExit('Could not reroute mobile label: ' + label)

# Validate only the actual mobile routing maps.
start = text.find('var R={')
end = text.find('};function norm', start)
if start < 0 or end < 0:
    raise SystemExit('Mobile routing maps not found')
route_maps = text[start:end]

for label in labels:
    pos = route_maps.find(label)
    if pos < 0:
        raise SystemExit('Route label missing after patch: ' + label)
    snippet = route_maps[pos:pos + 80]
    if '$cta' not in snippet:
        raise SystemExit('Route still does not use $cta: ' + label)

# Genuine application paths must remain application paths.
for app_path in ('/application', '/application/'):
    pos = route_maps.find(app_path)
    if pos < 0:
        raise SystemExit('Application route missing: ' + app_path)
    if '$app' not in route_maps[pos:pos + 80]:
        raise SystemExit('Genuine application route was accidentally changed: ' + app_path)

path.write_text(text, encoding='utf-8')

# Cache bust homepage assets once more.
plugin_path = Path('wp-content/plugins/impact-accs-homepage/impact-accs-homepage.php')
plugin = plugin_path.read_text(encoding='utf-8')
m = re.search(r'Version:\s*(\d+)\.(\d+)\.(\d+)', plugin)
if not m:
    raise SystemExit('Homepage version not found')
old = '.'.join(m.groups())
new = m.group(1) + '.' + m.group(2) + '.' + str(int(m.group(3)) + 1)
plugin = plugin.replace('Version: ' + old, 'Version: ' + new, 1)
plugin = plugin.replace("'" + old + "'", "'" + new + "'")
plugin_path.write_text(plugin, encoding='utf-8')

print('Mobile CTA routes validated; homepage', old, '->', new)

for technical in (
    Path('.github/workflows/oneoff-mobile-cta-fix.yml'),
    Path('.github/oneoff_mobile_cta_fix.py'),
):
    if technical.exists():
        technical.unlink()
