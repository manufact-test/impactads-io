<?php
/**
 * Safe RU runtime layer for the mirrored homepage 3D/React hero.
 *
 * This deliberately runs after the original Next.js bundles instead of
 * rewriting them, because localized Turbopack chunks break React hydration.
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IAH_RU_Hero_Runtime {

	/**
	 * Register an output buffer immediately before the mirrored homepage renderer.
	 */
	public static function boot() {
		add_action( 'template_redirect', array( __CLASS__, 'start_buffer' ), -10000 );
	}

	/**
	 * Start buffering only for the RU site root.
	 */
	public static function start_buffer() {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}
		if ( ! self::is_root_request() || ! self::is_ru() ) {
			return;
		}

		ob_start( array( __CLASS__, 'inject_runtime' ) );
	}

	/**
	 * @return bool
	 */
	private static function is_root_request() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! is_string( $uri ) || '' === $uri ) {
			return false;
		}
		$path = strtok( $uri, '?' );
		return '/' === $path || '' === $path;
	}

	/**
	 * @return bool
	 */
	private static function is_ru() {
		if ( class_exists( 'IAC_I18n' ) ) {
			return IAC_I18n::is_ru();
		}

		$lang = 'ru';
		foreach ( array( 'iac_lang4', 'iac_lang3', 'iac_lang' ) as $name ) {
			if ( isset( $_COOKIE[ $name ] ) ) {
				$lang = sanitize_key( wp_unslash( $_COOKIE[ $name ] ) );
				break;
			}
		}
		return 'en' !== $lang;
	}

	/**
	 * Append safe RU runtimes after the mirrored React app.
	 *
	 * @param string $html Full response body.
	 * @return string
	 */
	public static function inject_runtime( $html ) {
		if ( ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		$tags = '';
		if ( false === strpos( $html, 'iah-ru-hero-runtime.js' ) ) {
			$src   = esc_url( IAH_URL . 'assets/js/iah-ru-hero-runtime.js?v=' . rawurlencode( IAH_VERSION ) );
			$tags .= '<script id="iah-ru-hero-runtime" src="' . $src . '"></script>';
		}
		if ( false === strpos( $html, 'iah-ru-hero-resolution.js' ) ) {
			$src   = esc_url( IAH_URL . 'assets/js/iah-ru-hero-resolution.js?v=' . rawurlencode( IAH_VERSION ) );
			$tags .= '<script id="iah-ru-hero-resolution" src="' . $src . '"></script>';
		}

		if ( '' === $tags ) {
			return $html;
		}

		if ( false !== stripos( $html, '</body>' ) ) {
			return preg_replace( '/<\/body>/i', $tags . '</body>', $html, 1 );
		}

		return $html . $tags;
	}
}

IAH_RU_Hero_Runtime::boot();
