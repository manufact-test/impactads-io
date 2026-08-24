(function () {
	'use strict';

	var isMobile = false;
	try {
		isMobile = window.matchMedia('(max-width: 1023px)').matches;
	} catch (e) {}

	var footerHtml = typeof window.__iahChromeFooter === 'string' ? window.__iahChromeFooter : '';
	var patchCount = 0;
	var maxPatches = 24;

	function loaderActive() {
		return !!document.querySelector('[data-loader-phase][role="status"]');
	}

	function hydrationReady() {
		if (loaderActive()) {
			return false;
		}
		return Date.now() - waitForReady.startedAt >= 5000;
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
			var m = document.cookie.match(/(?:^|;\s*)iac_lang4=(en|ru)/);
			if (!m) {
				m = document.cookie.match(/(?:^|;\s*)iac_lang3=(en|ru)/);
			}
			if (!m) {
				m = document.cookie.match(/(?:^|;\s*)iac_lang=(en|ru)/);
			}
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
			'<div class="iac-lang-switch iac-lang-switch--pill notranslate" role="group" aria-label="' + (lang === 'ru' ? 'Язык' : 'Language') + '">' +
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
			var maxSteps = isMobile ? 8 : 48;
			var delay = isMobile ? 120 : 50;
			function tick() {
				ensureHomeMobileMenuLang();
				step += 1;
				if (step < maxSteps) {
					window.setTimeout(tick, delay);
				}
			}
			tick();
		}

		document.addEventListener('click', function (event) {
			if (window.matchMedia('(min-width: 1024px)').matches) {
				return;
			}
			var btn = event.target.closest('header button.font-misc.fixed, header .lg\\:hidden button');
			if (!btn) {
				return;
			}
			window.setTimeout(function () {
				burstInject();
				patchMobileMenuLinks();
				if (typeof window.iacHomeI18nPatchMobileMenu === 'function') {
					window.iacHomeI18nPatchMobileMenu();
				}
			}, 120);
			window.setTimeout(function () {
				patchMobileMenuLinks();
				if (typeof window.iacHomeI18nPatchMobileMenu === 'function') {
					window.iacHomeI18nPatchMobileMenu();
				}
			}, 350);
		});

		window.addEventListener('resize', ensureHomeMobileMenuLang);
		if (!isMobile) {
			window.setInterval(function () {
				if (isHomeMobileMenuOpen()) {
					ensureHomeMobileMenuLang();
				}
			}, 200);
		}
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
		if (isHomeMirrorPage()) {
			return;
		}
		if (readLang() !== 'ru' || loaderActive() || !hydrationReady()) {
			return;
		}
		var map = getExactMap();
		var header = document.querySelector('header');
		if (!map || !header) {
			return;
		}

		header.querySelectorAll('a, button, span, [data-scramble]').forEach(function (el) {
			if (el.closest('.iac-lang-switch, .notranslate, #iac-home-lang-desktop, #iah-home-mobile-menu-lang, .lg\\:hidden, [data-main-menu]')) {
				return;
			}
			if (el.closest('header button.font-misc.fixed')) {
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
			if (el.closest('.iac-lang-switch, .lg\\:hidden, [data-main-menu]')) {
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
		// Do not observe header DOM — patching during React hydration caused error #418.
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

	function patchAllContactLinks() {
		var d = data();
		if (!d.contactUrl) {
			return;
		}
		document.querySelectorAll('a[href*="contact=true"], a[href="/contact"], a[href="/contact/"]').forEach(function (a) {
			a.setAttribute('href', d.contactUrl);
		});
	}

	function bindMenuNav(a, url) {
		if (!url || a.__iacMenuNav) {
			return;
		}
		a.__iacMenuNav = true;
		a.addEventListener(
			'click',
			function (e) {
				e.preventDefault();
				e.stopImmediatePropagation();
				window.location.assign(url);
			},
			true
		);
	}

	function menuRoutes() {
		var d = data();
		var home = (d.homeUrl || '/').replace(/\/?$/, '');
		return {
			'/about': d.aboutUrl || home + '/about/',
			'/blog': d.blogUrl || home + '/blog/',
			'/blog/': d.blogUrl || home + '/blog/',
			'/contact': d.contactUrl || home + '/contact/',
			'/contact/': d.contactUrl || home + '/contact/',
			'/application': d.applicationUrl || home + '/application/',
			'/features': home + '/accounts/platform-access/',
			'/accounts/agency-accounts': (d.featureUrls && d.featureUrls.agency) || home + '/accounts/agency-accounts/',
			'/accounts/platform-access': (d.featureUrls && d.featureUrls.platform) || home + '/accounts/platform-access/',
			'/accounts/team-supply': (d.featureUrls && d.featureUrls.team) || home + '/accounts/team-supply/'
		};
	}

	// Resolve the real WordPress destination for a mobile-menu anchor.
	function resolveMenuUrl(a) {
		var d = data();
		var href = a.getAttribute('href') || '';
		var label = (a.textContent || '').replace(/\s+/g, ' ').trim();
		var labelKey = label.toLowerCase();
		var labelRoutes = {
			about: d.aboutUrl || (d.homeUrl || '/').replace(/\/?$/, '') + '/about/',
			'о нас': d.aboutUrl || (d.homeUrl || '/').replace(/\/?$/, '') + '/about/',
			blog: d.blogUrl || (d.homeUrl || '/').replace(/\/?$/, '') + '/blog/',
			блог: d.blogUrl || (d.homeUrl || '/').replace(/\/?$/, '') + '/blog/',
			contact: d.contactUrl,
			контакты: d.contactUrl,
			'request access': d.applicationUrl,
			'get access': d.applicationUrl,
			'запросить доступ': d.applicationUrl,
			'получить доступ': d.applicationUrl,
			связаться: d.applicationUrl,
			'platform access': (d.featureUrls && d.featureUrls.platform) || (d.homeUrl || '/').replace(/\/?$/, '') + '/accounts/platform-access/',
			'agency accounts': (d.featureUrls && d.featureUrls.agency) || (d.homeUrl || '/').replace(/\/?$/, '') + '/accounts/agency-accounts/',
			'team supply': (d.featureUrls && d.featureUrls.team) || (d.homeUrl || '/').replace(/\/?$/, '') + '/accounts/team-supply/'
		};
		if (labelRoutes[labelKey]) {
			return labelRoutes[labelKey];
		}
		var isContact = href.indexOf('contact') !== -1 || /^(contact|контакты)$/i.test(label);
		var isRequest = href.indexOf('waitlist') !== -1 || /^(request access|get access|запросить доступ|получить доступ|связаться)$/i.test(label);
		if (isContact && d.contactUrl) {
			return d.contactUrl;
		}
		if (isRequest && d.applicationUrl) {
			return d.applicationUrl;
		}
		var routes = menuRoutes();
		var key = norm(href.split('#')[0].split('?')[0]);
		if (routes[key]) {
			return routes[key];
		}
		if (/^https?:\/\//i.test(href) || href.indexOf('/') === 0) {
			return href;
		}
		return '';
	}

	// Single capture-phase delegate: guarantees one tap = navigate on the
	// home mirror mobile menu, independent of React re-renders (scramble anim)
	// that would otherwise drop per-node listeners and require repeated taps.
	function bindMobileMenuNavDelegate() {
		if (window.__iacMenuNavDelegate) {
			return;
		}
		window.__iacMenuNavDelegate = true;

		function handleMenuNav(e) {
			if (window.matchMedia('(min-width: 1024px)').matches) {
				return;
			}
			var menu = document.querySelector('[data-main-menu]');
			if (!menu) {
				return;
			}
			var overlay = menu.closest('.fixed.inset-0');
			if (overlay) {
				var display = overlay.style.display || window.getComputedStyle(overlay).display;
				if (display && display === 'none') {
					return;
				}
			}
			var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
			if (!a || !menu.contains(a)) {
				return;
			}
			if (a.closest('.iac-lang-switch, .notranslate')) {
				return;
			}
			var url = resolveMenuUrl(a);
			if (!url) {
				return;
			}
			e.preventDefault();
			e.stopImmediatePropagation();
			window.location.href = url;
		}

		document.addEventListener('pointerdown', handleMenuNav, true);
		document.addEventListener('click', handleMenuNav, true);
	}

	function patchMobileMenuLinks() {
		var menu = document.querySelector('[data-main-menu]');
		if (!menu) {
			return;
		}

		menu.querySelectorAll('a[href]').forEach(function (a) {
			if (a.closest('.iac-lang-switch, .notranslate')) {
				return;
			}
			var url = resolveMenuUrl(a);
			if (!url) {
				return;
			}
			a.setAttribute('href', url);
			bindMenuNav(a, url);
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
		if (!hydrationReady() && Date.now() - waitForReady.startedAt < 10000) {
			return;
		}
		if (loaderActive() && Date.now() - waitForReady.startedAt < 8000) {
			return;
		}
		patchCount += 1;
		patchHeaderLinks();
		patchAllContactLinks();
		patchMobileMenuLinks();
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
		if (!hydrationReady() && Date.now() - waitForReady.startedAt < 10000) {
			window.setTimeout(waitForReady, 200);
			return;
		}
		if (loaderActive() && Date.now() - waitForReady.startedAt < 8000) {
			window.setTimeout(waitForReady, 200);
			return;
		}
		runPatch();
		window.setTimeout(runPatch, 2000);
		window.setTimeout(runPatch, 5000);
		window.setTimeout(runPatch, 9000);
		window.setInterval(function () {
			ensureLangSwitchVisible();
			if (!document.getElementById('iah-chrome-footer-root') && footerHtml) {
				mountChromeFooter();
				patchFooterLinks();
			}
			if (!isMobile) {
				ensureHomeMobileMenuLang();
			}
		}, isMobile ? 5000 : 2000);
	}

	window.setInterval(ensureLangSwitchVisible, isMobile ? 2500 : 400);

	waitForReady.startedAt = Date.now();
	bindHomeMobileMenuLang();
	bindMobileMenuNavDelegate();

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', waitForReady);
	} else {
		waitForReady();
	}
})();
