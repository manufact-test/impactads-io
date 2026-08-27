(function () {
	'use strict';

	function initAccountsDropdown() {
		if (document.documentElement.classList.contains('iah-home')) {
			return;
		}
		if (typeof window.iacInitAccountsDropdown === 'function') {
			window.iacInitAccountsDropdown();
		}
	}

	function initLangSwitch() {
		document.querySelectorAll('.iac-lang-switch-li, .iac-mobile-menu__lang').forEach(function (node) {
			node.remove();
		});
	}

	function boot() {
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
