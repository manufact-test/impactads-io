(function () {
	'use strict';

	var pairs = [
		['Launch blocked — access needed', 'Команде нужен аккаунт под залив'],
		['Buyer desk needs agency accounts before traffic goes live.', 'Подбор по спенду, USA и USD с проверкой до оплаты.'],
		['Supply stable', 'Медиабаингу нужны трастовые аккаунты'],
		['Repeat order channel active — terms unchanged.', 'Десятки аккаунтов от одного поставщика: проверка до оплаты и связь с владельцем.'],
		['Volume request — EU', 'Команде нужен стабильный объём аккаунтов'],
		['50 accounts · GEO locked. Terms needed before 18:00.', 'Десятки и сотни аккаунтов без простоев и поиска нового поставщика'],
		['@impact.accs Need EU accounts before launch.', '@founderads Проверил: спенд, гео и валюта совпадают. Готов оплатить.'],
		['Need EU accounts before launch.', 'Нужен аккаунт под залив?'],
		['Terms confirmed at 9:11 AM. Preparing delivery now.', 'Нужен Google Ads аккаунт: спенд $2 000–3 000, USA, USD, под серую вертикаль.'],
		['EU · 50 agency accounts — terms confirmed.', 'Спенд: $2 000–3 000 · Гео: USA · Валюта: USD'],
		['Delivery scheduled before the launch window.', 'Оплата — после проверки. Замена — пока аккаунт не тронут.'],
		['Matching supply for AgencyAccounts.RequestEU().', 'Оплата получена'],
		['Terms confirmed. Delivery scheduled before the launch window.', 'Админ-доступ передан. Замена действует, пока аккаунт не тронут. Поддержка 24/7, напрямую с владельцем.'],
		['Access confirmed', 'АККАУНТ ПРОВЕРЕН'],
		['Accounts delivered on agreed terms. Launch window open.', 'Параметры подтверждены. Теперь можно оплачивать.'],
		['Working resource — request logged, supply matching.', 'Аккаунт найден · готов к проверке'],
		['Active channels: EU desk, Agency pool, +3 more', 'Подбор трастовых аккаунтов для медиабаинга готов'],
		['5 years supplying access — repeat orders active', '50 спенд-аккаунтов под массовый залив.'],
		['Repeat order channel active. Terms unchanged.', 'USA · USD · белая история · цены по открытым тирам'],
		['Working resource ready for the next launch.', 'Каждый аккаунт передаём на проверку до оплаты.'],
		['Supply confirmed', '50 АККАУНТОВ ЗАРЕЗЕРВИРОВАНЫ'],
		['Supply matched', 'Личный канал с владельцем открыт'],
		['Terms confirmed. Delivery in progress.', 'Подбор, замена и следующие закупки — напрямую, 24/7.'],
		['Volume request — GEO: EU', 'ПОСТАВКА ДЛЯ КОМАНДЫ'],
		['Terms draft ready for EU · 50 accounts. Volume and GEO locked.', 'Условия по объёму готовы. 200 аккаунтов в месяц · USA · подбор по спенду и валюте'],
		['50 accounts · EU · delivery before 18:00.', 'Список аккаунтов под регулярные заливы. Цены — по открытым тирам.'],
		['Posted to #requests — volume logged.', 'ОБЪЁМ ЗАФИКСИРОВАН'],
		['Request logged — matching supply and terms.', 'Условия подтверждены. Формируем первую поставку.'],
		['Desk notified @team. Delivery queued.', 'По всем закупкам на связи лично владелец, 24/7.'],
		['VolumeRequestPending', 'Нужен аккаунт под залив?'],
		['REQUEST STATUS', 'ПАРАМЕТРЫ АККАУНТА'],
		['SUPPLY STATUS', 'ПАРАМЕТРЫ ПОДБОРА'],
		['VOLUME TERMS', 'ПРЕДЛОЖЕНИЕ ДЛЯ КОМАНДЫ'],
		['CONTACT TEAM', 'ПОДДЕРЖКА 24/7'],
		['SCROLL DOWN', 'ЛИСТАЙТЕ ВНИЗ'],
		['Denis A.', 'Медиабайер'],
		['Elena M.', 'Команда']
	];

	function isRussian() {
		return document.documentElement.lang === 'ru' || document.documentElement.classList.contains('iac-lang-ru');
	}

	function patch() {
		if (!isRussian()) return;
		var hero = document.querySelector('[data-section="home-hero"]');
		if (!hero) return;
		var walker = document.createTreeWalker(hero, NodeFilter.SHOW_TEXT, null);
		var node;
		while ((node = walker.nextNode())) {
			var parent = node.parentElement;
			if (!parent || parent.closest('script,style,canvas,svg,[data-loader-phase]')) continue;
			var value = node.nodeValue || '';
			if (!/[A-Za-z]/.test(value)) continue;
			for (var i = 0; i < pairs.length; i += 1) {
				if (value.indexOf(pairs[i][0]) !== -1) value = value.split(pairs[i][0]).join(pairs[i][1]);
			}
			if (value !== node.nodeValue) node.nodeValue = value;
		}
	}

	function schedule() {
		[22000, 24000, 28000, 36000].forEach(function (delay) {
			window.setTimeout(patch, delay);
		});
	}

	if (document.readyState === 'complete') schedule();
	else window.addEventListener('load', schedule, { once: true });
})();
