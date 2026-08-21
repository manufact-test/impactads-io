(function (window, document) {
	'use strict';

	function showChrome() {
		document.documentElement.classList.add('iac-frame-visible');

		document.querySelectorAll('[data-slot="frame"]').forEach(function (frame) {
			var wrap = frame.closest('.absolute.inset-0');
			if (wrap) {
				wrap.style.visibility = 'visible';
				wrap.style.opacity = '1';
			}
		});

		document.querySelectorAll('header, header nav, header ul, header li, header a, header button, header span').forEach(function (el) {
			el.style.opacity = '1';
			el.style.visibility = 'visible';
		});

		document.querySelectorAll('.fade-header').forEach(function (el) {
			el.style.opacity = '1';
		});
	}

	function revealFooterLinks() {
		document.querySelectorAll('footer a, footer .opacity-0, #iah-chrome-footer-root a').forEach(function (el) {
			el.style.opacity = '1';
		});
	}

	function cleanupPluginPreloader() {
		var iap = document.getElementById('iap-loader');
		if (iap && iap.parentNode) {
			iap.parentNode.removeChild(iap);
		}
		document.documentElement.classList.remove('iap-preloader-active');
	}

	function isRu() {
		if (window.iacData && window.iacData.lang) {
			return window.iacData.lang === 'ru';
		}
		return document.documentElement.lang === 'ru' || document.documentElement.classList.contains('iac-lang-ru');
	}

	function normalize(value) {
		return String(value || '').replace(/\s+/g, ' ').trim();
	}

	function replaceTextNodes(root, replacements) {
		if (!root || !replacements) return;
		var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
		var node;
		while ((node = walker.nextNode())) {
			var key = normalize(node.nodeValue);
			if (!key || !Object.prototype.hasOwnProperty.call(replacements, key)) continue;
			var next = replacements[key];
			if (node.nodeValue !== next) node.nodeValue = next;
		}
	}

	function closestSectionWithText(text) {
		var sections = document.querySelectorAll('main section, section');
		for (var i = 0; i < sections.length; i += 1) {
			if (normalize(sections[i].textContent).indexOf(text) !== -1) return sections[i];
		}
		return null;
	}

	function nearbyContains(el, needle, maxDepth) {
		var current = el;
		var depth = 0;
		while (current && current !== document.body && depth < (maxDepth || 7)) {
			if (normalize(current.textContent).indexOf(needle) !== -1) return true;
			current = current.parentElement;
			depth += 1;
		}
		return false;
	}

	function applicationUrl() {
		return window.iacData && window.iacData.applicationUrl ? window.iacData.applicationUrl : '/application/';
	}

	function patchHeaderCta() {
		var roots = [];
		var header = document.querySelector('header');
		var menu = document.querySelector('[data-main-menu]');
		if (header) roots.push(header);
		if (menu) roots.push(menu);

		roots.forEach(function (root) {
			replaceTextNodes(root, {
				'Request access': 'ПОДОБРАТЬ АККАУНТЫ',
				'REQUEST ACCESS': 'ПОДОБРАТЬ АККАУНТЫ',
				'Get access': 'ПОДОБРАТЬ АККАУНТЫ',
				'GET ACCESS': 'ПОДОБРАТЬ АККАУНТЫ',
				'Связаться': 'ПОДОБРАТЬ АККАУНТЫ',
				'СВЯЗАТЬСЯ': 'ПОДОБРАТЬ АККАУНТЫ'
			});
		});
	}

	function patchTimelineCtas() {
		var configs = [
			{ path: '/accounts/platform-access/', label: 'ПОДОБРАТЬ АККАУНТ' },
			{ path: '/accounts/agency-accounts/', label: 'УСЛОВИЯ ДЛЯ МЕДИАБАИНГА' },
			{ path: '/accounts/team-supply/', label: 'УСЛОВИЯ ДЛЯ КОМАНД' }
		];

		configs.forEach(function (config) {
			document.querySelectorAll('main a[href*="' + config.path + '"]').forEach(function (link) {
				replaceTextNodes(link, {
					'LEARN MORE': config.label,
					'READ MORE': config.label,
					'ПОДРОБНЕЕ': config.label
				});
			});
		});

		document.querySelectorAll('main button').forEach(function (button) {
			var label = normalize(button.textContent);
			if (label !== 'Связаться' && label !== 'СВЯЗАТЬСЯ' && label !== 'Request access' && label !== 'REQUEST ACCESS') return;

			if (nearbyContains(button, 'Трастовый аккаунт готов к проверке', 7)) {
				replaceTextNodes(button, {
					'Связаться': 'ПОЛУЧИТЬ НА ПРОВЕРКУ',
					'СВЯЗАТЬСЯ': 'ПОЛУЧИТЬ НА ПРОВЕРКУ',
					'Request access': 'ПОЛУЧИТЬ НА ПРОВЕРКУ',
					'REQUEST ACCESS': 'ПОЛУЧИТЬ НА ПРОВЕРКУ'
				});
			} else if (nearbyContains(button, 'Подбор для медиабаинга готов', 7)) {
				replaceTextNodes(button, {
					'Связаться': 'ЗАПРОСИТЬ ПОДБОР',
					'СВЯЗАТЬСЯ': 'ЗАПРОСИТЬ ПОДБОР',
					'Request access': 'ЗАПРОСИТЬ ПОДБОР',
					'REQUEST ACCESS': 'ЗАПРОСИТЬ ПОДБОР'
				});
			}
		});
	}

	function patchPurchaseCards() {
		var section = closestSectionWithText('ВЫБЕРИТЕ ФОРМАТ ЗАКУПКИ');
		if (!section) return;

		var cards = [
			{
				path: '/accounts/platform-access/',
				title: 'АККАУНТ ПОД ЗАЛИВ',
				badge: 'ПОД ЗАЛИВ',
				index: '01',
				cta: 'ПОДОБРАТЬ →'
			},
			{
				path: '/accounts/agency-accounts/',
				title: 'АККАУНТЫ ДЛЯ МЕДИАБАИНГА',
				badge: 'ДЛЯ МЕДИАБАИНГА',
				index: '02',
				cta: 'УЗНАТЬ УСЛОВИЯ →'
			},
			{
				path: '/accounts/team-supply/',
				title: 'РЕГУЛЯРНЫЙ ОБЪЁМ',
				badge: 'ПОД ОБЪЁМ',
				index: '03',
				cta: 'ЗАПРОСИТЬ ОБЪЁМ →'
			}
		];

		cards.forEach(function (config) {
			var card = section.querySelector('a[href*="' + config.path + '"]');
			if (!card) return;
			var title = card.querySelector('h2, h3');
			if (title && normalize(title.textContent) !== config.title) title.textContent = config.title;
			replaceTextNodes(card, {
				'ACCOUNTS': config.badge,
				'АККАУНТЫ': config.badge,
				'1': config.index,
				'2': config.index,
				'3': config.index,
				'ПОДРОБНЕЕ →': config.cta,
				'Читать →': config.cta,
				'Read More →': config.cta
			});
		});
	}

	function patchAssuranceSection() {
		var section = closestSectionWithText('ВСЁ ПОНЯТНО ДО ОПЛАТЫ');
		if (!section) return;
		var items = section.querySelectorAll('ul[role="list"] li');
		if (items.length >= 3) {
			var title = items[2].querySelector('h3');
			if (title && normalize(title.textContent) !== 'ЧЕСТНАЯ ГРАНИЦА ГАРАНТИИ') {
				title.textContent = 'ЧЕСТНАЯ ГРАНИЦА ГАРАНТИИ';
			}
		}
	}

	function patchManifesto() {
		var section = closestSectionWithText('ПОЧЕМУ МЫ');
		if (!section) return;
		replaceTextNodes(section, {
			'Resource over noise': 'ТОЛЬКО СПЕНД. БЕЗ ПУСТОГО ФАРМА.',
			'Account sellers have accumulated noise over the years. None of it makes launches faster. If access is infrastructure, the best interface is direct contact — not screenshots, not emojis, not random chats.': 'Мы продаём Google Ads аккаунты с реальной историей открутки. Не автореги и не фарм без трат: у аккаунта уже есть спенд, а значит — накопленный траст, выше лимиты, мягче модерация и меньше проверок при первом заливе. Вы платите за готовый рабочий ресурс и экономите время на самостоятельном прогреве перед заливом.',
			'Working resource': 'СНАЧАЛА ПРОВЕРЯЕТЕ. ПОТОМ ПЛАТИТЕ.',
			'Random Telegram sellers used to be the norm. Structured supply is the strength — clear request, fast contact, working access under terms your team can trust.': 'Получаете аккаунт и самостоятельно сверяете заявленные параметры: спенд, гео и валюту. Всё совпадает и аккаунт работает — оплачиваете в USDT TRC20. Не хотите проводить крупную сделку напрямую — подключаем гаранта. Комиссию оплачивает покупатель.',
			'Chaos is optional': 'ПОСТАВЩИК, КОТОРОГО НЕ НУЖНО МЕНЯТЬ',
			'Random sellers are broken. Unstable supply and vague terms. The future is structured access — clear request, fast contact, working resource.': 'Аккаунт не заходит или не соответствует заявленному спенду, гео или валюте — заменяем, пока вы не внесли в него изменения. Без тикетов, мелкого шрифта и споров. По каждой покупке на связи лично владелец, поддержка работает 24/7. За impact. — 7 лет на рынке, 15 000 выданных аккаунтов и 100+ активных команд.'
		});
		var link = section.querySelector('a[href*="/blog/manifesto"], a[data-iac-scroll-final]');
		if (!link) return;

		link.setAttribute('href', '#iac-final-cta');
		link.setAttribute('data-iac-scroll-final', '1');
		var currentTitle = '';
		section.querySelectorAll('h3').forEach(function (heading) {
			if (!currentTitle && normalize(heading.textContent)) currentTitle = normalize(heading.textContent);
		});

		var label = 'ПОДОБРАТЬ АККАУНТ';
		if (currentTitle.indexOf('ТОЛЬКО СПЕНД') !== -1) label = 'ПОДОБРАТЬ ПО СПЕНДУ';
		if (currentTitle.indexOf('СНАЧАЛА ПРОВЕРЯЕТЕ') !== -1) label = 'ПОЛУЧИТЬ НА ПРОВЕРКУ';
		replaceTextNodes(link, {
			'Read Blog': label,
			'Читать блог': label,
			'ПОДОБРАТЬ АККАУНТ': label,
			'ПОДОБРАТЬ ПО СПЕНДУ': label,
			'ПОЛУЧИТЬ НА ПРОВЕРКУ': label
		});
	}

	function patchFinalFormCta() {
		var section = closestSectionWithText('ПОЛУЧИТЕ АККАУНТ. ПРОВЕРЬТЕ. ПОТОМ ПЛАТИТЕ.') || document.querySelector('footer');
		if (!section) return;
		section.id = 'iac-final-cta';
		replaceTextNodes(section, {
			'Apply': 'ПОЛУЧИТЬ АККАУНТ НА ПРОВЕРКУ',
			'Request access': 'ПОЛУЧИТЬ АККАУНТ НА ПРОВЕРКУ',
			'REQUEST ACCESS': 'ПОЛУЧИТЬ АККАУНТ НА ПРОВЕРКУ',
			'Связаться': 'ПОЛУЧИТЬ АККАУНТ НА ПРОВЕРКУ',
			'СВЯЗАТЬСЯ': 'ПОЛУЧИТЬ АККАУНТ НА ПРОВЕРКУ'
		});
	}

	var patchQueued = false;
	function patchRussianHomepage() {
		if (!isRu()) return;
		patchQueued = false;
		document.documentElement.classList.add('iah-home', 'iac-lang-ru');
		patchHeaderCta();
		patchTimelineCtas();
		patchPurchaseCards();
		patchAssuranceSection();
		patchManifesto();
		patchFinalFormCta();
	}

	function queueRussianPatch() {
		if (patchQueued || !isRu()) return;
		patchQueued = true;
		window.requestAnimationFrame(patchRussianHomepage);
	}

	document.addEventListener('click', function (event) {
		if (!isRu()) return;
		var control = event.target && event.target.closest ? event.target.closest('header a, header button, [data-main-menu] a, [data-main-menu] button') : null;
		if (!control) return;
		if (normalize(control.textContent).indexOf('ПОДОБРАТЬ АККАУНТЫ') === -1) return;
		event.preventDefault();
		event.stopPropagation();
		if (event.stopImmediatePropagation) event.stopImmediatePropagation();
		window.location.href = applicationUrl();
	}, true);

	document.addEventListener('click', function (event) {
		if (!isRu()) return;
		var control = event.target && event.target.closest ? event.target.closest('button, a') : null;
		if (!control) return;
		if (control.matches('[data-iac-scroll-final]') || normalize(control.textContent).indexOf('ПОДОБРАТЬ ПО СПЕНДУ') !== -1) {
			var target = document.getElementById('iac-final-cta');
			if (target) {
				event.preventDefault();
				event.stopPropagation();
				if (event.stopImmediatePropagation) event.stopImmediatePropagation();
				target.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}
			return;
		}
		var section = control.closest('section');
		if (section && normalize(section.textContent).indexOf('ПОЧЕМУ МЫ') !== -1) {
			[80, 250, 650].forEach(function (delay) {
				window.setTimeout(function () {
					if (window.iacHomeI18nApply) window.iacHomeI18nApply();
					patchManifesto();
				}, delay);
			});
		}
	}, true);

	function boot() {
		cleanupPluginPreloader();
		var loaderHandled = false;

		function onLoaderDone() {
			if (loaderHandled) return;
			loaderHandled = true;
			showChrome();
			revealFooterLinks();
			// React hydrates the full mirrored document. Let its post-loader work
			// settle before changing any React-owned text nodes.
			window.setTimeout(queueRussianPatch, 5000);
		}

		if (!document.querySelector('[data-loader-phase][role="status"]')) {
			onLoaderDone();
			return;
		}

		var loaderCheck = window.setInterval(function () {
			if (!document.querySelector('[data-loader-phase][role="status"]')) {
				window.clearInterval(loaderCheck);
				onLoaderDone();
			}
		}, 250);

		window.setTimeout(function () {
			window.clearInterval(loaderCheck);
			onLoaderDone();
		}, 15000);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})(window, document);
