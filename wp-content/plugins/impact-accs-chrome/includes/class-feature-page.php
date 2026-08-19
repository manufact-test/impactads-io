<?php
/**
 * /accounts/* pages — Platform Access, Agency Accounts, Team Supply.
 *
 * @package ImpactAccsChrome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Account feature pages installer + content renderer.
 */
class IAC_Feature_Page {

	/**
	 * Option key for parent page ID.
	 */
	const OPTION_PARENT_ID = 'iac_accounts_parent_id';

	/**
	 * Legacy option key (features parent).
	 */
	const OPTION_LEGACY_PARENT_ID = 'iac_features_parent_id';

	/**
	 * Page slug => config.
	 */
	const FEATURES = array(
		'platform-access'  => array(
			'title'    => 'Platform Access',
			'template' => 'feature-conversational-debugging.html',
			'marker'   => 'impact-accs-account-platform-access',
		),
		'agency-accounts'  => array(
			'title'    => 'Agency Accounts',
			'template' => 'feature-autonomous-alerts.html',
			'marker'   => 'impact-accs-account-agency-accounts',
		),
		'team-supply'      => array(
			'title'    => 'Team Supply',
			'template' => 'feature-coding-agents.html',
			'marker'   => 'impact-accs-account-team-supply',
		),
	);

	/**
	 * Legacy slugs mapped to current slugs.
	 *
	 * @var array<string,string>
	 */
	const LEGACY_SLUGS = array(
		'conversational-debugging' => 'platform-access',
		'autonomous-alerts'        => 'agency-accounts',
		'coding-agents'            => 'team-supply',
	);

	/**
	 * Singleton.
	 *
	 * @var IAC_Feature_Page|null
	 */
	private static $instance = null;

	/**
	 * Instance getter.
	 *
	 * @return IAC_Feature_Page
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
		add_action( 'wp', array( $this, 'hide_theme_page_title' ) );
		add_action( 'template_redirect', array( $this, 'redirect_legacy_urls' ), 1 );
	}

	/**
	 * Remove Hello Elementor duplicate H1 on account routes.
	 */
	public function hide_theme_page_title() {
		if ( ! self::is_feature_page() ) {
			return;
		}
		add_filter( 'hello_elementor_page_title', '__return_false' );
		remove_filter( 'the_content', 'wpautop' );
		remove_filter( 'the_content', 'shortcode_unautop' );
	}

	/**
	 * Ensure parent + child account pages exist.
	 *
	 * @return int Parent page ID.
	 */
	public static function ensure_pages() {
		$parent_id = (int) get_option( self::OPTION_PARENT_ID, 0 );
		if ( $parent_id && 'publish' === get_post_status( $parent_id ) ) {
			$parent = $parent_id;
		} else {
			$existing = get_page_by_path( 'accounts' );
			if ( $existing instanceof WP_Post ) {
				$parent = (int) $existing->ID;
				update_option( self::OPTION_PARENT_ID, $parent );
			} else {
				$parent = wp_insert_post(
					array(
						'post_title'   => 'Accounts',
						'post_name'    => 'accounts',
						'post_status'  => 'publish',
						'post_type'    => 'page',
						'post_content' => '<!-- impact-accs-accounts -->',
					),
					true
				);
				if ( is_wp_error( $parent ) ) {
					return 0;
				}
				update_option( self::OPTION_PARENT_ID, $parent );
			}
		}

		foreach ( self::FEATURES as $slug => $config ) {
			self::ensure_account_page( $slug, $config, $parent );
		}

		self::maybe_migrate_legacy_pages( $parent );

		return (int) $parent;
	}

	/**
	 * One-time move /features/* → /accounts/*.
	 *
	 * @param int $accounts_parent_id Accounts parent page ID.
	 */
	private static function maybe_migrate_legacy_pages( $accounts_parent_id ) {
		$done = get_option( 'iac_accounts_migrated', '' );
		if ( $done === IAC_VERSION ) {
			return;
		}

		foreach ( self::FEATURES as $slug => $config ) {
			$existing = get_page_by_path( 'accounts/' . $slug );
			if ( $existing instanceof WP_Post ) {
				continue;
			}

			$legacy_slug = self::legacy_slug_for( $slug );
			if ( ! $legacy_slug ) {
				continue;
			}

			$legacy = get_page_by_path( 'features/' . $legacy_slug );
			if ( ! $legacy instanceof WP_Post ) {
				continue;
			}

			wp_update_post(
				array(
					'ID'          => $legacy->ID,
					'post_title'  => $config['title'],
					'post_name'   => $slug,
					'post_parent' => $accounts_parent_id,
					'post_status' => 'publish',
				)
			);
		}

		self::trash_duplicate_legacy_pages();
		self::cleanup_legacy_parent();

		update_option( 'iac_accounts_migrated', IAC_VERSION );
		flush_rewrite_rules( false );
	}

	/**
	 * Remove old /features/* pages when /accounts/* copies already exist.
	 */
	private static function trash_duplicate_legacy_pages() {
		foreach ( self::LEGACY_SLUGS as $legacy_slug => $new_slug ) {
			$legacy = get_page_by_path( 'features/' . $legacy_slug );
			$target = get_page_by_path( 'accounts/' . $new_slug );
			if (
				$legacy instanceof WP_Post &&
				$target instanceof WP_Post &&
				(int) $legacy->ID !== (int) $target->ID
			) {
				wp_trash_post( $legacy->ID );
			}
		}
	}

