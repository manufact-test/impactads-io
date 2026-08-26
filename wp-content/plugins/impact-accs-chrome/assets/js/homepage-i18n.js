(function () {
	'use strict';

	var SAFE_POST_LOAD_MS = 5500;

	function isHomePage() {
		return typeof iacData !== 'undefined' && (iacData.isHome === '1' || iacData.isFront === '1');
	}

	function patchHomepageSeo() {
		if (!isHomePage()) {
			return;
		}

		var title = iacData.seoTitle || '';
		var description = iacData.seoDescription || '';

		if (title) {
			document.title = title;
			document.querySelectorAll('meta[property="og:title"],meta[name="twitter:title"]').forEach(function (meta) {
				meta.setAttribute('content', title);
			});
		}

		if (description) {
			document.querySelectorAll('meta[name="description"],meta[property="og:description"],meta[name="twitter:description"]').forEach(function (meta) {
				meta.setAttribute('content', description);
			});
		}
	}

	function schedule() {
		window.setTimeout(patchHomepageSeo, SAFE_POST_LOAD_MS);
	}

	/* Visible RU copy is native. This helper touches metadata once, after hydration. */
	if (document.readyState === 'complete') {
		schedule();
	} else {
		window.addEventListener('load', schedule, { once: true });
	}
})();
