<?php
/**
 * Plugin Name: Impact Favicon Endpoint
 * Description: Keeps WordPress favicon behavior on WP pages and restores React-owned homepage head markup before hydration.
 * Version: 1.1.0
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

function impact_favicon_attachment_url() {
	$id = impact_favicon_attachment_id();
	if ( $id <= 0 ) {
		return '';
	}
	$url = wp_get_attachment_url( $id );
	return is_string( $url ) ? $url : '';
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
		$url = impact_favicon_attachment_url();
		if ( '' !== $url && false === strpos( $url, '/favicon.ico' ) && false === strpos( $url, '/impact-icon.ico' ) ) {
			wp_redirect( $url, 302, 'Impact Favicon Endpoint' ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			exit;
		}
	}
}
add_action( 'template_redirect', 'impact_favicon_serve_legacy_routes', -30000 );

function impact_favicon_render_wp_head() {
	$url = esc_url( impact_favicon_attachment_url() );
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

	if ( class_exists( 'IAC_Chrome' ) && method_exists( 'IAC_Chrome', 'instance' ) ) {
		$chrome = IAC_Chrome::instance();
		remove_action( 'wp_head', array( $chrome, 'render_favicon' ), 0 );
		remove_filter( 'get_site_icon_url', array( $chrome, 'filter_site_icon_url' ), 99 );
		remove_filter( 'site_icon_meta_tags', array( $chrome, 'filter_site_icon_meta_tags' ), 99 );
	}

	add_action( 'wp_head', 'impact_favicon_render_wp_head', 0 );
}
add_action( 'plugins_loaded', 'impact_favicon_take_over_wp_head', 200 );

/**
 * Restore React-owned metadata to exactly the values present in the exported
 * Next.js document. WordPress may add scripts/styles around it, but changing
 * React's own <title>/<link> nodes before hydrateRoot() causes React #418.
 *
 * The physical /favicon.ico and /impact-icon.ico files are authoritative on
 * the mirrored homepage, so the original Next favicon markup can stay intact.
 *
 * @param string $html Rendered homepage HTML.
 * @return string
 */
function impact_favicon_patch_home_html( $html ) {
	if ( ! is_string( $html ) || '' === $html || false === stripos( $html, '<head' ) ) {
		return $html;
	}

	$title = 'impact.accs | Closed Access Infrastructure';
	$desc  = 'Closed access infrastructure for media buying teams. Platform access, agency accounts, team supply, clear terms, and direct handoff.';

	$html = preg_replace( '#<title>[^<]*</title>#i', '<title>' . $title . '</title>', $html, 1 );
	$html = preg_replace( '#<meta\s+name="description"\s+content="[^"]*"\s*/?>#i', '<meta name="description" content="' . $desc . '"/>', $html, 1 );
	$html = preg_replace( '#<meta\s+property="og:title"\s+content="[^"]*"\s*/?>#i', '<meta property="og:title" content="' . $title . '"/>', $html, 1 );
	$html = preg_replace( '#<meta\s+property="og:description"\s+content="[^"]*"\s*/?>#i', '<meta property="og:description" content="' . $desc . '"/>', $html, 1 );
	$html = preg_replace( '#<meta\s+name="twitter:title"\s+content="[^"]*"\s*/?>#i', '<meta name="twitter:title" content="' . $title . '"/>', $html, 1 );
	$html = preg_replace( '#<meta\s+name="twitter:description"\s+content="[^"]*"\s*/?>#i', '<meta name="twitter:description" content="' . $desc . '"/>', $html, 1 );
	$html = preg_replace( '#<link\s+rel="canonical"\s+href="[^"]*"\s*/?>#i', '<link rel="canonical" href="https://impact.com"/>', $html, 1 );

	// Remove favicon markup injected by WordPress compatibility layers.
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

	$next_favicons = '<link rel="shortcut icon" href="/impact-icon.ico"/>'
		. '<link rel="icon" href="/favicon.ico" sizes="48x48" type="image/x-icon"/>'
		. '<link rel="icon" href="/impact-icon.ico"/>'
		. '<link rel="apple-touch-icon" href="/impact-icon.ico"/>';

	// Put them back at the exact boundary used by the exported Next document:
	// immediately before its first inline force-no-gpu script.
	$marker = '#(<script>\s*if\s*\(new URLSearchParams\(location\.search\)\.has\(\'force-no-gpu\'\)\))#i';
	$patched = preg_replace( $marker, $next_favicons . '$1', $html, 1, $count );
	if ( is_string( $patched ) && $count > 0 ) {
		$html = $patched;
	} else {
		$html = preg_replace( '/<\/head>/i', $next_favicons . '</head>', $html, 1 );
	}

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
