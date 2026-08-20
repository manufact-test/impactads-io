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

	var normalizedExactCache = null;
	function normalizedExactReplacements() {
		if (normalizedExactCache) return normalizedExactCache;
		normalizedExactCache = {};
		var source = window.iacData && window.iacData.exactReplacements ? window.iacData.exactReplacements : {};
		Object.keys(source).forEach(function (key) {
			var normalized = normalize(key);
			if (normalized) normalizedExactCache[normalized] = source[key];
		});
		return normalizedExactCache;
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

	function patchPreloader() {
		var loader = document.querySelector('[data-loader-phase][role="status"]');
		if (!loader) return;

		loader.setAttribute('aria-label', 'Загрузка');
		replaceTextNodes(loader, {
			'Initializing': 'Инициализация',
			'Click anywhere to enable sound': 'Нажмите, чтобы включить звук',
			'Sound muted': 'Звук выключен',
			'Sound enabled': 'Звук включён',
			'Impact System': 'Система Impact'
		});
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

	function patchHeroDialogs() {
		var hero = document.querySelector('[data-section="home-hero"]');
		if (!hero) return;

		// React conversations are rendered after hydration. Apply the same exact
		// TЗ dictionary to their live text nodes instead of rewriting the static
		// hydration bundle (which previously caused React #418 / black 3D).
		replaceTextNodes(hero, normalizedExactReplacements());
		replaceTextNodes(hero, {
			'@impact.accs': '@founderads',
			'impact.accs': 'impact.',
			'5XX': 'ПОД ЗАЛИВ',
			'P99': 'ПОД ОБЪЁМ',
			'Systems': 'МЕДИАБАИНГ'
		});

		hero.querySelectorAll('button').forEach(function (button) {
			var label = normalize(button.textContent);
			if (label === 'Отправить' || label === 'Send') {
				if (nearbyContains(button, 'ПАРАМЕТРЫ АККАУНТА', 9)) {
					replaceTextNodes(button, { 'Отправить': 'ПОДТВЕРДИТЬ', 'Send': 'ПОДТВЕРДИТЬ' });
				} else if (nearbyContains(button, 'ПАРАМЕТРЫ ПОДБОРА', 9)) {
					replaceTextNodes(button, { 'Отправить': 'ЗАРЕЗЕРВИРОВАТЬ', 'Send': 'ЗАРЕЗЕРВИРОВАТЬ' });
				} else if (nearbyContains(button, 'ПОСТАВКА ДЛЯ КОМАНДЫ', 9) || nearbyContains(button, 'ПРЕДЛОЖЕНИЕ ДЛЯ КОМАНДЫ', 9)) {
					replaceTextNodes(button, { 'Отправить': 'ЗАФИКСИРОВАТЬ', 'Send': 'ЗАФИКСИРОВАТЬ' });
				}
			}

			if (label === 'Связаться' || label === 'СВЯЗАТЬСЯ' || label === 'Request access' || label === 'REQUEST ACCESS') {
				if (nearbyContains(button, 'ПОСТАВКА ДЛЯ КОМАНДЫ', 8)) {
					replaceTextNodes(button, {
						'Связаться': 'ОБЪЁМНЫЕ УСЛОВИЯ',
						'СВЯЗАТЬСЯ': 'ОБЪЁМНЫЕ УСЛОВИЯ',
						'Request access': 'ОБЪЁМНЫЕ УСЛОВИЯ',
						'REQUEST ACCESS': 'ОБЪЁМНЫЕ УСЛОВИЯ'
					});
				}
			}
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
		var link = section.querySelector('a[href*="/blog/manifesto"]');
		if (!link) return;

		link.setAttribute('href', applicationUrl());
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
		var section = closestSectionWithText('ПОЛУЧИТЕ АККАУНТ. ПРОВЕРЬТЕ. ПОТОМ ПЛАТИТЕ.');
		if (!section) return;
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
		patchPreloader();
		patchHeaderCta();
		patchHeroDialogs();
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

	function installRussianCopyObserver() {
		if (!isRu() || !document.body) return;
		queueRussianPatch();
		var observer = new MutationObserver(queueRussianPatch);
		observer.observe(document.body, {
			childList: true,
			subtree: true,
			characterData: true
		});
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

	function boot() {
		cleanupPluginPreloader();
		installRussianCopyObserver();

		function onLoaderDone() {
			showChrome();
			revealFooterLinks();
			queueRussianPatch();
		}

		var loader = document.querySelector('[data-loader-phase][role="status"]');
		if (!loader) {
			onLoaderDone();
			return;
		}

		var observer = new MutationObserver(function () {
			if (!document.querySelector('[data-loader-phase][role="status"]')) {
				observer.disconnect();
				onLoaderDone();
			}
		});

		observer.observe(document.body, {
			childList: true,
			subtree: true,
			attributes: true,
			attributeFilter: ['data-loader-phase', 'style', 'class']
		});

		window.setTimeout(onLoaderDone, 15000);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})(window, document);
