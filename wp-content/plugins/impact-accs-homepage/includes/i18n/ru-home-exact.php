<?php
/**
 * Homepage exact EN→RU replacements.
 *
 * Short/shared labels live here because the client-side homepage translator treats
 * this map as exact text-node matches. Client copy v2 is merged last and wins.
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base = array(
	// Header / navigation.
	'About'          => 'О нас',
	'ABOUT'          => 'О НАС',
	'Blog'           => 'Блог',
	'BLOG'           => 'БЛОГ',
	'Contact'        => 'Контакты',
	'CONTACT'        => 'КОНТАКТЫ',
	'Accounts'       => 'Аккаунты',
	'ACCOUNTS'       => 'АККАУНТЫ',
	'Menu'           => 'Меню',
	'MENU'           => 'МЕНЮ',
	'Close'          => 'Закрыть',
	'CLOSE'          => 'ЗАКРЫТЬ',
	'Back to list'   => 'Назад к списку',
	'BACK TO LIST'   => 'НАЗАД К СПИСКУ',
	'SCROLL DOWN'    => 'ПРОКРУТИТЕ ВНИЗ',
	'Scroll down'    => 'Прокрутите вниз',
	'FEATURES'       => 'АККАУНТЫ',
	'Clear Delivery' => 'Чёткая поставка',

	// Sound / utility UI.
	'Sound: locked' => 'Звук: выкл',
	'SOUND: OFF'    => 'ЗВУК: ВЫКЛ',
	'SOUND: ON'     => 'ЗВУК: ВКЛ',
	'Sound: OFF'    => 'Звук: выкл',
	'Sound: ON'     => 'Звук: вкл',
	'Sound: on'     => 'Звук: вкл',
	'Sound: off'    => 'Звук: выкл',
	'Sound: locked' => 'Звук: выкл',
	'Sound: '       => 'Звук: ',
	'Enable sound'  => 'Включить звук',
	'Unmute sound'  => 'Включить звук',
	'Mute sound'    => 'Выключить звук',
	'Send'          => 'Отправить',
	'You'           => 'Вы',

	// Generic UI that is further contextualized by wp-bridge.js where needed.
	'Read more'   => 'Читать',
	'Read More'   => 'Читать',
	'Read Blog'   => 'Читать блог',
	'Read More →' => 'Читать →',
	'View'        => 'Смотреть',
	'LEARN MORE'  => 'ПОДРОБНЕЕ',
	'READ MORE'   => 'ПОДРОБНЕЕ',

	// Footer / misc.
	'CUSTOMER'    => 'КЛИЕНТЫ',
	'REVIEWS'     => 'ОТЗЫВЫ',
	'BACKED BY'   => 'ПРИ ПОДДЕРЖКЕ',
	'INVESTORS'   => 'ИНВЕСТОРЫ',
	'How it works'=> 'Как это работает',
	'Waitlist'    => 'Лист ожидания',
	'Impact starts with access' => 'Impact начинается с доступа',
);

$v2_file = __DIR__ . '/ru-home-v2.php';
$v2      = is_readable( $v2_file ) ? require $v2_file : array();
if ( ! is_array( $v2 ) ) {
	$v2 = array();
}

return array_merge( $base, $v2 );
