from pathlib import Path
import re

roots = [
    Path('wp-content/plugins'),
    Path('wp-content/themes'),
]
include_prefixes = ('impact-',)
needles = [
    re.compile(r'<form\b', re.I),
    re.compile(r'createElement\([\'\"]form[\'\"]\)', re.I),
    re.compile(r'<input\b', re.I),
    re.compile(r'<textarea\b', re.I),
    re.compile(r'type=[\'\"]submit[\'\"]', re.I),
    re.compile(r'waitlist', re.I),
    re.compile(r'application', re.I),
    re.compile(r'form-error|form-success|submit-error|submit-success', re.I),
]
text_ext = {'.php', '.js', '.css', '.html', '.htm', '.json', '.txt', '.md'}

hits = []
for root in roots:
    if not root.exists():
        continue
    for path in root.rglob('*'):
        if not path.is_file() or path.suffix.lower() not in text_ext:
            continue
        rel = path.as_posix()
        if '/plugins/' in rel:
            plugin = rel.split('/plugins/', 1)[1].split('/', 1)[0]
            if not plugin.startswith(include_prefixes):
                continue
        try:
            text = path.read_text(encoding='utf-8', errors='ignore')
        except Exception:
            continue
        lines = text.splitlines()
        matched_lines = []
        for i, line in enumerate(lines, 1):
            if any(rx.search(line) for rx in needles):
                matched_lines.append(i)
        if not matched_lines:
            continue
        windows = []
        seen = set()
        for n in matched_lines:
            start = max(1, n - 2)
            end = min(len(lines), n + 2)
            key = (start, end)
            if key in seen:
                continue
            seen.add(key)
            excerpt = '\n'.join(f'{j}: {lines[j-1]}' for j in range(start, end + 1))
            windows.append(excerpt)
            if len(windows) >= 12:
                break
        hits.append((rel, matched_lines[:80], windows))

out = ['# Public form audit', '']
for rel, line_nums, windows in hits:
    out += [f'## {rel}', '', 'Matched lines: ' + ', '.join(map(str, line_nums)), '']
    for w in windows:
        out += ['```text', w, '```', '']

Path('public-form-audit.md').write_text('\n'.join(out), encoding='utf-8')
print(f'Wrote {len(hits)} files to public-form-audit.md')
