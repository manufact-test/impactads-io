(function () {
	'use strict';

	var gsap = window.gsap;
	if (!gsap) {
		return;
	}

	var isHomeMirror = document.documentElement.classList.contains('iah-home');
	var root = document.getElementById('iac-root');
	if (!root && !isHomeMirror) {
		return;
	}

	var scope = root || document;
	var menuBtn = scope.querySelector('header .lg\\:hidden button');
	if (!menuBtn) {
		return;
	}

	var overlay = null;
	var panelEl = null;
	var trackEl = null;
	var menuTimeline = null;
	var open = false;
	var subOpen = false;
	var touchStart = null;
	var touchDragging = false;

	var CORNER_OFFSETS = [
		{ x: -1, y: -1 },
		{ x: 1, y: -1 },
		{ x: -1, y: 1 },
		{ x: 1, y: 1 },
	];

	function data() {
		return typeof iacData !== 'undefined' ? iacData : {};
	}

	function t(key, fallback) {
		var strings = data().strings || {};
		return strings[key] || fallback;
	}

	function home() {
		return (data().homeUrl || '/').replace(/\/?$/, '/');
	}

	function featureUrl(key, fallback) {
		var features = data().featureUrls || {};
		return features[key] || home() + fallback;
	}

	function mainLinks() {
		return [
			{ href: data().aboutUrl || home() + 'about/', label: t('about', 'About') },
			{ href: data().blogUrl || home() + 'blog/', label: t('blog', 'Blog') },
			{ href: data().contactUrl || home() + 'contact/', label: t('contact', 'Contact') },
			{ label: t('accounts', 'Accounts'), dropdown: true },
			{ href: data().applicationUrl || home() + 'application/', label: t('requestAccess', 'Request access'), accent: true },
		];
	}

	function iconSvg(path) {
		return (
			'<svg viewBox="0 0 24 24" fill="none" class="iac-mobile-menu__sub-icon" aria-hidden="true">' +
			'<path fill="currentColor" d="' +
			path +
			'"></path></svg>'
		);
	}

	var ACCOUNT_ICONS = {
		platform:
			'M20.25 7.125H17.625V4.5C17.625 4.00272 17.4275 3.52581 17.0758 3.17417C16.7242 2.82254 16.2473 2.625 15.75 2.625H3.75C3.25272 2.625 2.77581 2.82254 2.42417 3.17417C2.07254 3.52581 1.875 4.00272 1.875 4.5V16.5C1.87509 16.7123 1.93527 16.9203 2.04857 17.0998C2.16186 17.2794 2.32366 17.4232 2.51526 17.5147C2.70685 17.6062 2.92042 17.6416 3.13129 17.6168C3.34216 17.592 3.5417 17.5081 3.70688 17.3747L6.375 15.2184V17.25C6.375 17.7473 6.57254 18.2242 6.92417 18.5758C7.27581 18.9275 7.75272 19.125 8.25 19.125H16.8909L20.2931 21.8747C20.4583 22.0081 20.6578 22.092 20.8687 22.1168C21.0796 22.1416 21.2931 22.1062 21.4847 22.0147C21.6763 21.9232 21.8381 21.7794 21.9514 21.5998C22.0647 21.4203 22.1249 21.2123 22.125 21V9C22.125 8.50272 21.9275 8.02581 21.5758 7.67417C21.2242 7.32254 20.7473 7.125 20.25 7.125ZM4.125 14.1441V4.875H15.375V12.375H6.71063C6.45202 12.3743 6.20106 12.4627 6 12.6253L4.125 14.1441ZM19.875 18.6441L18 17.1253C17.7999 16.9635 17.5504 16.8751 17.2931 16.875H8.625V14.625H15.75C16.2473 14.625 16.7242 14.4275 17.0758 14.0758C17.4275 13.7242 17.625 13.2473 17.625 12.75V9.375H19.875V18.6441Z',
		agency:
			'M10.875 12.375V7.50001C10.875 7.20164 10.9935 6.91549 11.2045 6.70451C11.4155 6.49353 11.7016 6.37501 12 6.37501C12.2984 6.37501 12.5845 6.49353 12.7955 6.70451C13.0065 6.91549 13.125 7.20164 13.125 7.50001V12.375C13.125 12.6734 13.0065 12.9595 12.7955 13.1705C12.5845 13.3815 12.2984 13.5 12 13.5C11.7016 13.5 11.4155 13.3815 11.2045 13.1705C10.9935 12.9595 10.875 12.6734 10.875 12.375ZM22.125 8.58282V15.4172C22.1257 15.6635 22.0775 15.9075 21.9832 16.1351C21.8889 16.3626 21.7503 16.5692 21.5756 16.7428L16.7428 21.5756C16.5692 21.7504 16.3626 21.8889 16.1351 21.9832C15.9075 22.0775 15.6635 22.1257 15.4172 22.125H8.58282C8.3365 22.1257 8.09248 22.0775 7.86493 21.9832C7.63737 21.8889 7.4308 21.7504 7.25719 21.5756L2.42438 16.7428C2.24968 16.5692 2.11116 16.3626 2.01686 16.1351C1.92255 15.9075 1.87434 15.6635 1.87501 15.4172V8.58282C1.87434 8.33651 1.92255 8.09251 2.01686 7.86496C2.11116 7.63741 2.24968 7.43083 2.42438 7.2572L7.25719 2.42438C7.4308 2.24963 7.63737 2.11109 7.86493 2.01679C8.09248 1.92248 8.3365 1.87429 8.58282 1.87501H15.4172C15.6635 1.87429 15.9075 1.92248 16.1351 2.01679C16.3626 2.11109 16.5692 2.24963 16.7428 2.42438L21.5756 7.2572C21.7503 7.43083 21.8889 7.63741 21.9832 7.86496C22.0775 8.09251 22.1257 8.33651 22.125 8.58282ZM19.875 8.73845L15.2616 4.12501H8.73845L4.12501 8.73845V15.2616L8.73845 19.875H15.2616L19.875 15.2616V8.73845ZM12 14.625C11.7033 14.625 11.4133 14.713 11.1667 14.8778C10.92 15.0426 10.7277 15.2769 10.6142 15.551C10.5007 15.8251 10.471 16.1267 10.5288 16.4176C10.5867 16.7086 10.7296 16.9759 10.9393 17.1857C11.1491 17.3954 11.4164 17.5383 11.7074 17.5962C11.9983 17.6541 12.2999 17.6244 12.574 17.5108C12.8481 17.3973 13.0824 17.205 13.2472 16.9584C13.412 16.7117 13.5 16.4217 13.5 16.125C13.5 15.7272 13.342 15.3457 13.0607 15.0643C12.7794 14.783 12.3978 14.625 12 14.625Z',
		team:
			'M4.82893 9.79593L1.82893 6.79593C1.72405 6.69141 1.64084 6.56722 1.58406 6.43047C1.52728 6.29373 1.49805 6.14712 1.49805 5.99905C1.49805 5.85099 1.52728 5.70438 1.58406 5.56763C1.64084 5.43089 1.72405 5.30669 1.82893 5.20218L4.82893 2.20218C4.93358 2.09753 5.05782 2.01452 5.19454 1.95788C5.33127 1.90125 5.47782 1.8721 5.62581 1.8721C5.7738 1.8721 5.92035 1.90125 6.05707 1.95788C6.1938 2.01452 6.31804 2.09753 6.42268 2.20218C6.52733 2.30682 6.61034 2.43106 6.66698 2.56779C6.72361 2.70451 6.75276 2.85106 6.75276 2.99905C6.75276 3.14705 6.72361 3.29359 6.66698 3.43032C6.61034 3.56705 6.52733 3.69128 6.42268 3.79593L4.21862 5.99999L6.42081 8.20405C6.63215 8.4154 6.75089 8.70204 6.75089 9.00093C6.75089 9.29981 6.63215 9.58646 6.42081 9.7978C6.20946 10.0091 5.92282 10.1279 5.62393 10.1279C5.32505 10.1279 5.0384 10.0091 4.82706 9.7978L4.82893 9.79593ZM9.32893 9.79593C9.43345 9.90081 9.55764 9.98402 9.69439 10.0408C9.83113 10.0976 9.97774 10.1268 10.1258 10.1268C10.2739 10.1268 10.4205 10.0976 10.5572 10.0408C10.694 9.98402 10.8182 9.90081 10.9227 9.79593L13.9227 6.79593C14.0276 6.69141 14.1108 6.56722 14.1676 6.43047C14.2243 6.29373 14.2536 6.14712 14.2536 5.99905C14.2536 5.85099 14.2243 5.70438 14.1676 5.56763C14.1108 5.43089 14.0276 5.30669 13.9227 5.20218L10.9227 2.20218C10.7113 1.99083 10.4247 1.8721 10.1258 1.8721C9.82692 1.8721 9.54028 1.99083 9.32893 2.20218C9.11759 2.41352 8.99886 2.70017 8.99886 2.99905C8.99886 3.29794 9.11759 3.58458 9.32893 3.79593L11.5311 5.99999L9.32893 8.20405C9.22434 8.30853 9.14136 8.43261 9.08474 8.56918C9.02813 8.70575 8.99899 8.85215 8.99899 8.99999C8.99899 9.14783 9.02813 9.29422 9.08474 9.4308C9.14136 9.56737 9.22434 9.69144 9.32893 9.79593ZM18.7499 3.37499H16.8749C16.5765 3.37499 16.2904 3.49352 16.0794 3.70449C15.8684 3.91547 15.7499 4.20162 15.7499 4.49999C15.7499 4.79836 15.8684 5.08451 16.0794 5.29549C16.2904 5.50646 16.5765 5.62499 16.8749 5.62499H18.3749V18.375H5.62487V13.125C5.62487 12.8266 5.50635 12.5405 5.29537 12.3295C5.08439 12.1185 4.79824 12 4.49987 12C4.2015 12 3.91535 12.1185 3.70438 12.3295C3.4934 12.5405 3.37487 12.8266 3.37487 13.125V18.75C3.37487 19.2473 3.57242 19.7242 3.92405 20.0758C4.27568 20.4274 4.75259 20.625 5.24987 20.625H18.7499C19.2472 20.625 19.7241 20.4274 20.0757 20.0758C20.4273 19.7242 20.6249 19.2473 20.6249 18.75V5.24999C20.6249 4.75271 20.4273 4.2758 20.0757 3.92416C19.7241 3.57253 19.2472 3.37499 18.7499 3.37499Z',
	};

	function accountLinks() {
		return [
			{ key: 'platform', href: featureUrl('platform', 'accounts/platform-access/'), label: t('platformAccess', 'Platform Access') },
			{ key: 'agency', href: featureUrl('agency', 'accounts/agency-accounts/'), label: t('agencyAccounts', 'Agency Accounts') },
			{ key: 'team', href: featureUrl('team', 'accounts/team-supply/'), label: t('teamSupply', 'Team Supply') },
		];
	}

	function el(tag, className, text) {
		var node = document.createElement(tag);
		if (className) {
			node.className = className;
		}
		if (text) {
			node.textContent = text;
		}
		return node;
	}

	function scrambleFrame(text, chars, revealCount) {
		var out = '';
		for (var i = 0; i < text.length; i++) {
			if (text.charAt(i) === ' ') {
				out += ' ';
			} else if (i < revealCount) {
				out += text.charAt(i);
			} else {
				out += chars.charAt(Math.floor(Math.random() * chars.length));
			}
		}
		return out;
	}

	function animateTextScramble(element, config) {
		var text = config.text || element.getAttribute('data-scramble') || element.textContent || '';
		var chars = config.chars || '\u25A0\u25AA\u258C\u2590\u258C';
		var duration = config.duration || 0.8;
		var revealDelay = config.revealDelay || 0.3;
		var revealStart = revealDelay / duration;
		var state = { p: 0 };

		return gsap.to(state, {
			p: 1,
			duration: duration,
			ease: config.ease || 'none',
			onUpdate: function () {
				var prog = state.p;
				if (prog <= revealStart) {
					element.textContent = scrambleFrame(text, chars, 0);
					return;
				}
				var count = Math.ceil(((prog - revealStart) / (1 - revealStart)) * text.length);
				element.textContent = scrambleFrame(text, chars, count);
			},
			onComplete: function () {
				element.textContent = text;
			},
		});
	}

	function appendCorners(parent) {
		var border = '1.5px solid currentColor';
		var specs = [
			{ top: '-5px', left: '-5px', borderTop: border, borderLeft: border, borderTopLeftRadius: '1.5px' },
			{ top: '-5px', right: '-5px', borderTop: border, borderRight: border, borderTopRightRadius: '1.5px' },
			{ bottom: '-5px', left: '-5px', borderBottom: border, borderLeft: border, borderBottomLeftRadius: '1.5px' },
			{ bottom: '-5px', right: '-5px', borderBottom: border, borderRight: border, borderBottomRightRadius: '1.5px' },
		];

		specs.forEach(function (style, index) {
			var span = el('span');
			span.setAttribute('data-corner', String(index));
			span.className = 'iac-mobile-menu__corner';
			Object.keys(style).forEach(function (key) {
				span.style[key] = style[key];
			});
			parent.appendChild(span);
		});
	}

	function buildCrosshairNode(extraClass) {
		var node = el('div', 'iac-mobile-menu__crosshair' + (extraClass ? ' ' + extraClass : ''));
		node.setAttribute('data-crosshair', '');
		[
			'iac-mobile-menu__crosshair-q1',
			'iac-mobile-menu__crosshair-q2',
			'iac-mobile-menu__crosshair-q3',
			'iac-mobile-menu__crosshair-q4',
		].forEach(function (cls) {
			node.appendChild(el('div', cls));
		});
		return node;
	}

	function buildCrosshairLeft() {
		var wrap = el('div', 'iac-mobile-menu__crosshair-wrap iac-mobile-menu__crosshair-wrap--left');
		wrap.appendChild(buildCrosshairNode());
		return wrap;
	}

	function buildCrosshairRight() {
		var wrap = el('div', 'iac-mobile-menu__crosshair-wrap iac-mobile-menu__crosshair-wrap--right');
		wrap.appendChild(buildCrosshairNode());
		return wrap;
	}

	function buildFooterShape() {
		var wrap = el('div', 'iac-mobile-menu__footer-shape');
		wrap.innerHTML =
			'<svg width="100%" viewBox="0 0 1978 322" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMax meet" aria-hidden="true">' +
			'<path d="M1977.5 195.547C1977.5 210.847 1966.51 223.935 1951.43 226.574L1437.22 316.63C1423.6 319.014 1410.02 312.243 1403.73 299.933L1362.16 218.553C1356.6 207.681 1345.42 200.839 1333.21 200.839H646.33C634.122 200.839 622.943 207.681 617.389 218.553L575.808 299.943C569.521 312.249 555.949 319.021 542.337 316.644L26.5811 226.565C11.5032 223.932 0.500017 210.841 0.5 195.535V0.503906H1977.5V195.547Z" fill="url(#iac-menu-footer-fill)" stroke="url(#iac-menu-footer-stroke)" stroke-width="4"></path>' +
			'<defs><linearGradient id="iac-menu-footer-fill" x1="513.797" y1="321.183" x2="513.797" y2="-316.693" gradientUnits="userSpaceOnUse"><stop stop-color="#4D0308"></stop><stop offset="1" stop-color="#4D0308"></stop></linearGradient>' +
			'<linearGradient id="iac-menu-footer-stroke" x1="989" y1="321.183" x2="989" y2="16.9003" gradientUnits="userSpaceOnUse"><stop stop-color="#FF0027"></stop><stop offset="1" stop-color="#990017" stop-opacity="0"></stop></linearGradient></defs></svg>';
		return wrap;
	}

	function bindHomeLogo() {
		var logo = scope.querySelector('.pointer-events-none.fixed.inset-0 > a.lg\\:hidden[href]');
		if (logo) {
			logo.href = home();
		}
	}

	function buildOverlay() {
		overlay = el('div', 'iac-mobile-menu');
		overlay.id = 'iac-mobile-menu';
		overlay.setAttribute('aria-hidden', 'true');
		overlay.style.display = 'none';

		panelEl = el('div', 'iac-mobile-menu__panel');
		panelEl.addEventListener('click', function (event) {
			event.stopPropagation();
		});
		panelEl.addEventListener('touchstart', onTouchStart, { passive: true });
		panelEl.addEventListener('touchmove', onTouchMove, { passive: true });
		panelEl.addEventListener('touchend', onTouchEnd, { passive: true });

		var mainMenu = el('div', 'iac-mobile-menu__main');
		mainMenu.setAttribute('data-main-menu', '');

		mainMenu.appendChild(buildCrosshairLeft());
		mainMenu.appendChild(buildCrosshairRight());

		var backBtn = el('button', 'iac-mobile-menu__back');
		backBtn.type = 'button';
		backBtn.innerHTML =
			'<span class="iac-mobile-menu__back-badge">' +
			'<svg class="iac-mobile-menu__back-arrow" width="6" height="4" viewBox="0 0 6 4" aria-hidden="true"><polygon points="0,0 6,0 3,4"></polygon></svg>' +
			t('backToList', 'Back to list') + '</span>';
		backBtn.addEventListener('click', function () {
			setSubOpen(false);
		});

		var trackWrap = el('div', 'iac-mobile-menu__track-wrap');
		trackEl = el('div', 'iac-mobile-menu__track');

		var mainWrap = el('div', 'iac-mobile-menu__links');
		mainWrap.setAttribute('data-main-links', '');
		var list = el('ul', 'iac-mobile-menu__list');

		mainLinks().forEach(function (item) {
			var li = el('li', 'iac-mobile-menu__item' + (item.accent ? ' iac-mobile-menu__item--accent' : ''));
			if (item.dropdown) {
				var btn = el('button', 'iac-mobile-menu__accounts-trigger', item.label);
				btn.type = 'button';
				btn.addEventListener('click', function () {
					setSubOpen(true);
				});
				li.appendChild(btn);
			} else {
				var link = el('a', 'iac-mobile-menu__link', item.label);
				link.href = item.href;
				link.addEventListener('click', closeMenu);
				li.appendChild(link);
			}
			appendCorners(li);
			list.appendChild(li);
		});

		mainWrap.appendChild(list);

		var subWrap = el('div', 'iac-mobile-menu__sub');
		subWrap.setAttribute('data-sub-panel', '');
		subWrap.setAttribute('aria-hidden', 'true');
		var subTitle = el('p', 'iac-mobile-menu__sub-title', t('accounts', 'Accounts'));
		var subList = el('ul', 'iac-mobile-menu__sub-list');

		accountLinks().forEach(function (item) {
			var li = el('li');
			var link = el('a', 'iac-mobile-menu__sub-link');
			link.href = item.href;
			link.innerHTML = iconSvg(ACCOUNT_ICONS[item.key]) + '<span>' + item.label + '</span>';
			link.addEventListener('click', closeMenu);
			li.appendChild(link);
			subList.appendChild(li);
		});

		subWrap.appendChild(subTitle);
		subWrap.appendChild(subList);

		trackEl.appendChild(mainWrap);
		trackEl.appendChild(subWrap);
		trackWrap.appendChild(trackEl);

		var legal = el('div', 'iac-mobile-menu__legal');
		var legalLeft = el('div', 'iac-mobile-menu__legal-col');
		var legalCorp = el('p', 'iac-mobile-menu__legal-line', 'impact.corp\u00AE');
		legalCorp.setAttribute('data-scramble', 'impact.corp\u00AE');
		var legalRights = el('p', 'iac-mobile-menu__legal-line', t('rightsReserved', 'RIGHTS RESERVED'));
		legalRights.setAttribute('data-scramble', t('rightsReserved', 'RIGHTS RESERVED'));
		legalLeft.appendChild(legalCorp);
		legalLeft.appendChild(legalRights);
		var legalRight = el('div', 'iac-mobile-menu__legal-col iac-mobile-menu__legal-col--right');
		var legalAll = el('p', 'iac-mobile-menu__legal-line', t('allRightsReserved', 'all rights reserved'));
		legalAll.setAttribute('data-scramble', t('allRightsReserved', 'all rights reserved'));
		var legalYear = el('p', 'iac-mobile-menu__legal-line', '2026');
		legalYear.setAttribute('data-scramble', '2026');
		legalRight.appendChild(legalAll);
		legalRight.appendChild(legalYear);
		legal.appendChild(legalLeft);
		legal.appendChild(legalRight);

		mainMenu.appendChild(backBtn);
		mainMenu.appendChild(trackWrap);

		var mobileLang = el('div', 'iac-mobile-menu__lang lg:hidden');
		mobileLang.innerHTML =
			'<div class="iac-lang-switch iac-lang-switch--pill notranslate" role="group" aria-label="Language">' +
			'<button type="button" class="iac-lang-switch__btn iac-lang-switch__btn--active" data-lang="en" aria-pressed="true">EN</button>' +
			'<span class="iac-lang-switch__sep" aria-hidden="true">/</span>' +
			'<button type="button" class="iac-lang-switch__btn" data-lang="ru" aria-pressed="false">RU</button>' +
			'</div>';
		mainMenu.appendChild(mobileLang);

		mainMenu.appendChild(legal);
		mainMenu.appendChild(buildFooterShape());

		var glow = el('div', 'iac-mobile-menu__glow');
		glow.setAttribute('data-menu-glow', '');

		panelEl.appendChild(mainMenu);
		panelEl.appendChild(glow);
		overlay.appendChild(panelEl);

		overlay.addEventListener('click', closeMenu);
		document.body.appendChild(overlay);
	}

	function resetSubPanel() {
		subOpen = false;
		if (!trackEl) {
			return;
		}
		gsap.set(trackEl, { xPercent: 0 });
		var mainLinksEl = trackEl.querySelector('[data-main-links]');
		var subPanel = trackEl.querySelector('[data-sub-panel]');
		if (mainLinksEl) {
			gsap.set(mainLinksEl, { opacity: 1 });
		}
		if (subPanel) {
			gsap.set(subPanel, { opacity: 0 });
			subPanel.setAttribute('aria-hidden', 'true');
		}
		var leftCrosshair = overlay.querySelector('.iac-mobile-menu__crosshair-wrap--left');
		if (leftCrosshair) {
			leftCrosshair.classList.remove('iac-mobile-menu__crosshair-wrap--hidden');
		}
		var backBtn = overlay.querySelector('.iac-mobile-menu__back');
		if (backBtn) {
			backBtn.classList.remove('iac-mobile-menu__back--visible');
		}
	}

	function animateSubPanel(next) {
		if (!trackEl) {
			return;
		}
		var mainLinksEl = trackEl.querySelector('[data-main-links]');
		var subPanel = trackEl.querySelector('[data-sub-panel]');
		var leftCrosshair = overlay.querySelector('.iac-mobile-menu__crosshair-wrap--left');
		var backBtn = overlay.querySelector('.iac-mobile-menu__back');

		subOpen = next;
		if (subPanel) {
			subPanel.setAttribute('aria-hidden', next ? 'false' : 'true');
		}
		if (leftCrosshair) {
			leftCrosshair.classList.toggle('iac-mobile-menu__crosshair-wrap--hidden', next);
		}
		if (backBtn) {
			backBtn.classList.toggle('iac-mobile-menu__back--visible', next);
		}

		gsap.timeline({ defaults: { duration: 0.4, ease: 'power3.inOut' } })
			.to(trackEl, { xPercent: next ? -50 : 0 })
			.to(mainLinksEl, { opacity: next ? 0 : 1 }, '<')
			.to(subPanel, { opacity: next ? 1 : 0 }, '<');
	}

	function setSubOpen(next) {
		if (subOpen === next) {
			return;
		}
		animateSubPanel(next);
	}

	function animateMenu(shouldOpen) {
		if (!overlay || !panelEl) {
			return;
		}

		var mainMenu = overlay.querySelector('[data-main-menu]');
		var mainLinksWrap = overlay.querySelector('[data-main-links]');
		var items = mainLinksWrap ? mainLinksWrap.querySelectorAll('li') : [];
		var scrambles = mainMenu ? mainMenu.querySelectorAll('[data-scramble]') : [];
		var crosshairs = mainMenu ? mainMenu.querySelectorAll('[data-crosshair]') : [];
		var crosshairCells = mainMenu ? mainMenu.querySelectorAll('[data-crosshair] > div') : [];
		var glow = overlay.querySelector('[data-menu-glow]');
		var cornerSets = [];

		items.forEach(function (item) {
			cornerSets.push(Array.prototype.slice.call(item.querySelectorAll('[data-corner]')));
		});

		if (menuTimeline) {
			menuTimeline.kill();
		}

		if (shouldOpen) {
			cornerSets.forEach(function (corners) {
				corners.forEach(function (corner, index) {
					var offset = CORNER_OFFSETS[index];
					gsap.set(corner, { opacity: 0, x: 6 * offset.x, y: 6 * offset.y });
				});
			});

			gsap.set(overlay, { display: 'flex' });
			gsap.set(panelEl, { opacity: 0, yPercent: -100, y: 0 });
			gsap.set(items, { opacity: 0, y: 20 });
			gsap.set(scrambles, { opacity: 0 });
			gsap.set(crosshairs, { opacity: 0 });
			gsap.set(crosshairCells, { scaleX: 0, scaleY: 0, transformOrigin: '50% 50%' });
			if (glow) {
				gsap.set(glow, { opacity: 0 });
			}

			menuTimeline = gsap.timeline({ defaults: { ease: 'power3.out' } });
			menuTimeline.to(panelEl, { opacity: 0.95, yPercent: 0, duration: 0.5, ease: 'power3.out' });
			menuTimeline.to(crosshairs, { opacity: 1, duration: 0.01 }, 0.3);
			crosshairs.forEach(function (crosshair) {
				var cells = crosshair.querySelectorAll(':scope > div');
				cells.forEach(function (cell, index) {
					menuTimeline.to(cell, { scaleX: 1, scaleY: 1, duration: 0.3, ease: 'power2.out' }, 0.35 + 0.06 * index);
				});
			});

			items.forEach(function (item, row) {
				var start = 0.3 + 0.2 * row;
				var corners = cornerSets[row];
				corners.forEach(function (corner, index) {
					menuTimeline.to(corner, { opacity: 1, x: 0, y: 0, duration: 0.35, ease: 'power2.out' }, start + 0.03 * index);
				});
				menuTimeline.set(item, { opacity: 0.5, y: 10 }, start);
				menuTimeline.set(item, { opacity: 0.8 }, start + 0.06);
				menuTimeline.set(item, { opacity: 0.5 }, start + 0.12);
				menuTimeline.to(item, { opacity: 1, y: 0, duration: 0.3, ease: 'power2.out' }, start + 0.2);
				menuTimeline.to(corners, { opacity: 0, duration: 0.3, ease: 'power2.in' }, start + 0.9);
			});

			scrambles.forEach(function (node, index) {
				menuTimeline.to(node, { opacity: 1, duration: 0.01 }, 0.4 + 0.1 * index);
				menuTimeline.add(
					animateTextScramble(node, {
						text: node.getAttribute('data-scramble') || node.textContent,
						duration: 0.8,
						revealDelay: 0.3,
						speed: 0.4,
					}),
					0.4 + 0.1 * index
				);
			});

			if (glow) {
				menuTimeline.to(glow, { opacity: 1, duration: 0.6, ease: 'power2.out' }, 0.4);
			}
			return;
		}

		var allCorners = mainLinksWrap ? mainLinksWrap.querySelectorAll('[data-corner]') : [];
		menuTimeline = gsap.timeline({
			defaults: { ease: 'power4.in' },
			onComplete: function () {
				gsap.set(overlay, { display: 'none' });
				gsap.set(panelEl, { y: 0, yPercent: 0, opacity: 0 });
				resetSubPanel();
			},
		});

		menuTimeline
			.to(items, { opacity: 0, y: -10, duration: 0.25, stagger: 0.04 })
			.to(allCorners, { opacity: 0, duration: 0.15 }, '<')
			.to(scrambles, { opacity: 0, duration: 0.2 }, '<')
			.to(crosshairCells, { scaleX: 0, scaleY: 0, duration: 0.2, stagger: 0.03 }, '<');

		if (glow) {
			menuTimeline.to(glow, { opacity: 0, duration: 0.25 }, '<');
		}

		menuTimeline.to(panelEl, { opacity: 0, yPercent: -100, duration: 0.4, ease: 'power3.in' }, '<+0.05');
	}

	function onTouchStart(event) {
		touchStart = { y: event.touches[0].clientY, t: Date.now() };
		touchDragging = false;
	}

	function onTouchMove(event) {
		if (!touchStart || !panelEl) {
			return;
		}
		var delta = event.touches[0].clientY - touchStart.y;
		if (delta < 0) {
			touchDragging = true;
			var progress = Math.min(Math.abs(delta) / panelEl.offsetHeight, 1);
			gsap.set(panelEl, { y: delta, opacity: 0.95 - 0.65 * progress });
			var glow = overlay.querySelector('[data-menu-glow]');
			if (glow) {
				gsap.set(glow, { opacity: 1 - progress });
			}
		}
	}

	function onTouchEnd(event) {
		if (!touchStart || !panelEl) {
			return;
		}
		var delta = event.changedTouches[0].clientY - touchStart.y;
		var velocity = Math.abs(delta) / (Date.now() - touchStart.t);

		if (touchDragging && (delta < -80 || velocity > 0.5)) {
			if (menuTimeline) {
				menuTimeline.kill();
			}
			var duration = Math.max(0.12, Math.min(0.3, (panelEl.offsetHeight + delta) / (1000 * velocity || 600)));
			var glow = overlay.querySelector('[data-menu-glow]');
			gsap.to(panelEl, {
				y: -panelEl.offsetHeight,
				opacity: 0,
				duration: duration,
				ease: 'power2.in',
				onComplete: function () {
					gsap.set(overlay, { display: 'none' });
					gsap.set(panelEl, { y: 0, yPercent: 0, opacity: 0 });
					resetSubPanel();
					setOpenState(false);
				},
			});
			if (glow) {
				gsap.to(glow, { opacity: 0, duration: duration * 0.8, ease: 'power2.in' });
			}
		} else if (touchDragging) {
			var snapDuration = Math.max(0.2, Math.min(0.45, Math.abs(delta) / 400));
			gsap.to(panelEl, { y: 0, opacity: 0.95, duration: snapDuration, ease: 'power3.out' });
			var glowNode = overlay.querySelector('[data-menu-glow]');
			if (glowNode) {
				gsap.to(glowNode, { opacity: 1, duration: snapDuration, ease: 'power3.out' });
			}
		}

		touchStart = null;
		touchDragging = false;
	}

	function setOpenState(next) {
		open = next;
		menuBtn.textContent = open ? t('close', 'Close') : t('menu', 'Menu');
		menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
		menuBtn.classList.toggle('iac-mobile-menu-btn--open', open);
		document.documentElement.classList.toggle('iac-mobile-menu-open', open);
		if (overlay) {
			overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
		}
		document.body.style.overflow = open ? 'hidden' : '';
	}

	function setOpen(next) {
		if (open === next) {
			return;
		}
		setOpenState(next);
		if (!overlay) {
			return;
		}
		animateMenu(next);
	}

	function closeMenu() {
		setOpen(false);
	}

	function toggleMenu(event) {
		event.preventDefault();
		event.stopPropagation();
		if (!overlay) {
			buildOverlay();
			if (window.iacHeaderTools && typeof window.iacHeaderTools.initLangSwitch === 'function') {
				window.iacHeaderTools.initLangSwitch();
			}
		}
		setOpen(!open);
	}

	bindHomeLogo();
	menuBtn.setAttribute('aria-controls', 'iac-mobile-menu');
	menuBtn.setAttribute('aria-expanded', 'false');
	menuBtn.addEventListener('click', toggleMenu, isHomeMirror);

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && open) {
			closeMenu();
		}
	});
})();
