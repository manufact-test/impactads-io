(function () {
	'use strict';

	var SAFE_POST_LOAD_MS = 5500;
	var footerHtml = typeof window.__iahChromeFooter === 'string' ? window.__iahChromeFooter : '';
	var mounted = false;

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

	function revealFooterLinks(root) {
		if (!root) {
			return;
		}
		root.querySelectorAll('.iac-footer-label').forEach(function (el) {
			el.classList.remove('opacity-0');
		});
	}

	function patchFooterLinks(root) {
		if (!root) {
			return;
		}
		var d = data();
		var routes = {
			'/blog': d.blogUrl || '/blog/',
			'/about': d.aboutUrl || '/about/',
			'/contact': d.contactUrl || '/contact/',
			'/accounts/agency-accounts': (d.featureUrls && d.featureUrls.agency) || '/accounts/agency-accounts/',
			'/accounts/platform-access': (d.featureUrls && d.featureUrls.platform) || '/accounts/platform-access/',
			'/accounts/team-supply': (d.featureUrls && d.featureUrls.team) || '/accounts/team-supply/'
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
		revealFooterLinks(root);
	}

	function mountChromeFooter() {
		if (mounted || !footerHtml) {
			return;
		}

		var existing = document.getElementById('iah-chrome-footer-root');
		if (existing) {
			mounted = true;
			patchFooterLinks(existing);
			return;
		}

		var host = document.createElement('div');
		host.id = 'iah-chrome-footer-root';
		host.setAttribute('data-iah-chrome', '1');
		host.innerHTML = footerHtml;
		document.body.appendChild(host);
		mounted = true;
		patchFooterLinks(host);

		if (typeof window.iacInitFooterInteractive === 'function') {
			var chromeFooter = host.querySelector('footer');
			if (chromeFooter) {
				window.iacInitFooterInteractive(chromeFooter, host);
			}
		}

		document.querySelectorAll('footer:not([data-iah-chrome="1"])').forEach(function (footer) {
			if (!footer.closest('#iah-chrome-footer-root')) {
				footer.style.display = 'none';
				footer.setAttribute('aria-hidden', 'true');
			}
		});
	}

	function run() {
		mountChromeFooter();
		var root = document.getElementById('iah-chrome-footer-root');
		if (root) {
			patchFooterLinks(root);
		}
	}

	function schedule() {
		window.setTimeout(run, SAFE_POST_LOAD_MS);
	}

	/* No language polling, DOM translation or mutation observers on the homepage. */
	if (document.readyState === 'complete') {
		schedule();
	} else {
		window.addEventListener('load', schedule, { once: true });
	}
})();
