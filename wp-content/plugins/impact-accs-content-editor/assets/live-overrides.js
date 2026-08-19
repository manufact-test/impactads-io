(function () {
	'use strict';

	var cfg = window.iacceConfig;
	if (!cfg && typeof iacData !== 'undefined' && iacData.iacceConfig) {
		cfg = iacData.iacceConfig;
	}
	if (!cfg || !cfg.pairs || !cfg.pairs.length) {
		return;
	}

	var pairs = cfg.pairs.slice().sort(function (a, b) {
		return b.from.length - a.from.length;
	});

	var applying = false;

	function shouldSkip(el) {
		if (!el) {
			return true;
		}
		if (el.closest('script, style, noscript, [data-iac-no-i18n]')) {
			return true;
		}
		if (el.closest('[data-iacce-done]')) {
			return true;
		}
		return false;
	}

	/** Safe single-pair replace — no infinite prefix loops (optional → optionals). */
	function applyPair(text, from, to) {
		if (!text || !from || !to || from === to || text === to) {
			return text;
		}
		if (text === from) {
			return to;
		}
		// New text extends old (added letters at end): only exact match, never substring.
		if (to.indexOf(from) === 0 && to.length > from.length) {
			if (text === from) {
				return to;
			}
			return text;
		}
		if (text.indexOf(from) === -1) {
			return text;
		}
		var next = text.split(from).join(to);
		// Block if result still contains `from` (would loop on next pass).
		if (next !== to && next.indexOf(from) !== -1) {
			return text;
		}
		return next;
	}

	function replaceString(value) {
		if (!value || typeof value !== 'string') {
			return value;
		}
		var next = value;
		for (var i = 0; i < pairs.length; i++) {
			next = applyPair(next, pairs[i].from, pairs[i].to);
		}
		return next;
	}

	function markDone(el) {
		if (el && el.setAttribute) {
			el.setAttribute('data-iacce-done', '1');
		}
	}

	function applyExact(root) {
		if (!root) {
			return;
		}
		var selectors = 'h1,h2,h3,h4,h5,h6,a,button,span,p,li,label,td,th,strong,em,small,figcaption';
		root.querySelectorAll(selectors).forEach(function (el) {
			if (shouldSkip(el)) {
				return;
			}
			var trimmed = (el.textContent || '').trim();
			if (!trimmed) {
				return;
			}
			for (var i = 0; i < pairs.length; i++) {
				var from = pairs[i].from;
				var to = pairs[i].to;
				if (trimmed === to) {
					markDone(el);
					return;
				}
				if (trimmed === from) {
					if (el.querySelector('svg, img')) {
						var media = el.querySelector('svg, img');
						el.textContent = to;
						if (media) {
							el.appendChild(media);
						}
					} else {
						el.textContent = to;
					}
					markDone(el);
					break;
				}
			}
		});
	}

	function applyWalker(root) {
		if (!root) {
			return;
		}
		var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
		var node;
		while ((node = walker.nextNode())) {
			if (shouldSkip(node.parentElement)) {
				continue;
			}
			var text = node.nodeValue;
			if (!text || !text.trim()) {
				continue;
			}
			var next = replaceString(text);
			if (next !== text) {
				node.nodeValue = next;
				if (node.parentElement) {
					markDone(node.parentElement);
				}
			}
		}
	}

	function applyAttributes(root) {
		if (!root) {
			return;
		}
		root.querySelectorAll('[placeholder],[aria-label],[title]').forEach(function (el) {
			if (shouldSkip(el)) {
				return;
			}
			['placeholder', 'aria-label', 'title'].forEach(function (attr) {
				var val = el.getAttribute(attr);
				if (!val) {
					return;
				}
				for (var j = 0; j < pairs.length; j++) {
					if (val === pairs[j].to) {
						return;
					}
				}
				var next = replaceString(val);
				if (next !== val) {
					el.setAttribute(attr, next);
					markDone(el);
				}
			});
		});
	}

	function applyLinks(root) {
		var linkPairs = cfg.links || [];
		if (!linkPairs.length || !root) {
			return;
		}
		root.querySelectorAll('a[href]').forEach(function (el) {
			if (shouldSkip(el)) {
				return;
			}
			var href = el.getAttribute('href');
			if (!href) {
				return;
			}
			for (var i = 0; i < linkPairs.length; i++) {
				if (href === linkPairs[i].from) {
					el.setAttribute('href', linkPairs[i].to);
					markDone(el);
					break;
				}
			}
		});
	}

	function run() {
		if (applying) {
			return;
		}
		applying = true;
		try {
			var root = document.body || document.documentElement;
			applyLinks(root);
			applyExact(root);
			applyWalker(root);
			applyAttributes(root);
		} finally {
			applying = false;
		}
	}

	window.iacceApplyOverrides = run;

	var scheduled = false;
	function schedule() {
		if (scheduled || applying) {
			return;
		}
		scheduled = true;
		window.requestAnimationFrame(function () {
			scheduled = false;
			run();
		});
	}

	if (typeof MutationObserver !== 'undefined' && document.body) {
		var observer = new MutationObserver(function (mutations) {
			if (applying) {
				return;
			}
			for (var i = 0; i < mutations.length; i++) {
				var t = mutations[i].target;
				if (t && t.closest && t.closest('[data-iacce-done]')) {
					return;
				}
			}
			schedule();
		});
		observer.observe(document.body, { childList: true, subtree: true });
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', schedule);
	} else {
		schedule();
	}

	window.addEventListener('load', function () {
		setTimeout(schedule, 2000);
		setTimeout(schedule, 8000);
		setTimeout(schedule, 15000);
	});
})();
