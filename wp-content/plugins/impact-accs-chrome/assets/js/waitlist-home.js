(function () {
	'use strict';

	if (typeof iacData === 'undefined') {
		return;
	}

	function getApplicationUrl() {
		return (iacData.applicationUrl || '/application/').trim();
	}

	function isRequestAccessTarget(target) {
		if (!target || !target.closest) {
			return null;
		}

		var header = target.closest('header');
		if (!header) {
			return null;
		}

		var wrap = target.closest(
			'header [class*="scale-95"], header .pointer-events-auto.absolute.top-0.right-0'
		);
		if (wrap && /request access/i.test((wrap.textContent || '').replace(/\s+/g, ' ').trim())) {
			return wrap;
		}

		var el = target.closest('header button, header a[href*="waitlist"], button[data-slot="button"]');
		if (!el || !header.contains(el)) {
			return null;
		}

		if (!/request access/i.test((el.textContent || '').replace(/\s+/g, ' ').trim())) {
			return null;
		}

		return el;
	}

	function interceptNav(event) {
		if (!isRequestAccessTarget(event.target)) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();
		if (event.stopImmediatePropagation) {
			event.stopImmediatePropagation();
		}

		window.location.href = getApplicationUrl();
	}

	document.addEventListener('pointerdown', interceptNav, true);
	document.addEventListener('click', interceptNav, true);

	window.__iahOpenWaitlist = function () {
		window.location.href = getApplicationUrl();
	};
})();
