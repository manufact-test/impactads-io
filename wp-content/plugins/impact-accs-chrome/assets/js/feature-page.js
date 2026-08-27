(function () {
	'use strict';

	function boot() {
		var root = document.querySelector('.iac-feature-page');
		if (!root) {
			return;
		}

		var isPlatform = root.classList.contains('iac-feature-platform');
		var isAgency = root.classList.contains('iac-feature-agency');
		var isTeam = root.classList.contains('iac-feature-team');
		var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var tabTimers = [];
		var isRu = typeof iacData !== 'undefined' && iacData.lang === 'ru';
		var approvedRu = isRu && iacData.htmlReplacements ? iacData.htmlReplacements : {};
		var featureRu = {
			'Structured agency accounts · one desk channel': 'Структурированные агентские аккаунты · один канал desk',
			'Platform access queued · launch window open': 'Платформенный доступ в очереди · окно запуска открыто',
			'Geo Match': 'GEO-сопоставление',
			'GEO verified · US/EU tier match complete': 'GEO проверено · сопоставление US/EU завершено',
			'Volume Tier': 'Объёмный уровень',
			'Volume tier confirmed · repeat order terms set': 'Объёмный уровень подтверждён · условия повторного заказа зафиксированы',
			'Lock Terms': 'Зафиксировать условия',
			'Terms locked · replacement policy confirmed': 'Условия зафиксированы · политика замены подтверждена',
			'Direct Handoff': 'Прямая передача',
			'EU agency batch · 50 accounts ready for handoff': 'EU агентская партия · 50 аккаунтов готовы к передаче',
			'Direct channel confirmed · handoff today': 'Прямой канал подтверждён · передача сегодня',
			'Checking availability for your request…': 'Проверка доступности для вашего запроса...',
			'1. Confirm volume tier and replacement policy': '1. Подтвердите объёмный уровень и политику замены',
			'2. Lock terms before handoff': '2. Зафиксируйте условия до передачи',
		};

		function nativeCopy(text) {
			if (!isRu) {
				return text;
			}
			if (Object.prototype.hasOwnProperty.call(approvedRu, text)) {
				return approvedRu[text];
			}
			if (Object.prototype.hasOwnProperty.call(featureRu, text)) {
				return featureRu[text];
			}
			return text;
		}

		root.classList.add('iac-feature-ready');
		document.body.classList.add('iac-feature-ready');

		function showEl(el, on) {
			if (!el) {
				return;
			}
			el.style.transition = 'opacity 450ms ease, transform 450ms ease';
			if (on) {
				el.classList.remove('opacity-0');
				el.style.opacity = '1';
				if (el.classList.contains('scale-50')) {
					el.classList.remove('scale-50');
					el.style.transform = 'scale(1)';
				}
			} else {
				el.classList.add('opacity-0');
				el.style.opacity = '0';
			}
		}

		function setTabProgress(btn, active) {
			var wrap = btn.closest('.flex.flex-col');
			if (!wrap) {
				return;
			}
			var bar = wrap.querySelector('.bg-primary.h-full');
			if (!bar) {
				return;
			}
			bar.style.transformOrigin = 'left center';
			if (active) {
				bar.style.transition = 'transform 5s linear';
				bar.style.transform = 'scaleX(1)';
			} else {
				bar.style.transition = 'transform 200ms ease';
				bar.style.transform = 'scaleX(0)';
			}
		}

		function initTabPanels() {
			root.querySelectorAll('[data-tab-frame="true"]').forEach(function (frame) {
				var controls = frame.parentElement || frame;
				var buttons = controls.querySelectorAll('[data-tab-button="true"]');
				var panels = frame.querySelectorAll('[data-tab-content]');
				if (!buttons.length || !panels.length) {
					return;
				}

				var grid = frame.closest('[data-iac-tabs-grid="true"]') || frame.closest('[class*="grid-cols-3"]');
				var leftItems = grid && grid.children[0] ? grid.children[0].querySelectorAll('div.cursor-pointer') : [];
				var rightItems = grid && grid.children[2] ? grid.children[2].querySelectorAll(':scope > div.cursor-pointer') : [];
				var mobileWrap = grid && grid.children[1] ? grid.children[1].querySelector('.isolate.grid') : null;
				var mobileItems = mobileWrap ? mobileWrap.querySelectorAll(':scope > div') : [];
				var active = 0;
				var timer = null;

				function setSideItem(item, on) {
					if (!item) {
						return;
					}
					item.style.opacity = on ? '1' : '0.35';
					var inner = item.firstElementChild;
					if (inner) {
						inner.style.opacity = on ? '1' : '0.45';
						inner.classList.toggle('iac-side-active', on);
					}
				}

				function setMobileItem(item, on) {
					if (!item) {
						return;
					}
					item.style.opacity = on ? '1' : '0';
					item.style.zIndex = on ? '1' : '0';
					item.style.pointerEvents = on ? 'auto' : 'none';
					item.classList.toggle('pointer-events-none', !on);
				}

				function show(index) {
					active = (index + buttons.length) % buttons.length;
					buttons.forEach(function (btn, i) {
						var on = i === active;
						btn.setAttribute('data-tab-active', on ? 'true' : 'false');
						btn.classList.toggle('bg-[#0a0d0c]', on);
						btn.classList.toggle('bg-[#0a0d0c]/30', !on);
						btn.classList.toggle('shadow-[inset_0_-1px_2px_rgba(255,255,255,0.06),inset_0_1px_2px_rgba(0,0,0,0.2)]', on);
						btn.querySelectorAll('svg').forEach(function (icon) {
							icon.classList.toggle('text-muted', on);
							icon.classList.toggle('text-muted/25', !on);
						});
						setTabProgress(btn, on);
					});
					panels.forEach(function (panel, i) {
						var on = i === active;
						panel.classList.toggle('iac-tab-visible', on);
						panel.style.transition = 'opacity 400ms ease, transform 400ms ease';
						panel.style.opacity = on ? '1' : '0';
						panel.style.transform = on ? 'translateY(0)' : 'translateY(8px)';
						panel.style.pointerEvents = on ? 'auto' : 'none';
						panel.style.zIndex = on ? '2' : '1';
						panel.classList.toggle('pointer-events-none', !on);
					});

					var width = window.innerWidth || document.documentElement.clientWidth;
					leftItems.forEach(function (item, i) {
						var on = width < 1024 ? active === i : active < 3 && active === i;
						setSideItem(item, on);
					});
					rightItems.forEach(function (item, i) {
						setSideItem(item, width >= 1024 && active >= 3 && active - 3 === i);
					});
					mobileItems.forEach(function (item, i) {
						setMobileItem(item, width < 768 && active === i);
					});
				}

				function schedule() {
					if (timer) {
						window.clearInterval(timer);
					}
					if (reducedMotion) {
						return;
					}
					timer = window.setInterval(function () {
						show(active + 1);
					}, 5500);
					tabTimers.push(timer);
				}

				show(0);
				schedule();

				buttons.forEach(function (btn, i) {
					btn.addEventListener('click', function () {
						show(i);
						schedule();
					});
				});
				leftItems.forEach(function (item, i) {
					item.addEventListener('click', function () {
						show(i);
						schedule();
					});
				});
				rightItems.forEach(function (item, i) {
					item.addEventListener('click', function () {
						show(i + 3);
						schedule();
					});
				});
			});
		}

		function initSidePulses() {
			if (reducedMotion) {
				return;
			}
			var start = performance.now();
			root.querySelectorAll('linearGradient[id$="-pulse"]').forEach(function (grad) {
				var box = grad.getAttribute('y2') || '120';
				grad._iacPulse = parseFloat(box) || 120;
			});
			function tick(now) {
				var t = (now - start) / 1000;
				root.querySelectorAll('linearGradient[id$="-pulse"]').forEach(function (grad, idx) {
					var h = grad._iacPulse || 120;
					var y = ((t * 80 + idx * 30) % (h + 40)) - 40;
					grad.setAttribute('y1', String(y));
					grad.setAttribute('y2', String(y + 40));
				});
				requestAnimationFrame(tick);
			}
			requestAnimationFrame(tick);
		}

		function initPulseLines() {
			if (!isPlatform || reducedMotion) {
				return;
			}
			var svg = root.querySelector('section:first-of-type svg[viewBox="0 0 800 500"]');
			if (!svg) {
				return;
			}
			var pulses = [];
			svg.querySelectorAll('line[stroke*="pulse-grad"]').forEach(function (line) {
				var match = line.getAttribute('stroke').match(/pulse-grad-(\d+)/);
				if (!match) {
					return;
				}
				var grad = svg.querySelector('#pulse-grad-' + match[1]);
				if (!grad) {
					return;
				}
				pulses.push({
					grad: grad,
					x1: parseFloat(line.getAttribute('x1')),
					y1: parseFloat(line.getAttribute('y1')),
					x2: parseFloat(line.getAttribute('x2')),
					y2: parseFloat(line.getAttribute('y2')),
					phase: Math.random(),
				});
				line.setAttribute('stroke-opacity', '0.55');
			});
			if (!pulses.length) {
				return;
			}
			var start = performance.now();
			function tick(now) {
				var t = (now - start) / 1000;
				pulses.forEach(function (p) {
					var local = (t * 0.35 + p.phase) % 1;
					var seg = 0.14;
					var x1 = p.x1 + (p.x2 - p.x1) * local;
					var y1 = p.y1 + (p.y2 - p.y1) * local;
					var x2 = p.x1 + (p.x2 - p.x1) * Math.min(local + seg, 1);
					var y2 = p.y1 + (p.y2 - p.y1) * Math.min(local + seg, 1);
					p.grad.setAttribute('x1', String(x1));
					p.grad.setAttribute('y1', String(y1));
					p.grad.setAttribute('x2', String(x2));
					p.grad.setAttribute('y2', String(y2));
				});
				requestAnimationFrame(tick);
			}
			requestAnimationFrame(tick);
		}

		function initConstellation() {
			if (!isPlatform) {
				return;
			}
			var groups = root.querySelectorAll('[data-net="group"]');
			if (!groups.length) {
				return;
			}
			var portraits = root.querySelectorAll('[data-portrait-id]');
			var mobile = root.querySelectorAll('[data-net-mobile]');
			var active = 0;

			function portraitIndex(node) {
				var id = node.getAttribute('data-portrait-id') || '';
				var n = parseInt(id.replace(/\D/g, ''), 10);
				return isNaN(n) ? -1 : n;
			}

			function show(i) {
				active = (i + groups.length) % groups.length;
				var second = (active + Math.floor(groups.length / 2)) % groups.length;
				var actives = [active, second];

				groups.forEach(function (group, idx) {
					var on = actives.indexOf(idx) >= 0;
					showEl(group.querySelector('[data-net="message"]'), on);
					showEl(group.querySelector('[data-net="glow"]'), on);
				});

				portraits.forEach(function (portrait) {
					var idx = portraitIndex(portrait);
					var on = actives.indexOf(idx) >= 0;
					portrait.classList.toggle('iac-portrait-active', on);
					var img = portrait.querySelector('img');
					var tint = portrait.querySelector('[data-net-tint]');
					if (img) {
						img.style.opacity = on ? '1' : '0.55';
					}
					if (tint) {
						tint.style.opacity = on ? '0.65' : '0';
					}
				});

				mobile.forEach(function (node, idx) {
					showEl(node, actives.indexOf(idx) >= 0);
				});
			}

			show(0);
			if (!reducedMotion) {
				window.setInterval(function () {
					show(active + 1);
				}, 3200);
			}
		}

		function initBotMessages() {
			root.querySelectorAll('[data-demo-message-bot]').forEach(function (bot) {
				var dots = bot.querySelector('[data-icon-dots="true"]');
				if (dots) {
					dots.style.display = 'none';
				}
				bot.style.opacity = '1';
				var bubble = bot.querySelector('[data-slot="content"]');
				if (bubble) {
					bubble.style.opacity = '1';
				}
			});
		}

		function initDeskSection() {
			if (!isPlatform) {
				return;
			}
			var figure = root.querySelector('[data-iac-desk-figure="true"]');
			var grid = root.querySelector('[data-iac-desk-grid="true"]');
			if (!figure) {
				return;
			}

			var stack = figure.querySelector('.absolute.inset-0.flex.flex-col');
			var bubbles = stack ? stack.querySelectorAll(':scope > div') : [];
			var items = grid ? grid.querySelectorAll(':scope > div') : [];
			var bubbleActive = 0;
			var itemActive = 0;

			function showBubble(i) {
				if (!bubbles.length) {
					return;
				}
				bubbleActive = (i + bubbles.length) % bubbles.length;
				bubbles.forEach(function (bubble, idx) {
					var visible = idx <= bubbleActive;
					bubble.style.transition = 'opacity 550ms ease, transform 550ms ease';
					bubble.style.opacity = visible ? '1' : '0';
					bubble.style.transform = visible ? 'translateY(0)' : 'translateY(12px)';
					bubble.style.pointerEvents = visible ? 'auto' : 'none';
				});
			}

			function showDeskItem(i) {
				if (!items.length) {
					return;
				}
				itemActive = (i + items.length) % items.length;
				items.forEach(function (item, idx) {
					var on = idx === itemActive;
					item.style.transition = 'opacity 450ms ease';
					item.style.opacity = on ? '1' : '0.45';
					item.classList.toggle('iac-desk-active', on);
				});
			}

			showBubble(0);
			showDeskItem(0);

			items.forEach(function (item, i) {
				item.style.cursor = 'pointer';
				item.addEventListener('click', function () {
					showDeskItem(i);
				});
			});

			if (reducedMotion) {
				return;
			}

			window.setInterval(function () {
				showDeskItem(itemActive + 1);
			}, 4200);

			window.setInterval(function () {
				showBubble(bubbleActive + 1);
			}, 2800);
		}

		function initVizCharts() {
			var section = root.querySelector('#dynamic-visualization');
			if (!section || reducedMotion) {
				return;
			}
			section.querySelectorAll('[data-line="true"], path[data-line="true"]').forEach(function (line, i) {
				line.style.animation = 'iac-viz-line 2.8s ease-in-out ' + i * 0.12 + 's infinite alternate';
			});
			section.querySelectorAll('.bg-card.shadow-card').forEach(function (card, i) {
				card.style.animation = 'iac-viz-float 4.5s ease-in-out ' + i * 0.35 + 's infinite alternate';
			});
		}

		function initAnimatedGroups(groupSelector, interval) {
			if (isPlatform && groupSelector === '[data-net="group"]') {
				return;
			}
			var groups = root.querySelectorAll(groupSelector);
			if (!groups.length) {
				return;
			}
			var active = 0;
			function show(i) {
				active = (i + groups.length) % groups.length;
				groups.forEach(function (group, idx) {
					var on = idx === active;
					showEl(group.querySelector('[data-radar="message"], [data-net="message"]'), on);
					showEl(group.querySelector('[data-radar="glow"], [data-net="glow"]'), on);
					showEl(group.querySelector('[data-radar="arrow"]'), on);
					var head = group.querySelector('[data-radar="arrowhead"]');
					if (head) {
						head.setAttribute('opacity', on ? '1' : '0');
					}
				});
			}
			show(0);
			if (!reducedMotion) {
				window.setInterval(function () {
					show(active + 1);
				}, interval || 2600);
			}
		}

		function initAlertDemo() {
			if (root.querySelector('[data-tab-frame="true"]')) {
				return;
			}
			var wrapper = root.querySelector('[data-slot="item-wrapper"]');
			if (!wrapper || !root.querySelector('[data-demo-alert="true"]')) {
				return;
			}
			var panels = wrapper.querySelectorAll('[data-tab-content]');
			if (panels.length < 2) {
				return;
			}
			var active = 0;
			function show(i) {
				active = (i + panels.length) % panels.length;
				panels.forEach(function (panel, idx) {
					panel.style.transition = 'opacity 500ms ease, transform 500ms ease';
					panel.style.opacity = idx === active ? '1' : '0';
					panel.style.transform = idx === active ? 'translateY(0)' : 'translateY(12px)';
					panel.style.pointerEvents = idx === active ? 'auto' : 'none';
				});
			}
			show(0);
			if (!reducedMotion) {
				window.setInterval(function () {
					show(active + 1);
				}, 4500);
			}
		}

		function initAgentCarousel() {
			if (isTeam && root.querySelector('#gt-map-widget-v1')) {
				return;
			}
			if (isTeam && root.querySelector('.iac-desk-network')) {
				return;
			}
			var items = root.querySelectorAll('[data-agent-index]');
			if (!items.length) {
				return;
			}
			var agentsWrap = root.querySelector('[data-hero="agents"]');
			var heroPanel = root.querySelector('[data-hero="message"]');
			var title = root.querySelector('[data-hero="agents-title"]');
			var messageContent = heroPanel ? heroPanel.querySelector('[data-slot="content"]') : null;
			var heroFigure = root.querySelector('[data-hero="bg"]');
			var heroOverlay = root.querySelector('figure.relative.w-full.max-w-170');
			var platforms = [
				{ title: 'Agency Accounts', tag: '@Desk', text: 'Structured agency accounts · one desk channel' },
				{ title: 'Media Buying Access', tag: '@Desk', text: 'Platform access queued · launch window open' },
				{ title: 'Geo Match', tag: '@Desk', text: 'GEO verified · US/EU tier match complete' },
				{ title: 'Volume Tier', tag: '@Desk', text: 'Volume tier confirmed · repeat order terms set' },
				{ title: 'Lock Terms', tag: '@Desk', text: 'Terms locked · replacement policy confirmed' },
				{ title: 'Direct Handoff', tag: '@Desk', text: 'EU agency batch · 50 accounts ready for handoff' },
			];
			platforms.forEach(function (item) {
				item.title = nativeCopy(item.title);
				item.text = nativeCopy(item.text);
			});
			var defaultActive = isTeam ? 4 : 0;
			if (heroOverlay) {
				heroOverlay.classList.add('iac-hero-stage', 'notranslate');
				if (!heroOverlay.querySelector('.iac-hero-ship')) {
					var ship = document.createElement('div');
					ship.className = 'iac-hero-ship notranslate';
					ship.setAttribute('aria-hidden', 'true');
					heroOverlay.appendChild(ship);
				}
				if (!heroOverlay.querySelector('.iac-hero-beam')) {
					var beam = document.createElement('div');
					beam.className = 'iac-hero-beam notranslate';
					beam.setAttribute('aria-hidden', 'true');
					heroOverlay.appendChild(beam);
				}
			}
			if (agentsWrap) {
				agentsWrap.classList.add('notranslate');
			}
			if (heroPanel) {
				heroPanel.classList.remove('hidden');
				heroPanel.style.display = 'block';
				heroPanel.style.opacity = '1';
				heroPanel.style.visibility = 'visible';
				heroPanel.classList.add('iac-hero-message', 'notranslate');
			}
			if (title) {
				title.classList.remove('opacity-0');
				title.style.opacity = '1';
			}
			if (agentsWrap && !reducedMotion) {
				agentsWrap.setAttribute('data-looping', 'true');
			}
			items.forEach(function (el, idx) {
				var p = platforms[idx] || platforms[0];
				el.setAttribute('title', p.title);
				el.style.transition =
					'opacity 600ms cubic-bezier(0.33, 1, 0.68, 1), transform 600ms cubic-bezier(0.33, 1, 0.68, 1), box-shadow 600ms ease, background-color 600ms ease';
				if (!reducedMotion) {
					el.style.animation = 'iac-hero-badge-in 0.7s ease both';
					el.style.animationDelay = idx * 0.08 + 's';
				}
			});
			var msgTextEl = messageContent ? messageContent.querySelector('[data-hero="msg-text"]') : null;
			var msgTagEl = messageContent ? messageContent.querySelector('[data-hero="agent-name"]') : null;
			if (messageContent && !msgTextEl) {
				messageContent.innerHTML =
					'<span data-hero="msg-text" class="notranslate"></span> ' +
					'<span class="text-primary-foreground inline-block font-medium notranslate" data-hero="agent-name"></span>';
				msgTextEl = messageContent.querySelector('[data-hero="msg-text"]');
				msgTagEl = messageContent.querySelector('[data-hero="agent-name"]');
			}
			var active = 0;
			var cycling = false;
			function show(i) {
				active = (i + items.length) % items.length;
				var current = platforms[active] || platforms[0];
				cycling = true;
				items.forEach(function (el, idx) {
					var on = idx === active;
					el.setAttribute('data-active', on ? 'true' : 'false');
					el.classList.toggle('iac-hero-badge-active', on);
					el.style.opacity = on ? '1' : '0.32';
					el.style.transform = on ? 'scale(1.12) translateY(-6px)' : 'scale(0.92)';
					el.style.boxShadow = on ? '0 0 28px rgba(255, 0, 39, 0.45), 0 0 0 1px rgba(255, 0, 39, 0.35)' : '';
					el.style.backgroundColor = on ? 'rgba(255, 0, 36, 0.2)' : '';
					var svg = el.querySelector('svg');
					if (svg) {
						svg.style.opacity = on ? '1' : '0.35';
						svg.style.transform = on ? 'scale(1.05)' : 'scale(1)';
						svg.style.transition = 'opacity 500ms ease, transform 500ms ease';
					}
				});
				if (msgTextEl && msgTagEl) {
					if (heroPanel) {
						heroPanel.classList.add('iac-hero-message--swap');
					}
					window.setTimeout(function () {
						msgTextEl.textContent = current.text;
						msgTagEl.textContent = current.tag;
						if (heroPanel) {
							heroPanel.classList.remove('iac-hero-message--swap');
						}
					}, 180);
				}
				if (heroFigure && !reducedMotion) {
					heroFigure.style.transition = 'transform 900ms cubic-bezier(0.33, 1, 0.68, 1)';
					heroFigure.style.transform =
						active % 2 === 0 ? 'scale(1.025) translateX(-0.4%)' : 'scale(1.04) translateX(0.4%)';
				}
			}
			show(defaultActive);
			items.forEach(function (el, i) {
				el.addEventListener('mouseenter', function () {
					show(i);
				});
			});
			if (!reducedMotion) {
				window.setInterval(function () {
					show(active + 1);
				}, 3600);
			}
		}

		function initChartBars() {
			root.querySelectorAll('[data-bars="true"]').forEach(function (bars) {
				bars.querySelectorAll('[data-col="true"]').forEach(function (col, i) {
					col.style.transformOrigin = 'bottom';
					col.style.animation = reducedMotion ? 'none' : 'iac-feature-bar 2.4s ease-in-out ' + i * 0.08 + 's infinite alternate';
				});
			});
		}

		function initRadar() {
			root.querySelectorAll('[data-radar="dot"]').forEach(function (dot, i) {
				if (!reducedMotion) {
					dot.style.animation = reducedMotion ? 'none' : 'iac-feature-pulse 2s ease-in-out ' + i * 0.35 + 's infinite';
				}
			});
			if (reducedMotion) {
				return;
			}
			root.querySelectorAll('section:first-of-type radialGradient[id^="radar-wave"]').forEach(function (grad, i) {
				var circle = grad.parentNode && grad.parentNode.querySelector('circle');
				if (!circle) {
					return;
				}
				var maxR = parseFloat(circle.getAttribute('r') || '175') || 175;
				var start = performance.now();
				function pulse(now) {
					var t = ((now - start) / 1000 + i * 0.4) % 2.6;
					var r = (t / 2.6) * maxR;
					grad.setAttribute('r', String(r));
					circle.setAttribute('opacity', String(0.55 - (t / 2.6) * 0.45));
					requestAnimationFrame(pulse);
				}
				requestAnimationFrame(pulse);
			});
		}

		function initScrollFade() {
			root.querySelectorAll('.transition-opacity.duration-1000').forEach(function (el) {
				el.style.opacity = '1';
			});
		}

		function initTeamHero() {
			if (!isTeam) {
				return;
			}
			var wrap = root.querySelector('[data-iac-team-hero="true"]');
			if (!wrap) {
				return;
			}
			var svg = wrap.querySelector('.iac-team-hero-svg');
			var wave = wrap.querySelector('[data-team-radar="wave"]');
			var nodes = wrap.querySelectorAll('[data-team-node="true"]');
			var msg = wrap.querySelector('.iac-team-hero-msg');
			if (msg) {
				msg.style.opacity = '1';
				msg.style.transition = 'opacity 600ms ease, transform 600ms ease';
				msg.style.transform = 'translateY(0)';
			}
			var active = 0;
			function showNode(i) {
				active = (i + nodes.length) % nodes.length;
				nodes.forEach(function (node, idx) {
					var on = idx === active;
					node.style.transition = 'opacity 400ms ease, transform 400ms ease';
					node.style.opacity = on ? '1' : '0.55';
					node.style.transform = on ? 'scale(1.08)' : 'scale(1)';
					var circle = node.querySelector('circle');
					if (circle) {
						circle.setAttribute('stroke-opacity', on ? '1' : '0.45');
					}
				});
			}
			showNode(0);
			if (!reducedMotion && nodes.length) {
				window.setInterval(function () {
					showNode(active + 1);
				}, 2400);
			}
			if (!reducedMotion && wave && svg) {
				var start = performance.now();
				var maxR = 160;
				function pulse(now) {
					var t = ((now - start) / 1000) % 2.8;
					var r = 20 + (t / 2.8) * (maxR - 20);
					wave.setAttribute('r', String(r));
					wave.setAttribute('opacity', String(0.65 - (t / 2.8) * 0.55));
					requestAnimationFrame(pulse);
				}
				requestAnimationFrame(pulse);
			}
		}

		function initTeamSupplyDesk() {
			if (!isTeam) {
				return;
			}
			if (root.querySelector('#gt-map-widget-v1')) {
				return;
			}
			var network = root.querySelector('.iac-desk-network');
			if (!network) {
				return;
			}
			var heroOverlay = root.querySelector('figure.relative.w-full.max-w-170');
			if (heroOverlay) {
				heroOverlay.classList.add('iac-hero-stage', 'iac-desk-stage-wrap', 'notranslate');
			}
			network.setAttribute('data-looping', 'true');

			var heroPanel = root.querySelector('[data-hero="message"]');
			var msgTextEl = root.querySelector('[data-hero="msg-text"]');
			var msgTagEl = root.querySelector('[data-hero="agent-name"]');
			var terminal = root.querySelector('[data-terminal-output]');
			var nodes = root.querySelectorAll('.iac-desk-node[data-agent-index]');

			var cardMessages = [
				{ text: 'Launch window open · replacement policy confirmed', tag: '@TikTok' },
				{ text: 'EU agency batch · 50 accounts ready for handoff', tag: '@Desk' },
				{ text: 'Volume tier pending buyer sign-off', tag: '@GoogleAds' },
				{ text: 'Direct channel confirmed · handoff today', tag: '@Desk' },
			];
			cardMessages.forEach(function (item) {
				item.text = nativeCopy(item.text);
			});

			var terminalLines = [
				{ text: 'impact. status --geo EU --platform agency --volume 50', cls: 'iac-term-cmd' },
				{ text: 'Checking availability for your request…', cls: 'iac-term-dim' },
				{ text: '50 agency accounts confirmed for EU launch', cls: 'iac-term-ok' },
				{ text: 'Root cause identified:', cls: 'iac-term-head' },
				{ text: '• Repeat order queue at capacity for this geo', cls: 'iac-term-bullet' },
				{ text: '• Terms locked · handoff today via direct channel', cls: 'iac-term-bullet' },
				{ text: '• Volume tier terms still pending buyer sign-off', cls: 'iac-term-bullet' },
				{ text: 'Recommended fixes:', cls: 'iac-term-head' },
				{ text: '1. Confirm volume tier and replacement policy', cls: 'iac-term-num' },
				{ text: '2. Lock terms before handoff', cls: 'iac-term-num' },
				{ text: '3. Confirm replacement policy before next batch ships', cls: 'iac-term-num' },
			];
			terminalLines.forEach(function (line) {
				line.text = nativeCopy(line.text);
			});

			var activeNode = 4;
			var cardIndex = 0;
			var termIndex = 0;
			var termTimer = null;

			function setCard(i, animate) {
				if (!msgTextEl || !msgTagEl) {
					return;
				}
				cardIndex = (i + cardMessages.length) % cardMessages.length;
				var m = cardMessages[cardIndex];
				if (animate && heroPanel) {
					heroPanel.classList.add('iac-desk-message--swap');
					window.setTimeout(function () {
						msgTextEl.textContent = m.text;
						msgTagEl.textContent = m.tag;
						heroPanel.classList.remove('iac-desk-message--swap');
					}, 220);
				} else {
					msgTextEl.textContent = m.text;
					msgTagEl.textContent = m.tag;
				}
			}

			function setNode(i) {
				activeNode = (i + nodes.length) % nodes.length;
				nodes.forEach(function (node, idx) {
					var on = idx === activeNode;
					node.setAttribute('data-active', on ? 'true' : 'false');
					node.classList.toggle('iac-desk-node--active', on);
				});
			}

			function appendTerminalLine(line, instant) {
				if (!terminal) {
					return;
				}
				var row = document.createElement('div');
				row.className = 'iac-desk-term-line ' + (line.cls || '');
				if (instant || reducedMotion) {
					row.textContent = line.text;
					row.classList.add('iac-desk-term-line--visible');
					terminal.appendChild(row);
					terminal.scrollTop = terminal.scrollHeight;
					return;
				}
				terminal.appendChild(row);
				var chars = line.text.split('');
				var ci = 0;
				function tick() {
					if (ci >= chars.length) {
						row.classList.add('iac-desk-term-line--visible');
						terminal.scrollTop = terminal.scrollHeight;
						return;
					}
					row.textContent += chars[ci];
					ci += 1;
					window.setTimeout(tick, line.cls === 'iac-term-cmd' ? 12 : 8);
				}
				tick();
			}

			function runTerminal() {
				if (!terminal) {
					return;
				}
				terminal.innerHTML = '';
				termIndex = 0;
				function nextLine() {
					if (termIndex >= terminalLines.length) {
						window.setTimeout(function () {
							if (!reducedMotion) {
								runTerminal();
							}
						}, 4200);
						return;
					}
					appendTerminalLine(terminalLines[termIndex], reducedMotion);
					termIndex += 1;
					var delay = terminalLines[termIndex - 1].cls === 'iac-term-cmd' ? 900 : 480;
					termTimer = window.setTimeout(nextLine, delay);
				}
				nextLine();
			}

			setCard(0, false);
			setNode(activeNode);

			nodes.forEach(function (node, i) {
				node.addEventListener('mouseenter', function () {
					setNode(i);
				});
				node.addEventListener('focus', function () {
					setNode(i);
				});
			});

			if (reducedMotion) {
				terminalLines.forEach(function (line) {
					appendTerminalLine(line, true);
				});
				return;
			}

			runTerminal();

			window.setInterval(function () {
				setCard(cardIndex + 1, true);
			}, 4800);

			window.setInterval(function () {
				setNode(activeNode + 1);
			}, 3200);
		}

		function initTypingDots() {
			root.querySelectorAll('[data-demo-message-bot] [data-icon-dots="true"] span, [data-icon-dots="true"] span').forEach(function (dot, i) {
				dot.style.animation = reducedMotion ? 'none' : 'iac-feature-dot 1s ease-in-out ' + i * 0.15 + 's infinite';
			});
		}

		initTabPanels();
		initBotMessages();
		initSidePulses();
		initPulseLines();
		initConstellation();
		initDeskSection();
		initVizCharts();
		initAlertDemo();
		initAgentCarousel();
		initTeamSupplyDesk();
		initAnimatedGroups('[data-radar="group"]', 2600);
		initAnimatedGroups('[data-net="group"]', 3000);
		initChartBars();
		initRadar();
		initTeamHero();
		initScrollFade();
		initTypingDots();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
