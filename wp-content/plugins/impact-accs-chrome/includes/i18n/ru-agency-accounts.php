<?php
/**
 * Page-specific RU copy for /accounts/agency-accounts/.
 *
 * Text-only localization. Markup, animations, classes, data attributes and
 * interaction logic stay untouched; replacements happen server-side.
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

	$map = array(
		/* Hero. */
		'>agency<' => '>АГЕНТСКИЕ<',
		'>accounts<' => '>АККАУНТЫ<',
		'Random sellers fail when launch windows close. Verified agency accounts through one channel — availability, terms, and handoff before you go live.' => 'Случайные продавцы подводят, когда сроки запуска поджимают. Проверенные агентские аккаунты через один канал — наличие, условия и передача до старта рекламы.',
		'>Request access<' => '>ЗАПРОСИТЬ ДОСТУП<',
		'>impact.accs<' => '>impact.accs<',
		'>8:18 AM<' => '>08:18<',
		'>8:19 AM<' => '>08:19<',
		'>8:20 AM<' => '>08:20<',
		'>8:21 AM<' => '>08:21<',
		'>8:22 AM<' => '>08:22<',
		'50 agency accounts requested for EU geo' => 'Запрошено 50 агентских аккаунтов для региона ЕС',
		'Terms confirmation pending before handoff' => 'Перед передачей нужно подтвердить условия',
		'Replacement batch queued under desk terms' => 'Партия на замену подготовлена по условиям команды',
		'Volume tier upgrade requested for US desk' => 'Команда США запросила новый тариф по объёму',
		'3 accounts staged and ready for delivery' => '3 аккаунта подготовлены к передаче',
		'Launch window closes in 4 hours' => 'До запуска осталось 4 часа',
		'Repeat order terms expire in 12 hours' => 'Условия повторного заказа истекают через 12 часов',

		/* Supply / alert tabs. */
		'>SUPPLY<' => '>ПОСТАВКА<',
		'>Verified supply, clear terms<' => '>ПРОВЕРЕННАЯ ПОСТАВКА, ПОНЯТНЫЕ УСЛОВИЯ<',
		'>Volume requests<' => '>ЗАПРОСЫ НА ОБЪЁМ<',
		'Flags sudden volume requests and launch windows — confirms availability against your desk before traffic goes live.' => 'Отслеживаем срочные запросы на объём и сроки запуска — подтверждаем наличие под вашу команду до старта рекламы.',
		'>GEO matching<' => '>ПОДБОР ПО РЕГИОНУ<',
		'Matches buyer profile to the right GEO tier — EU, US, and LATAM batches confirmed before you commit.' => 'Подбираем подходящий регион под профиль покупателя — партии для ЕС, США и Латинской Америки подтверждаются до сделки.',
		'>Terms confirmation<' => '>ПОДТВЕРЖДЕНИЕ УСЛОВИЙ<',
		'Tracks handoff end-to-end — when access fails, you get replacement terms, not a dead account.' => 'Контролируем передачу от начала до конца — если доступ не работает, вы получаете замену по согласованным условиям, а не нерабочий аккаунт.',
		'>Supply holds<' => '>ЗАДЕРЖКИ ПОСТАВКИ<',
		'Spots when delivery stalls — no accounts released, no handoff scheduled, no response from the seller.' => 'Сразу видно, если поставка остановилась: аккаунты не переданы, передача не назначена, продавец не отвечает.',
		'>Repeat orders<' => '>ПОВТОРНЫЕ ЗАКАЗЫ<',
		'Surfaces repeat buyer requests — updated terms and volume tiers before the next batch ships.' => 'Учитываем повторные запросы — обновлённые условия и тариф по объёму подтверждаются до отправки следующей партии.',
		'>Direct channel<' => '>ПРЯМОЙ КАНАЛ<',
		'Compares random seller pricing to impact.accs terms — flags markup before you overpay mid-campaign.' => 'Сравниваем цены случайных продавцов с условиями impact.accs — наценка видна до того, как вы переплатите во время кампании.',

		/* Shared alert labels. */
		'>Severity:<' => '>ВАЖНОСТЬ:<',
		'>High<' => '>ВЫСОКАЯ<',
		'>Medium<' => '>СРЕДНЯЯ<',
		'>Status:<' => '>СТАТУС:<',
		'>Open<' => '>ОТКРЫТО<',
		'>Resolved<' => '>РЕШЕНО<',
		'>Root cause<' => '>ПРИЧИНА<',
		'>Recommended action<' => '>ЧТО ДЕЛАТЬ<',
		'>Contact team<' => '>НАПИСАТЬ КОМАНДЕ<',

		/* Alert 1. */
		'>9:18 AM<' => '>09:18<',
		'>Launch: Account Request Pending<' => '>ЗАПУСК: ЗАПРОС АККАУНТОВ В ОБРАБОТКЕ<',
		'A launch window is open and the team needs agency accounts before traffic goes live.' => 'Окно запуска открыто, команде нужны агентские аккаунты до старта рекламы.',
		'Volume request received — matching availability and terms for the team.' => 'Запрос на объём получен — подбираем наличие и условия для команды.',
		'Confirm availability, agree terms, and prepare access for delivery.' => 'Подтвердить наличие, согласовать условия и подготовить доступ к передаче.',

		/* Alert 2. */
		'>10:42 AM<' => '>10:42<',
		'>Volume: EU Agency Batch<' => '>ОБЪЁМ: АГЕНТСКАЯ ПАРТИЯ ДЛЯ ЕС<',
		'50 agency accounts requested for EU geo. Team needs confirmation before 18:00.' => 'Запрошено 50 агентских аккаунтов для ЕС. Команде нужно подтверждение до 18:00.',
		'Buyer profile matched to stable supply tier. Terms draft ready for review.' => 'Профиль покупателя сопоставлен со стабильной поставкой. Проект условий готов к проверке.',
		'Lock terms, confirm volume, and schedule delivery.' => 'Зафиксировать условия, подтвердить объём и назначить передачу.',

		/* Alert 3. */
		'>2:15 PM<' => '>14:15<',
		'>Supply Hold: Platform Access<' => '>ПОСТАВКА ПРИОСТАНОВЛЕНА: ДОСТУП К ПЛАТФОРМЕ<',
		'Handoff paused until buyer confirms GEO and vertical. No accounts released yet.' => 'Передача приостановлена до подтверждения региона и вертикали покупателем. Аккаунты ещё не переданы.',
		'Buyer requested TikTok access but terms for replacement policy were not confirmed.' => 'Покупатель запросил доступ к ТикТок, но условия замены ещё не подтверждены.',
		'Confirm replacement terms and resume delivery.' => 'Подтвердить условия замены и возобновить передачу.',

		/* Alert 4. */
		'>11:03 AM<' => '>11:03<',
		'>Repeat Order: Terms Update<' => '>ПОВТОРНЫЙ ЗАКАЗ: ОБНОВЛЕНИЕ УСЛОВИЙ<',
		'Repeat buyer needs updated volume terms before the next batch ships.' => 'Для повторного заказа нужно обновить условия по объёму до отправки следующей партии.',
		'Previous terms expired — new volume tier requested for the same desk.' => 'Предыдущие условия истекли — для той же команды запрошен новый тариф по объёму.',
		'Agree updated terms and release the next account batch.' => 'Согласовать обновлённые условия и передать следующую партию аккаунтов.',

		/* Alert 5. */
		'>4:37 PM<' => '>16:37<',
		'>Launch Risk: Supply Gap<' => '>РИСК ЗАПУСКА: РАЗРЫВ ПОСТАВКИ<',
		'Media buyer flagged unstable supply from a random seller — launch window closes in 4 hours.' => 'Команда сообщила о нестабильной поставке от случайного продавца — до запуска осталось 4 часа.',
		'Random seller stopped responding — buyer has no confirmed replacement terms and launch window is closing.' => 'Случайный продавец перестал отвечать — условия замены не подтверждены, а срок запуска заканчивается.',
		'Route request to impact.accs desk — confirm terms and prepare delivery.' => 'Передать запрос команде impact.accs — подтвердить условия и подготовить передачу.',

		/* Alert 6. */
		'>8:51 AM<' => '>08:51<',
		'>Cost Alert: Random Seller Markup<' => '>ПРЕДУПРЕЖДЕНИЕ О ЦЕНЕ: НАЦЕНКА СЛУЧАЙНОГО ПРОДАВЦА<',
		'Buyer overpaid a random seller by 340% vs agreed impact.accs terms. Repeat order recommended.' => 'Покупатель переплатил случайному продавцу 340% относительно согласованных условий impact.accs. Рекомендуется повторный заказ.',
		'Unverified supply failed mid-campaign — team needs structured replacement under known terms.' => 'Непроверенная поставка сорвалась во время кампании — команде нужна организованная замена на заранее известных условиях.',
		'Switch to impact.accs supply channel — clear terms, verified delivery.' => 'Перейти на канал поставки impact.accs — понятные условия и проверенная передача.',

		/* Memory. */
		'>Memory<' => '>ИСТОРИЯ<',
		'>Smarter every day<' => '>С КАЖДЫМ ЗАКАЗОМ ТОЧНЕЕ<',
		'impact.accs remembers every order and handoff — geo, volume tier, replacement terms, and what shipped last time. Repeat requests start with context, not from zero.' => 'impact.accs хранит историю каждого заказа и передачи: регион, объём, условия замены и состав прошлой партии. Повторные запросы начинаются с контекста, а не с нуля.',
		'>Learn from every order<' => '>ИСТОРИЯ КАЖДОГО ЗАКАЗА<',
		'Every delivery becomes desk history. impact.accs remembers what shipped, what replaced, and what terms applied — so the next batch starts with context.' => 'Каждая передача остаётся в истории команды. impact.accs помнит, что было передано, что заменено и на каких условиях — следующая партия начинается с готового контекста.',
		'>Your desk, understood<' => '>ЗНАЕМ ВАШУ КОМАНДУ<',
		'Terms adapt as your desk evolves. Volume tiers shift, GEO mix changes, replacement rules update. impact.accs tracks what\'s normal for your team — not a one-off quote from a random seller.' => 'Условия меняются вместе с вашей командой: объёмы растут, регионы меняются, правила замены обновляются. impact.accs знает обычный формат вашей работы, а не предлагает разовую цену случайного продавца.',
		'>Repeat on your terms<' => '>ПОВТОР НА ВАШИХ УСЛОВИЯХ<',
		'Repeat orders surface locked terms automatically. Same volume tier, same replacement policy — no renegotiating from scratch.' => 'Для повторного заказа автоматически поднимаются зафиксированные условия. Тот же объём, те же правила замены — без новых переговоров с нуля.',

		/* Signals. */
		'>Signals<' => '>СИГНАЛЫ<',
		'>Never miss a thing<' => '>НИЧЕГО НЕ ТЕРЯЕТСЯ<',
		'Every request — volume, GEO, terms, handoff, replacement — stays on one trail. impact.accs keeps availability, confirmation, and delivery in one place.' => 'Каждый запрос — объём, регион, условия, передача, замена — остаётся в одной истории. impact.accs хранит наличие, подтверждения и статус передачи в одном месте.',
		'>Requests and terms<' => '>ЗАПРОСЫ И УСЛОВИЯ<',
		'Every launch starts with volume and GEO. We confirm what\'s available for your desk before traffic goes live — not after accounts fail mid-campaign.' => 'Каждый запуск начинается с объёма и региона. Мы подтверждаем наличие для вашей команды до старта рекламы, а не после проблем с аккаунтами во время кампании.',
		'>Desk coordination<' => '>КООРДИНАЦИЯ КОМАНДЫ<',
		'Replacement rules and volume tiers stay on file. When you reorder, prior terms surface automatically — so you\'re not renegotiating from scratch every batch.' => 'Правила замены и условия по объёму сохраняются. При повторном заказе предыдущие договорённости поднимаются автоматически — не нужно заново обсуждать каждую партию.',
		'>Handoff updates<' => '>СТАТУС ПЕРЕДАЧИ<',
		'Delivery status isn’t noise. Staged, confirmed, or replaced — handoff updates stay tied to your request so nothing disappears between seller and desk.' => 'Статус передачи всегда привязан к запросу: подготовлено, подтверждено или заменено. Ничего не теряется между продавцом и вашей командой.',

		/* Included / cards. */
		'>What’s included<' => '>ЧТО ВХОДИТ<',
		'>Ready to launch<' => '>ВСЁ ГОТОВО К ЗАПУСКУ<',
		'Everything you need for agency supply — availability checks, delivery status, and a direct contact. No random sellers required.' => 'Всё для агентской поставки: проверка наличия, статус передачи и прямой контакт. Никаких случайных продавцов.',
		'>Batch confirmation<' => '>ПОДТВЕРЖДЕНИЕ ПАРТИИ<',
		'Multiple account requests, one confirmed batch. We group volume, GEO, and terms into a single handoff — not scattered messages from unknown sellers.' => 'Несколько запросов на аккаунты объединяются в одну подтверждённую партию. Объём, регион и условия собираются в одной передаче, а не в разрозненных сообщениях от неизвестных продавцов.',
		'>Delivery status<' => '>СТАТУС ПЕРЕДАЧИ<',
		'Real-time view of your request. Know what’s reserved, staged, delivered, or waiting on your sign-off.' => 'Актуальный статус запроса: что зарезервировано, подготовлено, передано или ждёт вашего подтверждения.',
		'>Direct contact<' => '>ПРЯМОЙ КОНТАКТ<',
		'One desk contact for availability, terms, and replacements. No ticket roulette — message the team that actually holds the supply.' => 'Один контакт по наличию, условиям и заменам. Без заявок по кругу — вы пишете команде, у которой действительно находится поставка.',

		/* Accessibility text for visual assets. */
		'alt="Anomaly memory illustration"' => 'alt="Иллюстрация истории заказов"',
		'alt="Anomaly signals illustration"' => 'alt="Иллюстрация сигналов"',
		'alt="Error clustering illustration"' => 'alt="Иллюстрация подтверждения партии"',
		'alt="System status illustration"' => 'alt="Иллюстрация статуса передачи"',

		/* Shared footer on this page. */
		'>REQUEST ACCESS<' => '>ЗАПРОСИТЬ ДОСТУП<',
		'Closed access infrastructure for media buying teams. Accounts and access for launch, tests, and scale.' => 'Закрытая инфраструктура для команд закупки рекламы. Аккаунты и доступ для запусков, тестов и масштабирования.',
		'placeholder="FIRST NAME"' => 'placeholder="ИМЯ"',
		'placeholder="LAST NAME"' => 'placeholder="ФАМИЛИЯ"',
		'placeholder="EMAIL ADDRESS"' => 'placeholder="ЭЛЕКТРОННАЯ ПОЧТА"',
		'placeholder="COMPANY NAME"' => 'placeholder="КОМПАНИЯ"',
		'>Apply<' => '>ОТПРАВИТЬ ЗАЯВКУ<',
		'>Access<' => '>ДОСТУП<',
	);

	uksort(
		$map,
		static function ( $a, $b ) {
			return strlen( $b ) - strlen( $a );
		}
	);

	return str_replace( array_keys( $map ), array_values( $map ), $html );
};
