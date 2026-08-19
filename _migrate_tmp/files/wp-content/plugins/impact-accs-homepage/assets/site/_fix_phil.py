from pathlib import Path
t = Path("_next/static/chunks/827ff3490ba1793e.js").read_text(encoding="utf-8")
i = t.find('children:"Impact Inc.')
j = t.find('"', i + 12)
# find closing quote - the string has escaped quotes inside
end = i + 12
while end < len(t):
    if t[end] == '"' and t[end - 1] != "\\":
        break
    end += 1
old = t[i:end + 1]
print("OLD_LEN", len(old))
Path("_phil_old.txt").write_text(old, encoding="utf-8")

new = 'children:"impact.accs is more than a shop \u2014 it\'s infrastructure for teams that run traffic."'
# In JS file inside double quotes, apostrophe in it's doesn't need escape
new = 'children:"impact.accs is more than a shop \u2014 it\'s infrastructure for teams that run traffic."'
# Actually file uses \' for it's in children - check
sub = t[i:end+1]
if "\\'" in sub:
    new = 'children:"impact.accs is more than a shop \u2014 it\\\'s infrastructure for teams that run traffic."'
Path("_phil_new.txt").write_text(new, encoding="utf-8")
