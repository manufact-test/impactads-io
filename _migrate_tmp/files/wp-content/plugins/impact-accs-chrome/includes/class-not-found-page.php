<?php
/**
 * Custom 404 — arcade-style page (Sazabi-inspired).
 *
 * @package ImpactAccsChrome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 404 handler.
 */
class IAC_Not_Found_Page {

	/**
	 * Singleton.
	 *
	 * @var IAC_Not_Found_Page|null
	 */
	private static $instance = null;

	/**
	 * Instance getter.
	 *
	 * @return IAC_Not_Found_Page
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_filter( 'template_include', array( $this, 'template_include' ), 99 );
	}

	/**
	 * Is current request a 404?
	 *
	 * @return bool
	 */
	public static function is_not_found() {
		return is_404();
	}

	/**
	 * Use plugin 404 template.
	 *
	 * @param string $template Current template.
	 * @return string
	 */
	public function template_include( $template ) {
		if ( ! self::is_not_found() ) {
			return $template;
		}

		if ( is_admin() || wp_doing_ajax() ) {
			return $template;
		}

		$custom = IAC_DIR . 'templates/404-impact.php';
		if ( is_readable( $custom ) ) {
			return $custom;
		}

		return $template;
	}
}
