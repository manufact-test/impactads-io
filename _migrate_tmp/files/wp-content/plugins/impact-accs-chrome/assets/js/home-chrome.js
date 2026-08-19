(function () {
	'use strict';

	var footerHtml = typeof window.__iahChromeFooter === 'string' ? window.__iahChromeFooter : '';
	var patchCount = 0;
	var maxPatches = 24;

	function loaderActive() {
		return !!document.querySelector('[data-loader-phase][role="status"]');
	}

	function hydrationReady() {
		return Date.now() - waitForReady.startedAt >= 10000;
	}

	function data() {
		return typeof iacData !== 'undefined' ? iacData : {};
	}

	function norm(path) {
		if (!path) {
			return '';
		}
		try {
			if (path.indexOf('http') === 0) {
				var u = new URL(path);
				path = u.pathname + u.search;
			}
		} catch (e) {}
		return path.replace(/\/+$/, '') || '/';
	}

	function readLang() {
		try {
			var m = document.cookie.match(/(?:^|;\s*)iac_lang=(en|ru)/);
			if (m) {
				return m[1];
			}
			var stored = localStorage.getItem('iac-lang');
			if (stored === 'ru' || stored === 'en') {
				return stored;
			}
		} catch (e) {}
		return 'en';
	}

	function langSwitchHtml() {
		var lang = readLang();
		function btn(code) {
			var active = lang === code;
			return (
				'<button type="button" class="iac-lang-switch__btn' +
				(active ? ' iac-lang-switch__btn--active' : '') +
				'" data-lang="' +
				code +
				'" aria-pressed="' +
				(active ? 'true' : 'false') +
				'">' +
				code.toUpperCase() +
				'</button>'
			);
		}
		return (
			'<div class="iac-lang-switch iac-lang-switch--pill notranslate" role="group" aria-label="Language">' +
			btn('en') +
			'<span class="iac-lang-switch__sep" aria-hidden="true">/</span>' +
			btn('ru') +
			'</div>'
		);
	}

	function isHomeMirrorPage() {
		if (document.documentElement.classList.contains('iah-home')) {
			return true;
		}
		var d = data();
		if (d.isFront === '1' || d.isHome === '1') {
			return true;
		}
		try {
			var path = window.location.pathname.replace(/\/+$/, '') || '/';
			var homePath = (d.homeUrl || '/').replace(/^https?:\/\/[^/]+/, '').replace(/\/+$/, '') || '/';
			return path === '/' || path === homePath;
		} catch (e) {}
		return false;
	}

	function ensureHomeLangSwitch() {
		if (!isHomeMirrorPage()) {
			return;
		}

		var desktop = document.getElementById('iac-home-lang-desktop');
		if (!desktop) {
			desktop = document.createElement('div');
			desktop.id = 'iac-home-lang-desktop';
			desktop.className = 'iac-home-lang-desktop iac-home-lang-desktop--overlay pointer-events-auto notranslate';
			document.body.appendChild(desktop);
		}
		desktop.innerHTML = langSwitchHtml();

		document.querySelectorAll('#iac-home-lang-mobile, .iac-home-lang-mobile').forEach(function (node) {
			if (node.parentNode) {
				node.parentNode.removeChild(node);
			}
		});
	}

	function isHomeMobileMenuOpen() {
		var mainMenu = findHomeMobileMainMenu();
		if (mainMenu) {
			var overlay = mainMenu.closest('.fixed.inset-0');
			if (overlay) {
				var display = overlay.style.display || window.getComputedStyle(overlay).display;
				if (display && display !== 'none') {
					return true;
				}
			}
		}
		var btn = document.querySelector('header button.font-misc.fixed, header .lg\\:hidden button');
		if (!btn) {
			return false;
		}
		return /close|закры/i.test((btn.textContent || '').trim());
	}

	function findHomeMobileMainMenu() {
		return document.querySelector('[data-main-menu]');
	}

	function findHomeMobileLegal(mainMenu) {
		if (!mainMenu) {
			return null;
		}
		var nodes = mainMenu.querySelectorAll('[data-scramble]');
		for (var i = 0; i < nodes.length; i++) {
			var text = (nodes[i].getAttribute('data-scramble') || nodes[i].textContent || '').toLowerCase();
			if (text.indexOf('impact') !== -1 || text.indexOf('rights reserved') !== -1) {
				var block = nodes[i].closest('div');
				while (block && block.parentElement !== mainMenu) {
					block = block.parentElement;
				}
				if (block && block.parentElement === mainMenu) {
					return block;
				}
			}
		}
		return mainMenu.querySelector('div.mt-10') || mainMenu.querySelector('div.contain-layout');
	}

	function ensureHomeMobileMenuLang() {
		if (!isHomeMirrorPage()) {
			return;
		}
		if (window.matchMedia('(min-width: 1024px)').matches) {
			return;
		}

		var host = document.getElementById('iah-home-mobile-menu-lang');
		if (!host) {
			host = document.createElement('div');
			host.id = 'iah-home-mobile-menu-lang';
			host.className = 'iah-home-mobile-menu__lang notranslate';
			document.body.appendChild(host);
		}

		var open = isHomeMobileMenuOpen();
		var mainMenu = findHomeMobileMainMenu();
		if (!open || !mainMenu) {
			host.style.display = 'none';
			host.hidden = true;
			return;
		}

		var anchor = mainMenu.querySelector('[data-main-links]') || findHomeMobileLegal(mainMenu);
		if (!anchor) {
			host.style.display = 'none';
			host.hidden = true;
			return;
		}

		var rect = anchor.getBoundingClientRect();
		host.hidden = false;
		host.style.display = 'flex';
		host.style.position = 'fixed';
		host.style.left = '50%';
		host.style.transform = 'translateX(-50%)';
		host.style.top = Math.round((rect.bottom || rect.top) + 20) + 'px';
		host.style.zIndex = '100000';
		host.style.justifyContent = 'center';
		host.style.alignItems = 'center';
		host.style.width = '100%';
		host.style.pointerEvents = 'auto';
		host.style.opacity = '1';
		host.style.visibility = 'visible';
		host.innerHTML = langSwitchHtml();

		if (window.iacHeaderTools && typeof window.iacHeaderTools.initLangSwitch === 'function') {
			window.iacHeaderTools.initLangSwitch();
		}
	}

	function bindHomeMobileMenuLang() {
		if (!isHomeMirrorPage() || window.__iahMobileMenuLangBound) {
			return;
		}
		window.__iahMobileMenuLangBound = true;

		function watchMainMenu() {
			var mainMenu = findHomeMobileMainMenu();
			if (!mainMenu) {
				window.setTimeout(watchMainMenu, 400);
				return;
			}
			new MutationObserver(function () {
				if (isHomeMobileMenuOpen()) {
					ensureHomeMobileMenuLang();
				}
			}).observe(mainMenu, { childList: true, subtree: true });
		}
		watchMainMenu();

		function burstInject() {
			var step = 0;
			function tick() {
				ensureHomeMobileMenuLang();
				step += 1;
				if (step < 48) {
					window.setTimeout(tick, 50);
				}
			}
			tick();
		}

		document.addEventListener(
			'click',
			function (event) {
				if (window.matchMedia('(min-width: 1024px)').matches) {
					return;
				}
				var btn = event.target.closest('header button.font-misc.fixed, header .lg\\:hidden button');
				if (btn) {
					burstInject();
				}
			},
			true
		);

		window.addEventListener('resize', ensureHomeMobileMenuLang);
		window.setInterval(function () {
			if (isHomeMobileMenuOpen()) {
				ensureHomeMobileMenuLang();
			}
		}, 200);
	}

	function patchHeaderLinks() {
		var d = data();
		var home = (d.homeUrl || '/').replace(/\/?$/, '');
		var routes = {
			'/about': d.aboutUrl || home + '/about/',
			'/blog': d.blogUrl || home + '/blog/',
			'/contact': d.contactUrl || home + '/contact/',
			'/application': d.applicationUrl || home + '/application/',
			'/accounts/agency-accounts': (d.featureUrls && d.featureUrls.agency) || home + '/accounts/agency-accounts/',
			'/accounts/platform-access': (d.featureUrls && d.featureUrls.platform) || home + '/accounts/platform-access/',
			'/accounts/team-supply': (d.featureUrls && d.featureUrls.team) || home + '/accounts/team-supply/',
			'/features/autonomous-alerts': (d.featureUrls && d.featureUrls.agency) || home + '/accounts/agency-accounts/',
			'/features/conversational-debugging': (d.featureUrls && d.featureUrls.platform) || home + '/accounts/platform-access/',
			'/features/coding-agents': (d.featureUrls && d.featureUrls.team) || home + '/accounts/team-supply/',
			'/features/coding-agents-welcome': (d.featureUrls && d.featureUrls.team) || home + '/accounts/team-supply/',
		};

		document.querySelectorAll('header a[href]').forEach(function (a) {
			var href = a.getAttribute('href') || '';
			var key = norm(href.split('#')[0].split('?')[0]);
			if (routes[key]) {
				a.setAttribute('href', routes[key]);
			}
			if (href.indexOf('?contact=true') !== -1 && d.contactUrl) {
				a.setAttribute('href', d.contactUrl);
			}
			if (href.indexOf('?waitlist=true') !== -1 && d.applicationUrl) {
				a.setAttribute('href', d.applicationUrl);
			}
			if (href.indexOf('/features/autonomous-alerts') !== -1 && d.featureUrls && d.featureUrls.agency) {
				a.setAttribute('href', d.featureUrls.agency);
			}
			if (href.indexOf('/features/conversational-debugging') !== -1 && d.featureUrls && d.featureUrls.platform) {
				a.setAttribute('href', d.featureUrls.platform);
			}
			if (href.indexOf('/features/coding-agents') !== -1 && d.featureUrls && d.featureUrls.team) {
				a.setAttribute('href', d.featureUrls.team);
			}
		});
	}

	function getExactMap() {
		var d = data();
		if (readLang() !== 'ru' || !d.exactReplacements || typeof d.exactReplacements !== 'object') {
			return null;
		}
		return d.exactReplacements;
	}

	function patchHeaderI18n() {
		if (readLang() !== 'ru' || !hydrationReady()) {
			return;
		}
		var map = getExactMap();
		var header = document.querySelector('header');
		if (!map || !header) {
			return;
		}

		header.querySelectorAll('a, button, span, [data-scramble]').forEach(function (el) {
			if (el.closest('.iac-lang-switch, .notranslate, #iac-home-lang-desktop, #iah-home-mobile-menu-lang')) {
				return;
			}
			var trimmed = (el.textContent || '').trim();
			if (!map[trimmed]) {
				return;
			}
			var svg = el.querySelector('svg');
			if (svg) {
				el.textContent = map[trimmed];
				el.appendChild(svg);
			} else {
				el.textContent = map[trimmed];
			}
		});

		header.querySelectorAll('[aria-label]').forEach(function (el) {
			if (el.closest('.iac-lang-switch')) {
				return;
			}
			var value = el.getAttribute('aria-label');
			if (value && map[value]) {
				el.setAttribute('aria-label', map[value]);
			}
		});
	}

	function ensureLangSwitchVisible() {
		if (!isHomeMirrorPage() || loaderActive()) {
			return;
		}
		ensureHomeLangSwitch();
		if (window.iacHeaderTools && typeof window.iacHeaderTools.initLangSwitch === 'function') {
			window.iacHeaderTools.initLangSwitch();
		}
	}

	function bindHeaderI18nObserver() {
		if (window.__iahHeaderI18nObs || readLang() !== 'ru') {
			return;
		}
		var header = document.querySelector('header');
		if (!header) {
			window.setTimeout(bindHeaderI18nObserver, 500);
			return;
		}
		window.__iahHeaderI18nObs = true;
		new MutationObserver(function () {
			if (hydrationReady()) {
				patchHeaderI18n();
			}
		}).observe(header, { childList: true, subtree: true, characterData: true });
	}

	function revealFooterLinks(root) {
		if (!root) {
			return;
		}
		root.querySelectorAll('.iac-footer-label').forEach(function (el) {
			el.classList.remove('opacity-0');
		});
	}

	function mountChromeFooter() {
		if (!footerHtml || document.getElementById('iah-chrome-footer-root')) {
			return;
		}
		var host = document.createElement('div');
		host.id = 'iah-chrome-footer-root';
		host.setAttribute('data-iah-chrome', '1');
		host.innerHTML = footerHtml;
		document.body.appendChild(host);
		revealFooterLinks(host);

		if (typeof window.iacInitFooterInteractive === 'function') {
			var chromeFooter = host.querySelector('footer');
			if (chromeFooter) {
				window.iacInitFooterInteractive(chromeFooter, host);
			}
		}

		document.querySelectorAll('footer:not([data-iah-chrome="1"])').forEach(function (f) {
			if (!f.closest('#iah-chrome-footer-root')) {
				f.style.display = 'none';
				f.setAttribute('aria-hidden', 'true');
			}
		});
	}

	function patchFooterLinks() {
		var d = data();
		var home = (d.homeUrl || '/').replace(/\/?$/, '');
		var root = document.getElementById('iah-chrome-footer-root') || document.querySelector('footer[data-iah-chrome="1"]') || document.querySelector('footer');
		if (!root) {
			return;
		}
		var routes = {
			'/blog': d.blogUrl || home + '/blog/',
			'/about': d.aboutUrl || home + '/about/',
			'/': d.homeUrl || home + '/',
			'/features/autonomous-alerts': (d.featureUrls && d.featureUrls.agency) || home + '/accounts/agency-accounts/',
			'/features/conversational-debugging': (d.featureUrls && d.featureUrls.platform) || home + '/accounts/platform-access/',
			'/features/coding-agents': (d.featureUrls && d.featureUrls.team) || home + '/accounts/team-supply/',
		};
		root.querySelectorAll('a[href]').forEach(function (a) {
			var href = a.getAttribute('href') || '';
			var key = norm(href.split('#')[0].split('?')[0]);
			if (routes[key]) {
				a.setAttribute('href', routes[key]);
			}
			if (href.indexOf('?contact=true') !== -1 && d.contactUrl) {
				a.setAttribute('href', d.contactUrl);
			}
		});
		root.querySelectorAll('span').forEach(function (s) {
			if ((s.textContent || '').trim() === 'Features') {
				s.textContent = 'ACCOUNTS';
			}
		});
		revealFooterLinks(root);
	}

	function runPatch() {
		if (patchCount >= maxPatches) {
			return;
		}
		if (!hydrationReady() && Date.now() - waitForReady.startedAt < 20000) {
			return;
		}
		if (loaderActive() && Date.now() - waitForReady.startedAt < 25000) {
			return;
		}
		patchCount += 1;
		patchHeaderLinks();
		patchHeaderI18n();
		bindHeaderI18nObserver();
		ensureLangSwitchVisible();
		ensureHomeMobileMenuLang();
		mountChromeFooter();
		patchFooterLinks();
		if (window.iacHeaderTools) {
			window.iacHeaderTools.initLangSwitch();
		}
		if (typeof window.iacHomeI18nApply === 'function') {
			window.iacHomeI18nApply();
		}
	}

	function waitForReady() {
		if (!hydrationReady() && Date.now() - waitForReady.startedAt < 20000) {
			window.setTimeout(waitForReady, 300);
			return;
		}
		if (loaderActive() && Date.now() - waitForReady.startedAt < 25000) {
			window.setTimeout(waitForReady, 200);
			return;
		}
		runPatch();
		window.setTimeout(runPatch, 1500);
		window.setTimeout(runPatch, 4000);
		window.setTimeout(runPatch, 8000);
		window.setInterval(function () {
			ensureLangSwitchVisible();
			if (!document.getElementById('iah-chrome-footer-root') && footerHtml) {
				mountChromeFooter();
				patchFooterLinks();
			}
			if (hydrationReady() && readLang() === 'ru') {
				patchHeaderI18n();
			}
			ensureHomeMobileMenuLang();
		}, 2000);
	}

	window.setInterval(ensureLangSwitchVisible, 400);

	waitForReady.startedAt = Date.now();
	bindHomeMobileMenuLang();

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', waitForReady);
	} else {
		waitForReady();
	}
})();
