<?php
/**
 * Plugin Name: Impact Runtime Safe Fixes
 * Description: Small WordPress/Hostinger compatibility fixes that do not alter Next.js hydration or script order.
 * Version: 1.0.0
 * Author: Impact
 *
 * @package ImpactRuntimeSafe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Let the WordPress Site Icon setting own favicon markup on regular pages.
 * The mirrored homepage reads the same setting through IAH_FAVICON_URL.
 */
function impact_runtime_safe_restore_site_icon() {
	if ( ! class_exists( 'IAC_Chrome' ) || ! method_exists( 'IAC_Chrome', 'instance' ) ) {
		return;
	}

	$chrome = IAC_Chrome::instance();

	remove_action( 'wp_head', array( $chrome, 'render_favicon' ), 0 );
	remove_filter( 'get_site_icon_url', array( $chrome, 'filter_site_icon_url' ), 99 );
	remove_filter( 'site_icon_meta_tags', array( $chrome, 'filter_site_icon_meta_tags' ), 99 );
}
add_action( 'plugins_loaded', 'impact_runtime_safe_restore_site_icon', 100 );

/**
 * Return the current request path without the query string.
 *
 * @return string
 */
function impact_runtime_safe_request_path() {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( ! is_string( $uri ) || '' === $uri ) {
		return '';
	}

	$path = wp_parse_url( $uri, PHP_URL_PATH );
	return is_string( $path ) ? $path : '';
}

/**
 * The static Next.js export still references Vercel Analytics, but production
 * now runs on Hostinger. Return a tiny local no-op instead of a guaranteed 404.
 * This does not enable analytics and does not touch the homepage DOM.
 */
function impact_runtime_safe_vercel_analytics_stub() {
	if ( '/_vercel/insights/script.js' !== impact_runtime_safe_request_path() ) {
		return;
	}

	status_header( 200 );
	if ( ! headers_sent() ) {
		header( 'Content-Type: application/javascript; charset=UTF-8' );
		header( 'Cache-Control: public, max-age=86400' );
		header( 'X-Robots-Tag: noindex, nofollow', true );
	}

	echo 'window.va=window.va||function(){};window.vaq=window.vaq||[];'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'template_redirect', 'impact_runtime_safe_vercel_analytics_stub', -20000 );
