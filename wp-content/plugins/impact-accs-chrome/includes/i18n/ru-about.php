<?php
/**
 * Page-specific RU copy for /about/.
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

	/* The shared footer is rendered separately from the About page template. */
	$html = str_replace(
		'@founderads · direct owner contact · verification before payment · support 24/7',
		'прямой контакт с владельцем · проверка до оплаты · поддержка 24/7',
		$html
	);

	if ( false === strpos( $html, 'iac-about-page' ) ) {
		return $html;
	}

	/* The brief requires removing the placeholder roster when real team data is unavailable. */
	$html = preg_replace( '/<section class="iac-about-team\b[\s\S]*?<\/section>/', '', $html, 1 );

	/* Hero. */
	$html = str_replace(
		'<h1 class="text-title text-foreground text-glow text-h1 text-left max-md:text-center">Meet impact.accs</h1>',
		'<h1 class="text-title text-foreground text-glow text-h1 text-left max-md:text-center">ЗНАКОМЬТЕСЬ:<br/>impact.</h1>',
		$html
	);
	$html = str_replace(
		'We&#x27;re building the access infrastructure teams actually need: closed, structured, and built for media buying at speed.',
		'Надёжный магазин трастовых Google Ads спенд-аккаунтов для медиабаинговых и арбитражных команд. Проверка до оплаты, честная замена и поддержка владельца 24/7.<span class="font-misc text-muted-foreground mt-4 block text-xs uppercase leading-[1.35]">7 лет на рынке · 15 000 выданных аккаунтов · 100+ активных команд</span>',
		$html
	);
	$html = str_replace( '>SCROLL DOWN<', '>ЛИСТАЙТЕ НИЖЕ<', $html );

	$map = array(
		'>ABOUT impact.accs<' => '>ОБ IMPACT.<',
		'>OUR VALUES<' => '>КАК МЫ РАБОТАЕМ<',
		'Values shape culture. Culture shapes service. These six principles are the foundation of how we build impact.accs.' => 'Не абстрактные ценности, а правила, которые команда проверяет в каждой покупке.',
		'>INTEGRITY<' => '>ЧЕСТНОСТЬ<',
		'Do the right thing, always. By our customers, our team, our partners. Honesty isn&#x27;t optional.' => 'Говорим прямо, что покрывает замена и где заканчивается наша ответственность. Без «100% без банов», вечных гарантий и мелкого шрифта.',
		'>URGENCY<' => '>СПИСОК АККАУНТОВ<',
		'Move fast. Be impatient. Never settle for complacency or comfort. Speed is a feature.' => 'Поддерживаем список под разовые и регулярные закупки. Чего нет сейчас — ищем под запрос и заранее называем реальный срок.',
		'>CRAFTSMANSHIP<' => '>СКОРОСТЬ<',
		'Take pride in your work. Care about design. Care about quality. Never ship garbage.' => 'Быстро отвечаем, передаём десятки аккаунтов на проверку и не затягиваем замену гарантийных позиций.',
		'>SERVICE<' => '>ЛИЧНАЯ ОТВЕТСТВЕННОСТЬ<',
		'Go above and beyond for customers. Extend that same care to teammates and partners. Service is everything.' => 'По каждой покупке общается владелец impact. Сделка не заканчивается после оплаты, а важные вопросы не передаются случайному менеджеру.',
		'>KINDNESS<' => '>ПОНЯТНОСТЬ<',
		'Be kind to everyone: teammates, customers, even competitors. Be direct, but always mindful. We&#x27;re all in this together.' => 'Открытые цены, тиры по спенду и простая последовательность: получили аккаунт, проверили параметры, затем оплатили.',
		'>FUN<' => '>ПРИНЦИПИАЛЬНОСТЬ<',
		'Enjoy the work. Enjoy each other. Do things because they&#x27;re cool. Smile through the pain.' => 'Работаем с серыми вертикалями. С чёрными — принципиально нет. Надёжный поставщик должен иметь границу, которую не переходит.',
		'>ORIGINS<' => '>ПОЧЕМУ ПОЯВИЛСЯ IMPACT.<',
		'>SPARK<' => '>ЧТО МЫ ИЗМЕНИЛИ<',
		'>MISSION<' => '>ЧТО ДЕЛАЕМ СЕЙЧАС<',
		'>VISION<' => '>КУДА ИДЁМ<',
		'>READY FOR ACTION<' => '>ВЫБЕРИТЕ ФОРМАТ ЗАКУПКИ<',
		'Random account shops make you hunt for supply. impact.accs works as your resource layer — clear terms, fast contact, repeat orders.' => 'Десятки аккаунтов под текущий залив, подбор под несколько связок или регулярные продажи сотен позиций. Везде — проверка до оплаты и контакт с владельцем.',
		'>Platform Access<' => '>ПОД ТЕКУЩИЙ ЗАЛИВ<',
		'Facebook, Google, TikTok — platform accounts and ad access through one channel. Tell us geo and volume; we reply with what&#x27;s available and on what terms.' => 'Подберём десятки трастовых Google Ads спенд-аккаунтов по нужному спенду, USA, USD и вертикали. Сначала проверяете, затем оплачиваете.',
		'Facebook, Google, TikTok — platform accounts and ad access through one channel. Tell us geo and volume; we reply with what\'s available and on what terms.' => 'Подберём десятки трастовых Google Ads спенд-аккаунтов по нужному спенду, USA, USD и вертикали. Сначала проверяете, затем оплачиваете.',
		'Facebook, Google, TikTok — platform accounts and ad access through one channel. Tell us geo and volume; we reply with what’s available and on what terms.' => 'Подберём десятки трастовых Google Ads спенд-аккаунты по нужному спенду, USA, USD и вертикали. Сначала проверяете, затем оплачиваете.',
		'>Agency Accounts<' => '>ДЛЯ МЕДИАБАИНГА<',
		'Random sellers fail when launch windows close. Verified agency accounts through one channel — availability, terms, and handoff before you go live.' => 'Соберём несколько связок в один заказ. Разные тиры при USA и USD — с проверкой каждой позиции и одним ответственным со стороны impact.',
		'>Team Supply<' => '>РЕГУЛЯРНЫЕ ПРОДАЖИ<',
		'Five years on the market. Repeat orders, volume terms, and supply that matches your launch tempo.' => 'Согласуем продажи сотен аккаунтов, типичные параметры и график, чтобы команда не искала нового продавца перед каждым заливом.',
		'@founderads · direct owner contact · verification before payment · support 24/7' => 'прямой контакт с владельцем · проверка до оплаты · поддержка 24/7',
	);

	uksort(
		$map,
		static function ( $a, $b ) {
			return strlen( $b ) - strlen( $a );
		}
	);
	$html = str_replace( array_keys( $map ), array_values( $map ), $html );

	/* Four large story blocks contain React comment separators, so replace their bodies by order. */
	$story_blocks = array(
		'Купить Google Ads аккаунт несложно. Гораздо сложнее найти поставщика, на которого можно положиться сегодня, через неделю и при следующей закупке.<br/><br/>Рынок шумный и непрозрачный: непредсказуемые списки аккаунтов, предоплата в никуда, спорные замены и поддержка, которая исчезает после сделки.',
		'Убрали из закупки лишний риск: показываем цены, передаём аккаунт на проверку до оплаты и заранее объясняем условия замены.<br/><br/>По каждой сделке отвечает владелец — тот же человек, который подбирает аккаунт и решает вопрос, если что-то пошло не так.',
		'Продаём только Google Ads спенд-аккаунты с реальной историей открутки в белых нишах — без авторегов и пустого фарма.<br/><br/>Подбираем по спенду, USA, USD, вертикали и объёму. Работаем только с медиабаинговыми и арбитражными командами — от десятков до сотен аккаунтов.',
		'Наша цель — стать поставщиком, которому доверяют по умолчанию: сначала в Google Ads аккаунтах, затем в других ресурсах для медиабаинга и на международном рынке.<br/><br/>Порядок принципиален: сначала заслужить доверие в основном продукте, затем расширять поставку и добавлять новые сервисы. Не наоборот.',
	);
	$story_index = 0;
	$html = preg_replace_callback(
		'/(<p[^>]*data-text-content="true">)[\s\S]*?(<\/p>)/',
		static function ( $matches ) use ( &$story_index, $story_blocks ) {
			if ( ! isset( $story_blocks[ $story_index ] ) ) {
				return $matches[0];
			}
			$out = $matches[1] . $story_blocks[ $story_index ] . $matches[2];
			$story_index++;
			return $out;
		},
		$html,
		4
	);

	/* Replace old EN screenshot thumbnails with RU intent panels without changing card geometry. */
	$visuals = array(
		'Platform Access' => '<div class="ease-out-expo aspect-965/600 w-full select-none bg-background/70 flex flex-col items-center justify-center gap-3 px-6 text-center"><span class="font-misc text-primary text-xs uppercase">ПОД ТЕКУЩИЙ ЗАЛИВ</span><span class="text-title text-foreground text-2xl">СПЕНД · USA · USD</span><span class="text-paragraph text-muted text-xs">ПРОВЕРКА ДО ОПЛАТЫ</span></div>',
		'Agency Accounts' => '<div class="ease-out-expo aspect-965/600 w-full select-none bg-background/70 flex flex-col items-center justify-center gap-3 px-6 text-center"><span class="font-misc text-primary text-xs uppercase">ДЛЯ МЕДИАБАИНГА</span><span class="text-title text-foreground text-2xl">НЕСКОЛЬКО СВЯЗОК</span><span class="text-paragraph text-muted text-xs">ОДИН ОТВЕТСТВЕННЫЙ</span></div>',
		'Team Supply' => '<div class="ease-out-expo aspect-965/600 w-full select-none bg-background/70 flex flex-col items-center justify-center gap-3 px-6 text-center"><span class="font-misc text-primary text-xs uppercase">РЕГУЛЯРНЫЕ ПРОДАЖИ</span><span class="text-title text-foreground text-2xl">СОТНИ АККАУНТОВ</span><span class="text-paragraph text-muted text-xs">ПОД ТЕМП КОМАНДЫ</span></div>',
	);
	foreach ( $visuals as $alt => $replacement ) {
		$pattern = '/<img alt="' . preg_quote( $alt, '/' ) . '"[^>]*about-cards\/[^"]+\.(?:png|webp|jpg|jpeg)[^>]*\/?>/i';
		$html = preg_replace( $pattern, $replacement, $html, 1 );
	}

	/* Context labels under purchase cards. */
	$card_labels = array(
		'1' => array( 'ПОД ЗАЛИВ', '01' ),
		'2' => array( 'МЕДИАБАИНГ', '02' ),
		'3' => array( 'СОТНИ АККАУНТОВ', '03' ),
	);
	$html = preg_replace_callback(
		'/<span([^>]*)>ACCOUNTS<\/span><span([^>]*)>([123])<\/span>/',
		static function ( $matches ) use ( $card_labels ) {
			if ( ! isset( $card_labels[ $matches[3] ] ) ) {
				return $matches[0];
			}
			return '<span' . $matches[1] . '>' . $card_labels[ $matches[3] ][0] . '</span><span' . $matches[2] . '>' . $card_labels[ $matches[3] ][1] . '</span>';
		},
		$html,
		3
	);

	$cta_labels = array( 'ПОДОБРАТЬ →', 'УЗНАТЬ УСЛОВИЯ →', 'ОБСУДИТЬ ОБЪЁМ →' );
	$cta_index  = 0;
	$html = preg_replace_callback(
		'/>Read More →<\/span>/',
		static function ( $matches ) use ( &$cta_index, $cta_labels ) {
			if ( ! isset( $cta_labels[ $cta_index ] ) ) {
				return $matches[0];
			}
			$label = $cta_labels[ $cta_index ];
			$cta_index++;
			return '>' . $label . '</span>';
		},
		$html,
		3
	);

	return $html;
};
