(function (window, document) {
	'use strict';

	function showChrome() {
		document.documentElement.classList.add('iac-frame-visible');

		document.querySelectorAll('[data-slot="frame"]').forEach(function (frame) {
			var wrap = frame.closest('.absolute.inset-0');
			if (wrap) {
				wrap.style.visibility = 'visible';
				wrap.style.opacity = '1';
			}
		});

		document.querySelectorAll('header, header nav, header ul, header li, header a, header button, header span').forEach(function (el) {
			el.style.opacity = '1';
			el.style.visibility = 'visible';
		});

		document.querySelectorAll('.fade-header').forEach(function (el) {
			el.style.opacity = '1';
		});
	}

	function revealFooterLinks() {
		document.querySelectorAll('footer a, footer .opacity-0, #iah-chrome-footer-root a').forEach(function (el) {
			el.style.opacity = '1';
		});
	}

	function cleanupPluginPreloader() {
		var iap = document.getElementById('iap-loader');
		if (iap && iap.parentNode) {
			iap.parentNode.removeChild(iap);
		}
		document.documentElement.classList.remove('iap-preloader-active');
	}

	function isRu() {
		if (window.iacData && window.iacData.lang) {
			return window.iacData.lang === 'ru';
		}
		return document.documentElement.lang === 'ru' || document.documentElement.classList.contains('iac-lang-ru');
	}

	function normalize(value) {
		return String(value || '').replace(/\s+/g, ' ').trim();
	}

	function replaceTextNodes(root, replacements) {
		if (!root || !replacements) return;
		var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
		var node;
		while ((node = walker.nextNode())) {
			var key = normalize(node.nodeValue);
			if (!key || !Object.prototype.hasOwnProperty.call(replacements, key)) continue;
			var next = replacements[key];
			if (node.nodeValue !== next) node.nodeValue = next;
		}
	}

	function closestSectionWithText(text) {
		var sections = document.querySelectorAll('main section, section');
		for (var i = 0; i < sections.length; i += 1) {
			if (normalize(sections[i].textContent).indexOf(text) !== -1) return sections[i];
		}
		return null;
	}

	function nearbyContains(el, needle, maxDepth) {
		var current = el;
		var depth = 0;
		while (current && current !== document.body && depth < (maxDepth || 7)) {
			if (normalize(current.textContent).indexOf(needle) !== -1) return true;
			current = current.parentElement;
			depth += 1;
		}
		return false;
	}

	function applicationUrl() {
		return window.iacData && window.iacData.applicationUrl ? window.iacData.applicationUrl : '/application/';
	}

	function patchHeaderCta() {
		var roots = [];
		var header = document.querySelector('header');
		var menu = document.querySelector('[data-main-menu]');
		if (header) roots.push(header);
		if (menu) roots.push(menu);

		roots.forEach(function (root) {
			replaceTextNodes(root, {
				'Request access': 'ПОДОБРАТЬ АККАУНТЫ',
				'REQUEST ACCESS': 'ПОДОБРАТЬ АККАУНТЫ',
				'Get access': 'ПОДОБРАТЬ АККАУНТЫ',
				'GET ACCESS': 'ПОДОБРАТЬ АККАУНТЫ',
				'Связаться': 'ПОДОБРАТЬ АККАУНТЫ',
				'СВЯЗАТЬСЯ': 'ПОДОБРАТЬ АККАУНТЫ'
			});
		});
	}

	function patchTimelineCtas() {
		var configs = [
			{ path: '/accounts/platform-access/', label: 'ПОДОБРАТЬ АККАУНТ' },
			{ path: '/accounts/agency-accounts/', label: 'УСЛОВИЯ ДЛЯ МЕДИАБАИНГА' },
			{ path: '/accounts/team-supply/', label: 'УСЛОВИЯ ДЛЯ КОМАНД' }
		];

		configs.forEach(function (config) {
			document.querySelectorAll('main a[href*="' + config.path + '"]').forEach(function (link) {
				replaceTextNodes(link, {
					'LEARN MORE': config.label,
					'READ MORE': config.label,
					'ПОДРОБНЕЕ': config.label
				});
			});
		});

		document.querySelectorAll('main button').forEach(function (button) {
			var label = normalize(button.textContent);
			if (label !== 'Связаться' && label !== 'СВЯЗАТЬСЯ' && label !== 'Request access' && label !== 'REQUEST ACCESS') return;

			if (nearbyContains(button, 'Трастовый аккаунт готов к проверке', 7)) {
				replaceTextNodes(button, {
					'Связаться': 'ПОЛУЧИТЬ НА ПРОВЕРКУ',
					'СВЯЗАТЬСЯ': 'ПОЛУЧИТЬ НА ПРОВЕРКУ',
					'Request access': 'ПОЛУЧИТЬ НА ПРОВЕРКУ',
					'REQUEST ACCESS': 'ПОЛУЧИТЬ НА ПРОВЕРКУ'
				});
			} else if (nearbyContains(button, 'Подбор для медиабаинга готов', 7)) {
				replaceTextNodes(button, {
					'Связаться': 'ЗАПРОСИТЬ ПОДБОР',
					'СВЯЗАТЬСЯ': 'ЗАПРОСИТЬ ПОДБОР',
					'Request access': 'ЗАПРОСИТЬ ПОДБОР',
					'REQUEST ACCESS': 'ЗАПРОСИТЬ ПОДБОР'
				});
			}
		});
	}

	function patchPurchaseCards() {
		var section = closestSectionWithText('ВЫБЕРИТЕ ФОРМАТ ЗАКУПКИ');
		if (!section) return;

		var cards = [
			{
				path: '/accounts/platform-access/',
				title: 'АККАУНТ ПОД ЗАЛИВ',
				badge: 'ПОД ЗАЛИВ',
				index: '01',
				cta: 'ПОДОБРАТЬ →'
			},
			{
				path: '/accounts/agency-accounts/',
				title: 'АККАУНТЫ ДЛЯ МЕДИАБАИНГА',
				badge: 'ДЛЯ МЕДИАБАИНГА',
				index: '02',
				cta: 'УЗНАТЬ УСЛОВИЯ →'
			},
			{
				path: '/accounts/team-supply/',
				title: 'РЕГУЛЯРНЫЙ ОБЪЁМ',
				badge: 'ПОД ОБЪЁМ',
				index: '03',
				cta: 'ЗАПРОСИТЬ ОБЪЁМ →'
			}
		];

		cards.forEach(function (config) {
			var card = section.querySelector('a[href*="' + config.path + '"]');
			if (!card) return;
			var title = card.querySelector('h2, h3');
			if (title && normalize(title.textContent) !== config.title) title.textContent = config.title;
			replaceTextNodes(card, {
				'ACCOUNTS': config.badge,
				'АККАУНТЫ': config.badge,
				'1': config.index,
				'2': config.index,
				'3': config.index,
				'ПОДРОБНЕЕ →': config.cta,
				'Читать →': config.cta,
				'Read More →': config.cta
			});
		});
	}

	function patchAssuranceSection() {
		var section = closestSectionWithText('ВСЁ ПОНЯТНО ДО ОПЛАТЫ');
		if (!section) return;
		var items = section.querySelectorAll('ul[role="list"] li');
		if (items.length >= 3) {
			var title = items[2].querySelector('h3');
			if (title && normalize(title.textContent) !== 'ЧЕСТНАЯ ГРАНИЦА ГАРАНТИИ') {
				title.textContent = 'ЧЕСТНАЯ ГРАНИЦА ГАРАНТИИ';
			}
		}
	}

	function patchManifesto() {
		var section = closestSectionWithText('ПОЧЕМУ МЫ') || closestSectionWithText('WHY US') || closestSectionWithText('Why Us');
		if (!section) return;

		replaceTextNodes(section, {
			'Resource over noise': 'ТОЛЬКО СПЕНД. БЕЗ ПУСТОГО ФАРМА.',
			'Account sellers have accumulated noise over the years. None of it makes launches faster. If access is infrastructure, the best interface is direct contact — not screenshots, not emojis, not random chats.': 'Мы продаём Google Ads аккаунты с реальной историей открутки. Не автореги и не фарм без трат: у аккаунта уже есть спенд, а значит — накопленный траст, выше лимиты, мягче модерация и меньше проверок при первом заливе. Вы платите за готовый рабочий ресурс и экономите время на самостоятельном прогреве перед заливом.',
			'Working resource': 'СНАЧАЛА ПРОВЕРЯЕТЕ. ПОТОМ ПЛАТИТЕ.',
			'Random Telegram sellers used to be the norm. Structured supply is the strength — clear request, fast contact, working access under terms your team can trust.': 'Получаете аккаунт и самостоятельно сверяете заявленные параметры: спенд, гео и валюту. Всё совпадает и аккаунт работает — оплачиваете в USDT TRC20. Не хотите проводить крупную сделку напрямую — подключаем гаранта. Комиссию оплачивает покупатель.',
			'Chaos is optional': 'ПОСТАВЩИК, КОТОРОГО НЕ НУЖНО МЕНЯТЬ',
			'Random sellers are broken. Unstable supply and vague terms. The future is structured access — clear request, fast contact, working resource.': 'Аккаунт не заходит или не соответствует заявленному спенду, гео или валюте — заменяем, пока вы не внесли в него изменения. Без тикетов, мелкого шрифта и споров. По каждой покупке на связи лично владелец, поддержка работает 24/7. За impact. — 7 лет на рынке, 15 000 выданных аккаунтов и 100+ активных команд.'
		});

		Array.prototype.forEach.call(section.querySelectorAll('a'), function (link) {
			var text = normalize(link.textContent).toLowerCase();
			var href = (link.getAttribute('href') || '').toLowerCase();
			var isAction = /read|learn|подробнее|связаться|подобрать|получить/.test(text) ||
				href.indexOf('/blog/manifesto') !== -1 ||
				href.indexOf('/features/') !== -1 ||
				href.indexOf('#iac-final-cta') !== -1;
			if (!isAction) return;

			link.setAttribute('href', '#iac-final-cta');
			link.setAttribute('data-iac-scroll-final', '1');

			var walker = document.createTreeWalker(link, NodeFilter.SHOW_TEXT);
			var node;
			while ((node = walker.nextNode())) {
				if (!normalize(node.nodeValue)) continue;
				node.nodeValue = 'СВЯЗАТЬСЯ';
				break;
			}
		});
	}

	function patchFinalFormCta() {
		var section = closestSectionWithText('ПОЛУЧИТЕ АККАУНТ. ПРОВЕРЬТЕ. ПОТОМ ПЛАТИТЕ.') || document.querySelector('footer');
		if (!section) return;
		section.id = 'iac-final-cta';

		var finalHeadings = section.querySelectorAll('h1, h2, h3');
		for (var headingIndex = 0; headingIndex < finalHeadings.length; headingIndex += 1) {
			var finalHeading = finalHeadings[headingIndex];
			if (normalize(finalHeading.textContent) !== 'ПОЛУЧИТЕ АККАУНТ. ПРОВЕРЬТЕ. ПОТОМ ПЛАТИТЕ.') continue;

			while (finalHeading.firstChild) finalHeading.removeChild(finalHeading.firstChild);
			['ПОЛУЧИТЕ АККАУНТ.', 'ПРОВЕРЬТЕ.', 'ПОТОМ ПЛАТИТЕ.'].forEach(function (line) {
				var lineElement = document.createElement('span');
				lineElement.className = 'iac-final-cta__line';
				lineElement.style.display = 'block';
				lineElement.textContent = line;
				finalHeading.appendChild(lineElement);
			});
			break;
		}

		var sections = document.querySelectorAll('main section, section');
		for (var i = 0; i < sections.length; i += 1) {
			var reviewText = normalize(sections[i].textContent).toUpperCase();
			if (!reviewText || reviewText.length > 9000) continue;
			var ruReviews = reviewText.indexOf('КЛИЕНТЫ') !== -1 && reviewText.indexOf('ОТЗЫВЫ') !== -1;
			var enReviews = reviewText.indexOf('CUSTOMER') !== -1 && reviewText.indexOf('REVIEWS') !== -1;
			if (ruReviews || enReviews) {
				sections[i].remove();
				break;
			}
		}

		var form = section.querySelector('form');
		var existing = section.querySelector('.iac-telegram-cta');
		if (existing) {
			if (form) form.remove();
			return;
		}
		if (!form) return;

		var link = document.createElement('a');
		link.className = 'iac-telegram-cta';
		link.href = 'https://t.me/founderads';
		link.target = '_blank';
		link.rel = 'noopener noreferrer';
		link.setAttribute('aria-label', 'Связаться в Telegram');

		var signal = document.createElement('span');
		signal.className = 'iac-telegram-cta__signal';
		signal.setAttribute('aria-hidden', 'true');
		signal.appendChild(document.createElement('span'));

		var copy = document.createElement('span');
		copy.className = 'iac-telegram-cta__copy';
		var status = document.createElement('span');
		status.className = 'iac-telegram-cta__status';
		status.textContent = 'НА СВЯЗИ';
		var title = document.createElement('span');
		title.className = 'iac-telegram-cta__title';
		title.textContent = 'НАПИСАТЬ В TELEGRAM';
		copy.appendChild(status);
		copy.appendChild(title);

		var arrow = document.createElement('span');
		arrow.className = 'iac-telegram-cta__arrow';
		arrow.setAttribute('aria-hidden', 'true');
		arrow.textContent = '↗';

		link.appendChild(signal);
		link.appendChild(copy);
		link.appendChild(arrow);
		form.replaceWith(link);
	}

	var faqItems = [
		{
			question: 'Чем спенд-аккаунты отличаются от фарма и агентских аккаунтов?',
			answer: [
				'Спенд-аккаунт уже имеет реальную историю рекламных расходов. Такие аккаунты обычно быстрее входят в залив: если не сработал лимит проверки, команда может начинать с дейли $2 000–5 000 в день. Фарм и самореги — новые аккаунты, которым нужен отдельный прогрев.',
				'При этом всё зависит от связки: где-то лучше работает фарм, а где-то помогает только спендовая история. Так называемые агентские аккаунты чаще всего тоже являются новыми аккаунтами без накопленного спенда.'
			]
		},
		{
			question: 'Какое гео у аккаунтов?',
			answer: ['Основное гео — USA. Также бывают аккаунты EU и СНГ: чаще KZ, реже Беларусь. Точный список доступных гео подтверждаем перед подбором.']
		},
		{
			question: 'Как привязать платёжный профиль?',
			answer: ['Платёжный профиль может быть уже создан — тогда достаточно привязать карту. Если платёжный сетап сброшен, нужно заново добавить платёжный способ. В зависимости от настроек можно использовать карту или банковский счёт.']
		},
		{
			question: 'Какие карты нужно использовать?',
			answer: ['Можно использовать любые подходящие карты, но страна карты и платёжные настройки должны соответствовать гео аккаунта. Например, для аккаунта, зарегистрированного на Германию, нужна карта Германии.']
		},
		{
			question: 'Какие прокси лучше использовать?',
			answer: ['Лучше использовать резидентские или мобильные прокси. Гео прокси подбирайте под гео аккаунта и сохраняйте стабильное окружение при работе.']
		},
		{
			question: 'Как передаётся аккаунт: в Octo, антик, на почту или через MCC?',
			answer: ['Мы передаём аккаунт только на почту или добавляем администратором в MCC владельца. Обычно команда создаёт профиль в Octo или другом антидетект-браузере, передаёт нам готовую почту, а мы выдаём на неё админ-доступ.']
		},
		{
			question: 'Можно ли привязать кредитную линию?',
			answer: ['Да, кредитную линию можно привязать к любому аккаунту.']
		},
		{
			question: 'Есть ли в аккаунтах активные объявления и кампании?',
			answer: ['Примерно в 80% аккаунтов есть история десятков, а иногда и сотен объявлений и кампаний. Это старые аккаунты крупных мировых агентств. Текущее состояние кампаний можно увидеть при проверке аккаунта.']
		},
		{
			question: 'Как правильно прогреть аккаунт перед заливом?',
			answer: ['Привяжите карту, подождите 2–4 часа и создайте белую кампанию на видео или приложение. Часто используют популярный ролик с YouTube или приложение Telegram. Начните с дейли $20–40 и прогревайте аккаунт 2–4 дня, после чего переходите к основному заливу.']
		},
		{
			question: 'Есть ли замена в случае блокировки?',
			answer: ['Да. Аккаунт заменим, если до блокировки вы не внесли в него изменения. Для замены аккаунт должен остаться в исходном состоянии, чтобы его можно было вернуть в список продаж.']
		},
		{
			question: 'Пишете ли вы апелляции или это делает команда?',
			answer: ['Апелляцию после покупки подаёт владелец аккаунта — то есть команда. При необходимости Google может запросить документы по новой карте и платёжному профилю. impact. не подаёт апелляции вместо покупателя.']
		},
		{
			question: 'Пройдены ли верификации на аккаунтах?',
			answer: ['Платёжный сетап в большинстве аккаунтов сброшен, поэтому запрос на верификацию обычно появляется после подключения новой карты и начала трафика. Команда проходит верификацию самостоятельно, используя актуальные подлинные документы, соответствующие платёжным данным.']
		},
		{
			question: 'Какой возраст аккаунтов?',
			answer: ['Возраст аккаунтов — в среднем от 2 до 10 лет.']
		},
		{
			question: 'Есть ли скидки на оптовые покупки?',
			answer: ['Да. При большом объёме и понятной перспективе регулярных закупок согласуем индивидуальные оптовые цены. Обычно специальные условия начинаются при закупках от $50 000 в месяц.']
		},
		{
			question: 'С кем вы работали и кто может вас рекомендовать?',
			answer: ['Среди команд, с которыми мы работали: Traffic Devils, Index Team, Marlon Group, SEOStars, SevenGroup, Gipsy и другие известные медиабаинговые команды. Детали и рекомендации можно обсудить напрямую с владельцем.']
		},
		{
			question: 'Сколько в среднем спендит один Google Ads аккаунт на гемблинге?',
			answer: [
				'Фиксированного среднего нет — результат зависит от опыта команды и конкретной связки. Одни команды льют около $20 000 дейли с аккаунта со спендом $2 000, другие не могут отлить $1 000 с аккаунта со спендом $30 000.',
				'При массовом заливе работает объём: например, из 200 аккаунтов 40–70 могут показать высокий дейли и покрыть общие затраты. Это реальный кейс одной из топовых команд, работавших с impact.'
			]
		}
	];

	function ensureFaqStyles() {
		if (document.getElementById('iah-home-faq-styles')) return;
		var style = document.createElement('style');
		style.id = 'iah-home-faq-styles';
		style.textContent = [
			'#iah-home-faq{--iah-faq-red:var(--color-primary,#f40b32);--iah-faq-bg:var(--color-background,#050505);position:relative;isolation:isolate;overflow:hidden;background:var(--iah-faq-bg);color:#f7f7f7;padding:clamp(88px,9vw,164px) 0 clamp(72px,8vw,140px);}',
			'#iah-home-faq:before{content:"";position:absolute;inset:-18% -8% auto;z-index:-2;height:68%;background:radial-gradient(circle at 16% 32%,rgba(244,11,50,.18),transparent 42%),radial-gradient(circle at 83% 20%,rgba(244,11,50,.10),transparent 34%);filter:blur(16px);pointer-events:none;}',
			'#iah-home-faq:after{content:"";position:absolute;inset:0;z-index:-3;opacity:.18;background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px);background-size:64px 64px;mask-image:linear-gradient(to bottom,transparent 0,#000 14%,#000 88%,transparent 100%);pointer-events:none;}',
			'.iah-faq__shell{width:min(1500px,calc(100% - clamp(40px,8vw,140px)));margin:0 auto;}',
			'.iah-faq__header{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(260px,.75fr);gap:clamp(32px,6vw,100px);align-items:end;padding-bottom:clamp(42px,5vw,80px);border-bottom:1px solid rgba(255,255,255,.13);}',
			'.iah-faq__eyebrow{grid-column:1/-1;display:flex;align-items:center;gap:10px;margin-bottom:clamp(-18px,-1vw,-8px);font-size:11px;line-height:1;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.48);}',
			'.iah-faq__eyebrow-dot{width:6px;height:6px;border-radius:50%;background:var(--iah-faq-red);box-shadow:0 0 18px rgba(244,11,50,.8);animation:iahFaqPulse 1.9s ease-in-out infinite;}',
			'.iah-faq__title{margin:0;font:inherit;font-size:clamp(48px,7.1vw,118px);line-height:.88;letter-spacing:-.055em;text-transform:uppercase;font-weight:700;max-width:980px;text-wrap:balance;}',
			'.iah-faq__title span{display:block;color:var(--iah-faq-red);text-shadow:0 0 24px rgba(244,11,50,.28);}',
			'.iah-faq__lead{margin:0 0 4px;max-width:520px;font-size:clamp(16px,1.25vw,21px);line-height:1.55;color:rgba(255,255,255,.62);}',
			'.iah-faq__list{counter-reset:iahFaq;margin-top:0;}',
			'.iah-faq__item{position:relative;border-bottom:1px solid rgba(255,255,255,.12);overflow:hidden;opacity:0;transform:translateY(18px);transition:opacity .65s ease,transform .65s cubic-bezier(.22,1,.36,1),background-color .3s ease;}',
			'.iah-faq__item:before{content:"";position:absolute;left:0;top:0;bottom:0;width:2px;background:var(--iah-faq-red);transform:scaleY(0);transform-origin:top;transition:transform .35s cubic-bezier(.22,1,.36,1);}',
			'.iah-faq__item.is-visible{opacity:1;transform:none;}',
			'.iah-faq__item:hover,.iah-faq__item.is-open{background:linear-gradient(90deg,rgba(244,11,50,.075),rgba(244,11,50,0) 66%);}',
			'.iah-faq__item:hover:before,.iah-faq__item.is-open:before{transform:scaleY(1);}',
			'.iah-faq__item--extra{display:none;}',
			'.iah-faq__item--extra.is-revealed{display:block;}',
			'.iah-faq__question{width:100%;display:grid;grid-template-columns:72px minmax(0,1fr) 46px;align-items:center;gap:clamp(14px,2vw,32px);padding:clamp(22px,2.15vw,34px) clamp(4px,.45vw,8px);border:0;background:transparent;color:inherit;text-align:left;cursor:pointer;font:inherit;}',
			'.iah-faq__index{font-size:11px;letter-spacing:.18em;color:rgba(255,255,255,.32);font-variant-numeric:tabular-nums;transition:color .3s ease;}',
			'.iah-faq__item.is-open .iah-faq__index,.iah-faq__item:hover .iah-faq__index{color:var(--iah-faq-red);}',
			'.iah-faq__question-text{font-size:clamp(17px,1.45vw,24px);line-height:1.26;font-weight:600;letter-spacing:-.018em;}',
			'.iah-faq__plus{position:relative;width:34px;height:34px;justify-self:end;border:1px solid rgba(255,255,255,.18);border-radius:50%;transition:border-color .3s ease,background-color .3s ease,transform .45s cubic-bezier(.22,1,.36,1);}',
			'.iah-faq__plus:before,.iah-faq__plus:after{content:"";position:absolute;left:50%;top:50%;width:12px;height:1px;background:currentColor;transform:translate(-50%,-50%);}',
			'.iah-faq__plus:after{transform:translate(-50%,-50%) rotate(90deg);transition:opacity .25s ease;}',
			'.iah-faq__item.is-open .iah-faq__plus{border-color:rgba(244,11,50,.7);background:rgba(244,11,50,.12);transform:rotate(180deg);}',
			'.iah-faq__item.is-open .iah-faq__plus:after{opacity:0;}',
			'.iah-faq__panel{display:grid;grid-template-rows:0fr;transition:grid-template-rows .5s cubic-bezier(.22,1,.36,1);}',
			'.iah-faq__item.is-open .iah-faq__panel{grid-template-rows:1fr;}',
			'.iah-faq__answer-wrap{overflow:hidden;}',
			'.iah-faq__answer{max-width:960px;margin:0 64px 0 72px;padding:0 0 30px;font-size:clamp(15px,1.08vw,18px);line-height:1.7;color:rgba(255,255,255,.58);opacity:0;transform:translateY(-6px);transition:opacity .28s ease,transform .45s cubic-bezier(.22,1,.36,1);}',
			'.iah-faq__item.is-open .iah-faq__answer{opacity:1;transform:none;transition-delay:.08s;}',
			'.iah-faq__answer p{margin:0;}',
			'.iah-faq__answer p+p{margin-top:12px;}',
			'.iah-faq__more-row{display:flex;align-items:center;justify-content:space-between;gap:18px;padding-top:28px;opacity:0;transform:translateY(14px);transition:opacity .6s ease,transform .6s cubic-bezier(.22,1,.36,1);}',
			'.iah-faq__more-row.is-visible{opacity:1;transform:none;}',
			'.iah-faq__more{display:inline-flex;align-items:center;gap:14px;padding:0;border:0;background:none;color:#fff;font:inherit;font-size:12px;line-height:1;letter-spacing:.16em;text-transform:uppercase;cursor:pointer;}',
			'.iah-faq__more-icon{display:grid;place-items:center;width:34px;height:34px;border:1px solid rgba(255,255,255,.18);border-radius:50%;color:var(--iah-faq-red);transition:transform .35s ease,border-color .3s ease;}',
			'.iah-faq__more[aria-expanded="true"] .iah-faq__more-icon{transform:rotate(45deg);border-color:rgba(244,11,50,.65);}',
			'.iah-faq__count{font-size:11px;letter-spacing:.16em;color:rgba(255,255,255,.32);font-variant-numeric:tabular-nums;}',
			'.iah-faq__cta{position:relative;overflow:hidden;display:grid;grid-template-columns:minmax(0,1fr) auto;gap:clamp(28px,5vw,80px);align-items:end;margin-top:clamp(70px,8vw,126px);padding:clamp(34px,5vw,78px);border:1px solid rgba(244,11,50,.3);border-radius:clamp(20px,2.2vw,34px);background:linear-gradient(135deg,rgba(244,11,50,.20) 0,rgba(85,0,13,.26) 42%,rgba(8,8,8,.92) 100%);box-shadow:inset 0 1px rgba(255,255,255,.06),0 28px 80px rgba(0,0,0,.34);opacity:0;transform:translateY(24px);transition:opacity .75s ease,transform .75s cubic-bezier(.22,1,.36,1);}',
			'.iah-faq__cta.is-visible{opacity:1;transform:none;}',
			'.iah-faq__cta:before{content:"";position:absolute;inset:0;pointer-events:none;background:linear-gradient(90deg,transparent 0,rgba(255,255,255,.035) 49.7%,transparent 50.3%);background-size:180px 100%;opacity:.6;}',
			'.iah-faq__cta-copy{position:relative;z-index:1;max-width:900px;}',
			'.iah-faq__cta-kicker{display:block;margin-bottom:16px;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.42);}',
			'.iah-faq__cta-title{margin:0;font:inherit;font-size:clamp(30px,4vw,66px);line-height:.98;letter-spacing:-.045em;text-transform:uppercase;font-weight:700;}',
			'.iah-faq__cta-text{margin:22px 0 0;max-width:760px;font-size:clamp(15px,1.15vw,19px);line-height:1.6;color:rgba(255,255,255,.62);}',
			'.iah-faq__cta-action{position:relative;z-index:1;display:flex;flex-direction:column;align-items:flex-start;gap:14px;}',
			'.iah-faq__cta-button{position:relative;isolation:isolate;display:inline-flex;align-items:center;justify-content:center;min-height:58px;padding:0 28px;border:1px solid rgba(255,255,255,.14);border-radius:999px;background:var(--iah-faq-red);color:#fff;text-decoration:none;font:inherit;font-size:11px;font-weight:700;line-height:1;letter-spacing:.13em;text-transform:uppercase;box-shadow:0 0 0 1px rgba(244,11,50,.2),0 0 34px rgba(244,11,50,.22);cursor:pointer;transition:transform .28s ease,box-shadow .28s ease,background-color .28s ease;}',
			'.iah-faq__cta-button:hover{transform:translateY(-2px);box-shadow:0 0 0 1px rgba(244,11,50,.35),0 12px 44px rgba(244,11,50,.34);}',
			'.iah-faq__cta-meta{max-width:360px;font-size:10px;line-height:1.55;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.38);}',
			'.iah-faq-reveal{opacity:0;transform:translateY(24px);transition:opacity .75s ease,transform .75s cubic-bezier(.22,1,.36,1);}',
			'.iah-faq-reveal.is-visible{opacity:1;transform:none;}',
			'@keyframes iahFaqPulse{0%,100%{opacity:.45;transform:scale(.8)}50%{opacity:1;transform:scale(1.15)}}',
			'@media (max-width:900px){.iah-faq__header{grid-template-columns:1fr;gap:24px}.iah-faq__eyebrow{grid-column:1}.iah-faq__lead{max-width:680px}.iah-faq__cta{grid-template-columns:1fr}.iah-faq__question{grid-template-columns:46px minmax(0,1fr) 38px}.iah-faq__answer{margin-left:46px;margin-right:38px}}',
			'@media (max-width:600px){#iah-home-faq{padding-top:72px;padding-bottom:72px}.iah-faq__shell{width:calc(100% - 32px)}.iah-faq__title{font-size:clamp(42px,15vw,70px)}.iah-faq__question{grid-template-columns:34px minmax(0,1fr) 32px;gap:10px;padding:20px 2px}.iah-faq__question-text{font-size:17px}.iah-faq__plus{width:30px;height:30px}.iah-faq__answer{margin-left:34px;margin-right:20px;padding-bottom:24px;font-size:15px}.iah-faq__cta{padding:28px 22px;border-radius:20px}.iah-faq__cta-button{width:100%;padding:0 18px}.iah-faq__cta-action{width:100%}.iah-faq__more-row{padding-top:22px}}',
			'@media (prefers-reduced-motion:reduce){#iah-home-faq *,#iah-home-faq *:before,#iah-home-faq *:after{scroll-behavior:auto!important;animation:none!important;transition-duration:.01ms!important}.iah-faq__item,.iah-faq-reveal,.iah-faq__more-row,.iah-faq__cta{opacity:1!important;transform:none!important}}'
		].join('\n');
		document.head.appendChild(style);
	}

	function makeFaqItem(item, index) {
		var article = document.createElement('article');
		article.className = 'iah-faq__item' + (index >= 6 ? ' iah-faq__item--extra' : '');
		article.setAttribute('data-faq-index', String(index));

		var button = document.createElement('button');
		button.type = 'button';
		button.className = 'iah-faq__question';
		button.setAttribute('aria-expanded', index === 0 ? 'true' : 'false');
		button.setAttribute('aria-controls', 'iah-faq-panel-' + index);

		var number = document.createElement('span');
		number.className = 'iah-faq__index';
		number.setAttribute('aria-hidden', 'true');
		number.textContent = String(index + 1).padStart(2, '0');

		var question = document.createElement('span');
		question.className = 'iah-faq__question-text';
		question.textContent = item.question;

		var plus = document.createElement('span');
		plus.className = 'iah-faq__plus';
		plus.setAttribute('aria-hidden', 'true');

		button.appendChild(number);
		button.appendChild(question);
		button.appendChild(plus);

		var panel = document.createElement('div');
		panel.className = 'iah-faq__panel';
		panel.id = 'iah-faq-panel-' + index;
		panel.setAttribute('role', 'region');
		panel.setAttribute('aria-label', item.question);

		var answerWrap = document.createElement('div');
		answerWrap.className = 'iah-faq__answer-wrap';
		var answer = document.createElement('div');
		answer.className = 'iah-faq__answer';
		item.answer.forEach(function (paragraph) {
			var p = document.createElement('p');
			p.textContent = paragraph;
			answer.appendChild(p);
		});
		answerWrap.appendChild(answer);
		panel.appendChild(answerWrap);

		article.appendChild(button);
		article.appendChild(panel);
		if (index === 0) article.classList.add('is-open');

		button.addEventListener('click', function () {
			var open = article.classList.toggle('is-open');
			button.setAttribute('aria-expanded', open ? 'true' : 'false');
		});

		return article;
	}

	function observeFaqReveal(section) {
		var revealEls = section.querySelectorAll('.iah-faq-reveal,.iah-faq__item:not(.iah-faq__item--extra),.iah-faq__more-row,.iah-faq__cta');
		if (!('IntersectionObserver' in window)) {
			revealEls.forEach(function (el) { el.classList.add('is-visible'); });
			return;
		}
		var observer = new IntersectionObserver(function (entries, obs) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) return;
				var el = entry.target;
				var index = Number(el.getAttribute('data-faq-index'));
				if (!Number.isNaN(index) && index < 6) {
					window.setTimeout(function () { el.classList.add('is-visible'); }, index * 55);
				} else {
					el.classList.add('is-visible');
				}
				obs.unobserve(el);
			});
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
		revealEls.forEach(function (el) { observer.observe(el); });
	}

	function buildFaqSection() {
		var section = document.createElement('section');
		section.id = 'iah-home-faq';
		section.setAttribute('aria-labelledby', 'iah-faq-title');

		var shell = document.createElement('div');
		shell.className = 'iah-faq__shell';

		var header = document.createElement('div');
		header.className = 'iah-faq__header iah-faq-reveal';
		var eyebrow = document.createElement('div');
		eyebrow.className = 'iah-faq__eyebrow';
		var dot = document.createElement('span');
		dot.className = 'iah-faq__eyebrow-dot';
		dot.setAttribute('aria-hidden', 'true');
		eyebrow.appendChild(dot);
		eyebrow.appendChild(document.createTextNode('БАЗА ЗНАНИЙ · 16 ВОПРОСОВ'));

		var title = document.createElement('h2');
		title.id = 'iah-faq-title';
		title.className = 'iah-faq__title';
		var titleAccent = document.createElement('span');
		titleAccent.textContent = 'FAQ:';
		title.appendChild(titleAccent);
		title.appendChild(document.createTextNode(' ЧАСТЫЕ ВОПРОСЫ'));

		var lead = document.createElement('p');
		lead.className = 'iah-faq__lead';
		lead.textContent = 'Прямые ответы о спенд-аккаунтах, гео, оплате, передаче, прогреве, верификации и замене.';
		header.appendChild(eyebrow);
		header.appendChild(title);
		header.appendChild(lead);

		var list = document.createElement('div');
		list.className = 'iah-faq__list';
		list.setAttribute('role', 'list');
		faqItems.forEach(function (item, index) {
			var faqItem = makeFaqItem(item, index);
			faqItem.setAttribute('role', 'listitem');
			list.appendChild(faqItem);
		});

		var moreRow = document.createElement('div');
		moreRow.className = 'iah-faq__more-row';
		var more = document.createElement('button');
		more.type = 'button';
		more.className = 'iah-faq__more';
		more.setAttribute('aria-expanded', 'false');
		var moreIcon = document.createElement('span');
		moreIcon.className = 'iah-faq__more-icon';
		moreIcon.setAttribute('aria-hidden', 'true');
		moreIcon.textContent = '+';
		var moreText = document.createElement('span');
		moreText.textContent = 'ПОКАЗАТЬ БОЛЬШЕ';
		more.appendChild(moreIcon);
		more.appendChild(moreText);
		var count = document.createElement('span');
		count.className = 'iah-faq__count';
		count.textContent = '06 / 16';
		moreRow.appendChild(more);
		moreRow.appendChild(count);

		more.addEventListener('click', function () {
			var expanded = more.getAttribute('aria-expanded') === 'true';
			var extras = list.querySelectorAll('.iah-faq__item--extra');
			if (expanded) {
				extras.forEach(function (item) {
					item.classList.remove('is-revealed', 'is-visible', 'is-open');
					var button = item.querySelector('.iah-faq__question');
					if (button) button.setAttribute('aria-expanded', 'false');
				});
				more.setAttribute('aria-expanded', 'false');
				moreText.textContent = 'ПОКАЗАТЬ БОЛЬШЕ';
				count.textContent = '06 / 16';
				return;
			}
			extras.forEach(function (item, extraIndex) {
				item.classList.add('is-revealed');
				window.setTimeout(function () { item.classList.add('is-visible'); }, extraIndex * 55);
			});
			more.setAttribute('aria-expanded', 'true');
			moreText.textContent = 'СКРЫТЬ';
			count.textContent = '16 / 16';
		});

		var cta = document.createElement('aside');
		cta.className = 'iah-faq__cta';
		cta.setAttribute('aria-label', 'Подобрать аккаунты');
		var ctaCopy = document.createElement('div');
		ctaCopy.className = 'iah-faq__cta-copy';
		var ctaKicker = document.createElement('span');
		ctaKicker.className = 'iah-faq__cta-kicker';
		ctaKicker.textContent = 'НУЖЕН ПОДБОР / ВЛАДЕЛЕЦ НА СВЯЗИ 24/7';
		var ctaTitle = document.createElement('h3');
		ctaTitle.className = 'iah-faq__cta-title';
		ctaTitle.textContent = 'ОСТАЛСЯ ВОПРОС ИЛИ НУЖНЫ АККАУНТЫ?';
		var ctaText = document.createElement('p');
		ctaText.className = 'iah-faq__cta-text';
		ctaText.textContent = 'Напишите задачу владельцу impact.: спенд, гео, валюту, вертикаль и объём. Получите подходящие аккаунты, проверьте их и только потом оплачивайте.';
		ctaCopy.appendChild(ctaKicker);
		ctaCopy.appendChild(ctaTitle);
		ctaCopy.appendChild(ctaText);

		var ctaAction = document.createElement('div');
		ctaAction.className = 'iah-faq__cta-action';
		var ctaButton = document.createElement('button');
		ctaButton.type = 'button';
		ctaButton.className = 'iah-faq__cta-button';
		ctaButton.textContent = 'ПОДОБРАТЬ АККАУНТЫ';
		var ctaMeta = document.createElement('div');
		ctaMeta.className = 'iah-faq__cta-meta';
		ctaMeta.textContent = '@founderads · поддержка 24/7 · оплата после проверки · сделка через гаранта';
		ctaAction.appendChild(ctaButton);
		ctaAction.appendChild(ctaMeta);

		ctaButton.addEventListener('click', function () {
			var target = document.getElementById('iac-final-cta');
			if (target && !section.contains(target)) {
				target.scrollIntoView({ behavior: 'smooth', block: 'start' });
				window.setTimeout(function () {
					var field = target.querySelector('input, textarea, select');
					if (field) field.focus({ preventScroll: true });
				}, 650);
				return;
			}
			window.location.href = 'https://t.me/founderads';
		});

		cta.appendChild(ctaCopy);
		cta.appendChild(ctaAction);

		shell.appendChild(header);
		shell.appendChild(list);
		shell.appendChild(moreRow);
		shell.appendChild(cta);
		section.appendChild(shell);
		return section;
	}

	function ensureFaqSection() {
		if (!isRu()) return;
		var main = document.querySelector('main');
		if (!main || document.getElementById('iah-home-faq')) return;
		ensureFaqStyles();
		var section = buildFaqSection();
		var fade = null;
		for (var i = main.children.length - 1; i >= 0; i -= 1) {
			var child = main.children[i];
			if (child.classList && (child.classList.contains('fade-to-t') || child.classList.contains('fade-from-background'))) {
				fade = child;
				break;
			}
		}
		if (fade) main.insertBefore(section, fade);
		else main.appendChild(section);
		observeFaqReveal(section);
	}

	var patchQueued = false;
	function patchRussianHomepage() {
		if (!isRu()) return;
		patchQueued = false;
		patchHeaderCta();
		patchTimelineCtas();
		patchPurchaseCards();
		patchAssuranceSection();
		patchManifesto();
		patchFinalFormCta();
		ensureFaqSection();
	}

	function queueRussianPatch() {
		if (patchQueued || !isRu()) return;
		patchQueued = true;
		window.requestAnimationFrame(patchRussianHomepage);
	}

	document.addEventListener('click', function (event) {
		if (!isRu()) return;
		var control = event.target && event.target.closest ? event.target.closest('header a, header button, [data-main-menu] a, [data-main-menu] button') : null;
		if (!control) return;
		if (normalize(control.textContent).indexOf('ПОДОБРАТЬ АККАУНТЫ') === -1) return;
		event.preventDefault();
		event.stopPropagation();
		if (event.stopImmediatePropagation) event.stopImmediatePropagation();
		var target = document.getElementById('iac-final-cta');
		if (target) {
			target.scrollIntoView({ behavior: 'smooth', block: 'start' });
			return;
		}
		window.location.href = '#iac-final-cta';
	}, true);

	document.addEventListener('click', function (event) {
		if (!isRu()) return;
		var control = event.target && event.target.closest ? event.target.closest('button, a') : null;
		if (!control) return;
		if (control.matches('[data-iac-scroll-final], a[href*="/blog/manifesto"]') || normalize(control.textContent).indexOf('ПОДОБРАТЬ ПО СПЕНДУ') !== -1) {
			var target = document.getElementById('iac-final-cta');
			if (target) {
				event.preventDefault();
				event.stopPropagation();
				if (event.stopImmediatePropagation) event.stopImmediatePropagation();
				target.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}
			return;
		}
		var section = control.closest('section');
		if (section && normalize(section.textContent).indexOf('ПОЧЕМУ МЫ') !== -1) {
			[80, 250, 650].forEach(function (delay) {
				window.setTimeout(function () {
					if (window.iacHomeI18nApply) window.iacHomeI18nApply();
					patchManifesto();
				}, delay);
			});
		}
	}, true);

	function boot() {
		cleanupPluginPreloader();
		var loaderHandled = false;

		function onLoaderDone() {
			if (loaderHandled) return;
			loaderHandled = true;
			showChrome();
			revealFooterLinks();
			if (isRu()) {
				// Structural CTA/form cleanup is safe immediately after the site's own loader completes.
				window.setTimeout(function () {
					patchHeaderCta();
					patchManifesto();
					patchFinalFormCta();
				}, 120);
				window.setTimeout(ensureFaqSection, 1400);
			}
			// Keep the conservative full-page localization pass for the remaining long-page copy.
			window.setTimeout(queueRussianPatch, 5000);
		}

		if (!document.querySelector('[data-loader-phase][role="status"]')) {
			onLoaderDone();
			return;
		}

		var loaderCheck = window.setInterval(function () {
			if (!document.querySelector('[data-loader-phase][role="status"]')) {
				window.clearInterval(loaderCheck);
				onLoaderDone();
			}
		}, 250);

		window.setTimeout(function () {
			window.clearInterval(loaderCheck);
			onLoaderDone();
		}, 15000);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})(window, document);
