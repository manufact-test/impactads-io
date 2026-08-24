(function () {
	'use strict';

	function isHomePage() {
		if (document.documentElement.classList.contains('iah-home')) {
			return true;
		}
		if (typeof iacData !== 'undefined' && (iacData.isHome === '1' || iacData.isFront === '1')) {
			document.documentElement.classList.add('iah-home');
			return true;
		}
		return false;
	}

	var onHome = isHomePage();
	var isMobile = false;
	try {
		isMobile = window.matchMedia('(max-width: 1023px)').matches;
	} catch (e) {}

	function scheduleMorphPatch() {
		patchMorphTitles();
	}

	function startMorphWatcher() {
		if (isMobile) {
			var morphRuns = 0;
			var morphTimer = window.setInterval(function () {
				scheduleMorphPatch();
				morphRuns += 1;
				if (morphRuns >= 8) {
					window.clearInterval(morphTimer);
				}
			}, 3500);
			return;
		}
		window.setInterval(scheduleMorphPatch, 2000);
	}

	function readLang() {
		try {
			var match = document.cookie.match(/(?:^|;\s*)iac_lang4=(en|ru)/);
			if (!match) {
				match = document.cookie.match(/(?:^|;\s*)iac_lang3=(en|ru)/);
			}
			if (!match) {
				match = document.cookie.match(/(?:^|;\s*)iac_lang=(en|ru)/);
			}
			if (match) {
				return match[1];
			}
			var stored = localStorage.getItem('iac-lang');
			if (stored === 'ru' || stored === 'en') {
				return stored;
			}
		} catch (e) {}
		if (typeof iacData !== 'undefined' && (iacData.lang === 'ru' || iacData.lang === 'en')) {
			return iacData.lang;
		}
		return 'en';
	}

	if (readLang() !== 'ru') {
		return;
	}
	if (typeof iacData === 'undefined' || !iacData.htmlReplacements || typeof iacData.htmlReplacements !== 'object') {
		return;
	}

	var HOME_POST_LOADER_MS = onHome ? 5000 : 500;
	var HOME_HEADER_MS = onHome ? 5000 : 500;
	var pairsCache = null;
	var mapSnapshot = null;
	var exactSnapshot = null;
	var loaderDoneAt = 0;
	var observerStarted = false;
	var bodyObserver = null;
	var lastBodyRunAt = 0;
	var homeBootedAt = Date.now();

	if (iacData.htmlReplacements && typeof iacData.htmlReplacements === 'object') {
		mapSnapshot = iacData.htmlReplacements;
	}
	if (iacData.exactReplacements && typeof iacData.exactReplacements === 'object') {
		exactSnapshot = iacData.exactReplacements;
	}

	function loaderActive() {
		var loader = document.querySelector('[data-loader-phase][role="status"]');
		if (!loader) {
			return false;
		}
		try {
			var style = window.getComputedStyle(loader);
			var rect = loader.getBoundingClientRect();
			return style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0' && rect.width > 0 && rect.height > 0;
		} catch (e) {
			return true;
		}
	}

	function noteLoaderDone() {
		if (!loaderActive() && !loaderDoneAt) {
			loaderDoneAt = Date.now();
		}
	}

	function msSinceLoaderDone() {
		noteLoaderDone();
		if (!loaderDoneAt) {
			return 0;
		}
		return Date.now() - loaderDoneAt;
	}

	function hydrationReady() {
		// A hidden loader node can remain mounted indefinitely. Twenty seconds is
		// also a conservative hard ceiling for the exported React app to settle.
		if (loaderActive() && Date.now() - homeBootedAt < 20000) {
			return false;
		}
		noteLoaderDone();
		return msSinceLoaderDone() >= HOME_POST_LOADER_MS;
	}

	function headerReady() {
		if (loaderActive()) {
			return false;
		}
		noteLoaderDone();
		return msSinceLoaderDone() >= HOME_HEADER_MS;
	}

	function isMobileMenuControl(el) {
		if (!el || !el.closest) {
			return false;
		}
		return !!el.closest('header .lg\\:hidden button, header button.font-misc.fixed');
	}

	function isReactMobileMenu(el) {
		if (!el || !el.closest) {
			return false;
		}
		return !!el.closest('[data-main-menu], [data-main-links]');
	}

	function isDesktopHeaderNav(el) {
		if (!el || !el.closest) {
			return false;
		}
		return !!el.closest('header .lg\\:flex, header [class*="lg:flex"]');
	}

	function isDesktopNavDropdown(el) {
		if (!el || !el.closest) {
			return false;
		}
		return !!el.closest('[role="menu"]');
	}

	function normalizeText(value) {
		return (value || '')
			.replace(/\u2019/g, "'")
			.replace(/\u2018/g, "'")
			.replace(/\u00a0/g, ' ')
			.trim();
	}

	function lookupMap(map, key) {
		if (!map || !key) {
			return null;
		}
		if (map[key]) {
			return map[key];
		}
		var norm = normalizeText(key);
		if (map[norm]) {
			return map[norm];
		}
		return null;
	}

	function isSafeHomeKey(key) {
		if (!key || key.length < 4) {
			return false;
		}
		if (key.indexOf(' ') !== -1) {
			return key.length >= 8;
		}
		if (key.length >= 14) {
			return true;
		}
		if (/^[A-Z][A-Z0-9&.\/-]+$/.test(key) && key.length >= 8) {
			return true;
		}
		return false;
	}

	function getPairs() {
		var map = mapSnapshot;
		if (!map && typeof iacData !== 'undefined' && iacData.htmlReplacements) {
			map = iacData.htmlReplacements;
			if (map && typeof map === 'object') {
				mapSnapshot = map;
			}
		}
		if (!map || typeof map !== 'object') {
			return [];
		}
		if (pairsCache) {
			return pairsCache;
		}
		pairsCache = Object.keys(map)
			.filter(function (key) {
				var exact = getExactMap();
				return isSafeHomeKey(key) || (exact && exact[key]);
			})
			.sort(function (a, b) {
				return b.length - a.length;
			})
			.map(function (key) {
				return [key, map[key]];
			});
		return pairsCache;
	}

	function replaceInString(value, list) {
		var next = value;
		for (var i = 0; i < list.length; i++) {
			var key = list[i][0];
			var val = list[i][1];
			if (next.indexOf(key) === -1 && next.indexOf(normalizeText(key)) === -1) {
				continue;
			}
			if (normalizeText(next) === normalizeText(key)) {
				next = val;
				continue;
			}
			var escaped = key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
			next = next.replace(new RegExp('(^|[^A-Za-z\u0400-\u04FF])' + escaped + '(?![A-Za-z\u0400-\u04FF])', 'g'), function (match, before) {
				return (before || '') + val;
			});
		}
		return next;
	}

	function shouldSkip(el) {
		if (!el || !el.closest) {
			return true;
		}
		if (onHome) {
			if (isMobileMenuControl(el) || isReactMobileMenu(el)) {
				return true;
			}
			if (isDesktopHeaderNav(el) || isDesktopNavDropdown(el)) {
				return true;
			}
			return !!el.closest(
				'.iac-lang-switch, .notranslate, script, style, [data-iac-no-i18n], #iah-chrome-footer-root, #iac-home-lang-desktop, #iah-home-mobile-menu-lang, #iac-mobile-menu, [data-section="home-hero"], [data-scramble], [data-iac-i18n-done], canvas, [data-loader-phase], svg'
			);
		}
		return !!el.closest(
			'header, .iac-lang-switch, .notranslate, script, style, [data-iac-no-i18n], #iah-chrome-footer-root, #iac-mobile-menu, [data-scramble], [data-iac-i18n-done], canvas, [data-loader-phase]'
		);
	}

	function getExactMap() {
		if (exactSnapshot) {
			return exactSnapshot;
		}
		if (typeof iacData !== 'undefined' && iacData.exactReplacements && typeof iacData.exactReplacements === 'object') {
			exactSnapshot = iacData.exactReplacements;
			return exactSnapshot;
		}
		return null;
	}

	function setElementText(el, text) {
		var svg = el.querySelector('svg');
		if (svg) {
			el.textContent = text;
			el.appendChild(svg);
		} else {
			el.textContent = text;
		}
	}

	function patchTextNodes(el, map) {
		if (!el || !map) {
			return;
		}
		for (var i = 0; i < el.childNodes.length; i++) {
			var node = el.childNodes[i];
			if (node.nodeType !== 3) {
				continue;
			}
			var trimmed = (node.nodeValue || '').trim();
			var translated = lookupMap(map, trimmed);
			if (translated) {
				node.nodeValue = node.nodeValue.replace(trimmed, translated);
			}
		}
	}

	function applyExactSafe(root) {
		var map = getExactMap();
		if (!map || !root) {
			return 0;
		}

		var changed = 0;
		function patchEl(el) {
			if (!el || (el.closest && el.closest('[data-scramble]'))) {
				return;
			}
			var trimmed = (el.textContent || '').trim();
			var translated = lookupMap(map, trimmed);
			if (!trimmed || !translated) {
				return;
			}
			if (el.querySelector('svg')) {
				patchTextNodes(el, map);
			} else if (el.childNodes.length === 1 && el.childNodes[0].nodeType === 3) {
				el.childNodes[0].nodeValue = translated;
			} else {
				patchTextNodes(el, map);
			}
			changed += 1;
		}

		if (root.nodeType === 1 && root.matches && root.matches('a, button, span, p, li, label')) {
			patchEl(root);
		}
		root.querySelectorAll('a, button, span, p, li, label').forEach(patchEl);

		return changed;
	}

	function findHomeMenuButton() {
		return document.querySelector('header button.font-misc.fixed, header .lg\\:hidden button');
	}

	function patchMenuButtonLabel() {
		if (!onHome || readLang() !== 'ru') {
			return;
		}
		var map = getExactMap();
		var btn = findHomeMenuButton();
		if (!map || !btn) {
			return;
		}
		patchTextNodes(btn, map);
	}

	function applyExact(root) {
		var map = getExactMap();
		if (!map || !root) {
			return 0;
		}

		var changed = 0;
		var selector =
			'h1,h2,h3,h4,h5,h6,a,button,span,p,li,label,.text-title,.text-paragraph,.font-misc,strong,small,[data-slot=button]';
		root.querySelectorAll(selector).forEach(function (el) {
			if (shouldSkip(el)) {
				return;
			}
			var trimmed = (el.textContent || '').trim();
			var translated = lookupMap(map, trimmed);
			if (!trimmed || !translated) {
				return;
			}
			setElementText(el, translated);
			el.setAttribute('data-iac-i18n-done', '1');
			changed += 1;
		});

		root.querySelectorAll('[aria-label],[title],[placeholder],[alt]').forEach(function (el) {
			if (shouldSkip(el)) {
				return;
			}
			['aria-label', 'title', 'placeholder', 'alt'].forEach(function (attr) {
				var value = el.getAttribute(attr);
				var translated = value ? lookupMap(map, value) : null;
				if (translated) {
					el.setAttribute(attr, translated);
					changed += 1;
				}
			});
		});

		return changed;
	}

	function apply(root) {
		var list = getPairs();
		if (!list.length || !root) {
			return 0;
		}

		var changed = 0;
		var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
		var node;
		while ((node = walker.nextNode())) {
			if (shouldSkip(node.parentElement)) {
				continue;
			}
			var text = node.nodeValue;
			if (!text || !/[A-Za-z]/.test(text)) {
				continue;
			}
			var next = replaceInString(text, list);
			if (next !== text) {
				node.nodeValue = next;
				changed += 1;
			}
		}

		return changed;
	}

	function patchOpenMobileMenu() {
		if (!onHome || readLang() !== 'ru') {
			return;
		}
		var menu = document.querySelector('[data-main-menu]');
		if (!menu) {
			patchMenuButtonLabel();
			return;
		}
		patchMenuButtonLabel();
		applyExactSafe(menu.querySelector('[data-main-links]') || menu);
		var subPanel = menu.querySelector('[data-sub-panel]');
		if (subPanel) {
			applyExactSafe(subPanel);
		}
		menu.querySelectorAll('button').forEach(function (btn) {
			if (btn.closest('[data-scramble]')) {
				return;
			}
			applyExactSafe(btn);
		});
	}

	function watchHomeMobileMenu() {
		if (!onHome) {
			return;
		}
		function attach() {
			var menu = document.querySelector('[data-main-menu]');
			if (!menu) {
				window.setTimeout(attach, 400);
				return;
			}
			var pending = false;
			new MutationObserver(function () {
				if (pending) {
					return;
				}
				pending = true;
				window.requestAnimationFrame(function () {
					pending = false;
					patchOpenMobileMenu();
				});
			}).observe(menu, { childList: true, subtree: true, characterData: !isMobile });
		}
		attach();
	}

	function watchMenuButton() {
		if (!onHome) {
			return;
		}
		function attach() {
			var btn = findHomeMenuButton();
			if (!btn) {
				window.setTimeout(attach, 400);
				return;
			}
			new MutationObserver(function () {
				patchMenuButtonLabel();
			}).observe(btn, { childList: true, subtree: true, characterData: !isMobile });
			patchMenuButtonLabel();
		}
		attach();
	}

	function getDesktopHeaderRoot() {
		return document.querySelector('header .lg\\:flex') || document.querySelector('header [class*="lg:flex"]');
	}

	function patchDesktopHeaderNav() {
		if (!onHome || readLang() !== 'ru') {
			return;
		}
		var root = getDesktopHeaderRoot();
		if (root) {
			applyExactSafe(root);
		}
		var menu = document.querySelector('[role="menu"]');
		if (menu) {
			applyExactSafe(menu);
		}
	}

	function patchHeaderUtilities() {
		if (!onHome || readLang() !== 'ru') {
			return;
		}
		var header = document.querySelector('header');
		if (header) {
			var sound = header.querySelector('button[aria-label]');
			if (sound) {
				applyExactSafe(sound);
				var current = sound.getAttribute('aria-label');
				var translated = current ? lookupMap(getExactMap(), current) : null;
				if (translated) sound.setAttribute('aria-label', translated);
			}
		}
		document.querySelectorAll('.iac-lang-switch[aria-label]').forEach(function (switcher) {
			switcher.setAttribute('aria-label', 'Язык');
		});
	}

	function patchMorphTitles() {
		if (!onHome || readLang() !== 'ru') {
			return;
		}
		var exactMap = getExactMap();
		var map = mapSnapshot || (typeof iacData !== 'undefined' ? iacData.htmlReplacements : null);
		if (!map && !exactMap) {
			return;
		}
		var keys = [
			'Resource over noise',
			'Account sellers have accumulated noise over the years. None of it makes launches faster. If access is infrastructure, the best interface is direct contact — not screenshots, not emojis, not random chats.',
			'Working resource',
			'Random Telegram sellers used to be the norm. Structured supply is the strength — clear request, fast contact, working access under terms your team can trust.',
			'Chaos is optional',
			'Random sellers are broken. Unstable supply and vague terms. The future is structured access — clear request, fast contact, working resource.'
		];
		var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null);
		var node;
		while ((node = walker.nextNode())) {
			var parent = node.parentElement;
			if (!parent) {
				continue;
			}
			if (parent.closest('header, [data-main-menu], canvas, [data-scramble], [data-section="home-hero"]')) {
				continue;
			}
			var trimmed = normalizeText(node.nodeValue);
			if (!trimmed) {
				continue;
			}
			for (var i = 0; i < keys.length; i++) {
				var ru = lookupMap(exactMap, keys[i]) || lookupMap(map, keys[i]);
				if (ru && trimmed === normalizeText(keys[i])) {
					node.nodeValue = ru;
					break;
				}
			}
		}
	}

	function patchLocalizedAssets() {
		if (!onHome || readLang() !== 'ru') {
			return;
		}

		var assetBase = typeof iacData !== 'undefined' && iacData.assetBase ? iacData.assetBase : '/wp-content/plugins/impact-accs-homepage/assets/site/';
		assetBase = assetBase.replace(/\/?$/, '/');
		var heroImage = document.querySelector('img[src*="homepage-hero.66784594.webp"]');
		if (heroImage) {
			heroImage.removeAttribute('srcset');
			heroImage.removeAttribute('srcSet');
			heroImage.setAttribute('alt', 'Панель подбора аккаунтов impact.');
			var ruImage = assetBase + '_next/static/media/homepage-hero-ru.webp';
			if (heroImage.getAttribute('src') !== ruImage) {
				heroImage.setAttribute('src', ruImage);
			}
		}

		document.querySelectorAll('a[href="https://t.me/impactaccs"], a[href="https://wa.me/"]').forEach(function (link) {
			link.setAttribute('href', 'https://t.me/founderads');
		});
	}

	function runHeaderOnly() {
		if (!onHome || !headerReady()) {
			return;
		}
		patchDesktopHeaderNav();
		patchHeaderUtilities();
	}

	function run() {
		if (onHome) {
			runHeaderOnly();
			if (!hydrationReady()) {
				return;
			}
		} else if (!hydrationReady()) {
			return;
		}
		var root = document.body || document.documentElement;
		patchDesktopHeaderNav();
		patchHeaderUtilities();
		applyExact(root);
		apply(root);
		patchLocalizedAssets();
		patchOpenMobileMenu();
		patchMenuButtonLabel();
		patchMorphTitles();
	}

	window.iacHomeI18nApply = run;
	window.iacHomeI18nPatchMobileMenu = patchOpenMobileMenu;

	function startObserver() {
		// The React/WebGPU homepage owns its live DOM. A body-wide observer can
		// race React state transitions and blank the scene after scrolling.
		if (onHome || observerStarted || typeof MutationObserver === 'undefined' || !document.body) {
			return;
		}
		observerStarted = true;

		var pending = false;
		bodyObserver = new MutationObserver(function (mutations) {
			for (var i = 0; i < mutations.length; i++) {
				var target = mutations[i].target;
				if (!target || !target.closest) {
					continue;
				}
				if (target.closest('canvas, [data-scramble], [data-loader-phase], #iac-mobile-menu, [data-main-menu], header, [role="menu"], svg')) {
					return;
				}
			}
			if (pending) {
				return;
			}
			if (isMobile) {
				var now = Date.now();
				if (now - lastBodyRunAt < 900) {
					return;
				}
				lastBodyRunAt = now;
			}
			pending = true;
			window.requestAnimationFrame(function () {
				pending = false;
				if (onHome && !hydrationReady()) {
					runHeaderOnly();
					return;
				}
				if (!hydrationReady()) {
					return;
				}
				run();
			});
		});
		bodyObserver.observe(document.body, {
			childList: true,
			subtree: true,
			characterData: !isMobile,
		});
		if (isMobile && bodyObserver) {
			window.setTimeout(function () {
				if (bodyObserver) {
					bodyObserver.disconnect();
					bodyObserver = null;
				}
			}, 28000);
		}
	}

	function bootHome() {
		function tick() {
			noteLoaderDone();
			runHeaderOnly();
			if (!hydrationReady()) {
				window.setTimeout(tick, 150);
				return;
			}
			run();
			window.setTimeout(run, 400);
			window.setTimeout(run, 1200);
			window.setTimeout(run, 3000);
			window.setTimeout(run, 5000);
			window.setTimeout(run, 8000);
			window.setTimeout(run, 12000);
			window.setTimeout(startObserver, isMobile ? 2500 : 1500);
			watchHomeMobileMenu();
			watchMenuButton();
			startMorphWatcher();
		}
		tick();
	}

	function bootOther() {
		function waitForContent() {
			if (!hydrationReady()) {
				window.setTimeout(waitForContent, 200);
				return;
			}
			run();
			startObserver();
			window.setTimeout(run, 1200);
			window.setTimeout(run, 3000);
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', waitForContent);
		} else {
			waitForContent();
		}

		window.addEventListener('load', function () {
			window.setTimeout(run, 800);
		});
	}

	if (onHome) {
		bootHome();
	} else {
		bootOther();
	}
})();
