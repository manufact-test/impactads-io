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

	if (!isHomePage()) {
		return;
	}

	function readLang() {
		try {
			var match = document.cookie.match(/(?:^|;\s*)iac_lang=(en|ru)/);
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

	function loaderActive() {
		return !!document.querySelector('[data-loader-phase][role="status"]');
	}

	var pairsCache = null;
	var mapSnapshot = null;
	var exactSnapshot = null;
	var bootedAt = Date.now();

	if (typeof iacData !== 'undefined' && iacData.htmlReplacements && typeof iacData.htmlReplacements === 'object') {
		mapSnapshot = iacData.htmlReplacements;
	}
	if (typeof iacData !== 'undefined' && iacData.exactReplacements && typeof iacData.exactReplacements === 'object') {
		exactSnapshot = iacData.exactReplacements;
	}

	function hydrationReady() {
		return Date.now() - bootedAt >= 10000 && !loaderActive();
	}

	function isSafeHomeKey(key) {
		if (!key || key.length < 4) {
			return false;
		}
		if (key.indexOf(' ') !== -1) {
			return key.length >= 6;
		}
		if (key.length >= 16) {
			return true;
		}
		if (/^[A-Z][A-Z0-9&]+$/.test(key) && key.length >= 9) {
			return true;
		}
		return false;
	}

	function getPairs() {
		if (readLang() !== 'ru') {
			return [];
		}
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
			.filter(isSafeHomeKey)
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
			if (next.indexOf(key) === -1) {
				continue;
			}
			if (key.indexOf(' ') !== -1 || key.length >= 16) {
				next = next.split(key).join(val);
				continue;
			}
			if (next.trim() === key) {
				next = val;
				continue;
			}
			var escaped = key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
			next = next.replace(new RegExp('(^|\\b)' + escaped + '(\\b|$)', 'g'), function (match, before, after) {
				return (before || '') + val + (after || '');
			});
		}
		return next;
	}

	function shouldSkip(el) {
		return !el || el.closest('header, .iac-lang-switch, .notranslate, script, style, [data-iac-no-i18n], #iah-chrome-footer-root');
	}

	function getExactMap() {
		if (readLang() !== 'ru') {
			return null;
		}
		if (exactSnapshot) {
			return exactSnapshot;
		}
		if (typeof iacData !== 'undefined' && iacData.exactReplacements && typeof iacData.exactReplacements === 'object') {
			exactSnapshot = iacData.exactReplacements;
			return exactSnapshot;
		}
		return null;
	}

	function applyExact(root) {
		var map = getExactMap();
		if (!map || !root) {
			return 0;
		}

		var changed = 0;
		root.querySelectorAll('h1,h2,h3,h4,h5,h6,a,button,span,p,li,label,.text-title').forEach(function (el) {
			if (shouldSkip(el)) {
				return;
			}
			var trimmed = (el.textContent || '').trim();
			if (map[trimmed]) {
				if (el.querySelector('svg')) {
					var svg = el.querySelector('svg');
					el.textContent = map[trimmed];
					if (svg) {
						el.appendChild(svg);
					}
				} else {
					el.textContent = map[trimmed];
				}
				changed += 1;
			}
		});

		root.querySelectorAll('[aria-label],[title],[placeholder]').forEach(function (el) {
			if (shouldSkip(el)) {
				return;
			}
			['aria-label', 'title', 'placeholder'].forEach(function (attr) {
				var value = el.getAttribute(attr);
				if (value && map[value]) {
					el.setAttribute(attr, map[value]);
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

		root.querySelectorAll('[placeholder],[aria-label],[title]').forEach(function (el) {
			if (shouldSkip(el)) {
				return;
			}
			['placeholder', 'aria-label', 'title'].forEach(function (attr) {
				var value = el.getAttribute(attr);
				if (!value || !/[A-Za-z]/.test(value)) {
					return;
				}
				var next = replaceInString(value, list);
				if (next !== value) {
					el.setAttribute(attr, next);
					changed += 1;
				}
			});
		});

		return changed;
	}

	function run() {
		if (readLang() !== 'ru' || !hydrationReady()) {
			return;
		}
		if (typeof iacData !== 'undefined' && (iacData.isHome === '1' || iacData.isFront === '1')) {
			document.documentElement.classList.add('iah-home');
		}
		var root = document.body || document.documentElement;
		applyExact(root);
		apply(root);
	}

	window.iacHomeI18nApply = run;

	function startObserver() {
		if (typeof MutationObserver === 'undefined' || !document.body) {
			return;
		}
		var pending = false;
		var observer = new MutationObserver(function (mutations) {
			if (!hydrationReady()) {
				return;
			}
			for (var i = 0; i < mutations.length; i++) {
				if (mutations[i].target && mutations[i].target.closest && mutations[i].target.closest('header')) {
					return;
				}
			}
			if (pending) {
				return;
			}
			pending = true;
			window.requestAnimationFrame(function () {
				pending = false;
				run();
			});
		});
		observer.observe(document.body, { childList: true, subtree: true });
	}

	function boot() {
		if (readLang() !== 'ru') {
			return;
		}

		function waitForContent() {
			if (!hydrationReady()) {
				window.setTimeout(waitForContent, 500);
				return;
			}
			run();
			startObserver();
			window.setTimeout(run, 3000);
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', waitForContent);
		} else {
			waitForContent();
		}

		window.addEventListener('load', function () {
			window.setTimeout(run, 12000);
		});
	}

	boot();
})();
