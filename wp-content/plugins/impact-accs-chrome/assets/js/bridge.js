(function () {
	'use strict';

	if (typeof iacData === 'undefined') {
		return;
	}

	var waitlistModal = document.getElementById('iac-waitlist-modal') || document.getElementById('iac-contact-modal');
	var waitlistHost = document.getElementById('iac-waitlist-host');
	var root = document.getElementById('iac-root');
	var standalone = !root && waitlistModal && waitlistHost;
	var lenisInstance = null;
	var waitlistOpen = false;

	if (!root && !standalone) {
		return;
	}

	if (standalone) {
		initStandaloneWaitlist();
		return;
	}

	var PATH_CTA_CLOSED =
		'M37.5,0 C33.08,0 29.5,3.58 29.5,8 L29.5,17.7 L29.5,27.4 C29.5,31.82 33.08,35.4 37.5,35.4 Z';
	var PATH_CTA_OPEN =
		'M37.5,0 H0 C4.5,0 8.8,2.451 11.5,6.601 L26,28.8 C28.7,32.95 33,35.4 37.5,35.4 Z';

	document.documentElement.classList.add('iac-chrome-active');
	document.body.classList.add('iac-chrome-active', 'iac-chrome-ready');

	var isHome = iacData.isFront === '1';
	var isApplication = iacData.isApplication === '1';
	var isContact = iacData.isContact === '1';
	var isBlog = iacData.isBlog === '1';
	var isAbout = iacData.isAbout === '1';
	var isFeature = iacData.isFeature === '1';
	var isGridPage = isBlog || isAbout || isFeature;
	var frameMask = root.querySelector('[data-slot="frame"]');
	var frameLayer = frameMask ? frameMask.closest('.fixed.inset-0') : root.querySelector('.fixed.inset-0');
	var frameInner = frameLayer
		? frameLayer.querySelector('.absolute.inset-0.max-lg\\:hidden, .max-lg\\:hidden.absolute.inset-0')
		: null;
	var nav = root.querySelector('nav[class*="group/nav"]');
	var navSides = nav ? nav.querySelectorAll(':scope > div') : [];
	var headerBar =
		root.querySelector('header .px-sides.relative.hidden') ||
		root.querySelector('header .px-sides');
	var requestBtnWrap =
		root.querySelector('header [class*="scale-95"]') ||
		root.querySelector('header .pointer-events-auto.absolute.top-0.right-0');
	var ctaSvg = requestBtnWrap ? requestBtnWrap.querySelector('svg[viewBox="0 0 37.5 35.4"]') : null;
	var ctaPath = ctaSvg ? ctaSvg.querySelector('path') : null;
	var ctaInner = requestBtnWrap ? requestBtnWrap.querySelector('.bg-primary') : null;
	var ctaCorner = ctaInner ? ctaInner.querySelector('svg.absolute') : null;
	var waitlistDialog = waitlistModal ? waitlistModal.querySelector('.iac-waitlist-dialog') : null;
	var waitlistOverlay = waitlistModal ? waitlistModal.querySelector('.iac-waitlist-overlay') : null;

	if (frameInner) {
		frameInner.classList.add('iac-frame-layer');
		frameInner.style.visibility = 'visible';
		frameInner.style.opacity = '';
	}

	var scrollWrap = null;
	var scrollTrack = null;
	var scrollRail = null;
	var scrollThumb = null;
	var scrollHover = null;
	var scrollMetrics = { thumbTravel: 0, scrollRange: 0, thumbHeight: 0 };
	var scrollDrag = null;
	var scrollDragging = false;
	var frameVisible = false;
	var lastHeaderSpread = null;
	var footerReady = false;
	var footerColumns = [];

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

	function findScrollWrap() {
		var marked = root.querySelector('.iac-scrollbar-wrap');
		if (marked) {
			return marked;
		}

		if (frameLayer) {
			marked = frameLayer.querySelector('.iac-scrollbar-wrap');
			if (marked) {
				return marked;
			}

			var nodes = frameLayer.querySelectorAll('div[style*="28vh"]');
			for (var i = 0; i < nodes.length; i++) {
				var style = nodes[i].getAttribute('style') || '';
				if (style.indexOf('14px') !== -1 || style.indexOf('width:14') !== -1) {
					return nodes[i];
				}
			}

			marked = frameLayer.querySelector('div[style*="top:36vh"][style*="28vh"]');
			if (marked) {
				return marked;
			}
		}

		return null;
	}

	function initScrollbar() {
		if (isApplication || isContact) {
			return;
		}

		scrollWrap = findScrollWrap();
		if (!scrollWrap) {
			return;
		}

		scrollWrap.classList.add('iac-scrollbar-wrap');
		scrollWrap.style.pointerEvents = 'auto';

		scrollTrack = scrollWrap.querySelector('.iac-scrollbar-track') || scrollWrap.querySelector('.size-full') || scrollWrap.firstElementChild;
		if (scrollTrack) {
			scrollTrack.classList.remove('invisible');
			scrollTrack.classList.add('iac-scrollbar-track');
			scrollTrack.style.transformOrigin = '100% 50%';
		}

		scrollRail =
			scrollTrack &&
			(scrollTrack.querySelector('.iac-scrollbar-rail') ||
				scrollTrack.querySelector('.relative.h-full') ||
				scrollTrack.querySelector('[class*="bg-black"]') ||
				scrollTrack.querySelector('.touch-none.rounded-sm'));
		scrollThumb =
			(scrollTrack &&
				(scrollTrack.querySelector('.iac-scrollbar-thumb') ||
					scrollTrack.querySelector('[class*="DCE8E4"]'))) ||
			(scrollRail && scrollRail.querySelector('.iac-scrollbar-thumb'));

		if (scrollThumb) {
			scrollThumb.classList.add('iac-scrollbar-thumb');
			scrollThumb.classList.remove('top-0', 'inset-x-0', 'rounded-sm');
			scrollThumb.style.background = '#dce8e4';
			scrollThumb.style.position = 'absolute';
			scrollThumb.style.left = '0';
			scrollThumb.style.right = '0';
			scrollThumb.style.width = '100%';
			scrollThumb.style.borderRadius = '2px';
			scrollThumb.style.top = '0';
			scrollThumb.style.transform = 'translateY(0px)';
			scrollThumb.style.willChange = 'transform, height';
		}

		scrollHover =
			frameLayer.querySelector('.iac-scrollbar-hover') ||
			document.querySelector('.iac-scrollbar-hover');

		root.classList.add('iac-has-scrollbar');
		updateScrollbarVisibility(false);
		bindScrollbarInteractions();
	}

	function setupCtaMorph() {
		if (!ctaSvg || !ctaPath || ctaSvg.querySelector('.iac-cta-path-open')) {
			return;
		}

		ctaPath.classList.add('iac-cta-path-closed');
		ctaPath.setAttribute('d', PATH_CTA_CLOSED);

		var openPath = ctaPath.cloneNode(true);
		openPath.classList.remove('iac-cta-path-closed');
		openPath.classList.add('iac-cta-path-open');
		openPath.setAttribute('d', PATH_CTA_OPEN);
		openPath.style.opacity = '0';
		ctaSvg.appendChild(openPath);
	}

	function pageIsShort() {
		return document.documentElement.scrollHeight <= window.innerHeight + 40;
	}

	function getScrollTop() {
		var el = document.scrollingElement || document.documentElement;
		var native = el.scrollTop || window.pageYOffset || window.scrollY || 0;
		var lenisScroll =
			lenisInstance && typeof lenisInstance.scroll === 'number' && !isNaN(lenisInstance.scroll)
				? lenisInstance.scroll
				: 0;
		return Math.max(native, lenisScroll);
	}

	function setScrollTop(value) {
		var top = Math.max(0, value);
		if (lenisInstance) {
			lenisInstance.scrollTo(top, { immediate: true, force: true });
			return;
		}
		window.scrollTo(0, top);
	}

	function getMaxScroll() {
		var el = document.scrollingElement || document.documentElement;
		var viewport = window.innerHeight;
		var body = document.body;
		var scrollHeight = Math.max(
			el.scrollHeight,
			body ? body.scrollHeight : 0,
			body ? body.offsetHeight : 0
		);
		return Math.max(scrollHeight - viewport, 0);
	}

	function getApplicationUrl() {
		return ((iacData && iacData.applicationUrl) || '/application/').trim();
	}

	function goToApplication() {
		var url = getApplicationUrl();
		if (!url) {
			return;
		}

		try {
			var target = new URL(url, window.location.origin);
			if (window.location.pathname === target.pathname) {
				return;
			}
		} catch (e) {
			/* use location.href below */
		}

		window.location.href = url;
	}

	function shouldShowHeaderSpread() {
		if (waitlistOpen) {
			return true;
		}
		if (isGridPage || !isHome) {
			return true;
		}
		if (pageIsShort()) {
			return getScrollTop() > 20;
		}
		return getScrollTop() > 80;
	}

	function initGridChrome() {
		if (!isGridPage) {
			return;
		}
		updateHeaderSpread(true);
		lastHeaderSpread = true;
		frameVisible = false;
		setFrameVisible(true);
		updateThumb();
	}

	function shouldShowFrame() {
		return shouldShowHeaderSpread();
	}

	function setFrameVisible(visible) {
		if (frameVisible === visible) {
			return;
		}
		frameVisible = visible;
		root.classList.toggle('iac-frame-visible', visible);
		updateScrollbarVisibility(visible);
	}

	function updateScrollbarVisibility(visible) {
		if (!scrollTrack) {
			return;
		}
		scrollTrack.style.visibility = visible ? 'visible' : 'hidden';
		scrollTrack.style.transform = visible ? 'translate3d(0, 0, 0)' : 'translate3d(100%, 0, 0)';
	}

	function updateScrollUi() {
		var spread = shouldShowHeaderSpread();
		if (lastHeaderSpread !== spread) {
			lastHeaderSpread = spread;
			updateHeaderSpread(spread);
		}
		setFrameVisible(shouldShowFrame());
		updateThumb();
	}

	function updateChromeState() {
		updateScrollUi();
	}

	function updateHeaderSpread(visible) {
		if (headerBar) {
			headerBar.style.transition = visible
				? 'transform 400ms cubic-bezier(0.33, 1, 0.68, 1)'
				: 'transform 600ms cubic-bezier(0.5, 0, 0.75, 0) 100ms';
			headerBar.style.transform = 'translate3d(0, 0, 0)';
		}

		if (navSides.length >= 2) {
			navSides.forEach(function (side, index) {
				side.style.transition = visible
					? 'transform 400ms cubic-bezier(0.33, 1, 0.68, 1)'
					: 'transform 600ms cubic-bezier(0.5, 0, 0.75, 0)';
				if (visible) {
					side.style.transform = 'translateX(0px)';
				} else if (index === 0) {
					side.style.transform = 'translateX(calc(var(--spacer) / 2 - var(--nav-gap) / 2))';
				} else {
					side.style.transform = 'translateX(calc(var(--spacer) / -2 + var(--nav-gap) / 2))';
				}
			});
		}

		var closedPath = requestBtnWrap && requestBtnWrap.querySelector('.iac-cta-path-closed');
		var openPath = requestBtnWrap && requestBtnWrap.querySelector('.iac-cta-path-open');
		if (closedPath && openPath) {
			closedPath.style.opacity = visible ? '0' : '1';
			openPath.style.opacity = visible ? '1' : '0';
		} else if (ctaPath) {
			ctaPath.setAttribute('d', visible ? PATH_CTA_OPEN : PATH_CTA_CLOSED);
		}

		if (requestBtnWrap) {
			if (visible) {
				requestBtnWrap.classList.remove('scale-95');
				requestBtnWrap.classList.add('iac-header-cta-open');
				requestBtnWrap.style.translate = '0 0';
			} else {
				requestBtnWrap.classList.add('scale-95');
				requestBtnWrap.classList.remove('iac-header-cta-open');
				requestBtnWrap.style.translate = 'calc(-1 * var(--frame-inset, 3px)) var(--frame-inset, 3px)';
			}
		}

		if (ctaInner) {
			ctaInner.classList.toggle('iac-cta-inner-open', visible);
			ctaInner.classList.toggle('overflow-hidden', !visible);
			ctaInner.classList.toggle('rounded-r-lg', !visible);
			ctaInner.classList.toggle('shadow-none', !visible);
		}

		if (ctaCorner) {
			ctaCorner.classList.toggle('translate-y-0', visible);
			ctaCorner.classList.toggle('-translate-y-full', !visible);
		}
	}

	function updateThumb() {
		if (!scrollThumb) {
			return;
		}

		var railHeight = 0;
		if (scrollRail && scrollRail.clientHeight) {
			railHeight = scrollRail.clientHeight;
		} else if (scrollTrack && scrollTrack.clientHeight) {
			railHeight = scrollTrack.clientHeight;
		} else if (scrollWrap && scrollWrap.clientHeight) {
			railHeight = scrollWrap.clientHeight;
		}

		if (!railHeight) {
			return;
		}

		var viewport = window.innerHeight;
		var scrollHeight = Math.max(
			(document.scrollingElement || document.documentElement).scrollHeight,
			document.body ? document.body.scrollHeight : 0
		);
		var scrollRange = Math.max(0, scrollHeight - viewport);
		var thumbHeight = Math.max(
			railHeight * 0.06,
			Math.min((viewport / Math.max(scrollHeight, 1)) * railHeight, railHeight)
		);
		var thumbTravel = Math.max(railHeight - thumbHeight, 0);
		var ratio = scrollRange <= 0 ? 0 : Math.min(Math.max(getScrollTop() / scrollRange, 0), 1);
		var y = thumbTravel * ratio;

		scrollMetrics.thumbTravel = thumbTravel;
		scrollMetrics.scrollRange = scrollRange;
		scrollMetrics.thumbHeight = thumbHeight;

		scrollThumb.style.height = Math.round(thumbHeight) + 'px';
		scrollThumb.style.top = '0';
		scrollThumb.style.bottom = 'auto';
		if (!scrollDragging) {
			var yPx = Math.round(y) + 'px';
			scrollThumb.style.transform = 'translate3d(0, ' + yPx + ', 0)';
			scrollThumb.style.setProperty('--iac-thumb-y', yPx);
		}
	}

	function getThumbOffsetY() {
		if (!scrollThumb) {
			return 0;
		}
		var match = (scrollThumb.style.transform || '').match(/translate3d\(0,\s*([-\d.]+)px/);
		if (!match) {
			match = (scrollThumb.style.transform || '').match(/translateY\(([-\d.]+)px\)/);
		}
		return match ? parseFloat(match[1]) : 0;
	}

	function beginThumbDrag(target, event, thumbY) {
		if (!scrollRail || !scrollThumb) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();

		if (target.setPointerCapture) {
			try {
				target.setPointerCapture(event.pointerId);
			} catch (e) {
				/* ignore */
			}
		}

		scrollDragging = true;
		scrollDrag = {
			target: target,
			pointerId: event.pointerId,
			pointerY: event.clientY,
			thumbY: typeof thumbY === 'number' ? thumbY : getThumbOffsetY(),
		};
		document.documentElement.classList.add('iac-scrollbar-dragging');
	}

	function moveThumbDrag(event) {
		if (!scrollDragging || !scrollDrag || !scrollThumb) {
			return;
		}

		var travel = scrollMetrics.thumbTravel;
		if (!travel) {
			return;
		}

		var delta = event.clientY - scrollDrag.pointerY;
		var nextY = Math.max(0, Math.min(scrollDrag.thumbY + delta, travel));
		var scrollTarget = (nextY / travel) * scrollMetrics.scrollRange;

		scrollThumb.style.transform = 'translate3d(0, ' + Math.round(nextY) + 'px, 0)';
		scrollThumb.style.setProperty('--iac-thumb-y', Math.round(nextY) + 'px');
		setScrollTop(scrollTarget);
	}

	function endThumbDrag(event) {
		if (!scrollDragging) {
			return;
		}

		var target = scrollDrag && scrollDrag.target;
		if (target && target.releasePointerCapture && event && scrollDrag.pointerId != null) {
			try {
				if (target.hasPointerCapture && target.hasPointerCapture(event.pointerId)) {
					target.releasePointerCapture(event.pointerId);
				}
			} catch (e) {
				/* ignore */
			}
		}

		scrollDragging = false;
		scrollDrag = null;
		document.documentElement.classList.remove('iac-scrollbar-dragging');
	}

	function onRailPointerDown(event) {
		if (!scrollRail || !scrollThumb || event.button !== 0) {
			return;
		}

		var railBox = scrollRail.getBoundingClientRect();
		var thumbHeight = scrollMetrics.thumbHeight || scrollThumb.offsetHeight || 0;
		var nextY = Math.max(0, Math.min(event.clientY - railBox.top - thumbHeight / 2, scrollMetrics.thumbTravel));
		beginThumbDrag(scrollRail, event, nextY);
		moveThumbDrag(event);
	}

	function bindScrollbarInteractions() {
		if (!scrollThumb || scrollThumb.dataset.iacScrollbarBound === '1') {
			return;
		}
		scrollThumb.dataset.iacScrollbarBound = '1';

		scrollThumb.addEventListener('pointerdown', function (event) {
			if (event.button !== 0) {
				return;
			}
			event.stopPropagation();
			beginThumbDrag(scrollThumb, event, getThumbOffsetY());
		});

		if (scrollRail && scrollRail.dataset.iacScrollbarBound !== '1') {
			scrollRail.dataset.iacScrollbarBound = '1';
			scrollRail.addEventListener('pointerdown', onRailPointerDown);
		}

		document.addEventListener('pointermove', function (event) {
			if (scrollDragging) {
				moveThumbDrag(event);
			}
		});

		document.addEventListener('pointerup', endThumbDrag);
		document.addEventListener('pointercancel', endThumbDrag);
		document.addEventListener('lostpointercapture', endThumbDrag);

		if (scrollHover && scrollHover.dataset.iacScrollbarBound !== '1') {
			scrollHover.dataset.iacScrollbarBound = '1';
			scrollHover.addEventListener('pointerenter', function () {
				if (scrollWrap) {
					scrollWrap.classList.add('iac-scrollbar-hovering');
				}
			});
			scrollHover.addEventListener('pointerleave', function () {
				if (scrollWrap && !scrollDragging) {
					scrollWrap.classList.remove('iac-scrollbar-hovering');
				}
			});
		}
	}

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

	function bindFooterDelegation(footer) {
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

			var tries = 0;
			var tryLayout = function () {
				if (layoutFooterColumn(state) || tries > 20) {
					return;
				}
				tries += 1;
				window.requestAnimationFrame(tryLayout);
			};
			tryLayout();

			if (state.path) {
				var built = buildBracketPath(state.defaultCenter || 1, state.bracketBottom || 100, 20);
				state.path.style.strokeDasharray = String(built.length);
				state.path.style.transition = 'stroke-dashoffset 0.6s cubic-bezier(0.33, 0, 0.67, 1)';
				state.path.style.strokeDashoffset = String(built.length);
				window.requestAnimationFrame(function () {
					state.path.style.strokeDashoffset = '0';
				});
			}

			if (state.highlight) {
				state.highlight.classList.add('iac-footer-highlight-active');
				state.highlight.style.transform = 'translateY(0px) scaleX(0)';
				window.requestAnimationFrame(function () {
					if (layoutFooterColumn(state)) {
						setHighlightState(state, state.baseY, state.defaultCenter, state.baseWidth);
					}
				});
			}

			state.links.forEach(function (link, index) {
				window.setTimeout(function () {
					link.style.opacity = '1';
					link.classList.add('iac-footer-link-visible');
				}, index * 80);
			});
		}, delay);
	}

	function initFooter() {
		var footer = root.querySelector(':scope > footer');
		if (!footer) {
			return;
		}

		var cols = footer.querySelectorAll('.iac-footer-col, .relative.h-fit.w-max');
		if (!cols.length) {
			return;
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
			}

			var linksWrap =
				col.querySelector('.iac-footer-links') ||
				col.querySelector(':scope > div[class*="flex-col"]') ||
				col.querySelector('.relative.flex-col');
			var links = linksWrap ? linksWrap.querySelectorAll(':scope > a') : col.querySelectorAll('a');

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

		var run = function () {
			if (footerReady) {
				return;
			}
			footerReady = true;
			root.classList.add('iac-footer-ready');
			bindFooterDelegation(footer);
			footerColumns.forEach(function (state, index) {
				bindFooterColumn(state);
				animateFooterColumnReveal(state, index * 120);
			});
		};

		window.requestAnimationFrame(function () {
			run();
		});

		window.addEventListener('load', function () {
			footerColumns.forEach(function (state) {
				layoutFooterColumn(state);
			});
		});

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

	function handleFormSuccess(form) {
		var modalPanel = form.closest('.iac-waitlist-panel');

		if (modalPanel) {
			var successWrap = modalPanel.querySelector('.iac-waitlist-success');
			var submitRow = modalPanel.querySelector('.iac-waitlist-submit-row');
			var formWrap = modalPanel.querySelector('.iac-waitlist-form-wrap');
			if (formWrap) {
				formWrap.style.opacity = '0';
				formWrap.style.pointerEvents = 'none';
			}
			form.style.opacity = '0';
			form.style.pointerEvents = 'none';
			if (submitRow) {
				submitRow.style.opacity = '0';
				submitRow.style.pointerEvents = 'none';
			}
			if (successWrap) {
				successWrap.style.opacity = '1';
				successWrap.style.pointerEvents = 'auto';
				successWrap.classList.add('iac-visible');
				var status = successWrap.querySelector('[role="status"]');
				if (status) {
					status.style.opacity = '1';
					status.style.pointerEvents = 'auto';
				}
			}
			return;
		}

		var panel = form.closest('.relative.w-full') || form.parentElement;
		var status = panel ? panel.querySelector('[role="status"]') : null;
		form.classList.add('is-success');
		if (status) {
			status.style.opacity = '1';
			status.style.pointerEvents = 'auto';
		}
	}

	function bindAccessForms() {
		document.querySelectorAll('.iac-access-form').forEach(function (form) {
			if (form.dataset.iacBound) {
				return;
			}
			form.dataset.iacBound = '1';

			form.addEventListener('submit', function (event) {
				event.preventDefault();
				var requestFailed = (iacData.strings && iacData.strings.requestFailed) || 'Request failed';

				var error =
					form.querySelector('#iac-modal-waitlist-error') ||
					form.querySelector('#iac-application-error') ||
					form.querySelector('#iac-contact-error') ||
					document.getElementById('iac-waitlist-error');
				var fd = new FormData(form);

				if (error) {
					error.textContent = '';
					error.style.opacity = '0';
				}

				fd.append('action', 'iac_submit_access');
				fd.append('nonce', iacData.nonce);

				fetch(iacData.ajaxUrl, {
					method: 'POST',
					body: fd,
					credentials: 'same-origin',
				})
					.then(function (response) {
						return response.json();
					})
					.then(function (payload) {
						if (!payload.success) {
							throw new Error((payload.data && payload.data.message) || requestFailed);
						}
						handleFormSuccess(form);
					})
					.catch(function (err) {
						if (error) {
							error.textContent = (err.message || requestFailed).toUpperCase();
							error.style.opacity = '1';
						}
					});
			});
		});
	}

	function openWaitlist() {
		window.open('https://t.me/founderads', '_blank', 'noopener,noreferrer');
	}

	function closeWaitlist() {
		if (iacData.isApplication === '1' || iacData.isContact === '1') {
			window.location.href = iacData.homeUrl || '/';
			return;
		}

		if (!waitlistModal) {
			return;
		}

		waitlistOpen = false;
		if (root) {
			root.classList.remove('iac-waitlist-open');
		}
		if (waitlistHost) {
			waitlistHost.classList.remove('iac-waitlist-open');
		}
		document.documentElement.classList.remove('iac-waitlist-open');
		document.body.classList.remove('iac-waitlist-open');
		waitlistModal.classList.remove('iac-waitlist-open');
		waitlistModal.setAttribute('aria-hidden', 'true');

		if (typeof updateChromeState === 'function') {
			updateChromeState();
		}
	}

	function bindWaitlistModal() {
		if (!waitlistModal) {
			return;
		}

		waitlistModal.querySelectorAll('[data-iac-waitlist-close]').forEach(function (el) {
			el.addEventListener('click', function (event) {
				event.preventDefault();
				event.stopPropagation();
				closeWaitlist();
			});
		});

		document.addEventListener(
			'click',
			function (event) {
				if (!waitlistOpen) {
					return;
				}
				var closeEl = event.target.closest('[data-iac-waitlist-close]');
				if (closeEl) {
					event.preventDefault();
					event.stopPropagation();
					closeWaitlist();
				}
			},
			true
		);

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && waitlistOpen) {
				closeWaitlist();
			}
		});

		var contactTab = waitlistModal.querySelector('[data-iac-waitlist-contact]');
		if (contactTab) {
			contactTab.addEventListener('click', function (event) {
				event.preventDefault();
				var contactUrl = (iacData.contactUrl || '').trim();
				if (iacData.isApplication === '1') {
					if (contactUrl) {
						window.location.href = contactUrl;
					}
					return;
				}
				if (iacData.isContact === '1') {
					return;
				}
				closeWaitlist();
				if (contactUrl) {
					window.location.href = contactUrl;
				}
			});
		}

		var accessTab = waitlistModal.querySelector('[data-iac-waitlist-access]');
		if (accessTab) {
			accessTab.addEventListener('click', function (event) {
				if (iacData.isContact !== '1') {
					return;
				}
				event.preventDefault();
				var appUrl = (iacData.applicationUrl || '').trim();
				if (appUrl) {
					window.location.href = appUrl;
				}
			});
		}
	}

	function shouldOpenWaitlistFromUrl() {
		var params = new URLSearchParams(window.location.search);
		return params.has('waitlist') || window.location.hash === '#iac-waitlist-open';
	}

	function bindWaitlistTriggers() {
		var appUrl = getApplicationUrl();

		document.querySelectorAll('a[href="#iac-waitlist-open"], a[href*="waitlist=true"]').forEach(function (link) {
			link.setAttribute('href', appUrl);
		});

		document.querySelectorAll('header button').forEach(function (btn) {
			if (/request access|запросить доступ/i.test(btn.textContent || '')) {
				btn.addEventListener('click', function (event) {
					if (iacData.isApplication === '1' || iacData.isContact === '1') {
						if (iacData.isContact === '1') {
							event.preventDefault();
							window.location.href = iacData.applicationUrl || '/application/';
						}
						return;
					}
					event.preventDefault();
					goToApplication();
				});
			}
		});
	}

	function isRequestAccessTarget(target) {
		if (!target || !target.closest) {
			return null;
		}
		if (waitlistModal && waitlistModal.contains(target)) {
			return null;
		}

		var header = target.closest('header');
		if (!header) {
			return null;
		}

		var wrap = target.closest(
			'header [class*="scale-95"], header .pointer-events-auto.absolute.top-0.right-0'
		);
		if (wrap && /request access|запросить доступ/i.test((wrap.textContent || '').replace(/\s+/g, ' ').trim())) {
			return wrap;
		}

		var el = target.closest('header button, header a[href*="waitlist"], button[data-slot="button"]');
		if (!el || !header.contains(el)) {
			return null;
		}

		var text = (el.textContent || '').replace(/\s+/g, ' ').trim();
		if (!/request access|запросить доступ/i.test(text)) {
			return null;
		}

		return el;
	}

	function bindWaitlistCaptureTriggers() {
		function handleApplicationNav(event) {
            var directFinalCta = event.target && event.target.closest
                ? event.target.closest('a[data-iac-scroll-final]')
                : null;
            if (directFinalCta) {
                return;
            }
			if (iacData.isApplication === '1' || iacData.isContact === '1') {
				return;
			}
			if (!isRequestAccessTarget(event.target)) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();
			if (event.stopImmediatePropagation) {
				event.stopImmediatePropagation();
			}
			goToApplication();
		}

		document.addEventListener('pointerdown', handleApplicationNav, true);
		document.addEventListener('click', handleApplicationNav, true);
	}

	function initStandaloneWaitlist() {
		bindAccessForms();
		bindWaitlistModal();
		bindWaitlistTriggers();
		bindWaitlistCaptureTriggers();

		window.__iahOpenWaitlist = openWaitlist;
		if (window.__iahWaitlistPending) {
			window.__iahWaitlistPending = false;
			openWaitlist();
		}

		if (shouldOpenWaitlistFromUrl()) {
			window.setTimeout(openWaitlist, 150);
		}
	}

	function refreshScrollbar() {
		if (!scrollWrap) {
			initScrollbar();
		}
		if (scrollTrack) {
			scrollTrack.classList.remove('invisible');
		}
		updateThumb();
	}

	function initSmoothScroll() {
		if (typeof Lenis === 'undefined' || lenisInstance) {
			return;
		}

		lenisInstance = new Lenis({
			autoRaf: false,
			lerp: 0.1,
			smoothWheel: true,
			wheelMultiplier: 1,
		});
		window.lenis = lenisInstance;

		function raf(time) {
			if (lenisInstance) {
				lenisInstance.raf(time);
			}
			requestAnimationFrame(raf);
		}
		requestAnimationFrame(raf);

		lenisInstance.on('scroll', updateScrollUi);
	}

	function startChromeLoop() {
		function tick() {
			updateScrollUi();
			requestAnimationFrame(tick);
		}
		requestAnimationFrame(tick);
	}

	function bindFrameLogos() {
		var home = (iacData && iacData.homeUrl) || '/';
		var frameRoot = root.querySelector('.pointer-events-none.fixed.inset-0');
		if (!frameRoot) {
			return;
		}
		frameRoot.querySelectorAll('a[href]').forEach(function (link) {
			link.href = home;
		});
	}

	function initAccountsDropdown() {
		try {
			if (typeof window.iacInitAccountsDropdown === 'function') {
				window.iacInitAccountsDropdown();
			}
		} catch (err) {
			if (typeof console !== 'undefined' && console.warn) {
				console.warn('[iac] accounts dropdown init failed', err);
			}
		}
	}


	initScrollbar();
	setupCtaMorph();
	initFooter();
	bindAccessForms();
	if (waitlistModal) {
		bindWaitlistModal();
	}
	bindWaitlistTriggers();
	bindWaitlistCaptureTriggers();
	bindFrameLogos();

	if (isGridPage) {
		initGridChrome();
	} else if (isHome) {
		updateHeaderSpread(false);
		lastHeaderSpread = false;
	} else {
		updateHeaderSpread(true);
		lastHeaderSpread = true;
		setFrameVisible(true);
	}
	updateScrollUi();
	initAccountsDropdown();
	initSmoothScroll();
	refreshScrollbar();
	startChromeLoop();

	if (shouldOpenWaitlistFromUrl()) {
		window.setTimeout(goToApplication, 150);
	}

	window.addEventListener('scroll', updateScrollUi, { passive: true });
	window.addEventListener('resize', refreshScrollbar, { passive: true });
	window.addEventListener('load', refreshScrollbar, { passive: true });
	document.addEventListener('scroll', updateScrollUi, { passive: true, capture: true });
	window.addEventListener('wheel', updateScrollUi, { passive: true });
	window.addEventListener('iap:preloader:done', function () {
		refreshScrollbar();
		if (isGridPage) {
			initGridChrome();
			updateHeaderSpread(true);
			lastHeaderSpread = true;
		}
		initSmoothScroll();
		initAccountsDropdown();
		updateScrollUi();
	}, { passive: true });

	window.requestAnimationFrame(refreshScrollbar);
	window.setTimeout(refreshScrollbar, 120);
	window.setTimeout(refreshScrollbar, 600);

	if ((iacData.isApplication === '1' || iacData.isContact === '1') && waitlistModal) {
		waitlistOpen = true;
		if (iacData.isApplication === '1') {
			window.setTimeout(function () {
				var firstInput = waitlistModal.querySelector('#iac-wl-firstName');
				if (firstInput) {
					firstInput.focus();
				}
			}, 150);
		}
	}
})();
