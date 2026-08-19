(function () {
	'use strict';

	function iconSvg(path) {
		return (
			'<svg viewBox="0 0 24 24" fill="none" class="iac-accounts-dropdown__icon size-5 shrink-0 text-muted" aria-hidden="true">' +
			'<path fill="currentColor" d="' +
			path +
			'"></path></svg>'
		);
	}

	function roundPath(value) {
		return Math.round(value * 1000) / 1000;
	}

	function curtainClipPath(curtainWidth, curtainHeight, cardHeight) {
		if (!curtainWidth || !curtainHeight || !cardHeight) {
			return '';
		}
		var n = (curtainHeight / cardHeight) * 40;
		var qx = n - (6 * n) / curtainHeight;
		var qy = curtainHeight - 6;
		if (!isFinite(n) || !isFinite(qx) || !isFinite(qy)) {
			return '';
		}
		return (
			'M 0 0 L ' +
			roundPath(curtainWidth) +
			' 0 L ' +
			roundPath(curtainWidth) +
			' ' +
			roundPath(curtainHeight) +
			' L ' +
			roundPath(n + 6) +
			' ' +
			roundPath(curtainHeight) +
			' L ' +
			roundPath(n) +
			' ' +
			roundPath(curtainHeight) +
			' Q ' +
			roundPath(n) +
			' ' +
			roundPath(curtainHeight) +
			' ' +
			roundPath(qx) +
			' ' +
			roundPath(qy) +
			' L 0 0 Z'
		);
	}

	function cardClipPath(cardWidth, cardHeight) {
		if (!cardWidth || !cardHeight) {
			return '';
		}
		var qx = 40 - 320 / cardHeight;
		var qy = cardHeight - 8;
		if (!isFinite(qx) || !isFinite(qy)) {
			return '';
		}
		return (
			'M 0 0 L ' +
			roundPath(cardWidth) +
			' 0 L ' +
			roundPath(cardWidth) +
			' ' +
			roundPath(cardHeight) +
			' L 48 ' +
			roundPath(cardHeight) +
			' Q 40 ' +
			roundPath(cardHeight) +
			' ' +
			roundPath(qx) +
			' ' +
			roundPath(qy) +
			' L 0 0 Z'
		);
	}

	function removeForeignDropdowns() {
		document.querySelectorAll('.iac-accounts-dropdown:not([data-iac-portal="1"])').forEach(function (el) {
			if (el.parentNode) {
				el.parentNode.removeChild(el);
			}
		});
	}

	function initAccountsDropdown() {
		if (document.documentElement.classList.contains('iah-home')) {
			return;
		}
		removeForeignDropdowns();

		var wrap = document.querySelector('.iac-accounts-wrap');
		var trigger = wrap ? wrap.querySelector('.iac-accounts-trigger, button[aria-haspopup="true"]') : null;
		if (!trigger || trigger.dataset.iacAccountsBound === '1') {
			return;
		}

		document.querySelectorAll('.iac-accounts-dropdown[data-iac-portal="1"]').forEach(function (el) {
			if (el.parentNode) {
				el.parentNode.removeChild(el);
			}
		});

		if (!wrap.querySelector('.iac-accounts-bridge')) {
			var bridge = document.createElement('div');
			bridge.className = 'iac-accounts-bridge pointer-events-none absolute top-full right-0';
			bridge.setAttribute('aria-hidden', 'true');
			wrap.appendChild(bridge);
		}
		var bridge = wrap.querySelector('.iac-accounts-bridge');

		var data = typeof iacData !== 'undefined' ? iacData : {};
		var strings = data.strings || {};
		function t(key, fallback) {
			return strings[key] || fallback;
		}
		var home = (data.homeUrl || '/').replace(/\/?$/, '/');
		var featureUrls = data.featureUrls || {};
		var items = [
			{
				key: 'platform',
				href: featureUrls.platform || home + 'accounts/platform-access/',
				label: t('platformAccess', 'Platform Access'),
				icon: iconSvg(
					'M20.25 7.125H17.625V4.5C17.625 4.00272 17.4275 3.52581 17.0758 3.17417C16.7242 2.82254 16.2473 2.625 15.75 2.625H3.75C3.25272 2.625 2.77581 2.82254 2.42417 3.17417C2.07254 3.52581 1.875 4.00272 1.875 4.5V16.5C1.87509 16.7123 1.93527 16.9203 2.04857 17.0998C2.16186 17.2794 2.32366 17.4232 2.51526 17.5147C2.70685 17.6062 2.92042 17.6416 3.13129 17.6168C3.34216 17.592 3.5417 17.5081 3.70688 17.3747L6.375 15.2184V17.25C6.375 17.7473 6.57254 18.2242 6.92417 18.5758C7.27581 18.9275 7.75272 19.125 8.25 19.125H16.8909L20.2931 21.8747C20.4583 22.0081 20.6578 22.092 20.8687 22.1168C21.0796 22.1416 21.2931 22.1062 21.4847 22.0147C21.6763 21.9232 21.8381 21.7794 21.9514 21.5998C22.0647 21.4203 22.1249 21.2123 22.125 21V9C22.125 8.50272 21.9275 8.02581 21.5758 7.67417C21.2242 7.32254 20.7473 7.125 20.25 7.125ZM4.125 14.1441V4.875H15.375V12.375H6.71063C6.45202 12.3743 6.20106 12.4627 6 12.6253L4.125 14.1441ZM19.875 18.6441L18 17.1253C17.7999 16.9635 17.5504 16.8751 17.2931 16.875H8.625V14.625H15.75C16.2473 14.625 16.7242 14.4275 17.0758 14.0758C17.4275 13.7242 17.625 13.2473 17.625 12.75V9.375H19.875V18.6441Z'
				),
			},
			{
				key: 'agency',
				href: featureUrls.agency || home + 'accounts/agency-accounts/',
				label: t('agencyAccounts', 'Agency Accounts'),
				icon: iconSvg(
					'M10.875 12.375V7.50001C10.875 7.20164 10.9935 6.91549 11.2045 6.70451C11.4155 6.49353 11.7016 6.37501 12 6.37501C12.2984 6.37501 12.5845 6.49353 12.7955 6.70451C13.0065 6.91549 13.125 7.20164 13.125 7.50001V12.375C13.125 12.6734 13.0065 12.9595 12.7955 13.1705C12.5845 13.3815 12.2984 13.5 12 13.5C11.7016 13.5 11.4155 13.3815 11.2045 13.1705C10.9935 12.9595 10.875 12.6734 10.875 12.375ZM22.125 8.58282V15.4172C22.1257 15.6635 22.0775 15.9075 21.9832 16.1351C21.8889 16.3626 21.7503 16.5692 21.5756 16.7428L16.7428 21.5756C16.5692 21.7504 16.3626 21.8889 16.1351 21.9832C15.9075 22.0775 15.6635 22.1257 15.4172 22.125H8.58282C8.3365 22.1257 8.09248 22.0775 7.86493 21.9832C7.63737 21.8889 7.4308 21.7504 7.25719 21.5756L2.42438 16.7428C2.24968 16.5692 2.11116 16.3626 2.01686 16.1351C1.92255 15.9075 1.87434 15.6635 1.87501 15.4172V8.58282C1.87434 8.33651 1.92255 8.09251 2.01686 7.86496C2.11116 7.63741 2.24968 7.43083 2.42438 7.2572L7.25719 2.42438C7.4308 2.24963 7.63737 2.11109 7.86493 2.01679C8.09248 1.92248 8.3365 1.87429 8.58282 1.87501H15.4172C15.6635 1.87429 15.9075 1.92248 16.1351 2.01679C16.3626 2.11109 16.5692 2.24963 16.7428 2.42438L21.5756 7.2572C21.7503 7.43083 21.8889 7.63741 21.9832 7.86496C22.0775 8.09251 22.1257 8.33651 22.125 8.58282ZM19.875 8.73845L15.2616 4.12501H8.73845L4.12501 8.73845V15.2616L8.73845 19.875H15.2616L19.875 15.2616V8.73845ZM12 14.625C11.7033 14.625 11.4133 14.713 11.1667 14.8778C10.92 15.0426 10.7277 15.2769 10.6142 15.551C10.5007 15.8251 10.471 16.1267 10.5288 16.4176C10.5867 16.7086 10.7296 16.9759 10.9393 17.1857C11.1491 17.3954 11.4164 17.5383 11.7074 17.5962C11.9983 17.6541 12.2999 17.6244 12.574 17.5108C12.8481 17.3973 13.0824 17.205 13.2472 16.9584C13.412 16.7117 13.5 16.4217 13.5 16.125C13.5 15.7272 13.342 15.3457 13.0607 15.0643C12.7794 14.783 12.3978 14.625 12 14.625Z'
				),
			},
			{
				key: 'team',
				href: featureUrls.team || home + 'accounts/team-supply/',
				label: t('teamSupply', 'Team Supply'),
				icon: iconSvg(
					'M4.82893 9.79593L1.82893 6.79593C1.72405 6.69141 1.64084 6.56722 1.58406 6.43047C1.52728 6.29373 1.49805 6.14712 1.49805 5.99905C1.49805 5.85099 1.52728 5.70438 1.58406 5.56763C1.64084 5.43089 1.72405 5.30669 1.82893 5.20218L4.82893 2.20218C4.93358 2.09753 5.05782 2.01452 5.19454 1.95788C5.33127 1.90125 5.47782 1.8721 5.62581 1.8721C5.7738 1.8721 5.92035 1.90125 6.05707 1.95788C6.1938 2.01452 6.31804 2.09753 6.42268 2.20218C6.52733 2.30682 6.61034 2.43106 6.66698 2.56779C6.72361 2.70451 6.75276 2.85106 6.75276 2.99905C6.75276 3.14705 6.72361 3.29359 6.66698 3.43032C6.61034 3.56705 6.52733 3.69128 6.42268 3.79593L4.21862 5.99999L6.42081 8.20405C6.63215 8.4154 6.75089 8.70204 6.75089 9.00093C6.75089 9.29981 6.63215 9.58646 6.42081 9.7978C6.20946 10.0091 5.92282 10.1279 5.62393 10.1279C5.32505 10.1279 5.0384 10.0091 4.82706 9.7978L4.82893 9.79593ZM9.32893 9.79593C9.43345 9.90081 9.55764 9.98402 9.69439 10.0408C9.83113 10.0976 9.97774 10.1268 10.1258 10.1268C10.2739 10.1268 10.4205 10.0976 10.5572 10.0408C10.694 9.98402 10.8182 9.90081 10.9227 9.79593L13.9227 6.79593C14.0276 6.69141 14.1108 6.56722 14.1676 6.43047C14.2243 6.29373 14.2536 6.14712 14.2536 5.99905C14.2536 5.85099 14.2243 5.70438 14.1676 5.56763C14.1108 5.43089 14.0276 5.30669 13.9227 5.20218L10.9227 2.20218C10.7113 1.99083 10.4247 1.8721 10.1258 1.8721C9.82692 1.8721 9.54028 1.99083 9.32893 2.20218C9.11759 2.41352 8.99886 2.70017 8.99886 2.99905C8.99886 3.29794 9.11759 3.58458 9.32893 3.79593L11.5311 5.99999L9.32893 8.20405C9.22434 8.30853 9.14136 8.43261 9.08474 8.56918C9.02813 8.70575 8.99899 8.85215 8.99899 8.99999C8.99899 9.14783 9.02813 9.29422 9.08474 9.4308C9.14136 9.56737 9.22434 9.69144 9.32893 9.79593ZM18.7499 3.37499H16.8749C16.5765 3.37499 16.2904 3.49352 16.0794 3.70449C15.8684 3.91547 15.7499 4.20162 15.7499 4.49999C15.7499 4.79836 15.8684 5.08451 16.0794 5.29549C16.2904 5.50646 16.5765 5.62499 16.8749 5.62499H18.3749V18.375H5.62487V13.125C5.62487 12.8266 5.50635 12.5405 5.29537 12.3295C5.08439 12.1185 4.79824 12 4.49987 12C4.2015 12 3.91535 12.1185 3.70438 12.3295C3.4934 12.5405 3.37487 12.8266 3.37487 13.125V18.75C3.37487 19.2473 3.57242 19.7242 3.92405 20.0758C4.27568 20.4274 4.75259 20.625 5.24987 20.625H18.7499C19.2472 20.625 19.7241 20.4274 20.0757 20.0758C20.4273 19.7242 20.6249 19.2473 20.6249 18.75V5.24999C20.6249 4.75271 20.4273 4.2758 20.0757 3.92416C19.7241 3.57253 19.2472 3.37499 18.7499 3.37499Z'
				),
			},
		];

		var portal = document.createElement('div');
		portal.className = 'iac-accounts-dropdown pointer-events-none fixed inset-x-0 top-0 notranslate';
		portal.style.zIndex = '95';
		portal.setAttribute('data-iac-portal', '1');
		portal.setAttribute('aria-hidden', 'true');

		var dropdownBg = '#0a110d';

		var portalDefs = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
		portalDefs.setAttribute('width', '0');
		portalDefs.setAttribute('height', '0');
		portalDefs.setAttribute('aria-hidden', 'true');
		portalDefs.style.position = 'absolute';
		var portalDefsRoot = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
		portalDefs.appendChild(portalDefsRoot);

		var curtainClipId = 'iac-curtain-clip-' + Math.random().toString(36).slice(2, 9);
		var cardClipId = 'iac-card-clip-' + Math.random().toString(36).slice(2, 9);
		var curtainClipEl = document.createElementNS('http://www.w3.org/2000/svg', 'clipPath');
		curtainClipEl.setAttribute('id', curtainClipId);
		var curtainClipPathEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
		curtainClipEl.appendChild(curtainClipPathEl);
		portalDefsRoot.appendChild(curtainClipEl);
		var cardClipEl = document.createElementNS('http://www.w3.org/2000/svg', 'clipPath');
		cardClipEl.setAttribute('id', cardClipId);
		var cardClipPathEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
		cardClipEl.appendChild(cardClipPathEl);
		portalDefsRoot.appendChild(cardClipEl);

		var hitbox = document.createElement('div');
		hitbox.className = 'iac-accounts-dropdown__hitbox pointer-events-none absolute';
		hitbox.style.width = '0';
		hitbox.style.height = '0';
		hitbox.setAttribute('aria-hidden', 'true');

		var panel = document.createElement('div');
		panel.className = 'iac-accounts-dropdown__panel';
		panel.style.display = 'none';
		panel.setAttribute('role', 'menu');

		var curtain = document.createElement('div');
		curtain.className =
			'iac-accounts-dropdown__curtain bg-dropdown pointer-events-auto h-[calc(var(--spacing-header)+0.5em)] transition-transform duration-400 ease-[cubic-bezier(0.33,1,0.68,1)]';
		curtain.style.backgroundColor = dropdownBg;

		var body = document.createElement('div');
		body.className = 'iac-accounts-dropdown__body relative';

		var cornerSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
		cornerSvg.setAttribute('class', 'iac-accounts-dropdown__corner pointer-events-none absolute top-0');
		cornerSvg.setAttribute('aria-hidden', 'true');
		var cornerPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
		cornerPath.setAttribute('fill', 'var(--color-dropdown, #0a110d)');
		cornerSvg.appendChild(cornerPath);

		var card = document.createElement('div');
		card.className =
			'iac-accounts-dropdown__card bg-dropdown pointer-events-auto relative pr-6 shadow-[0_-2px_0_var(--color-dropdown)]';
		card.style.backgroundColor = dropdownBg;
		card.setAttribute('data-dropdown-card', 'true');

		var accent = document.createElement('span');
		accent.className = 'iac-accounts-dropdown__accent pointer-events-none absolute opacity-0 transition-opacity duration-200';
		accent.setAttribute('aria-hidden', 'true');

		var list = document.createElement('div');
		list.className = 'iac-accounts-dropdown__items relative flex w-max flex-col py-2';
		var menuLinks = [];

		items.forEach(function (item, index) {
			var link = document.createElement('a');
			link.className =
				'iac-accounts-dropdown__item group/item relative flex items-center gap-2 rounded-l-lg rounded-r-md bg-transparent pr-3 transition-[background-color] duration-200 hover:bg-white/[0.04]';
			link.href = item.href;
			link.setAttribute('role', 'menuitem');
			link.style.height = '48px';
			link.dataset.index = String(index);
			link.innerHTML =
				item.icon +
				'<span class="iac-accounts-dropdown__label font-misc text-sm tracking-wider uppercase transition-colors duration-200 text-muted-foreground group-hover/item:text-foreground">' +
				item.label +
				'</span>';

			link.addEventListener('mouseenter', function () {
				moveAccent(link, index);
			});
			link.addEventListener('mouseleave', function () {
				accent.style.opacity = '0';
			});

			list.appendChild(link);
			menuLinks.push(link);
		});

		var version = document.createElement('span');
		version.className =
			'iac-accounts-dropdown__version font-misc text-muted pointer-events-none absolute right-4 bottom-4 text-[10px] tracking-wider uppercase opacity-50';
		version.textContent = 'impact features v0.1';

		var fade = document.createElement('div');
		fade.className = 'from-dropdown pointer-events-none absolute right-0 bottom-0 h-10 w-1/2 bg-gradient-to-t to-transparent';

		list.insertBefore(accent, list.firstChild);
		card.appendChild(list);
		card.appendChild(version);
		card.appendChild(fade);
		body.appendChild(cornerSvg);
		body.appendChild(card);
		panel.appendChild(curtain);
		panel.appendChild(body);
		portal.appendChild(portalDefs);
		portal.appendChild(hitbox);
		portal.appendChild(panel);
		document.body.appendChild(portal);
		portal.style.display = 'none';

		var dropdownOpen = false;
		var closeTimer = null;
		var triggerLeft = 0;
		var accentAngle = 0;
		var dropTimeline = null;

		function buildDropTimeline() {
			if (typeof gsap === 'undefined') {
				return null;
			}
			if (dropTimeline) {
				dropTimeline.kill();
				dropTimeline = null;
			}
			panel.classList.add('iac-accounts-dropdown__panel--gsap');
			gsap.set(panel, { yPercent: 0, visibility: 'visible' });
			gsap.set(menuLinks, { opacity: 1, x: 0 });
			dropTimeline = gsap.timeline({ paused: true });
			dropTimeline.set(panel, { visibility: 'visible' });
			dropTimeline.from(panel, { yPercent: -100, duration: 0.4, ease: 'power3.out' });
			dropTimeline.from(
				menuLinks,
				{ opacity: 0, x: -8, duration: 0.3, stagger: 0.05, ease: 'power2.out' },
				'-=0.2'
			);
			dropTimeline.eventCallback('onComplete', function () {
				showDropdownItems();
				updateDropdownLayout();
			});
			dropTimeline.eventCallback('onReverseComplete', function () {
				finalizeClose();
			});
			return dropTimeline;
		}

		function showDropdownItems() {
			panel.classList.add('iac-accounts-dropdown__panel--visible');
			menuLinks.forEach(function (link) {
				link.classList.add('iac-accounts-dropdown__item--visible');
				link.style.transitionDelay = '';
			});
		}

		function hideDropdownItems() {
			panel.classList.remove('iac-accounts-dropdown__panel--visible');
			menuLinks.forEach(function (link) {
				link.classList.remove('iac-accounts-dropdown__item--visible');
				link.style.transitionDelay = '';
			});
		}

		function runOpenAnimation(retryCount) {
			panel.style.display = '';
			panel.style.visibility = 'visible';
			updateDropdownLayout();

			if (!hasDropdownBounds() && (retryCount || 0) < 16) {
				requestAnimationFrame(function () {
					runOpenAnimation((retryCount || 0) + 1);
				});
				return;
			}

			updateDropdownLayout();
			hideDropdownItems();

			if (typeof gsap !== 'undefined') {
				buildDropTimeline();
				if (dropTimeline) {
					if (dropTimeline.isActive() && dropTimeline.reversed()) {
						dropTimeline.pause(dropTimeline.duration());
						dropTimeline.reversed(false);
					}
					dropTimeline.play(0);
				} else {
					panel.classList.remove('iac-accounts-dropdown__panel--gsap');
					showDropdownItems();
				}
			} else {
				panel.classList.remove('iac-accounts-dropdown__panel--gsap');
				showDropdownItems();
			}

			if (menuLinks[0]) {
				moveAccent(menuLinks[0], 0);
			}
		}

		function getCurtainShift() {
			var root = document.getElementById('iac-root');
			if (document.documentElement.classList.contains('iah-home')) {
				return root && root.classList.contains('iac-frame-visible')
					? 'translateX(50%)'
					: 'translateX(33%)';
			}
			return 'translateX(50%)';
		}

		function hasDropdownBounds() {
			return (
				card.offsetWidth > 0 &&
				card.offsetHeight > 0 &&
				curtain.offsetWidth > 0 &&
				curtain.offsetHeight > 0
			);
		}

		function moveAccent(link, index) {
			var cardHeight = card.offsetHeight;
			if (!link || !cardHeight) {
				return;
			}
			var slantIndex = 8 + 48 * index;
			var accentTop = slantIndex + 4;
			var accentLeft = (40 / cardHeight) * accentTop;
			accent.style.opacity = '1';
			accent.style.top = accentTop + 'px';
			accent.style.left = accentLeft + 'px';
			accent.style.height = '40px';
			accent.style.transform = 'rotate(' + accentAngle + 'deg)';
		}

		function updateDropdownLayout() {
			if (!wrap || window.matchMedia('(max-width: 1023px)').matches) {
				return;
			}
			triggerLeft = trigger.getBoundingClientRect().left;
			body.style.marginLeft = triggerLeft - 16 + 'px';

			var cardWidth = card.offsetWidth;
			var cardHeight = card.offsetHeight;
			var curtainWidth = curtain.offsetWidth;
			var curtainHeight = curtain.offsetHeight;

			if (cardWidth && cardHeight) {
				cardClipPathEl.setAttribute('d', cardClipPath(cardWidth, cardHeight));
				card.style.clipPath = 'url(#' + cardClipId + ')';
				card.style.webkitClipPath = 'url(#' + cardClipId + ')';
				accentAngle = -(180 / Math.PI) * Math.atan(40 / cardHeight);
				menuLinks.forEach(function (link, index) {
					var slantIndex = 8 + 48 * index;
					var slantPadding = (40 / cardHeight) * (slantIndex + 48);
					link.style.paddingLeft = 'calc(' + slantPadding + 'px + 1rem)';
				});
				var cornerDiag = Math.sqrt(1600 + cardHeight * cardHeight);
				var cornerW = 8 + 320 / cornerDiag;
				var cornerH = (8 * cardHeight) / cornerDiag;
				cornerPath.setAttribute('d', 'M 8,0 L 0,0 Q 8,0 ' + cornerW + ',' + cornerH + ' Z');
				cornerSvg.setAttribute('width', String(cornerW));
				cornerSvg.setAttribute('height', String(cornerH));
				cornerSvg.setAttribute('viewBox', '0 0 ' + cornerW + ' ' + cornerH);
				cornerSvg.style.left = '-8px';
			}

			if (curtainWidth && curtainHeight && cardHeight) {
				var curtainPath = curtainClipPath(curtainWidth, curtainHeight, cardHeight);
				if (curtainPath) {
					curtainClipPathEl.setAttribute('d', curtainPath);
					curtain.style.clipPath = 'url(#' + curtainClipId + ')';
					curtain.style.webkitClipPath = 'url(#' + curtainClipId + ')';
				} else {
					curtain.style.clipPath = '';
					curtain.style.webkitClipPath = '';
				}
			} else {
				curtain.style.clipPath = '';
				curtain.style.webkitClipPath = '';
			}

			curtain.style.transform = getCurtainShift();

			if (dropdownOpen) {
				hitbox.classList.add('iac-accounts-dropdown__hitbox--active');
				hitbox.style.left = triggerLeft - 16 + 'px';
				hitbox.style.top = '0';
				hitbox.style.width = cardWidth + 56 + 'px';
				hitbox.style.height = curtainHeight + cardHeight + 'px';
			} else {
				clearHitbox();
			}
		}

		function clearHitbox() {
			hitbox.classList.remove('iac-accounts-dropdown__hitbox--active');
			hitbox.style.width = '0';
			hitbox.style.height = '0';
			hitbox.style.left = '';
			hitbox.style.top = '';
		}

		function finalizeClose() {
			dropdownOpen = false;
			setBridgeOpen(false);
			portal.classList.remove('iac-accounts-dropdown--open');
			portal.style.display = 'none';
			portal.setAttribute('aria-hidden', 'true');
			trigger.setAttribute('aria-expanded', 'false');
			clearHitbox();
			panel.style.display = 'none';
			panel.classList.remove('iac-accounts-dropdown__panel--visible', 'iac-accounts-dropdown__panel--gsap');
			card.style.clipPath = '';
			card.style.webkitClipPath = '';
			curtain.style.clipPath = '';
			curtain.style.webkitClipPath = '';
			accent.style.opacity = '0';
			menuLinks.forEach(function (link) {
				link.classList.remove('iac-accounts-dropdown__item--visible');
				link.style.transitionDelay = '';
			});
			if (dropTimeline) {
				dropTimeline.kill();
				dropTimeline = null;
			}
			if (typeof gsap !== 'undefined') {
				gsap.set(panel, { clearProps: 'all' });
				gsap.set(menuLinks, { clearProps: 'all' });
			}
		}

		function setBridgeOpen(open) {
			if (!bridge) {
				return;
			}
			bridge.classList.toggle('iac-accounts-bridge--open', open);
			bridge.setAttribute('aria-hidden', open ? 'false' : 'true');
		}

		function setDropdownOpen(open) {
			if (dropdownOpen === open) {
				return;
			}
			if (!open) {
				if (typeof gsap !== 'undefined' && dropTimeline && dropTimeline.progress() > 0) {
					dropdownOpen = false;
					trigger.setAttribute('aria-expanded', 'false');
					setBridgeOpen(false);
					clearHitbox();
					hideDropdownItems();
					dropTimeline.reverse();
					return;
				}
				finalizeClose();
				return;
			}

			dropdownOpen = true;
			setBridgeOpen(true);
			portal.style.display = 'block';
			portal.classList.add('iac-accounts-dropdown--open');
			portal.setAttribute('aria-hidden', 'false');
			trigger.setAttribute('aria-expanded', 'true');
			runOpenAnimation(0);
		}

		function openDropdown() {
			if (closeTimer) {
				window.clearTimeout(closeTimer);
				closeTimer = null;
			}
			if (window.matchMedia('(max-width: 1023px)').matches) {
				return;
			}
			if (typeof gsap !== 'undefined' && dropTimeline && dropTimeline.isActive() && dropTimeline.reversed()) {
				dropTimeline.pause(dropTimeline.duration());
				dropTimeline.reversed(false);
			}
			setDropdownOpen(true);
		}

		function scheduleClose() {
			if (closeTimer) {
				window.clearTimeout(closeTimer);
			}
			closeTimer = window.setTimeout(function () {
				closeTimer = null;
				setDropdownOpen(false);
			}, 250);
		}

		wrap.addEventListener('mouseenter', openDropdown);
		wrap.addEventListener('mouseleave', scheduleClose);
		portal.addEventListener('mouseenter', openDropdown);
		portal.addEventListener('mouseleave', scheduleClose);

		trigger.addEventListener('click', function () {
			if (window.matchMedia('(max-width: 1023px)').matches) {
				return;
			}
			if (typeof gsap !== 'undefined' && dropTimeline && dropTimeline.isActive()) {
				return;
			}
			setDropdownOpen(!dropdownOpen);
		});

		window.addEventListener('resize', updateDropdownLayout, { passive: true });

		trigger.dataset.iacAccountsBound = '1';
	}

	function resetAccountsDropdown() {
		if (document.documentElement.classList.contains('iah-home')) {
			return;
		}
		document.querySelectorAll('.iac-accounts-dropdown[data-iac-portal="1"]').forEach(function (el) {
			if (el.parentNode) {
				el.parentNode.removeChild(el);
			}
		});
		document.querySelectorAll('.iac-accounts-trigger').forEach(function (trigger) {
			trigger.dataset.iacAccountsBound = '';
		});
		removeForeignDropdowns();
		initAccountsDropdown();
	}

	function bootAccountsDropdown() {
		if (document.documentElement.classList.contains('iah-home')) {
			return;
		}
		initAccountsDropdown();
	}

	window.iacInitAccountsDropdown = initAccountsDropdown;
	window.iacResetAccountsDropdown = resetAccountsDropdown;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bootAccountsDropdown);
	} else {
		bootAccountsDropdown();
	}

	window.addEventListener(
		'iap:preloader:done',
		function () {
			resetAccountsDropdown();
		},
		{ passive: true }
	);
})();
