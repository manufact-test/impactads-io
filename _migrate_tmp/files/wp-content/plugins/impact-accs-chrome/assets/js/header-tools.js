(function () {
	'use strict';

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
		return 'en';
	}

	function writeLang(lang) {
		document.cookie = 'iac_lang=' + lang + ';path=/;max-age=' + 60 * 60 * 24 * 365;
		try {
			localStorage.setItem('iac-lang', lang);
		} catch (e) {}
		document.documentElement.lang = lang;
		document.documentElement.classList.toggle('iac-lang-ru', lang === 'ru');
	}

	function setActiveLang(root, lang) {
		root.querySelectorAll('[data-lang]').forEach(function (btn) {
			var on = btn.getAttribute('data-lang') === lang;
			btn.setAttribute('aria-pressed', on ? 'true' : 'false');
			btn.classList.toggle('iac-lang-switch__btn--active', on);
		});
	}

	function handleLangClick(e) {
		var btn = e.target.closest('.iac-lang-switch [data-lang]');
		if (!btn) {
			return;
		}
		e.preventDefault();
		e.stopPropagation();
		var lang = btn.getAttribute('data-lang');
		if (lang !== 'en' && lang !== 'ru') {
			return;
		}
		if (lang === readLang()) {
			return;
		}
		var root = btn.closest('.iac-lang-switch');
		if (root) {
			setActiveLang(root, lang);
		}
		writeLang(lang);
		var url = new URL(window.location.href);
		url.searchParams.delete('_iaclang');
		url.searchParams.delete('_t');
		window.location.replace(url.pathname + url.search + url.hash);
	}

	function initAccountsDropdown() {
		if (document.documentElement.classList.contains('iah-home')) {
			return;
		}
		if (typeof window.iacInitAccountsDropdown === 'function') {
			window.iacInitAccountsDropdown();
		}
	}

	function initLangSwitch() {
		var lang = readLang();
		writeLang(lang);
		document.querySelectorAll('.iac-lang-switch').forEach(function (root) {
			setActiveLang(root, lang);
		});
	}

	function boot() {
		document.addEventListener('pointerdown', handleLangClick, true);
		document.addEventListener('click', handleLangClick, true);
		initLangSwitch();
		initAccountsDropdown();
	}

	window.iacHeaderTools = {
		initLangSwitch: initLangSwitch,
		initAccountsDropdown: initAccountsDropdown,
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
