<?php
/**
 * Plugin Name: Impact Favicon Endpoint
 * Description: Makes WordPress Site Icon authoritative for legacy Next.js favicon URLs and page markup.
 * Version: 1.0.1
 * Author: Impact
 *
 * @package ImpactFaviconEndpoint
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function impact_favicon_request_path() {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( ! is_string( $uri ) || '' === $uri ) {
		return '';
	}
	$path = wp_parse_url( $uri, PHP_URL_PATH );
	return is_string( $path ) ? $path : '';
}

function impact_favicon_attachment_id() {
	return (int) get_option( 'site_icon', 0 );
}

function impact_favicon_endpoint_url() {
	$id = impact_favicon_attachment_id();
	return add_query_arg( 'v', $id > 0 ? $id : 'impact', home_url( '/favicon.ico' ) );
}

function impact_favicon_file() {
	$id = impact_favicon_attachment_id();
	if ( $id <= 0 ) {
		return '';
	}
	$file = get_attached_file( $id );
	return is_string( $file ) && is_readable( $file ) ? $file : '';
}

function impact_favicon_serve_legacy_routes() {
	$path = impact_favicon_request_path();
	if ( '/favicon.ico' !== $path && '/impact-icon.ico' !== $path ) {
		return;
	}

	$id   = impact_favicon_attachment_id();
	$file = impact_favicon_file();

	if ( $id > 0 && $file ) {
		$mime = get_post_mime_type( $id );
		if ( ! is_string( $mime ) || 0 !== strpos( $mime, 'image/' ) ) {
			$type = wp_check_filetype( $file );
			$mime = ! empty( $type['type'] ) ? $type['type'] : 'image/png';
		}

		status_header( 200 );
		if ( ! headers_sent() ) {
			header( 'Content-Type: ' . $mime );
			header( 'Content-Length: ' . (string) filesize( $file ) );
			header( 'Cache-Control: public, max-age=300, must-revalidate' );
			header( 'X-Content-Type-Options: nosniff' );
		}
		readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		exit;
	}

	if ( $id > 0 ) {
		$url = wp_get_attachment_url( $id );
		if ( is_string( $url ) && '' !== $url && false === strpos( $url, '/favicon.ico' ) && false === strpos( $url, '/impact-icon.ico' ) ) {
			wp_redirect( $url, 302, 'Impact Favicon Endpoint' ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			exit;
		}
	}
}
add_action( 'template_redirect', 'impact_favicon_serve_legacy_routes', -30000 );

function impact_favicon_render_wp_head() {
	$url = esc_url( impact_favicon_endpoint_url() );
	if ( ! $url ) {
		return;
	}
	echo '<link rel="icon" href="' . $url . '" sizes="32x32" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<link rel="shortcut icon" href="' . $url . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<link rel="apple-touch-icon" href="' . $url . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

function impact_favicon_take_over_wp_head() {
	remove_action( 'wp_head', 'wp_site_icon', 99 );
	remove_action( 'wp_head', 'impact_runtime_safe_render_site_icon', 0 );
	remove_action( 'wp_head', 'impact_favicon_render_wp_head', 0 );
	add_action( 'wp_head', 'impact_favicon_render_wp_head', 0 );
}
add_action( 'plugins_loaded', 'impact_favicon_take_over_wp_head', 200 );

function impact_favicon_patch_home_html( $html ) {
	if ( ! is_string( $html ) || '' === $html || false === stripos( $html, '<head' ) ) {
		return $html;
	}

	$html = preg_replace_callback(
		'#<link\b[^>]*>#i',
		static function ( $matches ) {
			$tag = $matches[0];
			if ( preg_match( '#\brel\s*=\s*(["\'])([^"\']*(?:apple-touch-icon|shortcut icon|icon)[^"\']*)\1#i', $tag ) ) {
				return '';
			}
			return $tag;
		},
		$html
	);

	$url  = esc_url( impact_favicon_endpoint_url() );
	$tags = '<link rel="icon" href="' . $url . '" sizes="32x32" />'
		. '<link rel="shortcut icon" href="' . $url . '" />'
		. '<link rel="apple-touch-icon" href="' . $url . '" />';

	$html = preg_replace_callback(
		'#<head\b[^>]*>#i',
		static function ( $matches ) use ( $tags ) {
			return $matches[0] . $tags;
		},
		$html,
		1
	);

	return is_string( $html ) ? $html : '';
}

function impact_favicon_start_home_buffer() {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}
	$path = impact_favicon_request_path();
	if ( '/' !== $path && '' !== $path ) {
		return;
	}
	ob_start( 'impact_favicon_patch_home_html' );
}
add_action( 'template_redirect', 'impact_favicon_start_home_buffer', -3000 );
