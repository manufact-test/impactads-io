(function () {
	'use strict';

	function buildBracketPath(topY, bottomY, radius) {
		return {
			d:
				'M ' +
				radius +
				' ' +
				topY +
				' L 1 ' +
				topY +
				' L 1 ' +
				bottomY +
				' L ' +
				radius +
				' ' +
				bottomY,
			length: 2 * (radius - 1) + (bottomY - topY),
		};
	}

	function initFooterInteractive(footer, scopeRoot) {
		if (!footer || footer.dataset.iacFooterInteractive === '1') {
			return;
		}

		var cols = footer.querySelectorAll('.iac-footer-col, .relative.h-fit.w-max');
		if (!cols.length) {
			return;
		}

		footer.dataset.iacFooterInteractive = '1';
		if (scopeRoot) {
			scopeRoot.classList.add('iac-footer-ready');
		} else {
			footer.classList.add('iac-footer-ready');
		}

		var footerColumns = [];

		function updateColumnBracket(state, centerY) {
			if (!state.path || !state.svg) {
				return;
			}
			var built = buildBracketPath(centerY, state.bracketBottom, 20);
			state.path.setAttribute('d', built.d);
			state.path.style.strokeDasharray = String(built.length);
			state.path.style.strokeDashoffset = '0';
		}

		function setHighlightState(state, y, bracketCenter, targetWidth) {
			if (!state.highlight || !state.baseWidth) {
				return;
			}
			var dy = Math.round(y - state.baseY);
			var scaleX = targetWidth / state.baseWidth;
			state.highlight.style.transformOrigin = 'left center';
			state.highlight.style.transform = 'translateY(' + dy + 'px) scaleX(' + scaleX + ')';
			state.highlight.classList.add('iac-footer-highlight-active');
			updateColumnBracket(state, bracketCenter);
		}

		function applyHighlightBase(state) {
			if (!state.highlight) {
				return;
			}
			state.highlight.style.left = state.baseX + 'px';
			state.highlight.style.top = state.baseY + 'px';
			state.highlight.style.width = state.baseWidth + 'px';
			state.highlight.style.height = state.baseHeight + 'px';
			state.highlight.style.transformOrigin = 'left center';
			state.highlight.style.transform = 'translateY(0px) scaleX(1)';
		}

		function layoutFooterColumn(state) {
			var col = state.col;
			var label = state.label;
			if (!state.path || !state.svg || !label || !state.highlight) {
				return false;
			}
			var box = col.getBoundingClientRect();
			var labelBox = label.getBoundingClientRect();
			if (!box.width || !box.height || !labelBox.width || !labelBox.height) {
				return false;
			}
			state.svg.setAttribute('viewBox', '0 0 ' + Math.round(box.width) + ' ' + Math.round(box.height));
			state.bracketBottom = box.height - 1;
			state.baseX = labelBox.left - box.left;
			state.baseY = labelBox.top - box.top;
			state.baseWidth = labelBox.width;
			state.baseHeight = labelBox.height;
			state.defaultCenter = state.baseY + state.baseHeight / 2;
			applyHighlightBase(state);
			setHighlightState(state, state.baseY, state.defaultCenter, state.baseWidth);
			return true;
		}

		function activateFooterLink(state, link) {
			var col = state.col;
			var box = col.getBoundingClientRect();
			var label = state.label;
			if (!label || !state.highlight) {
				return;
			}
			var labelBox = label.getBoundingClientRect();
			var linkBox = link.getBoundingClientRect();
			if (!box.width || !labelBox.width || !linkBox.width) {
				return;
			}
			state.baseX = labelBox.left - box.left;
			state.baseY = labelBox.top - box.top;
			state.baseWidth = labelBox.width;
			state.baseHeight = labelBox.height;
			var linkY = Math.round(linkBox.top - box.top + (linkBox.height - state.baseHeight) / 2);
			var bracketCenter = linkY + state.baseHeight / 2;
			if (state.activeLink && state.activeLink !== link) {
				state.activeLink.style.removeProperty('color');
			}
			state.activeLink = link;
			state.label.style.setProperty('color', '#fff', 'important');
			link.style.setProperty('color', '#fff', 'important');
			setHighlightState(state, linkY, bracketCenter, linkBox.width + 12);
		}

		function resetFooterColumn(state) {
			if (state.activeLink) {
				state.activeLink.style.removeProperty('color');
				state.activeLink = null;
			}
			if (state.label) {
				state.label.style.removeProperty('color');
			}
			layoutFooterColumn(state);
		}

		function bindFooterColumn(state) {
			if (!state.links.length || state.col.dataset.iacFooterBound === '1') {
				return;
			}
			state.col.dataset.iacFooterBound = '1';
			state.links.forEach(function (link) {
				link.classList.add('iac-footer-link');
				link.addEventListener('pointerenter', function () {
					activateFooterLink(state, link);
				});
				link.addEventListener('focus', function () {
					activateFooterLink(state, link);
				});
			});
			if (state.labelWrap) {
				state.labelWrap.addEventListener('pointerenter', function () {
					resetFooterColumn(state);
				});
			}
		}

		function bindFooterDelegation() {
			footer.addEventListener('mouseover', function (event) {
				var link = event.target.closest('.iac-footer-link, .iac-footer-col a, .relative.h-fit.w-max a');
				if (link && footer.contains(link)) {
					var col = link.closest('.iac-footer-col, .relative.h-fit.w-max');
					if (!col) {
						return;
					}
					for (var i = 0; i < footerColumns.length; i++) {
						if (footerColumns[i].col === col) {
							activateFooterLink(footerColumns[i], link);
							return;
						}
					}
				}
				if (event.target.closest('.iac-footer-label')) {
					var labelCol = event.target.closest('.iac-footer-col, .relative.h-fit.w-max');
					if (!labelCol) {
						return;
					}
					for (var j = 0; j < footerColumns.length; j++) {
						if (footerColumns[j].col === labelCol) {
							resetFooterColumn(footerColumns[j]);
							return;
						}
					}
				}
			});
		}

		function animateFooterColumnReveal(state, delay) {
			window.setTimeout(function () {
				if (state.labelWrap) {
					state.labelWrap.style.opacity = '1';
				}
				state.links.forEach(function (link, index) {
					window.setTimeout(function () {
						link.style.opacity = '1';
						link.classList.add('iac-footer-link-visible');
					}, index * 80);
				});
				var tries = 0;
				var tryLayout = function () {
					if (layoutFooterColumn(state) || tries > 20) {
						return;
					}
					tries += 1;
					window.requestAnimationFrame(tryLayout);
				};
				tryLayout();
			}, delay);
		}

		cols.forEach(function (col) {
			col.style.position = 'relative';
			var highlight =
				col.querySelector('.iac-footer-highlight') ||
				col.querySelector(':scope > .bg-primary.pointer-events-none.absolute') ||
				col.querySelector(':scope > .bg-primary.absolute');
			if (highlight) {
				highlight.classList.add('iac-footer-highlight');
			}
			var labelWrap = col.querySelector('.iac-footer-label') || col.querySelector(':scope > .flex.opacity-0, :scope > .flex.opacity-100');
			if (labelWrap) {
				labelWrap.classList.add('iac-footer-label');
				labelWrap.style.opacity = '1';
			}
			var linksWrap =
				col.querySelector('.iac-footer-links') ||
				col.querySelector(':scope > div[class*="flex-col"]') ||
				col.querySelector('.relative.flex-col');
			var links = linksWrap ? linksWrap.querySelectorAll(':scope > a') : col.querySelectorAll('a');
			links.forEach(function (link) {
				link.style.opacity = '1';
			});
			var state = {
				col: col,
				svg: col.querySelector('svg[preserveAspectRatio="none"]'),
				path: col.querySelector('svg[preserveAspectRatio="none"] path'),
				highlight: highlight,
				labelWrap: labelWrap,
				label: labelWrap ? labelWrap.querySelector('span.font-misc') : null,
				linksWrap: linksWrap,
				links: links,
				bracketBottom: 0,
				baseX: 0,
				baseY: 0,
				baseWidth: 0,
				baseHeight: 0,
				defaultCenter: 0,
				activeLink: null,
			};
			footerColumns.push(state);
			col.addEventListener('mouseleave', function () {
				resetFooterColumn(state);
			});
		});

		bindFooterDelegation();
		footerColumns.forEach(function (state, index) {
			bindFooterColumn(state);
			animateFooterColumnReveal(state, index * 120);
		});

		window.addEventListener(
			'load',
			function () {
				footerColumns.forEach(function (state) {
					layoutFooterColumn(state);
				});
			},
			{ once: true }
		);

		window.addEventListener(
			'resize',
			function () {
				footerColumns.forEach(function (state) {
					layoutFooterColumn(state);
				});
			},
			{ passive: true }
		);
	}

	window.iacInitFooterInteractive = initFooterInteractive;
})();
