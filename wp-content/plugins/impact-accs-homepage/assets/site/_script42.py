import re, sys
from pathlib import Path
sys.stdout.reconfigure(encoding='utf-8')

h = Path("about.html").read_text(encoding="utf-8")
scripts = re.findall(r'<script[^>]*>(.*?)</script>', h, re.DOTALL)
print(scripts[42])
