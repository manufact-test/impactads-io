<?php
/**
 * Page-specific RU copy for /contact/.
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
		'href="tel:+74953128467"' => 'href="https://t.me/founderads" target="_blank" rel="noopener noreferrer"',
		'href="mailto:contact@impact.accs"' => 'href="https://t.me/founderads" target="_blank" rel="noopener noreferrer"',
		'href="mailto:partners@impact.accs"' => 'href="https://t.me/founderads" target="_blank" rel="noopener noreferrer"',
		'<span class="font-misc text-foreground text-sm leading-none tracking-wider uppercase">Phone</span>' => '<span class="font-misc text-foreground text-sm leading-none tracking-wider uppercase">Telegram</span>',
		'<span class="font-misc text-foreground text-sm leading-none tracking-wider uppercase">Email</span>' => '<span class="font-misc text-foreground text-sm leading-none tracking-wider uppercase">Поддержка</span>',
		'<span class="font-misc text-foreground text-sm leading-none tracking-wider uppercase">Office</span>' => '<span class="font-misc text-foreground text-sm leading-none tracking-wider uppercase">Что прислать</span>',
		'+7 (495) 312-84-67' => '@founderads',
		'contact@impact.accs' => '24/7',
		'<span class="iac-contact-office-line">12 Lenin Street,</span>' => '<span class="iac-contact-office-line">Спенд · USA · USD</span>',
		'<span class="iac-contact-office-line">123112 Moscow, Russia</span>' => '<span class="iac-contact-office-line">Вертикаль · объём</span>',
		'Email us' => 'НАПИСАТЬ @FOUNDERADS',
		'Mon–Fri 09:00–18:00 MSK ·' => 'Владелец impact. отвечает лично ·',
		'partners@impact.accs' => '@founderads',
		'>Access<' => '>Заявка<',
		'>Contact<' => '>Контакты<',
		'>Close<' => '>Закрыть<',
		'aria-label="Close"' => 'aria-label="Закрыть"',
	);

	uksort(
		$map,
		static function ( $a, $b ) {
			return strlen( $b ) - strlen( $a );
		}
	);

	return str_replace( array_keys( $map ), array_values( $map ), $html );
};
