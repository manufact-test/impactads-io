(function () {
	'use strict';

	if (typeof iacData === 'undefined' || iacData.isAbout !== '1') {
		return;
	}

	var root = document.querySelector('.iac-about-page');
	if (!root) {
		return;
	}

	var teamSection = root.querySelector('.iac-about-team, section.py-section.bg-background');
	var teamHoverLock = false;
	var activeIndex = 0;

	function syncSlotStates(item, active) {
		item.setAttribute('data-active', active ? 'true' : 'false');
		item.setAttribute('data-any-active', 'true');
		item.querySelectorAll('[data-slot]').forEach(function (slot) {
			slot.setAttribute('data-active', active ? 'true' : 'false');
		});
	}

	function setDesktopTeamActive(index, fromUser) {
		if (typeof index !== 'number' || index < 0) {
			return;
		}

		var listItems = root.querySelectorAll('[data-team-list-item]');
		var frames = root.querySelectorAll('[data-team-frame]');

		if (!listItems.length) {
			return;
		}

		index = Math.max(0, Math.min(listItems.length - 1, index));
		activeIndex = index;

		if (fromUser) {
			teamHoverLock = true;
		}

		listItems.forEach(function (item, i) {
			syncSlotStates(item, i === index);
		});

		frames.forEach(function (frame) {
			var frameIndex = parseInt(frame.getAttribute('data-team-frame-index') || '-1', 10);
			frame.setAttribute('data-active', frameIndex === index ? 'true' : 'false');
		});

		teamSection && teamSection.setAttribute('data-team-active-index', String(index));
	}

	function setMobileTeamActive(index) {
		var mobileWrap = root.querySelector('.flex.flex-col.items-center.lg\\:hidden');
		if (!mobileWrap) {
			return;
		}

		var track = mobileWrap.querySelector('.flex.h-full.w-fit.items-center');
		var photoButtons = mobileWrap.querySelectorAll('.overflow-x-clip button.aspect-square');
		var rosterButtons = mobileWrap.querySelectorAll('.flex.flex-col > button');
		var nameEl = mobileWrap.querySelector('.mt-3 .text-title.text-primary');
		var titleEl = mobileWrap.querySelector('.mt-3 .font-misc.text-muted-foreground');

		if (!track || !photoButtons.length) {
			return;
		}

		index = Math.max(0, Math.min(photoButtons.length - 1, index));

		photoButtons.forEach(function (btn, i) {
			var active = i === index;
			btn.style.opacity = active ? '1' : '0.35';
			btn.style.transform = active ? 'scale(1.12)' : 'scale(1)';
			btn.classList.toggle('iac-team-photo-active', active);
		});

		track.style.marginLeft =
			'calc(50% - ' +
			index +
			' * (var(--photo-size) + var(--photo-gap)) - var(--photo-size) / 2)';

		if (rosterButtons[index]) {
			var activeBtn = rosterButtons[index];
			var name = activeBtn.querySelector('.text-title');
			var title = activeBtn.querySelector('.font-misc');
			if (nameEl && name) {
				nameEl.textContent = name.textContent.trim();
			}
			if (titleEl && title) {
				titleEl.textContent = title.textContent.trim();
			}
			rosterButtons.forEach(function (btn, i) {
				btn.classList.toggle('iac-team-roster-active', i === index);
			});
		}
	}

	function bindDesktopTeam() {
		var listItems = root.querySelectorAll('[data-team-list-item]');
		var frames = root.querySelectorAll('[data-team-frame]');

		if (!listItems.length) {
			return;
		}

		listItems.forEach(function (item) {
			var index = parseInt(item.getAttribute('data-team-item') || '0', 10);

			item.addEventListener('mouseenter', function () {
				setDesktopTeamActive(index, true);
			});

			item.addEventListener('focus', function () {
				setDesktopTeamActive(index, true);
			});

			item.addEventListener('click', function () {
				setDesktopTeamActive(index, true);
			});
		});

		frames.forEach(function (frame) {
			var index = parseInt(frame.getAttribute('data-team-frame-index') || '-1', 10);
			if (index < 0) {
				return;
			}
			frame.addEventListener('mouseenter', function () {
				setDesktopTeamActive(index, true);
			});
		});

		if (teamSection) {
			teamSection.addEventListener('mouseleave', function () {
				teamHoverLock = false;
			});
		}

		setDesktopTeamActive(0, false);
	}

	function bindMobileTeam() {
		var mobileWrap = root.querySelector('.flex.flex-col.items-center.lg\\:hidden');
		if (!mobileWrap) {
			return;
		}

		var photoButtons = mobileWrap.querySelectorAll('.overflow-x-clip button.aspect-square');
		var rosterButtons = mobileWrap.querySelectorAll('.flex.flex-col > button');

		photoButtons.forEach(function (btn, index) {
			btn.addEventListener('mouseenter', function () {
				setMobileTeamActive(index);
			});
			btn.addEventListener('click', function () {
				setMobileTeamActive(index);
			});
		});

		rosterButtons.forEach(function (btn, index) {
			btn.addEventListener('mouseenter', function () {
				setMobileTeamActive(index);
			});
			btn.addEventListener('click', function () {
				setMobileTeamActive(index);
			});
		});

		setMobileTeamActive(0);
	}

	function bindValueCards() {
		root.querySelectorAll('[data-value-card]').forEach(function (card) {
			card.addEventListener('mouseenter', function () {
				card.classList.add('iac-value-card-active');
			});
			card.addEventListener('mouseleave', function () {
				card.classList.remove('iac-value-card-active');
			});
		});
	}

	function bindMissionReveal() {
		var blocks = root.querySelectorAll('[data-text-block]');
		if (!blocks.length || !('IntersectionObserver' in window)) {
			blocks.forEach(function (block) {
				block.classList.add('iac-about-revealed');
			});
			return;
		}

		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('iac-about-revealed');
					}
				});
			},
			{ threshold: 0.2, rootMargin: '0px 0px -10% 0px' }
		);

		blocks.forEach(function (block) {
			observer.observe(block);
		});
	}

	function bindTeamScroll() {
		var listItems = root.querySelectorAll('[data-team-list-item]');
		if (!teamSection || !listItems.length || window.matchMedia('(max-width: 1023px)').matches) {
			return;
		}

		var ticking = false;

		function updateFromScroll() {
			ticking = false;
			if (teamHoverLock) {
				return;
			}

			var rect = teamSection.getBoundingClientRect();
			var viewHeight = window.innerHeight || document.documentElement.clientHeight;
			var start = viewHeight * 0.2;
			var end = rect.height - viewHeight * 0.4;
			if (end <= 0) {
				return;
			}

			var progress = (start - rect.top) / end;
			progress = Math.max(0, Math.min(1, progress));
			var index = Math.round(progress * (listItems.length - 1));

			if (index !== activeIndex) {
				setDesktopTeamActive(index, false);
			}
		}

		function onScroll() {
			if (!ticking) {
				ticking = true;
				window.requestAnimationFrame(updateFromScroll);
			}
		}

		window.addEventListener('scroll', onScroll, { passive: true });
		updateFromScroll();
	}

	function init() {
		bindValueCards();
		bindMissionReveal();
		bindDesktopTeam();
		bindMobileTeam();
		bindTeamScroll();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
