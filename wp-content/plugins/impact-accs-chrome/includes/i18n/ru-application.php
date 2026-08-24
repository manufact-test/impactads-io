<?php
/**
 * Page-specific RU copy for /application/.
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
		'Closed access<br/>infrastructure' => 'Google Ads<br/>спенд-аккаунты',
		'Closed access infrastructure for media buying teams. Accounts and access for launch, tests, and scale.' => 'Подберём трастовые Google Ads спенд-аккаунты под вашу задачу. Сначала проверяете спенд, USA и USD — затем оплачиваете.',
		'Tell us about your team and what you\'re looking for…' => 'Укажите гео USA, валюту USD, вертикаль, нужный спенд и объём аккаунтов.',
		'TELL US ABOUT YOUR TEAM AND WHAT YOU\'RE LOOKING FOR…' => 'УКАЖИТЕ USA, USD, ВЕРТИКАЛЬ, СПЕНД И ОБЪЁМ АККАУНТОВ',
		'By signing up, you agree to our' => 'Отправляя заявку, вы соглашаетесь с',
		'We\'ll be in touch when it\'s your turn.' => 'Владелец impact. ответит в Telegram или по эл. почте.',
		'We’ll be in touch when it’s your turn.' => 'Владелец impact. ответит в Telegram или по эл. почте.',
		'impact.accs access request' => 'Заявка на подбор impact.',
		'placeholder="JOHN@ACME.COM"' => 'placeholder="ivan@company.ru"',
		'placeholder="JOHN"' => 'placeholder="@username"',
		'placeholder="DOE"' => 'placeholder="$2 000–3 000"',
		'>Email Address<' => '>Эл. почта<',
		'>First Name<' => '>Ваш Telegram<',
		'>Last Name<' => '>Нужный спенд<',
		'>Your Message<' => '>Задача<',
		'>Request Access<' => '>ЗАПРОСИТЬ ПОДБОР<',
		'>Request access<' => '>ЗАПРОСИТЬ ПОДБОР<',
		'>You\'re in<' => '>ЗАЯВКА ОТПРАВЛЕНА<',
		'>You’re in<' => '>ЗАЯВКА ОТПРАВЛЕНА<',
		'>Pilot:<' => '>Статус:<',
		'>Access<' => '>Заявка<',
		'>Contact<' => '>Контакты<',
		'>Close<' => '>Закрыть<',
		'aria-label="Close"' => 'aria-label="Закрыть"',
		'Terms of Service' => 'Условиями использования',
		'Privacy Policy' => 'Политикой конфиденциальности',
		' and ' => ' и ',
	);

	uksort(
		$map,
		static function ( $a, $b ) {
			return strlen( $b ) - strlen( $a );
		}
	);

	return str_replace( array_keys( $map ), array_values( $map ), $html );
};
