<?php
/**
 * Plugin Name: Impact Favicon Endpoint
 * Description: Makes WordPress Site Icon authoritative for legacy Next.js favicon URLs and page markup.
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

function impact_favicon_endpoint_url() {
	$id = impact_favicon_attachment_id();
	return add_query_arg( 'v', $id > 0 ? $id : 'impact', home_url( '/favicon.ico' ) );
}

function impact_favicon_file( $attachment_id = 0 ) {
	$id = $attachment_id > 0 ? (int) $attachment_id : impact_favicon_attachment_id();
	if ( $id <= 0 ) {
		return '';
	}
	$file = get_attached_file( $id );
	return is_string( $file ) && is_readable( $file ) ? $file : '';
}

/**
 * Convert the selected Site Icon into a standards-compliant ICO containing
 * a 256x256 PNG payload. This gives Hostinger a real physical /favicon.ico,
 * so its static-file layer never has to route an .ico request through WP.
 *
 * @param string $source Source image path.
 * @return string Binary ICO data, or an empty string on failure.
 */
function impact_favicon_build_ico( $source ) {
	$data = @file_get_contents( $source ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_string( $data ) || '' === $data ) {
		return '';
	}

	$png    = '';
	$width  = 256;
	$height = 256;

	if ( function_exists( 'imagecreatefromstring' ) && function_exists( 'imagecreatetruecolor' ) && function_exists( 'imagepng' ) ) {
		$image = @imagecreatefromstring( $data ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( $image ) {
			$src_w = imagesx( $image );
			$src_h = imagesy( $image );
			$dst   = imagecreatetruecolor( $width, $height );
			if ( $dst ) {
				imagealphablending( $dst, false );
				imagesavealpha( $dst, true );
				$transparent = imagecolorallocatealpha( $dst, 0, 0, 0, 127 );
				imagefill( $dst, 0, 0, $transparent );
				imagecopyresampled( $dst, $image, 0, 0, 0, 0, $width, $height, $src_w, $src_h );
				ob_start();
				imagepng( $dst, null, 9 );
				$png = (string) ob_get_clean();
				imagedestroy( $dst );
			}
			imagedestroy( $image );
		}
	}

	// Fallback for servers without GD when the WordPress Site Icon is already PNG.
	if ( '' === $png && 0 === strncmp( $data, "\x89PNG\r\n\x1a\n", 8 ) ) {
		$png = $data;
		$info = @getimagesize( $source ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( is_array( $info ) ) {
			$width  = max( 1, (int) $info[0] );
			$height = max( 1, (int) $info[1] );
		}
	}

	if ( '' === $png ) {
		return '';
	}

	$w_byte = $width >= 256 ? 0 : $width;
	$h_byte = $height >= 256 ? 0 : $height;
	$header = pack( 'vvv', 0, 1, 1 );
	$entry  = pack( 'CCCCvvVV', $w_byte, $h_byte, 0, 0, 1, 32, strlen( $png ), 22 );

	return $header . $entry . $png;
}

/**
 * Atomically mirror the current WordPress Site Icon to physical root files.
 * Hostinger serves .ico requests as static assets before WordPress, which is
 * why a PHP template_redirect endpoint alone still returned 404.
 *
 * @param int $attachment_id Optional explicit Site Icon attachment ID.
 * @return bool
 */
function impact_favicon_sync_root_files( $attachment_id = 0 ) {
	$id     = $attachment_id > 0 ? (int) $attachment_id : impact_favicon_attachment_id();
	$source = impact_favicon_file( $id );
	if ( $id <= 0 || ! $source ) {
		return false;
	}

	$ico = impact_favicon_build_ico( $source );
	if ( '' === $ico ) {
		return false;
	}

	$targets = array(
		trailingslashit( ABSPATH ) . 'favicon.ico',
		trailingslashit( ABSPATH ) . 'impact-icon.ico',
	);

	foreach ( $targets as $target ) {
		$tmp = $target . '.tmp-' . wp_generate_password( 8, false, false );
		$written = @file_put_contents( $tmp, $ico, LOCK_EX ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		if ( false === $written || $written !== strlen( $ico ) ) {
			@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.unlink_unlink
			return false;
		}
		if ( ! @rename( $tmp, $target ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.rename_rename
			@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.unlink_unlink
			return false;
		}
		@chmod( $target, 0644 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.chmod_chmod
	}

	update_option( 'impact_favicon_materialized_site_icon', $id, false );
	return true;
}

/**
 * Ensure physical favicon files exist on normal requests, but avoid rewriting
 * them on every hit when the selected Site Icon has not changed.
 */
function impact_favicon_maybe_sync_root_files() {
	$id = impact_favicon_attachment_id();
	if ( $id <= 0 ) {
		return;
	}

	$known       = (int) get_option( 'impact_favicon_materialized_site_icon', 0 );
	$favicon     = trailingslashit( ABSPATH ) . 'favicon.ico';
	$impact_icon = trailingslashit( ABSPATH ) . 'impact-icon.ico';

	if ( $known === $id && is_readable( $favicon ) && filesize( $favicon ) > 22 && is_readable( $impact_icon ) && filesize( $impact_icon ) > 22 ) {
		return;
	}

	impact_favicon_sync_root_files( $id );
}
add_action( 'init', 'impact_favicon_maybe_sync_root_files', 1 );

/**
 * Refresh physical files immediately whenever WordPress Site Icon changes.
 *
 * @param mixed $old_value Previous option value.
 * @param mixed $new_value New option value.
 */
function impact_favicon_on_site_icon_update( $old_value, $new_value ) {
	$new_id = (int) $new_value;
	if ( $new_id > 0 && $new_id !== (int) $old_value ) {
		impact_favicon_sync_root_files( $new_id );
	}
}
add_action( 'update_option_site_icon', 'impact_favicon_on_site_icon_update', 10, 2 );

/**
 * PHP fallback if a web server does route a missing legacy icon through WP.
 */
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
}
add_action( 'template_redirect', 'impact_favicon_serve_legacy_routes', -30000 );

function impact_favicon_render_wp_head() {
	$url = esc_url( impact_favicon_endpoint_url() );
	if ( ! $url ) {
		return;
	}
	echo '<link rel="icon" href="' . $url . '" sizes="32x32" type="image/x-icon" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<link rel="shortcut icon" href="' . $url . '" type="image/x-icon" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$touch = function_exists( 'get_site_icon_url' ) ? get_site_icon_url( 180 ) : '';
	if ( $touch ) {
		echo '<link rel="apple-touch-icon" href="' . esc_url( $touch ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
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

	$url   = esc_url( impact_favicon_endpoint_url() );
	$touch = function_exists( 'get_site_icon_url' ) ? get_site_icon_url( 180 ) : '';
	$tags  = '<link rel="icon" href="' . $url . '" sizes="32x32" type="image/x-icon" />'
		. '<link rel="shortcut icon" href="' . $url . '" type="image/x-icon" />';
	if ( $touch ) {
		$tags .= '<link rel="apple-touch-icon" href="' . esc_url( $touch ) . '" />';
	}

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
