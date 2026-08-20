(function (window, document) {
	'use strict';

	function isRu() {
		if (window.iacData && window.iacData.lang) return window.iacData.lang === 'ru';
		return document.documentElement.classList.contains('iac-lang-ru') || document.documentElement.lang === 'ru';
	}

	if (!isRu()) return;

	function norm(value) {
		return String(value || '').replace(/\s+/g, ' ').trim();
	}

	function replaceExact(root, map) {
		if (!root) return;
		var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
		var node;
		while ((node = walker.nextNode())) {
			var key = norm(node.nodeValue);
			if (!key || !Object.prototype.hasOwnProperty.call(map, key)) continue;
			var next = map[key];
			if (node.nodeValue !== next) node.nodeValue = next;
		}
	}

	function containsAny(value, needles) {
		value = norm(value);
		for (var i = 0; i < needles.length; i += 1) {
			if (value.indexOf(needles[i]) !== -1) return true;
		}
		return false;
	}

	function patchPreloader() {
		var loader = document.querySelector('[data-loader-phase][role="status"]');
		if (!loader) return;
		loader.setAttribute('aria-label', 'Загрузка');
		replaceExact(loader, {
			'Loading': 'Загрузка',
			'Initializing': 'Инициализация',
			'Click anywhere to enable sound': 'Нажмите, чтобы включить звук',
			'Sound muted': 'Звук выключен',
			'Sound enabled': 'Звук включён',
			'Impact System': 'Система Impact'
		});
	}

	function ensureIntroStyles() {
		if (document.getElementById('iah-ru-intro-style')) return;
		var style = document.createElement('style');
		style.id = 'iah-ru-intro-style';
		style.textContent = [
			'#iah-ru-hero-intro{position:fixed;inset:0;z-index:6;display:flex;align-items:center;justify-content:center;pointer-events:none;opacity:1;visibility:visible;transition:opacity .45s ease,visibility .45s ease}',
			'#iah-ru-hero-intro.is-hidden{opacity:0;visibility:hidden}',
			'#iah-ru-hero-intro .iah-ru-intro-mask{position:absolute;left:50%;top:50%;width:min(94vw,1180px);height:min(58vh,560px);transform:translate(-50%,-50%);background:radial-gradient(ellipse at center,rgba(1,4,2,.98) 0%,rgba(1,4,2,.94) 48%,rgba(1,4,2,.68) 61%,rgba(1,4,2,0) 78%);filter:blur(7px)}',
			'#iah-ru-hero-intro .iah-ru-intro-copy{position:relative;display:flex;flex-direction:column;align-items:center;text-align:center;text-transform:uppercase;color:#dfe5e3;text-shadow:0 0 12px rgba(223,229,227,.18);font-family:var(--font-eurostile),var(--font-teko),Arial,sans-serif;line-height:.86;letter-spacing:-.035em;animation:iahRuIntroIn .85s steps(8,end) both}',
			'#iah-ru-hero-intro .iah-ru-intro-brand{margin-bottom:18px;font-size:clamp(16px,1.35vw,24px);font-weight:600;letter-spacing:.16em;opacity:.72}',
			'#iah-ru-hero-intro .iah-ru-intro-main{font-size:clamp(34px,5.8vw,88px);font-weight:600;white-space:nowrap}',
			'#iah-ru-hero-intro .iah-ru-intro-meta{margin-top:24px;font-family:var(--font-misc),Arial,sans-serif;font-size:clamp(10px,.82vw,13px);line-height:1.2;letter-spacing:.08em;opacity:.55}',
			'@keyframes iahRuIntroIn{0%{opacity:0;filter:blur(3px)}12%{opacity:.25}18%{opacity:.05}27%{opacity:.62}34%{opacity:.18}48%{opacity:.84}100%{opacity:1;filter:blur(0)}}',
			'@media(max-width:767px){#iah-ru-hero-intro .iah-ru-intro-mask{width:100vw;height:52vh}#iah-ru-hero-intro .iah-ru-intro-copy{width:94vw;line-height:.94}#iah-ru-hero-intro .iah-ru-intro-main{font-size:clamp(29px,9.2vw,48px);white-space:normal}#iah-ru-hero-intro .iah-ru-intro-brand{margin-bottom:12px}#iah-ru-hero-intro .iah-ru-intro-meta{width:86vw;margin-top:18px;font-size:10px}}'
		].join('');
		document.head.appendChild(style);
	}

	function heroHasLiveAlerts() {
		var hero = document.querySelector('[data-section="home-hero"]');
		if (!hero) return false;
		var active = hero.querySelectorAll('[data-interactive="true"]');
		for (var i = 0; i < active.length; i += 1) {
			var text = norm(active[i].textContent);
			if (containsAny(text, [
				'Команде нужен аккаунт под залив',
				'Медиабаингу нужны трастовые аккаунты',
				'Команде нужен стабильный объём аккаунтов',
				'Launch blocked', 'Supply stable', 'Volume request'
			])) return true;
		}
		return false;
	}

	function updateIntroVisibility() {
		var overlay = document.getElementById('iah-ru-hero-intro');
		if (!overlay) return;
		var hidden = window.scrollY > Math.min(180, Math.max(90, window.innerHeight * 0.15)) || heroHasLiveAlerts();
		overlay.classList.toggle('is-hidden', hidden);
	}

	function ensureIntroOverlay() {
		if (document.querySelector('[data-loader-phase][role="status"]')) return;
		if (!document.querySelector('[data-section="home-hero"]')) return;
		ensureIntroStyles();
		if (!document.getElementById('iah-ru-hero-intro')) {
			var overlay = document.createElement('div');
			overlay.id = 'iah-ru-hero-intro';
			overlay.setAttribute('aria-hidden', 'true');
			overlay.innerHTML = '<div class="iah-ru-intro-mask"></div><div class="iah-ru-intro-copy"><div class="iah-ru-intro-brand">IMPACT</div><div class="iah-ru-intro-main">GOOGLE ADS АККАУНТЫ</div><div class="iah-ru-intro-main">С РЕАЛЬНЫМ СПЕНДОМ</div><div class="iah-ru-intro-meta">7 лет на рынке · 15 000 аккаунтов · 100+ активных команд</div></div>';
			document.body.appendChild(overlay);
		}
		updateIntroVisibility();
	}

	function findBranch(hero, needles) {
		var nodes = hero.querySelectorAll('[data-interactive]');
		for (var i = 0; i < nodes.length; i += 1) {
			if (containsAny(nodes[i].textContent, needles)) return nodes[i];
		}
		return null;
	}

	function patchThreeLineCard(root, headers, lines) {
		if (!root) return;
		var ps = root.querySelectorAll('p');
		for (var i = 0; i < ps.length; i += 1) {
			if (!containsAny(ps[i].textContent, headers)) continue;
			var box = ps[i].parentElement;
			if (!box) continue;
			var direct = [];
			for (var j = 0; j < box.children.length; j += 1) {
				if (box.children[j].tagName === 'P') direct.push(box.children[j]);
			}
			if (direct.length >= 3) {
				direct[0].textContent = lines[0];
				direct[1].textContent = lines[1];
				direct[2].textContent = lines[2];
			}
			return;
		}
	}

	function patchComposer(root, oldNeedles, replacement, hasLeadingMention) {
		if (!root) return;
		var fields = root.querySelectorAll('[data-type-text]');
		for (var i = 0; i < fields.length; i += 1) {
			var field = fields[i];
			if (!containsAny(field.textContent, oldNeedles)) continue;
			if (field.querySelectorAll('span').length > 4) continue;
			field.textContent = (hasLeadingMention ? ' ' : '') + replacement;
		}
	}

	function patchSlackContents(root, rules) {
		if (!root) return;
		var blocks = root.querySelectorAll('[data-slot="content"]');
		for (var i = 0; i < blocks.length; i += 1) {
			var text = norm(blocks[i].textContent);
			for (var j = 0; j < rules.length; j += 1) {
				if (containsAny(text, rules[j].needles)) {
					blocks[i].textContent = rules[j].text;
					break;
				}
			}
		}
	}

	function patchRedBranch(root) {
		if (!root) return;
		replaceExact(root, {
			'Launch blocked — access needed': 'Команде нужен аккаунт под залив',
			'Buyer desk needs agency accounts before traffic goes live.': 'Подбор по спенду, USA и USD с проверкой до оплаты.',
			'Access confirmed': 'АККАУНТ ПРОВЕРЕН',
			'Accounts delivered on agreed terms. Launch window open.': 'Параметры подтверждены. Теперь можно оплачивать.',
			'Передаём на проверку. Оплата — после подтверждения.': 'Параметры подтверждены. Теперь можно оплачивать.',
			'Denis A.': 'Медиабайер',
			'You': 'Вы',
			'APP': '24/7',
			'impact.accs': 'impact.',
			'@impact.accs': '@founderads',
			'Send': 'ПОДТВЕРДИТЬ'
		});

		patchSlackContents(root, [
			{ needles: ['Need EU accounts before launch.', 'Нужен аккаунт под залив?'], text: '@founderads Нужен аккаунт под залив? Подберём по спенду, гео и валюте. Проверите до оплаты.' },
			{ needles: ['VolumeRequestPending', 'AgencyAccounts', 'Нужен Google Ads аккаунт:'], text: 'Нужен Google Ads аккаунт: спенд $2 000–3 000, USA, USD, под серую вертикаль.' },
			{ needles: ['Matching supply for', 'Оплата получена'], text: 'Оплата получена\nАдмин-доступ передан. Замена действует, пока аккаунт не тронут.\nПоддержка 24/7, напрямую с владельцем.' }
		]);

		patchThreeLineCard(root, ['Request status', 'ПАРАМЕТРЫ АККАУНТА'], [
			'ПАРАМЕТРЫ АККАУНТА',
			'Спенд: $2 000–3 000 · Гео: USA · Валюта: USD',
			'Оплата — после проверки. Замена — пока аккаунт не тронут.'
		]);

		patchComposer(root, ['Request @impact.accs terms', 'Проверил: спенд'], '@founderads Проверил: спенд, гео и валюта совпадают. Готов оплатить.', false);
	}

	function patchGreenBranch(root) {
		if (!root) return;
		replaceExact(root, {
			'Supply stable': 'Медиабаингу нужны трастовые аккаунты',
			'Repeat order channel active — terms unchanged.': 'Десятки аккаунтов от одного поставщика: проверка до оплаты и связь с владельцем.',
			'Supply confirmed': '50 АККАУНТОВ ЗАРЕЗЕРВИРОВАНЫ',
			'Working resource ready for the next launch.': 'Передаём на проверку. Оплата — после подтверждения.',
			'You': 'Вы',
			'APP': '24/7',
			'impact.accs': 'impact.',
			'@impact.accs': '@founderads',
			'Send': 'ЗАРЕЗЕРВИРОВАТЬ'
		});

		patchSlackContents(root, [
			{ needles: ['Active channels:', 'Нужны аккаунты для медиабаинга?'], text: 'Нужны аккаунты для медиабаинга?\nПодбор трастовых аккаунтов для медиабаинга готов: 50 спенд-аккаунтов под массовый залив.' }
		]);

		patchThreeLineCard(root, ['Supply status', 'ПАРАМЕТРЫ ПОДБОРА'], [
			'ПАРАМЕТРЫ ПОДБОРА',
			'USA · USD · белая история · цены по открытым тирам',
			'Каждый аккаунт передаём на проверку до оплаты.'
		]);

		patchComposer(root, ['Repeat order confirmed', 'Закрепите 50 аккаунтов'], '@founderads Закрепите 50 аккаунтов и пришлите их на проверку.', false);
	}

	function patchYellowBranch(root) {
		if (!root) return;
		replaceExact(root, {
			'Volume request — EU': 'Команде нужен стабильный объём аккаунтов',
			'50 accounts · GEO locked. Terms needed before 18:00.': 'Десятки и сотни аккаунтов без простоев и поиска нового поставщика',
			'Volume request — GEO: EU': 'ПОСТАВКА ДЛЯ КОМАНДЫ',
			'Buyer desk needs agency accounts before traffic goes live. GEO: EU.': 'Нужен стабильный объём под регулярные заливы. Подбор по спенду, гео и валюте. Условия — под объём.',
			'Severity:': '',
			'Status:': '',
			'High': 'ОБЪЁМ: 200 АККАУНТОВ / МЕСЯЦ',
			'Open': 'ГЕО: USA',
			'Request': 'ФОРМАТ РАБОТЫ',
			'Next step': 'ПОДДЕРЖКА 24/7',
			'Request access': 'ОБЪЁМНЫЕ УСЛОВИЯ',
			'Contact team': 'ПОДДЕРЖКА 24/7',
			'Elena M.': 'Команда',
			'You': 'Вы',
			'APP': '24/7',
			'impact.accs': 'impact.',
			'@impact.accs': '@founderads',
			'Supply matched': 'ОБЪЁМ ЗАФИКСИРОВАН',
			'Terms confirmed. Delivery in progress.': 'Условия подтверждены. Формируем первую поставку.',
			'Личный канал с владельцем открыт': 'ОБЪЁМ ЗАФИКСИРОВАН',
			'Подбор, замена и следующие закупки — напрямую, 24/7.': 'Условия подтверждены. Формируем первую поставку.',
			'Send': 'ЗАФИКСИРОВАТЬ'
		});

		patchSlackContents(root, [
			{ needles: ['confirm availability for EU', 'Нужен стабильный объём'], text: 'Нужен стабильный объём под регулярные заливы. Подбор по спенду, гео и валюте. Условия — под объём.' },
			{ needles: ['Terms draft ready for EU', 'Условия по объёму готовы'], text: 'Условия по объёму готовы\n200 аккаунтов в месяц · USA · подбор по спенду и валюте' },
			{ needles: ['Posted to #requests', 'Запрос принят владельцем'], text: 'Запрос принят владельцем\nАккаунты подбираем по согласованным параметрам.\nУсловия сохранены для следующих поставок.' }
		]);

		patchThreeLineCard(root, ['Volume terms', 'ПРЕДЛОЖЕНИЕ ДЛЯ КОМАНДЫ'], [
			'ПРЕДЛОЖЕНИЕ ДЛЯ КОМАНДЫ',
			'Список аккаунтов под регулярные заливы. Цены — по открытым тирам.',
			'Объём и график поставок согласуем индивидуально.'
		]);

		patchComposer(root, ['lock terms and confirm delivery', 'Условия подходят'], '@founderads Условия подходят. Зафиксируйте объём и график продаж.', false);
	}

	function patchHero() {
		var hero = document.querySelector('[data-section="home-hero"]');
		if (!hero) return;

		replaceExact(hero, {
			'You': 'Вы',
			'APP': '24/7',
			'impact.accs': 'impact.',
			'@impact.accs': '@founderads'
		});

		patchRedBranch(findBranch(hero, ['Launch blocked', 'Команде нужен аккаунт под залив']));
		patchGreenBranch(findBranch(hero, ['Supply stable', 'Медиабаингу нужны трастовые аккаунты']));
		patchYellowBranch(findBranch(hero, ['Volume request — EU', 'Команде нужен стабильный объём аккаунтов']));
		updateIntroVisibility();
	}

	var queued = false;
	function patchAll() {
		queued = false;
		patchPreloader();
		ensureIntroOverlay();
		patchHero();
	}

	function queuePatch() {
		if (queued) return;
		queued = true;
		window.requestAnimationFrame(patchAll);
	}

	patchPreloader();

	function boot() {
		patchAll();
		var observer = new MutationObserver(queuePatch);
		observer.observe(document.body, { childList: true, subtree: true, characterData: true });
		window.addEventListener('scroll', updateIntroVisibility, { passive: true });
		window.addEventListener('resize', updateIntroVisibility, { passive: true });
		window.setTimeout(patchAll, 250);
		window.setTimeout(patchAll, 1000);
		window.setTimeout(patchAll, 3000);
	}

	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
	else boot();
})(window, document);
