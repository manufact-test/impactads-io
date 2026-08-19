<?php
/**
 * Serves mirrored static assets at root paths (/models, /textures, /audio, /sequences, /_next/image).
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Static asset proxy.
 */
class IAH_Asset_Proxy {

	/**
	 * Allowed top-level folders inside assets/site/.
	 *
	 * @var array<int,string>
	 */
	private static $allowed_roots = array(
		'models',
		'textures',
		'audio',
		'sequences',
		'basis',
		'draco',
		'assets',
		'_next/static/media',
	);

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_action( 'plugins_loaded', array( __CLASS__, 'early_serve' ), 1 );
		add_action( 'init', array( __CLASS__, 'register_rewrite' ) );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_serve' ), 0 );
	}

	/**
	 * Serve static files before WP routing (fallback when rewrites are stale).
	 */
	public static function early_serve() {
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['iah_static'] ) ) {
			$static = sanitize_text_field( wp_unslash( $_GET['iah_static'] ) );
			if ( $static ) {
				self::serve_file( $static );
			}
		}

		if ( self::is_image_request() ) {
			self::serve_next_image();
		}

		$static = self::detect_static_path();
		if ( $static ) {
			self::serve_file( $static );
		}
	}

	/**
	 * Rewrite rules.
	 */
	public static function register_rewrite() {
		add_rewrite_rule( '^_next/image/?', 'index.php?iah_next_image=1', 'top' );
		add_rewrite_rule( '^models/(.+)$', 'index.php?iah_static=models/$1', 'top' );
		add_rewrite_rule( '^textures/(.+)$', 'index.php?iah_static=textures/$1', 'top' );
		add_rewrite_rule( '^audio/(.+)$', 'index.php?iah_static=audio/$1', 'top' );
		add_rewrite_rule( '^sequences/(.+)$', 'index.php?iah_static=sequences/$1', 'top' );
		add_rewrite_rule( '^basis/(.+)$', 'index.php?iah_static=basis/$1', 'top' );
		add_rewrite_rule( '^draco/(.+)$', 'index.php?iah_static=draco/$1', 'top' );
		add_rewrite_rule( '^assets/(.+)$', 'index.php?iah_static=assets/$1', 'top' );
	}

	/**
	 * Query vars.
	 *
	 * @param array<int,string> $vars Vars.
	 * @return array<int,string>
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'iah_next_image';
		$vars[] = 'iah_static';
		return $vars;
	}

	/**
	 * Serve if matched.
	 */
	public static function maybe_serve() {
		if ( get_query_var( 'iah_next_image' ) || self::is_image_request() ) {
			self::serve_next_image();
			return;
		}

		$static = get_query_var( 'iah_static' );
		if ( ! $static ) {
			$static = self::detect_static_path();
		}

		if ( $static ) {
			self::serve_file( $static );
		}
	}

	/**
	 * @return bool
	 */
	private static function is_image_request() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return ( false !== strpos( $uri, '_next/image' ) );
	}

	/**
	 * Parse /models/... style request from URI.
	 *
	 * @return string|null
	 */
	private static function detect_static_path() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$path = wp_parse_url( $uri, PHP_URL_PATH );
		if ( ! is_string( $path ) ) {
			return null;
		}
		$path = ltrim( $path, '/' );
		foreach ( self::$allowed_roots as $root ) {
			if ( 0 === strpos( $path, $root . '/' ) || $path === $root ) {
				return $path;
			}
		}
		return null;
	}

	/**
	 * Next image optimizer fallback.
	 */
	private static function serve_next_image() {
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
		self::serve_file( ltrim( $path, '/' ) );
	}

	/**
	 * Stream file from assets/site.
	 *
	 * @param string $relative Relative path under assets/site.
	 */
	private static function serve_file( $relative ) {
		$relative = str_replace( array( '..', '\\' ), '', $relative );
		$relative = ltrim( $relative, '/' );

		$allowed = false;
		foreach ( self::$allowed_roots as $root ) {
			if ( 0 === strpos( $relative, $root . '/' ) || $relative === $root ) {
				$allowed = true;
				break;
			}
		}
		if ( ! $allowed ) {
			status_header( 403 );
			exit;
		}

		$file = IAH_DIR . 'assets/site/' . $relative;
		if ( ! is_readable( $file ) ) {
			status_header( 404 );
			exit;
		}

		$mime = wp_check_filetype( $file );
		$type = ! empty( $mime['type'] ) ? $mime['type'] : self::mime_for( $file );
		if ( $type ) {
			header( 'Content-Type: ' . $type );
		}
		header( 'Cache-Control: public, max-age=31536000, immutable' );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		readfile( $file );
		exit;
	}

	/**
	 * Fallback MIME types for mirrored assets.
	 *
	 * @param string $file File path.
	 * @return string
	 */
	private static function mime_for( $file ) {
		$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
		$map = array(
			'glb'  => 'model/gltf-binary',
			'webm' => 'audio/webm',
			'exr'  => 'image/aces',
			'ktx2' => 'image/ktx2',
			'webp' => 'image/webp',
			'png'  => 'image/png',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif'  => 'image/gif',
			'svg'  => 'image/svg+xml',
			'wasm' => 'application/wasm',
			'js'   => 'application/javascript',
			'pdf'  => 'application/pdf',
		);
		return isset( $map[ $ext ] ) ? $map[ $ext ] : '';
	}
}

IAH_Asset_Proxy::boot();
