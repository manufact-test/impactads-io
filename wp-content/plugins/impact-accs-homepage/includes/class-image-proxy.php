<?php
/**
 * Serves /_next/image requests using mirrored static files.
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image proxy for Next.js Image component.
 */
class IAH_Image_Proxy {

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_action( 'init', array( __CLASS__, 'register_rewrite' ) );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_serve' ), 0 );
	}

	/**
	 * Rewrite rule for root /_next/image URLs.
	 */
	public static function register_rewrite() {
		add_rewrite_rule( '^_next/image/?', 'index.php?iah_next_image=1', 'top' );
	}

	/**
	 * Query var.
	 *
	 * @param array<int,string> $vars Vars.
	 * @return array<int,string>
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'iah_next_image';
		return $vars;
	}

	/**
	 * Serve image when URI matches Next image optimizer pattern.
	 */
	public static function maybe_serve() {
		if ( ! self::is_image_request() ) {
			return;
		}

		self::serve();
	}

	/**
	 * Detect image optimizer request.
	 *
	 * @return bool
	 */
	private static function is_image_request() {
		if ( get_query_var( 'iah_next_image' ) ) {
			return true;
		}

		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return ( false !== strpos( $uri, '_next/image' ) );
	}

	/**
	 * Output file bytes.
	 */
	public static function serve() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$raw = isset( $_GET['url'] ) ? wp_unslash( $_GET['url'] ) : '';
		if ( '' === $raw ) {
			status_header( 400 );
			exit;
		}

		$path = wp_parse_url( urldecode( $raw ), PHP_URL_PATH );
		if ( ! is_string( $path ) || '' === $path ) {
			status_header( 400 );
			exit;
		}

		$path = ltrim( $path, '/' );
		if ( 0 !== strpos( $path, 'assets/' ) && 0 !== strpos( $path, '_next/static/media/' ) ) {
			status_header( 403 );
			exit;
		}

		$file = IAH_DIR . 'assets/site/' . $path;
		if ( ! is_readable( $file ) ) {
			status_header( 404 );
			exit;
		}

		$mime = wp_check_filetype( $file );
		if ( ! empty( $mime['type'] ) ) {
			header( 'Content-Type: ' . $mime['type'] );
		}
		header( 'Cache-Control: public, max-age=31536000, immutable' );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		readfile( $file );
		exit;
	}
}

IAH_Image_Proxy::boot();
