(function () {
	'use strict';

	function isHomePage() {
		if (document.documentElement.classList.contains('iah-home')) {
			return true;
		}
		if (typeof iacData !== 'undefined' && (iacData.isHome === '1' || iacData.isFront === '1')) {
			return true;
		}
		return false;
	}

	if (!isHomePage()) {
		return;
	}

	/* Native RU chunks own visible homepage copy. This file only keeps document metadata in sync. */
	document.documentElement.classList.add('iah-home');
	document.documentElement.lang = 'ru';

	if (typeof iacData === 'undefined') {
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
})();
