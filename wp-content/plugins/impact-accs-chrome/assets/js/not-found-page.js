(function () {
	'use strict';

	var canvas = document.getElementById('iac-nf-canvas');
	if (!canvas || typeof window.IACSpaceInvaders !== 'function') {
		return;
	}

	var hud = document.getElementById('iac-nf-hud');
	var scoreEl = document.getElementById('iac-nf-score');
	var waveEl = document.getElementById('iac-nf-wave');
	var livesEl = document.getElementById('iac-nf-lives');
	var restartBtn = document.getElementById('iac-nf-restart');
	var restartEndBtn = document.getElementById('iac-nf-restart-end');
	var dim = document.getElementById('iac-nf-dim');
	var idleCenter = document.getElementById('iac-nf-idle-center');
	var tapHint = document.getElementById('iac-nf-tap-hint-wrap');
	var statusCard = document.getElementById('iac-nf-status-card');
	var statusTitle = document.getElementById('iac-nf-status-title');
	var bossBanner = document.getElementById('iac-nf-boss-banner');
	var actionsPlaying = document.getElementById('iac-nf-actions-playing');
	var isRu = document.documentElement.lang === 'ru' || document.documentElement.classList.contains('iac-lang-ru');
	var scoreLabel = isRu ? 'СЧЁТ' : 'SCORE';
	var waveLabel = isRu ? 'УРОВЕНЬ' : 'WAVE';

	var game = window.IACSpaceInvaders(canvas, {});
	game.start();

	function renderLives(count) {
		if (!livesEl) {
			return;
		}
		var html = '';
		for (var i = 0; i < count; i++) {
			html +=
				'<svg viewBox="0 0 10 10" class="iac-nf-life"><polygon points="5,1 9,5 5,9 1,5"></polygon></svg>';
		}
		livesEl.innerHTML = html;
		livesEl.setAttribute('aria-label', isRu ? 'Осталось жизней: ' + count : count + ' lives remaining');
	}

	function setVisible(el, show) {
		if (!el) {
			return;
		}
		if (show) {
			el.removeAttribute('hidden');
		} else {
			el.setAttribute('hidden', 'hidden');
		}
	}

	function bindRestart(btn) {
		if (!btn) {
			return;
		}
		btn.addEventListener('click', function (event) {
			event.preventDefault();
			game.restart();
		});
	}

	function applyState(state, score, wave, lives) {
		if (scoreEl) {
			scoreEl.textContent = scoreLabel + ' ' + String(score).padStart(6, '0');
		}
		if (waveEl) {
			waveEl.textContent = waveLabel + ' ' + wave;
		}
		renderLives(lives);

		var playing = state === 'playing' || state === 'bossintro';
		var idle = state === 'idle';
		var ended = state === 'gameover' || state === 'won';

		if (hud) {
			hud.classList.toggle('iac-nf-hud--hidden', idle);
			hud.setAttribute('aria-hidden', idle ? 'true' : 'false');
		}
		if (dim) {
			dim.classList.toggle('iac-nf-dim--off', playing);
		}
		setVisible(idleCenter, idle);
		setVisible(tapHint, idle);
		setVisible(bossBanner, state === 'bossintro');
		setVisible(statusCard, ended);
		setVisible(actionsPlaying, playing);
		if (statusTitle && ended) {
			if (isRu) {
				statusTitle.innerHTML = state === 'won' ? 'Система<br>восстановлена' : 'Система<br>не отвечает';
			} else {
				statusTitle.innerHTML = state === 'won' ? 'System<br>cleared' : 'System<br>down';
			}
		}
	}

	canvas.addEventListener('gamestatechange', function (event) {
		var detail = event.detail || {};
		applyState(detail.state || 'idle', detail.score || 0, detail.wave || 1, detail.lives || 3);
	});

	bindRestart(restartBtn);
	bindRestart(restartEndBtn);

	applyState('idle', 0, 1, 3);
})();
