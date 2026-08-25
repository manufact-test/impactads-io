<?php
/**
 * Legacy application route cleanup.
 *
 * The public /application page and access form have been retired. This small
 * compatibility class exists only so older templates/scripts resolve former
 * access links to /contact/ while the obsolete WordPress page is removed.
 *
 * @package ImpactAccsChrome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove the retired application page and route legacy query links to Contact.
 */
class IAC_Application_Page {

	/**
	 * Legacy option key for the old page ID.
	 */
	const OPTION_PAGE_ID = 'iac_application_page_id';

	/**
	 * Singleton.
	 *
	 * @var IAC_Application_Page|null
	 */
	private static $instance = null;

	/**
	 * Instance getter.
	 *
	 * @return IAC_Application_Page
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register compatibility redirects for the retired query-string entrypoints.
	 */
	private function __construct() {
		add_action( 'template_redirect', array( $this, 'redirect_legacy_queries' ), -1000 );
	}

	/**
	 * Delete the old WordPress application page if it still exists.
	 *
	 * @return int Always zero: the page must never be recreated.
	 */
	public static function ensure_page() {
		$page_id = (int) get_option( self::OPTION_PAGE_ID, 0 );
		if ( $page_id ) {
			$page = get_post( $page_id );
			if ( $page instanceof WP_Post && 'page' === $page->post_type && 'application' === $page->post_name ) {
				wp_delete_post( $page_id, true );
			}
		}

		$legacy = get_page_by_path( 'application', OBJECT, 'page' );
		if ( $legacy instanceof WP_Post ) {
			wp_delete_post( $legacy->ID, true );
		}

		delete_option( self::OPTION_PAGE_ID );
		delete_transient( 'iac_application_page_created' );

		return 0;
	}

	/**
	 * Compatibility URL used by old cached markup/scripts.
	 *
	 * @return string
	 */
	public static function url() {
		if ( class_exists( 'IAC_Contact_Page' ) ) {
			return IAC_Contact_Page::url();
		}
		return home_url( '/contact/' );
	}

	/**
	 * The retired page is never a valid current page.
	 *
	 * @return bool
	 */
	public static function is_application_page() {
		return false;
	}

	/**
	 * Preserve old bookmarked query URLs without reviving the popup/form.
	 */
	public function redirect_legacy_queries() {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		$contact  = isset( $_GET['contact'] ) ? sanitize_text_field( wp_unslash( $_GET['contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$waitlist = isset( $_GET['waitlist'] ) ? sanitize_text_field( wp_unslash( $_GET['waitlist'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'true' !== strtolower( $contact ) && 'true' !== strtolower( $waitlist ) ) {
			return;
		}

		wp_safe_redirect( self::url(), 301 );
		exit;
	}
}
