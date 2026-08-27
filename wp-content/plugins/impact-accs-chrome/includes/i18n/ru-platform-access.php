<?php
/**
 * Page-specific RU copy for /accounts/platform-access/.
 *
 * Text-only localization. The source markup, visual structure and interactions
 * remain unchanged; replacements happen server-side before the HTML is sent.
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

	$replace = static function ( $source, $map ) {
		if ( '' === $source || empty( $map ) ) {
			return $source;
		}
		return str_replace( array_keys( $map ), array_values( $map ), $source );
	};

	$replace_once = static function ( $source, $search, $replacement ) {
		$position = strpos( $source, $search );
		if ( false === $position ) {
			return $source;
		}
		return substr_replace( $source, $replacement, $position, strlen( $search ) );
	};

	/* Feature body: split by stable section IDs so repeated labels can receive
	 * different Russian copy in different screens without touching classes,
	 * data attributes or client-side behavior. */
	if ( false !== strpos( $html, 'iac-feature-platform' ) ) {
		$marker_second = 'id="conversational-debugging-examples"';
		$marker_third  = 'id="dynamic-visualization"';
		$marker_fourth = 'id="slack-conversations"';
		$marker_fifth  = 'id="other-features"';

		$pos_second = strpos( $html, $marker_second );
		$pos_third  = strpos( $html, $marker_third );
		$pos_fourth = strpos( $html, $marker_fourth );
		$pos_fifth  = strpos( $html, $marker_fifth );

		if (
			false !== $pos_second &&
			false !== $pos_third &&
			false !== $pos_fourth &&
			false !== $pos_fifth &&
			$pos_second < $pos_third &&
			$pos_third < $pos_fourth &&
			$pos_fourth < $pos_fifth
		) {
			$hero   = substr( $html, 0, $pos_second );
			$second = substr( $html, $pos_second, $pos_third - $pos_second );
			$third  = substr( $html, $pos_third, $pos_fourth - $pos_third );
			$fourth = substr( $html, $pos_fourth, $pos_fifth - $pos_fourth );
			$fifth  = substr( $html, $pos_fifth );

			$hero = $replace(
				$hero,
				array(
					'>platform<' => '>ТРАСТОВЫЕ<br class="hidden md:block"/>СПЕНД-АККАУНТЫ<',
					'>access<' => '>ПОД ЗАЛИВ<',
					'Facebook, Google, TikTok — platform accounts and ad access through one channel. Tell us geo and volume; we reply with what’s available and on what terms.' => 'Подберём десятки Google Ads аккаунтов под нужный спенд, USA, USD и вертикаль. Получаете доступ, проверяете параметры — и только потом оплачиваете.',
					'Facebook, Google, TikTok — platform accounts and ad access through one channel. Tell us geo and volume; we reply with what\'s available and on what terms.' => 'Подберём десятки Google Ads аккаунтов под нужный спенд, USA, USD и вертикаль. Получаете доступ, проверяете параметры — и только потом оплачиваете.',
					'>Request access<' => '>ПОЛУЧИТЬ АККАУНТЫ НА ПРОВЕРКУ<',
					'Is platform access ready for the EU launch?' => 'Нужны 50 аккаунтов · USA · USD · спенд $2 000–3 000',
					'Volume tier terms still need sign-off before release' => 'Трастовые аккаунты найдены · цена — $400 за позицию',
					'Launch blocked — access not confirmed' => 'Можно проверить всю партию до оплаты?',
					'TikTok batch — supply tight' => 'Да. Проверяете доступ, спенд, гео и валюту',
					'Repeat batch queued — capacity check needed' => 'Откуда аккаунты и какая у них история?',
					'Previous supplier fell through mid-batch' => 'Аккаунты от цифровых агентств · история открутки белая',
					'Team waiting on handoff' => 'Что, если параметры части аккаунтов не совпадут?',
					'Geo mismatch on last batch' => 'Заменим позиции, пока вы не внесли изменения',
					'>Team Lead<' => '>Команда<',
					'>now<' => '>сейчас<',
				)
			);

			$second = $replace(
				$second,
				array(
					'>How to order<' => '>КАК ПРОХОДИТ ПОКУПКА<',
					'>Availability, terms, handoff<' => '>ОТ ЗАПРОСА ДО ДОСТУПА<',
					'>Availability<' => '>ЗАДАЧА<',
					'Platform, geo, and volume — we confirm what’s in stock and when it can hand off.' => 'Укажите нужный спенд, USA, USD, вертикаль и объём — десятки или сотни аккаунтов.',
					'>Terms<' => '>ПОДБОР<',
					'Volume tier, replacement policy, and payment — agreed in writing before accounts ship.' => 'Проверяем актуальный список аккаунтов и формируем подбор с подходящими параметрами.',
					'>Handoff<' => '>ПРОВЕРКА<',
					'Once terms are set, accounts are prepared and passed to your team.' => 'Передаём аккаунты до оплаты. Команда проверяет вход, спенд, гео и валюту каждой позиции.',
					'>Before launch<' => '>ОПЛАТА<',
					'Slot count, geos, and open terms — confirmed before traffic goes live.' => 'Если всё совпадает — оплачиваете в USDT TRC20 или проводите сделку через гаранта.',
					'>Volume<' => '>ПЕРЕДАЧА<',
					'Batch size and timing agreed upfront, including repeat and multi-geo orders.' => 'После оплаты передаём аккаунты на ваши почты и выдаём администраторский доступ.',
					'>Your request<' => '>ЗАМЕНА<',
					'Where your request stands — reserved, in progress, or waiting on your sign-off.' => 'Если до изменений позиция не работает или параметры не совпадают — заменим её.',

					'Need 50 EU platform accounts for launch — what’s available?' => 'Нужны 50 трастовых Google Ads аккаунтов: спенд $2 000–3 000, USA, USD, под серую вертикаль.',
					'50 EU slots reserved for you.' => 'Задача принята. Проверяем список и подбираем аккаунты под указанные параметры.',
					'Volume tier and replacement policy confirmed.' => 'СПЕНД · ГЕО · ВАЛЮТА · ОБЪЁМ',
					'Handoff today. 12 EU slots still open after this batch.' => '',

					'When does the TikTok batch ship?' => 'Есть 50 подходящих аккаунтов в текущем списке?',
					'Still open on the TikTok batch:' => 'Да. 50 аккаунтов: спенд $2 000–3 000 · USA · USD · трастовая история. Цена — $400 за позицию.',
					'Volume tier terms need your sign-off.' => 'Всю партию можно получить на проверку до оплаты.',
					'Replacement policy — same as last batch or updated tier?' => 'ПАРТИЯ ПОДОБРАНА',

					'Can we lock terms for the 50-account EU request?' => 'Проверили. Все аккаунты работают, параметры совпадают.',
					'>On your batch:<' => '>Доступы отправлены на указанные почты. Проверьте вход, спенд, гео и валюту каждой позиции.<',
					'>EU geo: 50 slots confirmed<' => '>ПРОВЕРКА ДО ОПЛАТЫ<',
					'>Terms draft — waiting on your sign-off<' => '><',
					'>Replacement policy: not locked<' => '><',
					'>Handoff channel: ready<' => '><',
					'Next step: confirm terms and schedule delivery today.' => '',

					'How many EU accounts still need terms sign-off?' => 'Условия подходят. Как оплатить партию?',
					'Launch in 4 hours. 12 accounts still waiting on terms.' => 'Цена — $400 за позицию. Оплата в USDT TRC20. По желанию проведём сделку через гаранта.',
					'3 geos covered: EU (50), US (30), APAC (20).' => 'ОПЛАТА ПОСЛЕ ПРОВЕРКИ',
					'Volume tier locked. Replacement policy still needs buyer sign-off.' => '',

					'Is the TikTok batch still on schedule?' => 'Доступы получены. Переходим к настройке залива.',
					'Two items still open on the TikTok batch:' => 'Оплата получена. Аккаунты переданы на ваши почты, администраторский доступ активен.',
					'Volume tier terms need buyer sign-off before release.' => 'АДМИН-ДОСТУП ПЕРЕДАН',
					'Replacement policy flag must be confirmed before accounts ship. ETA: handoff tomorrow if terms lock today.' => '',

					'Are we cleared for the EU launch batch?' => 'Что делать, если до настройки часть аккаунтов перестанет работать?',
					'>EU batch status:<' => '>Напишите владельцу. Пока вы не внесли изменения, нерабочие или несоответствующие позиции заменим.<',
					'>Confirmed:<' => '>ПОДДЕРЖКА 24/7<',
					'>38 of 50 accounts<' => '><',
					'>Terms:<' => '><',
					'>volume tier locked, replacement pending<' => '><',
					'>Geo match:<' => '><',
					'>EU verified<' => '><',
					'>Handoff:<' => '><',
					'>direct channel ready<' => '><',
					'12 accounts still need terms sign-off before delivery today.' => '',

					'placeholder="Facebook EU, 50 accounts..."' => 'placeholder="СПЕНД · ГЕО · ВАЛЮТА · ОБЪЁМ"',
					'>Team Lead<' => '>Вы<',
					'>Elena M.<' => '>Вы<',
					'>Tom Nagengast<' => '>Вы<',
					'>impact.accs<' => '>impact. 24/7<',
					'>9:14 AM<' => '>09:14<',
					'>3:12 PM<' => '>15:12<',
					'>10:03 AM<' => '>10:03<',
					'>11:47 AM<' => '>11:47<',
					'>2:30 PM<' => '>14:30<',
					'>4:05 PM<' => '>16:05<',
				)
			);

			/* In the third screen the same short English labels occur both in
			 * decorative graphs and in benefit cards. Replace graph labels first,
			 * then translate the remaining card labels. */
			$third = $replace_once( $third, '>Availability<', '>ПРОВЕРКА АККАУНТА<' );
			$third = $replace_once( $third, '>Handoff<', '>ПЕРЕДАЧА ДОСТУПА<' );
			$third = $replace(
				$third,
				array(
					'>What you get<' => '>ЧТО ВЫ ПОЛУЧАЕТЕ<',
					'>Clear answers<' => '>ВСЁ ИЗВЕСТНО<',
					'>upfront<' => '>ДО ОПЛАТЫ<',
					'One channel for availability, terms, and handoff — confirmed before you commit.' => 'До сделки вы знаете спенд, USA, USD, цену по тиру и условия замены. Получаете партию, проверяете параметры — и только потом оплачиваете.',
					'>9:03 – 9:19<' => '>ДО ОПЛАТЫ<',
					'>9:03<' => '>ДОСТУП<',
					'>9:07<' => '>СПЕНД<',
					'>9:11<' => '>ГЕО<',
					'>9:15<' => '>ВАЛЮТА<',
					'>9:19<' => '><',
					'>4s<' => '>4 с<',
					'>1s<' => '>1 с<',
					'>100ms<' => '>100 мс<',
					'Sep 01, 10:30:00<!-- --> – <!-- -->Oct 15, 10:30:00' => 'ПОСЛЕ ПОДТВЕРЖДЕНИЯ',
					'>Availability<' => '>РЕАЛЬНАЯ ИСТОРИЯ<',
					'What’s in stock for your geo and platform — confirmed before you move forward.' => 'Трастовые Google Ads аккаунты с реальным спендом и историей открутки в белых нишах. Без авторегов и пустого фарма.',
					'>Terms<' => '>ПАРАМЕТРЫ И ЦЕНА<',
					'Volume tier, replacement policy, and payment — locked in writing.' => 'Спенд, гео, валюта и цена по открытому тиру согласованы заранее. Все параметры можно проверить до оплаты.',
					'>Handoff<' => '>ДОСТУП И ЗАМЕНА<',
					'The same reply goes to your buyer and lead — one contact, one conversation.' => 'Аккаунты передаются на ваши почты с администраторским доступом. Несоответствующие позиции заменим до ваших изменений.',
				)
			);

			/* The first “Terms” in screen four is the status-card action; the
			 * later occurrence is the second benefit title. */
			$fourth = $replace_once( $fourth, '>Terms<', '>ЗАМЕНА ДО ИЗМЕНЕНИЙ<' );

			$status_pattern = '/<span class="text-foreground\/50">Status:<\/span> <span class="text-foreground font-semibold">Open<\/span>/';
			$fourth = preg_replace(
				$status_pattern,
				'<span class="text-foreground/50">СТАТУС:</span> <span class="text-foreground font-semibold">ГОТОВ К ПРОВЕРКЕ</span>',
				$fourth,
				1
			);
			$fourth = preg_replace(
				$status_pattern,
				'<span class="text-foreground/50">ОТВЕТСТВЕННЫЙ:</span> <span class="text-foreground font-semibold">ВЛАДЕЛЕЦ</span>',
				$fourth,
				1
			);

			$fourth = $replace(
				$fourth,
				array(
					'>For teams<' => '>ПОДДЕРЖКА 24/7<',
					'>One team,<' => '>ОДИН ЧАТ.<',
					'>one contact<' => '>ОДИН ОТВЕТСТВЕННЫЙ.<',
					'One contact for the whole team. Same terms, same handoff.' => 'По каждой покупке отвечает лично владелец impact. Подбор, проверка, оплата и замена — без менеджеров и переходов между чатами.',
					'>impact.accs<' => '>impact.<',
					'>EU batch — terms open<' => '>Условия замены подтверждены<',
					'Launch in 4 hours. Team needs 50 EU platform accounts — terms and replacement policy still open.' => 'Если до ваших изменений позиция не работает или не соответствует заявленному спенду, USA или USD — заменим.',
					'>Confirm terms<' => '>ПРОВЕРКА ДО ОПЛАТЫ<',
					'>Team Lead<' => '>Вы<',
					'>Elena M.<' => '>Вы<',
					'>@impact.accs<' => '>@founderads<',
					'EU batch — are we cleared on terms?' => 'Нужны 50 аккаунтов USA в USD со спендом $2 000–3 000. Что есть в списке?',
					'Volume tier terms still need sign-off before release' => '50 трастовых аккаунтов есть. Цена — $400 за позицию. Передаю на проверку до оплаты.',
					'Also need TikTok accounts — same handoff window?' => 'Если доступ не работает или параметры не совпадут?',
					'>Availability<' => '>ЛИЧНО С ВЛАДЕЛЬЦЕМ<',
					'Facebook, Google, TikTok — one channel. Stock, terms, and timing in one reply.' => 'По каждой покупке отвечает владелец impact., а не менеджер, бот или оператор поддержки.',
					'>Terms<' => '>ВСЁ В ОДНОМ ЧАТЕ<',
					'Volume tier and replacement policy — agreed once, shared with the whole desk.' => 'Подбор аккаунта, проверка параметров, оплата, передача доступа и вопросы по замене — в Telegram.',
					'>Handoff<' => '>ОТВЕТСТВЕННОСТЬ 24/7<',
					'Accounts passed to your team. Repeat orders run on terms already on file.' => 'До ваших изменений за работоспособность и соответствие аккаунта заявленным параметрам отвечает impact.',
					'>9:17 AM<' => '>09:17<',
					'>9:16 AM<' => '>09:16<',
					'>9:18 AM<' => '>09:18<',
					'>Status:<' => '>СТАТУС:<',
					'>Open<' => '>ГОТОВ К ПРОВЕРКЕ<',
				)
			);

			$fifth = $replace(
				$fifth,
				array(
					'>More context<' => '>ПОВТОРНЫЕ ЗАКАЗЫ<',
					'>What stays on file<' => '>ОДИН РАЗ ОБСУДИЛИ. ДАЛЬШЕ — БЫСТРЕЕ.<',
					'Your geos, volume tiers, and agreed terms stay on record — repeat orders start faster.' => 'Спенд, гео, валюта, вертикаль и формат закупки остаются в истории общения с владельцем impact. При следующем заказе не придётся объяснять задачу заново.',
					'>Platforms<' => '>ПАРАМЕТРЫ ЗАЛИВА<',
					'Facebook, Google, TikTok, and other platforms — matched to your geo and volume.' => 'Фиксируем нужный спенд, USA, USD, вертикаль и объём — десятки или сотни аккаунтов. Следующий подбор начинаем с понятной задачи.',
					'>Repeat orders<' => '>ИСТОРИЯ ЗАКАЗОВ<',
					'Same geo, same terms — next batch without starting over.' => 'Предыдущие параметры и согласованный формат работы остаются в одном диалоге. Команда сообщает новый объём — владелец проверяет актуальный список и предлагает аккаунты.',
					'>Your contact<' => '>ЛИЧНЫЙ КОНТАКТ<',
					'One dedicated contact. Clear terms, structured handoff.' => 'На связи остаётся тот же владелец, который знает задачи команды и предыдущие покупки. Без нового менеджера и повторного объяснения условий.',
				)
			);

			$feature = $hero . $second . $third . $fourth . $fifth;
			$feature = $replace(
				$feature,
				array(
					'EU verified'                              => 'USA проверено',
					'>@impact<'                                => '>@founderads<',
					'alt="Team desk illustration"'            => 'alt="Иллюстрация медиабаинговой команды"',
					'alt="Multi-platform access layer"'        => 'alt="Схема подбора аккаунта"',
					'alt="Repeat order flow"'                  => 'alt="Схема повторного заказа"',
					'alt="Dedicated contact handoff"'          => 'alt="Схема работы с владельцем impact."',
					'alt="Avatar Team Lead"'                   => 'alt="Аватар участника команды"',
					'alt="Avatar Elena M."'                    => 'alt="Аватар участника диалога"',
					'alt="Avatar Asif Arman"'                  => 'alt="Аватар участника диалога"',
					'alt="Avatar Alex Holovach"'               => 'alt="Аватар участника диалога"',
					'alt="Avatar Tom Nagengast"'               => 'alt="Аватар участника диалога"',
					'alt="Asif Arman"'                         => 'alt=""',
					'alt="Andrew Aymeloglu"'                   => 'alt=""',
					'alt="Elena M."'                           => 'alt=""',
					'alt="Alex Holovach"'                      => 'alt=""',
					'alt="Lewis Liu"'                          => 'alt=""',
					'alt="Tom Nagengast"'                      => 'alt=""',
					'alt="Rupa Vemulapalli"'                   => 'alt=""',
					'alt="Daniel Young"'                       => 'alt=""',
					'alt="Hadley Callaway"'                    => 'alt=""',
					'alt="Abhi Aiyer"'                        => 'alt=""',
					'alt="Demetrios Brinkmann"'                => 'alt=""',
					'alt="Ivan Burazin"'                       => 'alt=""',
					'alt="Harrison Chase"'                     => 'alt=""',
					'alt="Sonny Gupta"'                        => 'alt=""',
					'alt="Jay Hack"'                           => 'alt=""',
				)
			);

			return $feature;
		}
	}

	/* Shared footer: only visual copy is changed for this page. FAQ markup and
	 * any unrelated footer/legal content are intentionally untouched. */
	if ( false !== stripos( $html, '<footer' ) ) {
		$html = $replace(
			$html,
			array(
				'>REQUEST ACCESS<' => '>ПОЛУЧИТЕ АККАУНТЫ.<br/>ПРОВЕРЬТЕ. ПОТОМ ПЛАТИТЕ.<',
				'Closed access infrastructure for media buying teams. Accounts and access for launch, tests, and scale.' => 'Напишите, что нужно. Владелец impact. подберёт аккаунты и передаст их на проверку — без предоплаты вслепую.',
				'>НАПИСАТЬ В TELEGRAM<' => '>НАПИСАТЬ В ТЕЛЕГРАМ<',
				'@founderads · direct owner contact · verification before payment · support 24/7' => 'прямой контакт с владельцем · проверка до оплаты · поддержка 24/7',
				'placeholder="FIRST NAME"' => 'placeholder="ВАШ TELEGRAM ИЛИ ЭЛ. ПОЧТА · @username или example@mail.com"',
				'placeholder="LAST NAME"' => 'placeholder="НУЖНЫЙ СПЕНД · например, $2 000–3 000"',
				'placeholder="EMAIL ADDRESS"' => 'placeholder="ГЕО И ВАЛЮТА · например, USA · USD"',
				'placeholder="COMPANY NAME"' => 'placeholder="ВЕРТИКАЛЬ И ОБЪЁМ · Нутра · 50 аккаунтов регулярно"',
				'>Apply<' => '>ПОЛУЧИТЬ АККАУНТЫ НА ПРОВЕРКУ<',
				'>Request access<' => '>ПОЛУЧИТЬ АККАУНТЫ НА ПРОВЕРКУ<',
				'>Access<' => '>ЗАЯВКА<',
				'aria-label="First name"' => 'aria-label="Ваш Telegram или эл. почта"',
				'aria-label="Last name"' => 'aria-label="Нужный спенд"',
				'aria-label="Email address"' => 'aria-label="Гео и валюта"',
				'aria-label="Company name"' => 'aria-label="Вертикаль и объём"',
			)
		);
	}

	return $html;
};
