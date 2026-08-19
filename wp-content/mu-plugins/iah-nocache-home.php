<?php
/**
 * Must-use: homepage no-cache headers + lang cookie migration.
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Drop legacy iac_lang cookie (stale CDN cache keys).
 */
function iah_migrate_lang_cookie() {
	if ( ! empty( $_COOKIE['iac_lang4'] ) ) {
		if ( ! headers_sent() && ( ! empty( $_COOKIE['iac_lang'] ) || ! empty( $_COOKIE['iac_lang3'] ) ) ) {
			setcookie( 'iac_lang', '', time() - YEAR_IN_SECONDS, '/' );
			setcookie( 'iac_lang3', '', time() - YEAR_IN_SECONDS, '/' );
		}
		return;
	}

	$lang = 'en';
	if ( ! empty( $_COOKIE['iac_lang3'] ) ) {
		$lang = sanitize_key( wp_unslash( $_COOKIE['iac_lang3'] ) );
	} elseif ( ! empty( $_COOKIE['iac_lang'] ) ) {
		$lang = sanitize_key( wp_unslash( $_COOKIE['iac_lang'] ) );
	} else {
		return;
	}

	if ( ! in_array( $lang, array( 'en', 'ru' ), true ) ) {
		return;
	}

	if ( ! headers_sent() ) {
		setcookie( 'iac_lang', '', time() - YEAR_IN_SECONDS, '/' );
		setcookie( 'iac_lang3', '', time() - YEAR_IN_SECONDS, '/' );
		setcookie( 'iac_lang4', $lang, time() + YEAR_IN_SECONDS, '/' );
	}

	$_COOKIE['iac_lang4'] = $lang;
	unset( $_COOKIE['iac_lang'], $_COOKIE['iac_lang3'] );
}
add_action( 'plugins_loaded', 'iah_migrate_lang_cookie', 0 );

/**
 * Never cache mirrored homepage at CDN/browser.
 */
function iah_home_nocache_headers() {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}

	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$path = is_string( $uri ) ? wp_parse_url( $uri, PHP_URL_PATH ) : '';
	if ( '/' !== $path && '' !== $path ) {
		return;
	}

	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	if ( headers_sent() ) {
		return;
	}

	header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private' );
	header( 'CDN-Cache-Control: no-store' );
	header( 'X-LiteSpeed-Cache-Control: no-cache' );
}
add_action( 'send_headers', 'iah_home_nocache_headers', 0 );
