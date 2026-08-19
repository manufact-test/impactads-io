import re
from pathlib import Path

# investors in 692
t = Path("_next/static/chunks/692acfebb5322696.js").read_text(encoding="utf-8")

# find arrays of investors - look for profilePicture patterns
pics = re.findall(r'profilePicture:"([^"]+)"', t)
print("profile pictures:", len(pics))
for p in pics[:30]:
    print(" ", p)

# extract person entries for credits - id, name, title
for m in re.finditer(r'\{id:"([^"]+)",name:"([^"]+)",title:"([^"]+)"(?:,company:"([^"]*)")?,profilePicture:"([^"]+)"\}', t):
    print(m.group(2), "|", m.group(3), "|", m.group(4) or "", "|", m.group(5))

# if different order
for m in re.finditer(r'name:"([^"]+)",title:"([^"]+)"', t):
    s = m.group(0)
    if "Software Engineer" not in s and "Founder" not in s and "Staff" not in s:
        if any(x in s for x in ["Capital", "Brex", "Vercel", "Graphite", "Anthropic", "LangChain", "Codegen", "Browserbase", "Daytona", "Replit", "Homebrew", "Mastra", "MLOps", "Fastino", "Tavily", "Untapped"]):
            print("inv:", m.group(1), m.group(2))
