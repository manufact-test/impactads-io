"""DEPRECATED — broke JS runtime. Use _apply_accs_html_only.py instead."""
from __future__ import annotations

from pathlib import Path

ROOT = Path(__file__).resolve().parent

# Longest-first to avoid partial overlaps
REPLACEMENTS: list[tuple[str, str]] = [
    # --- Footer / global CTAs ---
    (
        "The AI-native observability platform for fast-moving engineering teams. Ship with confidence.",
        "Closed access infrastructure for media buying teams. Accounts and access for launch, tests, and scale.",
    ),
    (
        "The AI-native observability platform for fast-moving engineering teams.",
        "Closed access infrastructure for media buying teams.",
    ),
    ("Ship with confidence.", "Working resource. No noise."),
    ("SAVE THE DAY", "REQUEST ACCESS"),
    ("Stop Wondering", "Impact starts with access"),
    ("Get Access", "Request access"),
    ("Join the Waitlist", "Request access"),
    ("Sign up for early access to Impact.", "Request access to impact.accs."),
    ("Impact Waitlist Credential", "impact.accs access request"),
    ("We'll be in touch when it's your turn.", "We'll respond with terms and availability."),
    ("impact.corp®", "impact.accs"),
    ("impact.corp", "impact.accs"),
    ("ai-native", "closed access"),
    ("Impact is", "impact.accs"),
    ("Initializing", "Loading access"),
    ("Impact System", "impact.accs system"),
    # --- Meta ---
    (
        "Observability is broken. We're here to fix it. Learn about Impact's philosophy and meet the team.",
        "Closed account infrastructure for media buying teams. Learn how impact.accs helps teams launch faster.",
    ),
    (
        "Observability is broken. We&#x27;re here to fix it. Learn about Impact&#x27;s philosophy and meet the team.",
        "Closed account infrastructure for media buying teams. Learn how impact.accs helps teams launch faster.",
    ),
    (
        "Observability is broken. We're here to fix it.",
        "Access is part of performance infrastructure.",
    ),
    (
        "Observability is broken. We&#x27;re here to fix it.",
        "Access is part of performance infrastructure.",
    ),
    (
        "Engineering insights, product updates, and deep dives from the Impact team.",
        "Notes on access, supply, and building infrastructure for media buying teams.",
    ),
    # --- Feature nav ---
    ("Autonomous Alerts", "Agency Accounts"),
    ("Conversational Debugging", "Media Buying Access"),
    ("Coding Agents Welcome", "Team Supply"),
    # --- Feature descriptions (footer nav) ---
    (
        "Rich, actionable alerts that ",
        "Reliable account supply that ",
    ),
    (
        "Claude Code, Codex, Cursor — whatever. Impact integrates tightly with all major coding agents.",
        "Facebook, Google, TikTok — whatever platform you run. impact.accs supplies access under your team's workflow.",
    ),
    (
        "Interrogate your app in plain language. Stop digging through mountains of telemetry. Ask questions, get answers.",
        "Send a request in plain language. Get availability, terms, and delivery — not another sales pitch.",
    ),
    # --- Feature page meta (HTML) ---
    (
        "A persistent AI agent inside your comms channel. Ask what’s failing. It responds with live system state and pinpoints anomalies before they become incidents.",
        "Agency accounts prepared for media buying workflows. Stable supply, clear terms, and replacement when the issue is on our side.",
    ),
    (
        "A persistent AI agent inside your comms channel. Ask what's failing. It responds with live system state and pinpoints anomalies before they become incidents.",
        "Agency accounts prepared for media buying workflows. Stable supply, clear terms, and replacement when the issue is on our side.",
    ),
    (
        "AI agents that understand your infrastructure. Adaptive UI that reconfigures per anomaly, surfacing only what matters, so your team can fix it faster.",
        "Access infrastructure built for teams that scale. Volume terms, fast contact, and supply that matches your launch tempo.",
    ),
    ("All agents supported", "All major platforms"),
    # --- Home carousel (827ff...) ---
    (
        "Choose your storage region and data center. Meet data sovereignty requirements and ensure your observability data stays exactly where you need it.",
        "Agency accounts, platform access, and resources matched to your geo, vertical, and launch requirements.",
    ),
    (
        "Data encrypted in transit and at rest. Protected from your infrastructure to our storage, and automatically deleted when you’re done.",
        "Verified supply and clear terms. If the issue is on our side, we always make a replacement.",
    ),
    (
        "Data encrypted in transit and at rest. Protected from your infrastructure to our storage, and automatically deleted when you're done.",
        "Verified supply and clear terms. If the issue is on our side, we always make a replacement.",
    ),
    (
        "Don't get lost in dashboards. Impact generates charts, tables, diagrams, and code blocks on the fly. Exactly what you need to see, and nothing more.",
        "No chaos in chats. You get what you asked for — access that matches the task, volume, and timeline.",
    ),
    (
        "Enterprise-grade security built in, not bolted on. Impact's platform is compliant so your data stays protected at every layer.",
        "Closed infrastructure for teams that take access seriously. Structured process instead of random sellers.",
    ),
    (
        "Fine-grained access control for every team member. Set permissions, track access, maintain complete audit trails.",
        "Individual terms for solo buyers. Volume terms and dedicated contact for teams.",
    ),
    (
        "From alert to root cause in seconds. Impact pinpoints the exact file, commit, or line of code responsible for any issue. No guessing, no git blame deep-dives.",
        "From request to delivery in hours, not days. Clear availability, terms, and handoff — no endless back-and-forth.",
    ),
    (
        "Impact is more than a tool — it's a philosophy. We're taking a radically different approach to observability.",
        "impact.accs is more than a shop — it's infrastructure for teams that run traffic seriously.",
    ),
    (
        "Observability platforms have accumulated hundreds of features over the past decade. None of it makes systems more reliable. If observability is about answering questions, the best interface is chat — not dashboards, not config files, not training manuals.",
        "Account sellers have accumulated noise over the years — screenshots, emojis, and random Telegram chats. Teams don't need more promises. They need a clear request, fast contact, and working access.",
    ),
    (
        "Observability that works where you do. Impact connects to your tools so incident response happens in the flow of work, not outside it.",
        "Access that works at the speed of your launches. impact.accs fits into how media buying teams already work.",
    ),
    (
        "One platform, dozens of data sources. Send logs in any format from any cloud or technology. Instrumentation is effortless, not invasive.",
        "One contact, multiple access categories. Agency accounts, platform access, and supply under volume when you need it.",
    ),
    (
        "Static monitors are broken. They're tedious to maintain, constantly misfire, and train teams to ignore real alerts. The future is autonomous alerts — AI that watches continuously and only escalates what actually matters.",
        "Random Telegram sellers are broken. Unstable supply, vague terms, and zero accountability. The future is structured access — clear request, fast response, working resource.",
    ),
    (
        "Traditional observability tools make you work for answers. Impact works for you — anticipating needs, surfacing insights, and eliminating busywork.",
        "Traditional account shops make you hunt for supply. impact.accs works as your resource layer — clear terms, fast contact, repeat orders.",
    ),
    (
        "Unstructured logs used to be a weakness. Now they're a strength. AI can process natural language at scale — finding patterns in logs that structured metrics could never capture.",
        "Clear requests used to get lost in chat noise. Now they're the workflow — tell us what you need, get availability and terms, move on to launch.",
    ),
    (
        "The intake service is experiencing high error rates while attempting to publish logs to Kinesis.",
        "A launch window is open and the team needs agency accounts before traffic goes live.",
    ),
    (
        "Kinesis shard throughput limit exceeded due to a spike in log volume from the intake service.",
        "Volume request received — matching availability and terms for the team.",
    ),
    ("Intake: Kinesis Publish Timeout", "Request: agency account supply"),
    (
        "Scale up the Kinesis stream shard count and add retry backoff to the intake publisher.",
        "Confirm availability, agree terms, and prepare access for delivery.",
    ),
    (
        "Sure. I've launched a Cursor cloud agent to increase the timeout on the",
        "Confirmed. Preparing replacement access and updating terms for the",
    ),
    ("Monitoring services: ", "Supply status: "),
    ("Spawning a fix agent to patch", "Preparing replacement access for"),
    # --- About page story ---
    (
        "for companies of all sizes during the AI era. Not by adding AI features to legacy tools, but by rethinking observability from first principles — conversational, intelligent, and built for teams who move fast.",
        "for media buying teams that need speed and stability. Not by adding noise to legacy Telegram shops, but by building closed access infrastructure from first principles — structured, fast, and built for teams who launch.",
    ),
    (
        "for companies of all sizes during the AI era. Not by adding AI features to legacy tools, but by rethinking observability from first principles - conversational, intelligent, and built for teams who move fast.",
        "for media buying teams that need speed and stability. Not by adding noise to legacy Telegram shops, but by building closed access infrastructure from first principles - structured, fast, and built for teams who launch.",
    ),
    ("default observability solution", "default access channel"),
    (
        "A world where",
        "A market where",
    ),
    (
        "Our team founded the infrastructure and observability teams at",
        "Our team spent years in account supply and media buying before building",
    ),
    (
        "We fell in love with the promise of observability — the idea that you could truly understand and control complex systems. But we also witnessed firsthand the",
        "We saw how much launch speed depends on access — the idea that teams could move fast with the right resource. But we also witnessed firsthand the",
    ),
    (
        "We fell in love with the promise of observability - the idea that you could truly understand and control complex systems. But we also witnessed firsthand the",
        "We saw how much launch speed depends on access - the idea that teams could move fast with the right resource. But we also witnessed firsthand the",
    ),
    ("challenges and complexity", "chaos and wasted time"),
    ("The dashboards. The alert fatigue. The 3am pages that could have been prevented.", "The chat hunting. The bad supply. The launches delayed by unreliable sellers."),
    (
        "In 2025, everything changed. We found ourselves building",
        "Over five years in the market, we built",
    ),
    ("mind-blowing agentic software products", "a reliable supply channel"),
    (
        "and using futuristic AI coding tools like Cursor and Claude Code. But observability still felt painfully manual, ",
        "for teams running traffic at scale. But account supply still felt painfully chaotic, ",
    ),
    ("stuck in the past", "stuck in Telegram noise"),
    (
        "That's when it clicked: if AI can write code, why can't it understand production systems?",
        "That's when it clicked: if teams need speed, why should access still depend on random sellers?",
    ),
    (
        "That\\'s when it clicked: if AI can write code, why can\\'t it understand production systems?",
        "That\\'s when it clicked: if teams need speed, why should access still depend on random sellers?",
    ),
    (
        "We're building the observability platform we always wanted: conversational, intelligent, and built for teams who ship fast.",
        "We're building the access infrastructure we always wanted: closed, fast, and built for teams who launch.",
    ),
    (
        "Values shape culture. Culture shapes products. These six principles are the foundation of how we build Impact.",
        "Values shape culture. Culture shapes service. These six principles are the foundation of how we build impact.accs.",
    ),
    (
        "Do the right thing, always. By our customers, our team, our investors. Honesty isn't optional.",
        "Do the right thing, always. By our clients, our team, our partners. Honesty isn't optional.",
    ),
    (
        "Go above and beyond for customers. Extend that same care to teammates and partners. Service is everything.",
        "Go above and beyond for clients. Extend that same care to teammates and partners. Service is everything.",
    ),
    (
        "Be kind to everyone: teammates, customers, even competitors. Be direct, but always mindful. We're all in this together.",
        "Be direct with everyone: clients, teammates, partners. Respect the market, but stay precise. We're here to work.",
    ),
    (
        "Take pride in your work. Care about design. Care about quality. Never ship garbage.",
        "Take pride in your work. Care about process. Care about quality. Never ship bad resource.",
    ),
    (
        "Move fast. Be impatient. Never settle for complacency or comfort. Speed is a feature.",
        "Move fast. Teams don't wait. Speed of response and delivery is part of the product.",
    ),
    (
        "Enjoy the work. Enjoy each other. Do things because they're cool. Smile through the pain.",
        "Stay calm under load. Launch days are intense — our job is to remove friction.",
    ),
    # --- Team names (remove old company people) ---
    ('Sherwood Callaway",title:"Founder"', 'Team Lead",title:"Founder"'),
    ('Asif Arman",title:"Software Engineer"', 'Supply Ops",title:"Operations"'),
    ('Andrew Aymeloglu",title:"Software Engineer"', 'Client Ops",title:"Client Manager"'),
    ('Ed Carrel",title:"Software Engineer"', 'Quality",title:"Quality Control"'),
    ('Alex Holovach",title:"Software Engineer"', 'Partnerships",title:"Partnerships"'),
    ('Daniel Wang",title:"Software Engineer"', 'Support",title:"Support"'),
    ('Hadley Hahn",title:"Software Engineer"', 'Onboarding",title:"Onboarding"'),
    ('Henry Chen",title:"Software Engineer"', 'Volume",title:"Volume Supply"'),
    ('Justin Ko",title:"Founding Engineer"', 'Infrastructure",title:"Infrastructure"'),
    ('Lewis Lin",title:"Software Engineer"', 'Verification",title:"Verification"'),
    ('Rupa Patel",title:"Software Engineer"', 'Accounts",title:"Account Ops"'),
    ('Tom Smith",title:"Software Engineer"', 'Delivery",title:"Delivery"'),
    ("Brex", "the market"),
    # --- Blog ---
    ("Impact's Journal", "impact.accs journal"),
    ("Impact Is Part of Y Combinator P26", "Five Years in the Market"),
    (
        "Impact is joining Y Combinator's P26 batch as we continue building the future of AI-native observability.",
        "impact.accs has spent five years supplying accounts and access to media buying teams — quietly, without the noise.",
    ),
    (
        "Impact is joining Y Combinator&#x27;s P26 batch as we continue building the future of AI-native observability.",
        "impact.accs has spent five years supplying accounts and access to media buying teams — quietly, without the noise.",
    ),
    ("The Impact Manifesto", "The impact.accs Manifesto"),
    (
        "AI has changed what's possible in software systems. Observability hasn't caught up. This manifesto lays out a path forward.",
        "Accounts are not a minor expense — they are infrastructure. This manifesto explains how impact.accs thinks about access.",
    ),
    (
        "AI has changed what&#x27;s possible in software systems. Observability hasn&#x27;t caught up. This manifesto lays out a path forward.",
        "Accounts are not a minor expense — they are infrastructure. This manifesto explains how impact.accs thinks about access.",
    ),
    (
        "In 2026, production systems evolve continuously, behave probabilistically, and change faster than humans can track. Yet the tools we use to understand them are still rooted in assumptions from a bygone era.",
        "Teams launch continuously, test aggressively, and scale faster than most suppliers can keep up. Yet access is still bought through chaos, screenshots, and blind trust.",
    ),
    (
        "Something has to change. This manifesto lays out a path forward.",
        "Something has to change. This is how we think about it.",
    ),
    ("We're playing a losing game. We don't need better monitors. We need a n", "We're playing a losing game. We don't need louder sellers. We need a c"),
    ("1. Less is More", "1. Access Is Infrastructure"),
    ("Modern observability is bloated.", "Most account suppliers are chaotic."),
    (
        "Observability vendors promised clarity. What we got was complexity.",
        "Sellers promised the best prices. Teams got wasted time.",
    ),
    ("Less is more.", "Structure beats noise."),
    ("Why is production down?", "Need accounts for launch?"),
    ("Who is affected?", "Who handles the volume?"),
    ("What changed?", "What access is required?"),
    ("How do we fix it right now?", "How fast can we get supply?"),
    ("2. Logs Are All You Need", "2. Resource Over Promises"),
    (
        'For years, observability has been defined by the "Three Pillars": logs, metrics, and traces.',
        'For years, the market sold accounts like a commodity: screenshots, emojis, and random Telegram chats.',
    ),
    ("Logs are all you need.", "Working resource is what you need."),
    ("3. Monitoring Is Dead", "3. Trust Is Built in Process"),
    (
        "The future is Autonomous Alerts: AI that continuously watches your app in production, investigates issues, and only escalates when absolutely necessary.",
        "The future is a reliable supply channel: clear request, fast contact, working access, and replacement when the issue is on our side.",
    ),
    ("Monitoring is finally dead.", "Chaos is finally optional."),
    ("Introducing: Impact", "Introducing: impact.accs"),
    (
        "Impact is an AI-native observability platform built on these principles.",
        "impact.accs is closed account infrastructure built on these principles.",
    ),
    ("Less noise. Less overhead. Less complexity.", "Less noise. Less chaos. Less wasted time."),
    ("More clarity. More confidence. More speed.", "More speed. More stability. More control."),
    (
        "If this way of thinking resonates with you, come build with us.",
        "If this way of working fits your team, request access.",
    ),
    (
        "Today we're announcing that Impact is part of Y Combinator's P26 batch. We're building AI-native observability for engineering teams who want answers, not more bells and whistles.",
        "impact.accs has been supplying accounts and access to media buying teams for five years. We work as a resource layer: no noise, clear communication, and focus on practical results.",
    ),
    (
        "Over the next few months we'll be heads down on the product, expanding our early customer base, and growing the team. If you're an engineer who cares about reliability, AI, and developer experience, we're hiring.",
        "We continue expanding supply, improving processes, and working with teams that need volume and speed. If your team runs traffic and needs a reliable access channel, get in touch.",
    ),
    (
        "This isn't our first time in the program. Impact founder Sherwood Callaway previously went through YC as a cofounder of Opkit in the S21 cohort, alongside Impact founding engineer Justin Ko.",
        "Our team has been in this market for years. We know what buyers need: speed, predictability, and a supplier who understands the work.",
    ),
    (
        "To our early users and design partners: thank you. We're building this for you.",
        "To the teams we work with: thank you. We build this for teams that move fast.",
    ),
    ("The Impact Team", "The impact.accs team"),
    ("By Impact Team", "By impact.accs team"),
    ("Impact Team", "impact.accs team"),
    ("VISION", "VISION"),
    ("OBSERVABILITY", "ACCESS"),
    ("ANNOUNCEMENT", "UPDATE"),
    # --- Legal ---
    ("Impact Inc., 2261 Market Street, STE 85391, San Francisco, California 94114", "impact.accs"),
    ("2261 Market Street, STE 85391, San Francisco, California 94114", "impact.accs"),
    ("legal@impact.ai", "contact@impact.accs"),
    ("privacy@impact.ai", "contact@impact.accs"),
    (
        "the observability and log management services Impact provides",
        "the account and access infrastructure services impact.accs provides",
    ),
    (
        "ingestion, storage, analysis, and display of log data and observability telemetry",
        "provision, verification, delivery, and support of accounts and digital access resources",
    ),
    ("Impact Inc.", "impact.accs"),
    ("Copyright © 2026 Impact.", "Copyright © 2026 impact.accs."),
    # --- Misc product terms ---
    ("AI-native observability", "closed access infrastructure"),
    ("fast-moving engineering teams", "media buying teams"),
    ("engineering teams who want answers, not more bells and whistles", "media buying teams who need working access, not noise"),
    ("future of AI-native observability", "future of performance infrastructure"),
    ("future of closed access infrastructure", "future of performance infrastructure"),  # idempotent
    ("If you're an engineer who cares about reliability, AI, and developer experience, we're hiring.", "If your team needs a reliable access channel, request access."),
    ("Y Combinator", "the market"),
    ("Sherwood Callaway", "our team"),
    ("Justin Ko", "our team"),
    ("Opkit", "prior work"),
    ("https://www.linkedin.com/company/impact", "https://t.me/impactaccs"),
    ("https://x.com/impact", "https://t.me/impactaccs"),
    ("Careers", "Contact"),
    ("Waitlist", "Access"),
    ("Manifesto", "Manifesto"),
    # Manifesto intro (standalone)
    (
        "AI has changed what's possible in software systems. Observability hasn't caught up.",
        "We don't build a brand around noise. In performance, access, speed, and the ability to move without unnecessary losses matter.",
    ),
]

