<?php
/**
 * /blog index and child post pages.
 *
 * @package ImpactAccsChrome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blog pages installer + content renderer.
 */
class IAC_Blog_Page {

	/**
	 * Option key for blog index page ID.
	 */
	const OPTION_BLOG_ID = 'iac_blog_page_id';

	/**
	 * Post slug => template file map.
	 *
	 * @var array<string, string>
	 */
	const POSTS = array(
		'manifesto' => 'manifesto',
		'markets'   => 'markets',
	);

	/**
	 * Singleton.
	 *
	 * @var IAC_Blog_Page|null
	 */
	private static $instance = null;

	/**
	 * Instance getter.
	 *
	 * @return IAC_Blog_Page
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
	}

	/**
	 * Remove Hello Elementor duplicate H1 on blog routes.
	 */
	public function hide_theme_page_title() {
		if ( ! self::is_blog_index() && ! self::is_blog_post() ) {
			return;
		}
		add_filter( 'hello_elementor_page_title', '__return_false' );
		remove_filter( 'the_content', 'wpautop' );
		remove_filter( 'the_content', 'shortcode_unautop' );
	}

	/**
	 * Ensure blog index and post pages exist.
	 *
	 * @return int Blog index page ID.
	 */
	public static function ensure_pages() {
		$blog_id = self::ensure_blog_index();
		if ( ! $blog_id ) {
			return 0;
		}

		self::migrate_legacy_blog_slugs( $blog_id );

		foreach ( self::POSTS as $slug => $title_key ) {
			self::ensure_post_page( $slug, $blog_id, $title_key );
		}

		return $blog_id;
	}

	/**
	 * Rename legacy blog post slugs (yc-p26 → markets).
	 *
	 * @param int $blog_id Blog index page ID.
	 */
	private static function migrate_legacy_blog_slugs( $blog_id ) {
		$legacy = get_page_by_path( 'blog/yc-p26' );
		if ( ! ( $legacy instanceof WP_Post ) || 'yc-p26' !== $legacy->post_name ) {
			return;
		}

		$markets = get_page_by_path( 'blog/markets' );
		if ( $markets instanceof WP_Post ) {
			return;
		}

		wp_update_post(
			array(
				'ID'        => $legacy->ID,
				'post_name' => 'markets',
			)
		);
	}

	/**
	 * Ensure blog index page exists.
	 *
	 * @return int Page ID.
	 */
	private static function ensure_blog_index() {
		$page_id = (int) get_option( self::OPTION_BLOG_ID, 0 );
		if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
			return $page_id;
		}

		$existing = get_page_by_path( 'blog' );
		if ( $existing instanceof WP_Post ) {
			update_option( self::OPTION_BLOG_ID, $existing->ID );
			return (int) $existing->ID;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => 'Blog',
				'post_name'    => 'blog',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- impact-accs-blog -->',
			),
			true
		);

		if ( is_wp_error( $page_id ) ) {
			return 0;
		}

		update_option( self::OPTION_BLOG_ID, $page_id );
		return (int) $page_id;
	}

	/**
	 * Ensure a child blog post page exists.
	 *
	 * @param string $slug      Post slug.
	 * @param int    $parent_id Parent blog page ID.
	 * @param string $title_key Title lookup key.
	 * @return int Page ID.
	 */
	private static function ensure_post_page( $slug, $parent_id, $title_key ) {
		$existing = get_page_by_path( 'blog/' . $slug );
		if ( $existing instanceof WP_Post ) {
			return (int) $existing->ID;
		}

		$titles = array(
			'manifesto' => 'The impact.accs Manifesto',
			'markets'   => 'Five Years in the Market',
		);

		$page_id = wp_insert_post(
			array(
				'post_title'   => isset( $titles[ $title_key ] ) ? $titles[ $title_key ] : ucfirst( $slug ),
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_parent'  => $parent_id,
				'post_content' => '<!-- impact-accs-blog-' . $slug . ' -->',
			),
			true
		);

		if ( is_wp_error( $page_id ) ) {
			return 0;
		}

		return (int) $page_id;
	}

	/**
	 * Blog index URL.
	 *
	 * @return string
	 */
	public static function url() {
		$page_id = (int) get_option( self::OPTION_BLOG_ID, 0 );
		if ( $page_id ) {
			$link = get_permalink( $page_id );
			if ( is_string( $link ) && '' !== $link ) {
				return $link;
			}
		}

		$page = get_page_by_path( 'blog' );
		if ( $page instanceof WP_Post ) {
			return get_permalink( $page );
		}

		return home_url( '/blog/' );
	}

	/**
	 * Post URL by slug.
	 *
	 * @param string $slug Post slug.
	 * @return string
	 */
	public static function post_url( $slug ) {
		$page = get_page_by_path( 'blog/' . $slug );
		if ( $page instanceof WP_Post ) {
			return get_permalink( $page );
		}

		return home_url( '/blog/' . $slug . '/' );
	}

	/**
	 * Is current request the blog index?
	 *
	 * @return bool
	 */
	public static function is_blog_index() {
		if ( ! is_page() ) {
			return false;
		}

		$page_id = (int) get_option( self::OPTION_BLOG_ID, 0 );
		if ( $page_id && get_queried_object_id() === $page_id ) {
			return true;
		}

		$post = get_queried_object();
		return $post instanceof WP_Post && 'blog' === $post->post_name && 0 === (int) $post->post_parent;
	}

	/**
	 * Current blog post slug, if any.
	 *
	 * @return string
	 */
	public static function get_post_slug() {
		if ( ! is_page() ) {
			return '';
		}

		$post = get_queried_object();
		if ( ! $post instanceof WP_Post ) {
			return '';
		}

		$dynamic = apply_filters( 'iac_blog_resolve_post_slug', '', $post );
		if ( is_string( $dynamic ) && '' !== $dynamic ) {
			return $dynamic;
		}

		if ( ! array_key_exists( $post->post_name, self::POSTS ) ) {
			return '';
		}

		$blog_id = (int) get_option( self::OPTION_BLOG_ID, 0 );
		if ( $blog_id && (int) $post->post_parent === $blog_id ) {
			return $post->post_name;
		}

		$parent = get_post( (int) $post->post_parent );
		if ( $parent instanceof WP_Post && 'blog' === $parent->post_name ) {
			return $post->post_name;
		}

		return '';
	}

	/**
	 * Is current request a blog post page?
	 *
	 * @return bool
	 */
	public static function is_blog_post() {
		return '' !== self::get_post_slug();
	}

	/**
	 * Replace page content with blog layout.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function filter_content( $content ) {
		if ( ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		if ( self::is_blog_index() ) {
			return IAC_Chrome::instance()->render_blog_page();
		}

		$slug = self::get_post_slug();
		if ( $slug ) {
			return IAC_Chrome::instance()->render_blog_post( $slug );
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

		if ( ! get_transient( 'iac_blog_pages_created' ) ) {
			return;
		}

		delete_transient( 'iac_blog_pages_created' );

		$url = self::url();
		echo '<div class="notice notice-success is-dismissible"><p>';
		echo esc_html__( 'Impact.accs: страницы Blog созданы.', 'impact-accs-chrome' );
		echo ' <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Открыть /blog', 'impact-accs-chrome' ) . '</a>';
		echo '</p></div>';
	}
}
