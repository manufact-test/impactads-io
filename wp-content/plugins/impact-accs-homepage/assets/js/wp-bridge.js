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

	function boot() {
		cleanupPluginPreloader();

		function onLoaderDone() {
			showChrome();
			revealFooterLinks();
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
			attributeFilter: ['data-loader-phase', 'style', 'class'],
		});

		window.setTimeout(onLoaderDone, 15000);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})(window, document);
