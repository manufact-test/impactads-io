<?php
/**
 * /application page — full-page access request form.
 *
 * @package ImpactAccsChrome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Application page installer + content renderer.
 */
class IAC_Application_Page {

	/**
	 * Option key for page ID.
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
	 * Constructor.
	 */
	private function __construct() {
		add_filter( 'the_content', array( $this, 'filter_content' ), 1 );
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );
	}

	/**
	 * Ensure application page exists.
	 *
	 * @return int Page ID.
	 */
	public static function ensure_page() {
		$page_id = (int) get_option( self::OPTION_PAGE_ID, 0 );
		if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
			return $page_id;
		}

		$existing = get_page_by_path( 'application' );
		if ( $existing instanceof WP_Post ) {
			update_option( self::OPTION_PAGE_ID, $existing->ID );
			return (int) $existing->ID;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => 'Request Access',
				'post_name'    => 'application',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- impact-accs-application -->',
			),
			true
		);

		if ( is_wp_error( $page_id ) ) {
			return 0;
		}

		update_option( self::OPTION_PAGE_ID, $page_id );
		return (int) $page_id;
	}

	/**
	 * Application page URL.
	 *
	 * @return string
	 */
	public static function url() {
		$page_id = (int) get_option( self::OPTION_PAGE_ID, 0 );
		if ( $page_id ) {
			$link = get_permalink( $page_id );
			if ( is_string( $link ) && '' !== $link ) {
				return $link;
			}
		}

		$page = get_page_by_path( 'application' );
		if ( $page instanceof WP_Post ) {
			return get_permalink( $page );
		}

		return home_url( '/application/' );
	}

	/**
	 * Is current request the application page?
	 *
	 * @return bool
	 */
	public static function is_application_page() {
		if ( ! is_page() ) {
			return false;
		}

		$page_id = (int) get_option( self::OPTION_PAGE_ID, 0 );
		if ( $page_id && get_queried_object_id() === $page_id ) {
			return true;
		}

		$post = get_queried_object();
		return $post instanceof WP_Post && 'application' === $post->post_name;
	}

	/**
	 * Replace page content with application layout.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function filter_content( $content ) {
		if ( ! self::is_application_page() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		return IAC_Chrome::instance()->render_application_page();
	}

	/**
	 * Admin notice after page creation.
	 */
	public function admin_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! get_transient( 'iac_application_page_created' ) ) {
			return;
		}

		delete_transient( 'iac_application_page_created' );

		$url = self::url();
		echo '<div class="notice notice-success is-dismissible"><p>';
		echo esc_html__( 'Impact.accs: страница заявки создана.', 'impact-accs-chrome' );
		echo ' <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Открыть /application', 'impact-accs-chrome' ) . '</a>';
		echo '</p></div>';
	}
}