MANIFESTO_PROSE = """<div class="prose prose-blog md:prose-lg container-prose w-full min-w-0"><p>We don't build a brand around noise. In performance, access, speed, and the ability to move without unnecessary losses matter.</p>
<p>Accounts are not a minor expense — they are infrastructure. For a team, they affect launch speed, test tempo, and the ability to scale.</p>
<p>impact.accs exists for teams that work with traffic seriously: they calculate fast, decide fast, and don't want to depend on random sellers.</p>
<h2 id="1-access-is-infrastructure"><a href="#1-access-is-infrastructure" class="group block no-underline">1. Access Is Infrastructure</a></h2>
<p>Most account suppliers are chaotic. Screenshots, emojis, vague terms, and endless chat hunting.</p>
<p>Sellers promised the best prices. Teams got wasted time, bad supply, and delayed launches.</p>
<p>Structure beats noise. Teams need clear answers:</p>
<ul>
<li>Need accounts for launch?</li>
<li>Who handles the volume?</li>
<li>What access is required?</li>
<li>How fast can we get supply?</li>
</ul>
<h2 id="2-resource-over-promises"><a href="#2-resource-over-promises" class="group block no-underline">2. Resource Over Promises</a></h2>
<p>For years, the market sold accounts like a commodity: availability posts, loud claims, and zero accountability.</p>
<p>Working resource is what you need — verified supply, clear terms, and replacement when the issue is on our side.</p>
<p>We don't promise magic. We provide access that helps teams do their work.</p>
<h2 id="3-trust-is-built-in-process"><a href="#3-trust-is-built-in-process" class="group block no-underline">3. Trust Is Built in Process</a></h2>
<p>Random Telegram sellers are broken: unstable supply, vague conditions, and no support when launches are live.</p>
<p>The future is a reliable supply channel: request → match → terms → access → repeat.</p>
<p>Five years in the market. Fast contact. Clear process. Replacement when the problem is on our side.</p>
<h2 id="introducing-impact-accs"><a href="#introducing-impact-accs" class="group block no-underline">Introducing: impact.accs</a></h2>
<p>impact.accs is closed account infrastructure built on these principles — the first module of the impact. performance ecosystem.</p>
<p>Less noise. Less chaos. Less wasted time.</p>
<p>More speed. More stability. More control.</p>
<p>If this way of working fits your team, request access.</p></div>"""

