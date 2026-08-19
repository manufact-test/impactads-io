<?php
/**
 * Must-use: rescue stale CDN/blog index on site root.
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline rescue when WordPress blog index slips through CDN cache.
 */
function iah_home_rescue_head() {
	if ( is_admin() ) {
		return;
	}

	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$path = is_string( $uri ) ? wp_parse_url( $uri, PHP_URL_PATH ) : '';
	if ( '/' !== $path && '' !== $path ) {
		return;
	}

	echo '<script>(function(){try{var p=location.pathname||"/";if(p!=="/"&&p!=="")return;if(location.search.indexOf("_lc=")!==-1)return;if(document.documentElement.classList.contains("iah-home"))return;var b=document.body,t=b?b.textContent||"":"";if(t.indexOf("Hello world!")!==-1||t.indexOf("Welcome to WordPress")!==-1||!document.documentElement.classList.contains("iah-home"))location.replace("/?_lc=2")}catch(e){}})();</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'iah_home_rescue_head', -99999 );

/**
 * Nocache for cache-busted home URL too.
 */
function iah_home_lc_nocache_headers() {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}

	$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( ! is_string( $uri ) || false === strpos( $uri, '_lc=' ) ) {
		return;
	}

	$path = wp_parse_url( $uri, PHP_URL_PATH );
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
add_action( 'send_headers', 'iah_home_lc_nocache_headers', 0 );
