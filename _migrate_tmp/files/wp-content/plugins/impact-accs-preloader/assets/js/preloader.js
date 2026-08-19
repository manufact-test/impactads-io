(function (window, document) {
	'use strict';

	var ENTER_MS = 600;
	var MIN_ACTIVE_MS = 1200;
	var MAX_WAIT_MS = 5500;
	var EXIT_FALLBACK_MS = 1200;

	var booted = false;

	function boot() {
		if (booted) {
			return true;
		}

		var root = document.getElementById('iap-loader');
		if (!root) {
			return false;
		}

		booted = true;
		document.documentElement.classList.add('iap-preloader-active');

		var bg = root.querySelector('[data-loader-bg]');
		var percentEl = root.querySelector('[data-iap-percent]');
		var markerEl = root.querySelector('[data-iap-marker]');
		var blocks = root.querySelectorAll('[data-iap-block]');
		var soundLabel = document.getElementById('iap-sound-label');

		var phase = 'entering';
		var progress = 0;
		var target = 0.05;
		var activeSince = 0;
		var rafId = 0;
		var finishRequested = false;
		var startedAt = Date.now();

		function setPhase(next) {
			phase = next;
			root.setAttribute('data-loader-phase', next);
			if (next === 'active') {
				var icon = root.querySelector('.iap-pulse-icon');
				if (icon) {
					icon.style.animation = 'none';
					void icon.offsetWidth;
					icon.style.animation = '';
				}
			}
		}

		function clamp(value, min, max) {
			return Math.min(max, Math.max(min, value));
		}

		function bump(next) {
			target = clamp(Math.max(target, next), 0, 1);
		}

		function updateUi() {
			var pct = Math.round(progress * 100);
			var lit = Math.round(progress * 10);

			if (percentEl) {
				percentEl.textContent = pct + '%';
			}
			if (markerEl) {
				markerEl.style.left = pct + '%';
			}
			for (var i = 0; i < blocks.length; i++) {
				if (progress > 0 && i < lit) {
					blocks[i].classList.add('iap-block-lit');
				} else {
					blocks[i].classList.remove('iap-block-lit');
				}
			}
		}

		function onIdle() {
			if (phase === 'idle') {
				return;
			}
			setPhase('idle');
			window.cancelAnimationFrame(rafId);
			root.remove();
			document.documentElement.classList.remove('iap-preloader-active');
			window.dispatchEvent(new CustomEvent('iap:preloader:done'));
		}

		function requestFinish() {
			if (finishRequested) {
				return;
			}
			finishRequested = true;
			bump(1);
			setPhase('exiting');
			window.setTimeout(function () {
				if (phase === 'exiting') {
					onIdle();
				}
			}, EXIT_FALLBACK_MS);
		}

		function maybeFinish() {
			if (finishRequested || phase !== 'active') {
				return;
			}
			if (progress < 0.98 || target < 1) {
				return;
			}
			if (Date.now() - activeSince < MIN_ACTIVE_MS) {
				return;
			}
			requestFinish();
		}

		function tick() {
			progress += (target - progress) * 0.14;
			if (target >= 1 && progress > 0.995) {
				progress = 1;
			}
			updateUi();
			maybeFinish();
			if (phase !== 'idle') {
				rafId = window.requestAnimationFrame(tick);
			}
		}

		function onBgAnimationEnd(event) {
			if (phase === 'exiting' && event.target === bg) {
				onIdle();
			}
		}

		function unlockSound() {
			if (soundLabel) {
				soundLabel.textContent = 'Sound enabled';
			}
			document.documentElement.dataset.muted = 'false';
			window.dispatchEvent(new CustomEvent('iap:sound:unlock'));
		}

		document.addEventListener(
			'click',
			function () {
				unlockSound();
			},
			{ capture: true, once: true }
		);

		if (bg) {
			bg.addEventListener('animationend', onBgAnimationEnd);
		}

		// Time-based progress — не зависит от lazy-load картинок и window.load.
		var autoTimer = window.setInterval(function () {
			var elapsed = Date.now() - startedAt;
			bump(Math.min(0.88, 0.05 + (elapsed / 2800) * 0.83));
		}, 80);

		function onReady() {
			bump(0.45);
		}

		function onLoaded() {
			bump(1);
			window.clearInterval(autoTimer);
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', onReady, { once: true });
		} else {
			onReady();
		}

		if (document.readyState === 'complete') {
			onLoaded();
		} else {
			window.addEventListener('load', onLoaded, { once: true });
		}

		window.setTimeout(onLoaded, MAX_WAIT_MS);

		window.setTimeout(function () {
			setPhase('active');
			activeSince = Date.now();
			updateUi();
			tick();
		}, ENTER_MS);

		window.setTimeout(function () {
			if (phase === 'entering' || phase === 'active') {
				requestFinish();
			}
		}, MAX_WAIT_MS + MIN_ACTIVE_MS);

		updateUi();
		return true;
	}

	window.IAPPreloader = { boot: boot };

	function tryBoot() {
		boot();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', tryBoot);
	} else {
		tryBoot();
	}
})(window, document);