EXTRA_REPLACEMENTS: list[tuple[str, str]] = [
    ("impact.accs an closed access infrastructure", "impact.accs is closed access infrastructure"),
    (
        "AI has changed what&#x27;s possible in software systems. Observability hasn&#x27;t caught up.",
        "We don&#x27;t build a brand around noise. In performance, access, speed, and the ability to move without unnecessary losses matter.",
    ),
    # Manifesto leftovers
    (
        "Over the past decade, observability platforms have accumulated hundreds of features, modules, dashboards, and configuration options. While these elements look impressive in demos, they rarely lead to more reliable systems. More often, they do the opposite by creating a steep learning curve and increasing cognitive load.",
        "Over the years, account sellers have accumulated noise — screenshots, emoji posts, and chaotic Telegram threads. It looks active, but it rarely helps teams launch faster. More often it wastes time and adds operational risk.",
    ),
    (
        "The DevOps movement gained traction around a simple idea: the people who build software should also operate it. But observability tools aren&#x27;t built for product engineers. They are designed for platform specialists with deep expertise in infrastructure, databases, and distributed systems. This is unfair.",
        "Media buying teams run on speed. But most account supply still depends on random sellers, unclear terms, and manual verification. Teams shouldn't lose launch windows because access is the bottleneck.",
    ),
    (
        "The DevOps movement gained traction around a simple idea: the people who build software should also operate it. But observability tools aren't built for product engineers. They are designed for platform specialists with deep expertise in infrastructure, databases, and distributed systems. This is unfair.",
        "Media buying teams run on speed. But most account supply still depends on random sellers, unclear terms, and manual verification. Teams shouldn't lose launch windows because access is the bottleneck.",
    ),
    (
        "Product engineers don&#x27;t want more observability bells and whistles. They want answers:",
        "Buyers don't want more seller noise. They want working resource:",
    ),
    (
        "Product engineers don't want more observability bells and whistles. They want answers:",
        "Buyers don't want more seller noise. They want working resource:",
    ),
    (
        "And if observability is fundamentally about answering questions, it follows that the best UI for observability is chat.",
        "If access is part of performance infrastructure, the best workflow is direct: request, terms, delivery, repeat.",
    ),
    (
        "We don&#x27;t make observability better by adding more features. We make it better by cutting back.",
        "We don't make account supply better by shouting louder. We make it better with structure.",
    ),
    (
        "We don't make observability better by adding more features. We make it better by cutting back.",
        "We don't make account supply better by shouting louder. We make it better with structure.",
    ),
    (
        "For years, observability has been defined by the &quot;Three Pillars&quot;: logs, metrics, and traces. The belief is that all three are required to understand production systems.",
        "For years, sellers defined value by price and availability posts. The belief was that the cheapest listing wins.",
    ),
    (
        'For years, observability has been defined by the "Three Pillars": logs, metrics, and traces. The belief is that all three are required to understand production systems.',
        "For years, sellers defined value by price and availability posts. The belief was that the cheapest listing wins.",
    ),
    (
        "Everyone knows how to write a log line. Everyone knows how to read stdout. Logs match how humans naturally reason about systems: what happened, in what order, and with what context.",
        "Every buyer knows how to send a request. Every team knows what they need for launch. Supply should match that clarity.",
    ),
    (
        "On a fundamental level, logs, metrics, and traces share the same underlying DNA: events. Metrics are aggregated events. Traces are collections of start and end events. Logs are events masquerading under a different name. The implications of this are profound: with logs, you can reconstruct metrics and traces, giving you three &quot;pillars&quot; for the price of one.",
        "On a fundamental level, access categories differ by platform, geo, and workflow — but the buyer need is the same: working resource under clear terms.",
    ),
    (
        "Historically, logs were dismissed because they were unstructured. Today, that weakness is a strength. AI excels at processing large volumes of unstructured text. It can surface patterns, identify anomalies, and extract meaning from natural language at scale. Metrics and traces could never hope to be as rich.",
        "Historically, account supply was dismissed as a side purchase. Today it is infrastructure. The right channel reduces launch delay, stabilizes tests, and supports volume.",
    ),
    (
        "Logs have challenges, too. Cost and availability are chief among them. But these aren&#x27;t insurmountable. Cost can be addressed with summarization, compression, tiering, and intelligent retention. Availability can be improved through automatic instrumentation: AI that adds meaningful log lines directly to code.",
        "Supply has challenges too: quality and timing. They are addressed with verification, clear replacement terms, and a team that understands launch pressure.",
    ),
    (
        "The Bitter Lesson taught us that AI progress doesn&#x27;t come from clever hacks. It comes from simple, scalable primitives. A similar rule applies to observability.",
        "The market taught us that speed doesn't come from louder sellers. It comes from reliable process. The same rule applies to access.",
    ),
    (
        "Before observability, there was monitoring. Monitoring is about detection: predefined thresholds on CPU, memory, or other signals. Observability is about understanding: the ability to explore systems and explain failures.",
        "Before structured supply, there was chat hunting. Chat hunting is random search. Structured access is about predictable delivery under terms your team can trust.",
    ),
    (
        "Today, software teams think of monitoring as a necessary evil — a painful but essential part of running production systems. But this consensus doesn&#x27;t change the truth: monitoring is fundamentally broken.",
        "Today, many teams treat account search as a necessary evil. That doesn't change the truth: chaotic supply is fundamentally broken.",
    ),
    (
        "These tradeoffs might be acceptable if monitors reliably caught issues. They don&#x27;t.",
        "Random sellers might be acceptable if they never failed launches. They do.",
    ),
    (
        "Monitoring fails because it&#x27;s reactive. Teams create monitors after incidents, not before. Meanwhile, the systems they manage are complex, constantly changing, and rarely fail the same way twice.",
        "Chat supply fails because it's reactive. Teams find a seller after a failed launch, not before. Meanwhile campaigns change daily.",
    ),
    (
        "We&#x27;re playing a losing game. We don&#x27;t need better monitors. We need a new paradigm.",
        "We're playing a losing game. We don't need louder sellers. We need a reliable channel.",
    ),
    (
        "The future is <strong>Agency Accounts</strong>: AI that continuously watches your app in production, investigates issues, and only escalates when absolutely necessary.",
        "The future is <strong>structured access</strong>: clear request, fast contact, working resource, and replacement when the issue is on our side.",
    ),
    (
        "No more static monitors. No more false positives. No more endless toil.",
        "No more chat hunting. No more bad supply. No more launch delays.",
    ),
    (
        "But it&#x27;s also more than a product. It&#x27;s a new philosophy for software reliability in the AI era.",
        "But it is also more than a shop. It is infrastructure for teams that run traffic.",
    ),
    # Legal DPA
    (
        "Company Personal Data may include any Personal Data contained in log data, error reports, traces, metrics, and related telemetry submitted by Company to the Services, including but not limited to:",
        "Company Personal Data may include any Personal Data contained in account requests, contact details, delivery records, and related service data submitted by Company to the Services, including but not limited to:",
    ),
    (
        "Any other Personal Data Company chooses to include in its log or telemetry data.",
        "Any other Personal Data Company chooses to include in its account or service requests.",
    ),
    (
        "The categories of Data Subjects are determined by Company and may include Company&#x27;s customers, employees, contractors, and end users whose Personal Data appears in log or telemetry data submitted to the Services.",
        "The categories of Data Subjects are determined by Company and may include Company&#x27;s customers, employees, contractors, and team members whose Personal Data appears in account or service data submitted to the Services.",
    ),
    # Meta / titles
    ("Impact | About", "impact.accs | About"),
    ("Impact | Blog", "impact.accs | Blog"),
    ("Impact — AI-native observability", "impact.accs — closed access infrastructure"),
    ("AI-native observability for engineering teams", "Closed access infrastructure for media buying teams"),
    ("Observability platform", "Access infrastructure"),
    ("observability platform", "access infrastructure"),
    # Feature page titles in meta
    ("Autonomous Alerts — Impact", "Agency Accounts — impact.accs"),
    ("Conversational Debugging — Impact", "Media Buying Access — impact.accs"),
    ("Coding Agents Welcome — Impact", "Team Supply — impact.accs"),
    ("Coding Agents Welcome", "Team Supply"),
    # Home / about leftover
    ("meet the team.", "meet the team behind impact.accs."),
    ("/assets/team/sherwood.webp", "/assets/team/hadley.webp"),
    ("sherwood.webp", "hadley.webp"),
    ("Sherwood Callaway", "Team Lead"),
    ("Brex", "the market"),
    # Meta / SEO (index + all pages)
    ("Impact | AI-Native Observability", "impact.accs | Closed Access Infrastructure"),
    ("Impact | AI-Native Access infrastructure", "impact.accs | Closed Access Infrastructure"),
    (
        "Autonomous alerts. Conversational debugging. Coding agent integrations. impact.accs a new, AI-first approach to observability. Built for teams who ship fast.",
        "Closed access infrastructure for media buying teams. Agency accounts, platform access, and supply under volume.",
    ),
    (
        "Autonomous alerts. Conversational debugging. Coding agent integrations. Impact brings a new, AI-first approach to observability. Built for teams who ship fast.",
        "Closed access infrastructure for media buying teams. Agency accounts, platform access, and supply under volume.",
    ),
    (
        "Autonomous alerts. Conversational debugging. Coding agent integrations.",
        "Agency accounts. Media buying access. Team supply.",
    ),
    (
        "impact.accs a new, AI-first approach to observability. Built for teams who ship fast.",
        "impact.accs — closed account infrastructure for teams that launch, test, and scale.",
    ),
    (
        "Impact brings a new, AI-first approach to observability. Built for teams who ship fast.",
        "impact.accs — closed account infrastructure for teams that launch, test, and scale.",
    ),
    (
        "Taking a radically different approach to observability.",
        "Building closed access infrastructure for performance teams.",
    ),
    (
        "ensure your observability data stays exactly where you need it",
        "match access categories to your geo, vertical, and launch requirements",
    ),
    ("Conversational debugging", "Media buying access"),
    ("Coding agent integrations", "Team supply"),
    ("AI-first approach to observability", "closed access infrastructure"),
    ("Built for teams who ship fast", "Built for teams who launch fast"),
    ("ship fast", "launch fast"),
    # DPA leftover list items
    ("log data, error reports, traces, metrics, and related telemetry", "account requests, contact details, delivery records, and related service data"),
    (
        "taking a radically different approach to observability.",
        "building closed access infrastructure for performance teams.",
    ),
    (
        "We&#x27;re taking a radically different approach to observability.",
        "We&#x27;re building closed access infrastructure for performance teams.",
    ),
    (
        "Choose your storage region and data center. Meet data sovereignty requirements and ensure your observability data stays exactly where you need it.",
        "Agency accounts, platform access, and resources matched to your geo, vertical, and launch requirements.",
    ),
    (
        "Choose your storage region and data center. Meet data sovereignty requirements and ensure your observability data stays exactly where you need\u00a0it.",
        "Agency accounts, platform access, and resources matched to your geo, vertical, and launch requirements.",
    ),
    ("Team Supply for coding agents", "Team Supply"),
    ("Coding Agents Welcome | Impact", "Team Supply | impact.accs"),
    ("coding agents", "media buying teams"),
    ("Coding agents", "Media buying teams"),
    ('companyLogo:"/assets/credits/brex.svg"', 'companyLogo:"/assets/favicon-source.png"'),
    ('/assets/credits/brex.svg', '/assets/favicon-source.png'),
    ("Jay Hack", "Partner"),
    ("George Hurn-Malo", "Partner"),
    ("Pioneer Fund", "Private network"),
    ("Alt Capital", "Private network"),
    ("sherwood-callaway", "impact-accs"),
    ("observability dashboard", "access panel"),
    ("Observability dashboard", "Access panel"),
    (">Coding Agents</title>", ">Team Supply</title>"),
    ("Impact | Coding Agents", "impact.accs | Team Supply"),
    ("Impact | Conversational Debugging", "impact.accs | Media Buying Access"),
    ("Impact | Autonomous Alerts", "impact.accs | Agency Accounts"),
    (
        "Coding agents that understand your stack. Built for the way engineering teams work today.",
        "Access infrastructure built for teams that scale. Volume terms, fast contact, reliable supply.",
    ),
    (
        "Your AI-native observability copilot. Ask questions in plain English and get answers instantly.",
        "Send requests in plain language. Get availability, terms, and delivery — without the noise.",
    ),
]

