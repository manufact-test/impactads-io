<?php
/**
 * Page-specific RU copy for the custom 404 component.
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
		'Page<br>not<br>found' => 'Страница<br>не<br>найдена',
		'System<br>cleared' => 'Система<br>восстановлена',
		'System<br>down' => 'Система<br>не отвечает',
		'Now face<br>the final boss' => 'А теперь —<br>финальный уровень',
		'aria-label="Space Invaders 404 Game"' => 'aria-label="Игра 404"',
		'aria-label="3 lives remaining"' => 'aria-label="Осталось жизней: 3"',
		'aria-label="Restart"' => 'aria-label="Начать заново"',
		'SCORE 000000' => 'СЧЁТ 000000',
		'WAVE 1' => 'УРОВЕНЬ 1',
		'Go back home' => 'На главную',
		'Back home' => 'На главную',
		'Tap to play' => 'Нажмите, чтобы играть',
		'Restart' => 'Заново',
		'Page not found — 404' => 'Страница не найдена — 404',
	);

	uksort(
		$map,
		static function ( $a, $b ) {
			return strlen( $b ) - strlen( $a );
		}
	);

	return str_replace( array_keys( $map ), array_values( $map ), $html );
};