	/**
	 * 301 redirect old /features/* URLs to /accounts/*.
	 */
	public function redirect_legacy_urls() {
		if ( is_admin() ) {
			return;
		}

		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		if ( ! is_string( $request ) || '' === $request ) {
			return;
		}

		$path = trim( (string) parse_url( $request, PHP_URL_PATH ), '/' );
		if ( '' === $path || 0 !== strpos( $path, 'features/' ) ) {
			return;
		}

		$legacy_slug = substr( $path, strlen( 'features/' ) );
		$legacy_slug = trim( $legacy_slug, '/' );

		if ( isset( self::LEGACY_SLUGS[ $legacy_slug ] ) ) {
			wp_safe_redirect( self::url( self::LEGACY_SLUGS[ $legacy_slug ] ), 301 );
			exit;
		}

		if ( 'coding-agents-welcome' === $legacy_slug ) {
			wp_safe_redirect( self::url( 'team-supply' ), 301 );
			exit;
		}
	}

	/**
	 * Trash empty legacy /features parent after migration.
	 */
	private static function cleanup_legacy_parent() {
		$legacy_parent = get_page_by_path( 'features' );
		if ( ! $legacy_parent instanceof WP_Post ) {
			return;
		}

		$children = get_pages(
			array(
				'post_type'   => 'page',
				'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
				'parent'      => $legacy_parent->ID,
				'number'      => 1,
			)
		);

		if ( empty( $children ) ) {
			wp_trash_post( $legacy_parent->ID );
		}
	}

	/**
	 * Legacy slug for a current slug, if any.
	 *
	 * @param string $slug Current slug.
	 * @return string
	 */
	private static function legacy_slug_for( $slug ) {
		foreach ( self::LEGACY_SLUGS as $legacy => $current ) {
			if ( $current === $slug ) {
				return $legacy;
			}
		}
		return '';
	}

	/**
	 * Ensure a single account child page exists.
	 *
	 * @param string               $slug      Page slug.
	 * @param array<string,string> $config    Page config.
	 * @param int                  $parent_id Parent page ID.
	 * @return int Page ID.
	 */
	private static function ensure_account_page( $slug, $config, $parent_id ) {
		$existing = get_page_by_path( 'accounts/' . $slug );
		if ( $existing instanceof WP_Post ) {
			return (int) $existing->ID;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => $config['title'],
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_parent'  => $parent_id,
				'post_content' => '<!-- ' . $config['marker'] . ' -->',
			),
			true
		);

		if ( is_wp_error( $page_id ) ) {
			return 0;
		}

		return (int) $page_id;
	}

	/**
	 * Account page URL by slug.
	 *
	 * @param string $slug Page slug.
	 * @return string
	 */
	public static function url( $slug ) {
		if ( isset( self::LEGACY_SLUGS[ $slug ] ) ) {
			$slug = self::LEGACY_SLUGS[ $slug ];
		}

		if ( ! isset( self::FEATURES[ $slug ] ) ) {
			return home_url( '/accounts/' . $slug . '/' );
		}

		$page = get_page_by_path( 'accounts/' . $slug );
		if ( $page instanceof WP_Post ) {
			$link = get_permalink( $page );
			if ( is_string( $link ) && '' !== $link ) {
				return $link;
			}
		}

		return home_url( '/accounts/' . $slug . '/' );
	}

	/**
	 * All account URLs for JS dropdown.
	 *
	 * @return array<string,string>
	 */
	public static function urls_for_js() {
		return array(
			'platform' => self::url( 'platform-access' ),
			'agency'   => self::url( 'agency-accounts' ),
			'team'     => self::url( 'team-supply' ),
		);
	}

	/**
	 * Resolve current page slug.
	 *
	 * @return string
	 */
	public static function get_slug() {
		if ( ! is_page() ) {
			return '';
		}

		$post = get_queried_object();
		if ( ! $post instanceof WP_Post ) {
			return '';
		}

		if ( array_key_exists( $post->post_name, self::FEATURES ) ) {
			$slug = $post->post_name;
		} elseif ( array_key_exists( $post->post_name, self::LEGACY_SLUGS ) ) {
			$slug = self::LEGACY_SLUGS[ $post->post_name ];
		} else {
			return '';
		}

		$parent_id = (int) get_option( self::OPTION_PARENT_ID, 0 );
		if ( $parent_id && (int) $post->post_parent === $parent_id ) {
			return $slug;
		}

		$parent = get_post( (int) $post->post_parent );
		if ( $parent instanceof WP_Post && 'accounts' === $parent->post_name ) {
			return $slug;
		}

		$legacy_parent_id = (int) get_option( self::OPTION_LEGACY_PARENT_ID, 0 );
		if ( $legacy_parent_id && (int) $post->post_parent === $legacy_parent_id ) {
			return $slug;
		}

		$legacy_parent = get_page_by_path( 'features' );
		if ( $legacy_parent instanceof WP_Post && (int) $post->post_parent === (int) $legacy_parent->ID ) {
			return $slug;
		}

		return '';
	}

	/**
	 * Is current request an account feature page?
	 *
	 * @return bool
	 */
	public static function is_feature_page() {
		return '' !== self::get_slug();
	}

	/**
	 * Replace page content with account layout.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function filter_content( $content ) {
		if ( ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$slug = self::get_slug();
		if ( $slug ) {
			return IAC_Chrome::instance()->render_feature_page( $slug );
		}

		return $content;
	}

	/**
	 * Admin notice after pages creation.
	 */
	public function admin_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! get_transient( 'iac_feature_pages_created' ) ) {
			return;
		}

		delete_transient( 'iac_feature_pages_created' );

		$url = self::url( 'platform-access' );
		echo '<div class="notice notice-success is-dismissible"><p>';
		echo esc_html__( 'Impact.accs: страницы Accounts созданы.', 'impact-accs-chrome' );
		echo ' <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Открыть Platform Access', 'impact-accs-chrome' ) . '</a>';
		echo '</p></div>';
	}
}