SKIP_SUBSTRINGS = (
    "ImpactIcon",
    "impact-sound-muted",
    "impact-loader",
    "impact-upload-fix",
    "impact-loader-timeout",
    "useDebug",
    "DebugProvider",
    "debug mode",
    "debugBindings",
    "useDebugState",
)


def apply_replacements(text: str) -> str:
    for old, new in REPLACEMENTS:
        if old == new:
            continue
        text = text.replace(old, new)
    for old, new in EXTRA_REPLACEMENTS:
        if old == new:
            continue
        text = text.replace(old, new)
    return text


def rewrite_manifesto(text: str) -> str:
    import re

    pattern = re.compile(
        r'<div class="prose prose-blog md:prose-lg container-prose w-full min-w-0">.*?</div>(?=<div class="max-md:hidden">)',
        re.DOTALL,
    )
    if pattern.search(text):
        return pattern.sub(MANIFESTO_PROSE, text, count=1)
    return text


def cleanup_remaining(text: str, path: Path) -> str:
    import re

    if path.suffix == ".html":
        text = re.sub(r"\bobservability\b", "access infrastructure", text, flags=re.IGNORECASE)
        text = re.sub(r"\btelemetry\b", "service data", text, flags=re.IGNORECASE)
        text = text.replace("sherwood.webp", "hadley.webp")
        text = text.replace("Sherwood", "Team Lead")
    elif path.suffix == ".js" and path.name in {
        "827ff3490ba1793e.js",
        "f7f1c59a71681025.js",
        "29df6672875b8547.js",
        "ba5d7afdb6dc00cc.js",
        "9583d4a1bf83f1e7.js",
        "ac0d4738355e77b1.js",
        "0476edb0af9ab771.js",
        "d53e27b68750e6f9.js",
    }:
        text = re.sub(r"\bobservability\b", "access infrastructure", text, flags=re.IGNORECASE)
        text = text.replace("sherwood-callaway", "impact-accs")
    return text


def patch_file(path: Path) -> bool:
    try:
        original = path.read_text(encoding="utf-8")
    except (UnicodeDecodeError, OSError):
        return False
    updated = apply_replacements(original)
    if "manifesto" in path.name and path.suffix == ".html":
        updated = rewrite_manifesto(updated)
    updated = cleanup_remaining(updated, path)
    if updated != original:
        path.write_text(updated, encoding="utf-8")
        return True
    return False


def main() -> None:
    changed: list[str] = []
    for pattern in ("**/*.html", "**/*.js"):
        for path in ROOT.glob(pattern):
            if path.name.startswith("_"):
                continue
            if "node_modules" in path.parts:
                continue
            if patch_file(path):
                changed.append(str(path.relative_to(ROOT)))
    print(f"Updated {len(changed)} files")
    for p in sorted(changed):
        print(" ", p)


if __name__ == "__main__":
    main()
