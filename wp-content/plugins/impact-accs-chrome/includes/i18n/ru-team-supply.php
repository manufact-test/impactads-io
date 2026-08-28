<?php
/**
 * Page-specific RU copy for /accounts/team-supply/.
 *
 * Text-only localization for the Team Supply account page. Existing markup,
 * interactions and tab identifiers stay intact; visible copy is replaced on
 * the server before the global RU map runs.
 *
 * @package ImpactAccsChrome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return static function ( $html ) {
	if ( ! is_string( $html ) || '' === $html ) {
		return $html;
	}

	$replace_once = static function ( $source, $search, $replace ) {
		$position = strpos( $source, $search );
		if ( false === $position ) {
			return $source;
		}
		return substr_replace( $source, $replace, $position, strlen( $search ) );
	};

	/* Contextual replacements belong only to the feature template, not footer. */
	if ( false !== strpos( $html, 'iac-feature-team' ) ) {
		$html = $replace_once( $html, '>Request access<', '>СОГЛАСОВАТЬ ПОСТАВКУ<' );
		$html = $replace_once( $html, '>Request access<', '>ОБСУДИТЬ ОБЪЁМ<' );

		/* The same encoded paragraph is reused on screens three and four. */
		$shared_encoded = 'Ask what&#x27;s available, what&#x27;s pending, and what blocks your launch — in plain language. One channel for availability, terms, and delivery.';
		$html = $replace_once(
			$html,
			$shared_encoded,
			'Список под объём, трастовые аккаунты с белой историей и быстрая замена позиции. Команда продолжает залив, а не ищет нового продавца.'
		);
		$html = $replace_once(
			$html,
			$shared_encoded,
			'Один владелец ведёт весь цикл: принимает запрос, подтверждает список, передаёт аккаунты на проверку, решает замены и собирает следующую поставку.'
		);
	}

	$map = array(
		/* Hero. */
		'>team<' => '>РЕГУЛЯРНАЯ<br/>ПОСТАВКА<',
		'>supply<' => '>ДЛЯ КОМАНДЫ<',
		'Supply infrastructure for media buying teams — not random sellers. One desk for volume, geo, terms, and handoff when your team scales access.' => 'Сотни трастовых Google Ads спенд-аккаунтов под объём, USA, USD и вертикаль команды. Проверяете каждую поставку до оплаты, лично отвечает владелец impact.',
		'title="Toggle View"' => 'title="Сменить вид"',
		'aria-label="Toggle map view"' => 'aria-label="Сменить вид карты"',
		'Loading team supply map...' => 'Загружаем карту поставок...',

		/* Screen 2: regular supply process. */
		'>Command-line interface<' => '>КАК РАБОТАЕМ<',
		'>Supply for your desk<' => '>ПОСТАВКА ПОД ТЕМП КОМАНДЫ<',
		'>Request supply<' => '>ПЕРЕДАЁТЕ ЗАДАЧУ<',
		'Ask what\'s available, what\'s pending, and what blocks your launch — in plain language. One channel for availability, terms, and delivery.' => 'Указываете объём, нужные тиры, гео, валюты, вертикали и желаемый график закупок.',
		'>Check availability<' => '>СОГЛАСОВЫВАЕМ ФОРМАТ<',
		'Check availability with a plain request. Filter by geo, platform, volume, or vertical — get a clear answer in one thread.' => 'Фиксируем параметры подбора, цены по тирам, порядок проверки и условия замены.',
		'>Order history<' => '>ПОДТВЕРЖДАЕМ СПИСОК<',
		'Find previous orders by keyword. Every request is searchable — you never lose context on locked terms or past batches.' => 'Проверяем, какие аккаунты доступны сейчас и что потребуется найти под запрос. Срок сообщаем заранее.',
		'>Track delivery<' => '>ПЕРЕДАЁМ НА ПРОВЕРКУ<',
		'Track delivery in real time for any batch. Filter by geo, platform, or status and follow handoff from the same channel.' => 'Команда проверяет вход, спенд, гео и валюту каждого аккаунта до оплаты.',
		'>Lock terms<' => '>ПРИНИМАЕМ ОПЛАТУ<',
		'Lock terms for a new volume tier in one step. Clear conditions, replacement policy, and delivery window — before access ships.' => 'После подтверждения оплачиваете в USDT TRC20. По запросу проводим сделку через гаранта.',
		'>Manage batches<' => '>ПОВТОРЯЕМ ПОСТАВКУ<',
		'List, inspect, and manage all open batches from one desk. See geo, volume tier, terms status, and delivery at a glance.' => 'Следующий заказ начинаем с известных параметров. Актуальный список и цену подтверждаем заново.',

		/* Central state 01: team request. */
		'>DESK<' => '>ЗАКАЗ<',
		'impact messages send &quot;Why are my agency accounts for EU launch?&quot; --wait' => 'ЗАПРОС КОМАНДЫ',
		'Analyzing your access infrastructure data...' => 'Объём: 200 аккаунтов',
		'50 agency accounts confirmed for EU launch' => 'Гео: USA',
		'Root cause identified:' => 'Валюта: USD',
		'  • Repeat order queue at capacity for this geo' => 'Спенд: $2 000–3 000',
		'  • Terms locked · handoff today via direct channel' => 'Формат: регулярная закупка',
		'  • Volume tier terms still pending buyer sign-off' => 'Статус: запрос принят владельцем impact.',
		'Recommended fixes:' => 'ОБЪЁМ · СПЕНД · ГЕО · ВАЛЮТА · ВЕРТИКАЛЬ',
		'  1. 1. Confirm volume tier and replacement policy' => '',
		'  2. 2. Lock terms before handoff' => '',
		'  3. Confirm replacement policy before next batch ships' => '',

		/* Central state 02: supply terms. */
		'impact.accs status --geo EU --platform agency --volume 50' => 'УСЛОВИЯ ПОСТАВКИ',
		'Checking availability for your request...' => 'Количество: 200 аккаунтов',
		'Status for EU agency batch:' => 'Цена: по открытому тиру',
		'  Step                  Status               Detail' => 'Проверка: до оплаты',
		'  2026-03-19T18:15:42Z  terms-desk      Repeat order channel overloaded' => 'Замена: пока аккаунт не тронут',
		'  2026-03-19T18:15:43Z  terms-desk      Repeat order channel overloaded' => 'Оплата: USDT TRC20 / гарант',
		'  2026-03-19T18:16:12Z  supply-match      Volume tier pending buyer sign-off' => 'Статус: ждём подтверждения команды.',
		'  2026-03-19T18:16:18Z  handoff         Replacement policy not confirmed' => 'УСЛОВИЯ ИЗВЕСТНЫ ЗАРАНЕЕ',
		'  2026-03-19T18:16:45Z  terms-desk      Repeat order channel overloaded' => '',
		'  2026-03-19T18:17:03Z  supply-match      Volume tier pending buyer sign-off' => '',

		/* Central state 03: confirmed list. */
		'impact.accs history search &quot;EU repeat order&quot;' => 'СПИСОК ПРОВЕРЕН',
		'Searching orders for &quot;EU repeat order&quot;...' => 'Доступный объём подтверждён владельцем.',
		'Found 4 matching threads:' => 'Если всей партии нет сейчас, согласуем частичную поставку и срок поиска оставшихся аккаунтов.',
		'  EU agency batch — terms locked, handoff today              2 hours ago' => 'Никаких замен гео, валюты или тира без согласования.',
		'  US agency batch — terms locked, handoff tomorrow           1 day ago' => 'НАЛИЧИЕ И СРОК ПОДТВЕРЖДЕНЫ',
		'  TikTok access — replacement policy confirmed        3 days ago' => '',
		'  Repeat EU order — same terms as last batch               1 week ago' => '',

		/* Central state 04: batch inspection. */
		'impact.accs track --batch EU-agency-50' => 'ПАРТИЯ ПЕРЕДАНА НА ПРОВЕРКУ',
		'Tracking delivery for batch EU-agency-50...' => 'Проверьте по каждой позиции:',
		'Press Ctrl+C to stop' => 'вход в аккаунт;',
		'18:42:13  INFO   [desk]    Request completed GET /api/v1/projects 24ms' => 'заявленный спенд;',
		'18:42:13  INFO   [terms] Background job completed 156ms' => 'гео;',
		'18:42:14  ERROR  [desk]    Terms sign-off pending' => 'валюту;',
		'18:42:14  INFO   [match] 50 accounts matched to EU geo' => 'администраторский доступ.',
		'18:42:15  ERROR  [terms] Handoff scheduled — direct channel' => 'До окончания проверки не вносите изменения.',
		'18:42:16  INFO   [desk]    Cache hit for user sessions 2ms' => 'СНАЧАЛА ПРОВЕРКА',
		'18:42:17  ERROR  [match] Replacement policy confirmed' => '',

		/* Central state 05: payment and handoff. */
		'impact data-sources connect' => 'ПАРАМЕТРЫ ПОДТВЕРЖДЕНЫ',
		'? Select platform: Facebook · Google · TikTok · Other' => 'Команда приняла трастовые аккаунты.',
		'? Batch name: EU-agency-50' => 'Оплата: USDT TRC20',
		'Created batch EU-agency-50' => 'Дополнительно: сделка через гаранта',
		'Next steps:' => 'После оплаты: окончательная передача админ-доступа',
		'  1. Send request: geo, platform, volume, vertical' => 'ПОТОМ ОПЛАТА',
		'  2. Confirm geo and vertical match' => '',
		'  3. Confirm terms and replacement policy' => '',
		'  4. Access ships via direct handoff' => '',

		/* Central state 06: repeat supply. */
		'impact projects list' => 'СЛЕДУЮЩАЯ ПОСТАВКА',
		'  Project                  Environment  Region     Status' => 'Прошлые параметры сохранены в диалоге.',
		'  web-api-production       production   us-west-2  active' => 'Команда сообщает:',
		'  web-api-staging          staging      us-west-2  active' => 'новый объём;',
		'  mobile-backend-prod      production   us-east-1  active' => 'что изменилось в гео, валюте или тирах;',
		'  data-pipeline-staging    staging      us-west-2  active' => 'нужный срок.',
		'  EU-agency-50            confirmed  EU         ready' => 'Владелец проверяет новый список аккаунтов и подтверждает актуальную цену.',
		'5 projects found' => 'БЕЗ СТАРТА С НУЛЯ',

		/* Screen 3: media buying value. */
		'>Skills<' => '>ДЛЯ МЕДИАБАИНГА<',
		'>Built for buyers<' => '>КОМАНДА ЛЬЁТ. ПОСТАВКА НЕ ТОРМОЗИТ.<',
		'>confirm-availability<' => '>ОБЪЁМ ПОД ЗАДАЧУ<',
		'Confirm availability with full context. Match geo, platform, and volume — see terms and delivery window in one place.' => 'Подбираем сотни аккаунтов, тиры, USA и USD под темп команды. Чего нет в списке — ищем под запрос.',
		'>review-terms<' => '>СТАБИЛЬНОЕ КАЧЕСТВО<',
		'Get instant desk-wide status. Check open batches, locked terms, and pending handoffs across every platform.' => 'Только трастовые Google Ads аккаунты с реальным спендом и белой историей. Без авторегов и пустого фарма.',
		'>track-handoff<' => '>ЗАМЕНА БЕЗ СПОРОВ<',
		'Follow any order through the full pipeline. Request → match → terms → access → repeat — with nothing lost between steps.' => 'Если до изменений аккаунт не работает или его параметры не совпадают, заменяем именно эту позицию.',
		'alt="Skills illustration"' => 'alt="Иллюстрация поставки для медиабаинга"',

		/* Screen 4: repeat supplier. */
		'>More features<' => '>ФОРМАТ ДЛЯ КОМАНД<',
		'>Built for desks<' => '>ПОСТАВЩИК ДЛЯ ПОВТОРНЫХ ЗАКУПОК<',
		'>Buyer-ready process<' => '>ПЛАНОВЫЙ ОБЪЁМ<',
		'Clear buyer-ready process. Structured requests, confirmed terms, and tracked handoff — so your desk always knows what ships next.' => 'Согласуем типичные тиры, USA, USD и частоту закупок. Перед каждой поставкой подтверждаем актуальный список, цену и срок.',
		'>Direct channel<' => '>ПРЯМОЙ КОНТАКТ<',
		'Structured supply channel for media buying desks. Request access, lock terms, track delivery, and repeat orders — without random seller chats.' => 'Все вопросы решаются лично с владельцем impact. в Telegram: подбор, проверка, оплата, замена и повторные заказы. Поддержка работает 24/7.',
		'>Repeat orders<' => '>СЛЕДУЮЩИЙ ЗАКАЗ — БЫСТРЕЕ<',
		'Structured repeat-order flow. Every batch, term, and handoff accessible through one desk — built for teams that scale access.' => 'Предыдущие параметры и формат работы остаются в истории диалога. Команда сообщает новый объём, а не объясняет весь процесс заново.',
		'alt="Agent-friendly docs illustration"' => 'alt="Иллюстрация планового объёма"',
		'alt="MCP server illustration"' => 'alt="Иллюстрация прямого контакта"',
		'alt="Repeat orders illustration"' => 'alt="Иллюстрация повторных заказов"',

		/* Final request form on this page only. */
		'>REQUEST ACCESS<' => '>ПОЛУЧИТЕ АККАУНТЫ.<br/>ПРОВЕРЬТЕ. ПОТОМ ПЛАТИТЕ.<',
		'Closed access infrastructure for media buying teams. Accounts and access for launch, tests, and scale.' => 'Напишите, что нужно. Владелец impact. подберёт аккаунты и передаст их на проверку — без предоплаты вслепую.',
		'@founderads · direct owner contact · verification before payment · support 24/7' => 'прямой контакт с владельцем · проверка до оплаты · поддержка 24/7',
		'placeholder="FIRST NAME"' => 'placeholder="ВАШ TELEGRAM ИЛИ ЭЛ. ПОЧТА · @username или example@mail.com"',
		'placeholder="LAST NAME"' => 'placeholder="НУЖНЫЙ СПЕНД · Например, $2 000–3 000"',
		'placeholder="EMAIL ADDRESS"' => 'placeholder="ГЕО И ВАЛЮТА · Например, USA · USD"',
		'placeholder="COMPANY NAME"' => 'placeholder="ВЕРТИКАЛЬ И ОБЪЁМ · Нутра · 50 аккаунтов регулярно"',
		'aria-label="First name"' => 'aria-label="Ваш Telegram или эл. почта"',
		'aria-label="Last name"' => 'aria-label="Нужный спенд"',
		'aria-label="Email address"' => 'aria-label="Гео и валюта"',
		'aria-label="Company name"' => 'aria-label="Вертикаль и объём"',
		'>Apply<' => '>ПОЛУЧИТЬ АККАУНТЫ НА ПРОВЕРКУ<',
		'>Request access<' => '>ПОЛУЧИТЬ АККАУНТЫ НА ПРОВЕРКУ<',
		'>Access<' => '>ЗАЯВКА<',
	);

	uksort(
		$map,
		static function ( $a, $b ) {
			return strlen( $b ) - strlen( $a );
		}
	);

	return str_replace( array_keys( $map ), array_values( $map ), $html );
};