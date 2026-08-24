<?php
/**
 * 1:1 chrome from original impact.accs static HTML/CSS.
 *
 * @package ImpactAccsChrome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Chrome renderer.
 */
class IAC_Chrome {

	/**
	 * Singleton.
	 *
	 * @var IAC_Chrome|null
	 */
	private static $instance = null;

	/**
	 * Header printed flag.
	 *
	 * @var bool
	 */
	private $opened = false;

	/**
	 * Instance getter.
	 *
	 * @return IAC_Chrome
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
		add_filter( 'hello_elementor_header_footer', '__return_false' );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_theme_conflicts' ), 1000 );
		add_action( 'wp_head', array( $this, 'render_favicon' ), 0 );
		add_filter( 'get_site_icon_url', array( $this, 'filter_site_icon_url' ), 99 );
		add_filter( 'site_icon_meta_tags', array( $this, 'filter_site_icon_meta_tags' ), 99 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'language_attributes', array( $this, 'language_attributes' ) );
		add_action( 'wp_body_open', array( $this, 'render_opening' ), 1 );
		add_action( 'wp_footer', array( $this, 'render_closing' ), 5 );

		add_shortcode( 'impact_request_access', array( $this, 'shortcode_request_access' ) );
		add_action( 'wp_ajax_iac_submit_access', array( $this, 'ajax_submit_access' ) );
		add_action( 'wp_ajax_nopriv_iac_submit_access', array( $this, 'ajax_submit_access' ) );
	}

	/**
	 * Should render chrome?
	 *
	 * @return bool
	 */
	public function should_render() {
		if ( is_admin() || wp_doing_ajax() ) {
			return false;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}
		if ( is_feed() || is_embed() || is_robots() ) {
			return false;
		}
		if ( $this->is_elementor_editor() ) {
			return false;
		}
		if ( class_exists( 'IAH_Homepage' ) && IAH_Homepage::is_home_page() ) {
			return false;
		}
		$iah_page = (int) get_option( 'iah_page_id', 0 );
		if ( $iah_page && get_queried_object_id() === $iah_page ) {
			return false;
		}
		return (bool) apply_filters( 'iac_render_chrome', true );
	}

	/**
	 * Elementor editor detection.
	 *
	 * @return bool
	 */
	private function is_elementor_editor() {
		if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}
		if ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$p = \Elementor\Plugin::instance();
			if ( isset( $p->preview ) && method_exists( $p->preview, 'is_preview_mode' ) && $p->preview->is_preview_mode() ) {
				return true;
			}
			if ( isset( $p->editor ) && method_exists( $p->editor, 'is_edit_mode' ) && $p->editor->is_edit_mode() ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Favicon URL used site-wide when chrome is active.
	 *
	 * @return string
	 */
	public function favicon_url() {
		return IAC_URL . 'assets/site/assets/favicon.png';
	}

	/**
	 * Output favicon link tags.
	 */
	public function render_favicon() {
		if ( ! $this->should_render() ) {
			return;
		}

		$icon = esc_url( $this->favicon_url() );
		$ver  = esc_attr( IAC_VERSION );

		echo '<link rel="icon" href="' . $icon . '?v=' . $ver . '" type="image/png" sizes="any" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<link rel="shortcut icon" href="' . $icon . '?v=' . $ver . '" type="image/png" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<link rel="apple-touch-icon" href="' . $icon . '?v=' . $ver . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Override WordPress site icon when chrome renders.
	 *
	 * @param string $url Site icon URL.
	 * @return string
	 */
	public function filter_site_icon_url( $url ) {
		if ( ! $this->should_render() ) {
			return $url;
		}
		return $this->favicon_url();
	}

	/**
	 * Prevent duplicate WP site icon tags; chrome outputs its own.
	 *
	 * @param array<int,string> $tags Meta tags.
	 * @return array<int,string>
	 */
	public function filter_site_icon_meta_tags( $tags ) {
		if ( ! $this->should_render() ) {
			return $tags;
		}
		return array();
	}

	/**
	 * Enqueue original site CSS + bridge JS.
	 */
	public function enqueue_assets() {
		if ( ! $this->should_render() ) {
			return;
		}

		wp_enqueue_style(
			'impact-accs-site-1',
			IAC_URL . 'assets/site/_next/static/chunks/e404d10d1b589fe4.css',
			array(),
			IAC_VERSION
		);
		wp_enqueue_style(
			'impact-accs-site-2',
			IAC_URL . 'assets/site/_next/static/chunks/507126d1be67e5b2.css',
			array( 'impact-accs-site-1' ),
			IAC_VERSION
		);
		wp_enqueue_style(
			'impact-accs-wp',
			IAC_URL . 'assets/css/wp-overrides.css',
			array( 'impact-accs-site-2' ),
			IAC_VERSION
		);

		wp_enqueue_style(
			'impact-accs-accounts-dropdown',
			IAC_URL . 'assets/css/accounts-dropdown.css',
			array( 'impact-accs-wp' ),
			IAC_VERSION
		);

		wp_enqueue_style(
			'impact-accs-waitlist-modal',
			IAC_URL . 'assets/css/waitlist-modal.css',
			array( 'impact-accs-wp' ),
			IAC_VERSION
		);

		if ( class_exists( 'IAC_Application_Page' ) && IAC_Application_Page::is_application_page() ) {
			wp_enqueue_style(
				'impact-accs-application-page',
				IAC_URL . 'assets/css/application-page.css',
				array( 'impact-accs-waitlist-modal' ),
				IAC_VERSION
			);
		}

		if ( class_exists( 'IAC_Contact_Page' ) && IAC_Contact_Page::is_contact_page() ) {
			wp_enqueue_style(
				'impact-accs-contact-page',
				IAC_URL . 'assets/css/contact-page.css',
				array( 'impact-accs-waitlist-modal' ),
				IAC_VERSION
			);
		}

		if ( $this->uses_grid_background() ) {
			wp_enqueue_style(
				'impact-accs-blog-page',
				IAC_URL . 'assets/css/blog-page.css',
				array( 'impact-accs-wp' ),
				IAC_VERSION
			);
		}

		if ( class_exists( 'IAC_About_Page' ) && IAC_About_Page::is_about_page() ) {
			wp_enqueue_style(
				'impact-accs-about-page',
				IAC_URL . 'assets/css/about-page.css',
				array( 'impact-accs-wp' ),
				IAC_VERSION
			);

			wp_enqueue_script(
				'impact-accs-about-page',
				IAC_URL . 'assets/js/about-page.js',
				array( 'impact-accs-bridge' ),
				IAC_VERSION,
				true
			);
		}

		if ( class_exists( 'IAC_Feature_Page' ) && IAC_Feature_Page::is_feature_page() ) {
			wp_enqueue_style(
				'impact-accs-feature-page',
				IAC_URL . 'assets/css/feature-page.css',
				array( 'impact-accs-blog-page' ),
				IAC_VERSION
			);

			wp_enqueue_script(
				'impact-accs-feature-page',
				IAC_URL . 'assets/js/feature-page.js',
				array( 'impact-accs-bridge' ),
				IAC_VERSION,
				true
			);

			if ( 'team-supply' === IAC_Feature_Page::get_slug() ) {
				wp_enqueue_style(
					'impact-accs-team-supply-map',
					IAC_URL . 'assets/css/team-supply-map-widget.css',
					array( 'impact-accs-feature-page' ),
					IAC_VERSION
				);

				wp_enqueue_script(
					'impact-accs-team-supply-map',
					IAC_URL . 'assets/js/team-supply-map-widget.js',
					array(),
					IAC_VERSION,
					true
				);
			}
		}

		if ( class_exists( 'IAC_Not_Found_Page' ) && IAC_Not_Found_Page::is_not_found() ) {
			wp_enqueue_style(
				'impact-accs-not-found-page',
				IAC_URL . 'assets/css/not-found-page.css',
				array( 'impact-accs-wp' ),
				IAC_VERSION
			);

			wp_enqueue_script(
				'impact-accs-not-found-game',
				IAC_URL . 'assets/js/not-found-game.js',
				array(),
				IAC_VERSION,
				true
			);

			wp_enqueue_script(
				'impact-accs-not-found-page',
				IAC_URL . 'assets/js/not-found-page.js',
				array( 'impact-accs-not-found-game' ),
				IAC_VERSION,
				true
			);
		}

		wp_add_inline_style(
			'impact-accs-wp',
			'html.iac-chrome-active{--frame-inset:3px;--primary:#ff0027;--foreground:#fafafa;--muted:#808987;--muted-foreground:#b8c6c2;--dropdown:#0a110d;--color-dropdown:#0a110d;--spacing-header:calc(var(--frame-inset,3px)+40px)}'
		);

		wp_enqueue_script(
			'impact-accs-gsap',
			IAC_URL . 'assets/js/gsap.min.js',
			array(),
			'3.12.5',
			true
		);

		wp_enqueue_script(
			'impact-accs-lenis',
			IAC_URL . 'assets/js/lenis.min.js',
			array(),
			'1.1.18',
			true
		);

		wp_enqueue_script(
			'impact-accs-footer-interactive',
			IAC_URL . 'assets/js/footer-interactive.js',
			array(),
			IAC_VERSION,
			true
		);

		wp_enqueue_script(
			'impact-accs-accounts-dropdown',
			IAC_URL . 'assets/js/accounts-dropdown.js',
			array(),
			IAC_VERSION,
			true
		);

		wp_enqueue_style(
			'impact-accs-mobile-menu',
			IAC_URL . 'assets/css/mobile-menu.css',
			array( 'impact-accs-wp' ),
			IAC_VERSION
		);

		wp_enqueue_script(
			'impact-accs-mobile-menu',
			IAC_URL . 'assets/js/mobile-menu.js',
			array( 'impact-accs-gsap' ),
			IAC_VERSION,
			true
		);

		wp_enqueue_style(
			'impact-accs-header-tools',
			IAC_URL . 'assets/css/header-tools.css',
			array( 'impact-accs-wp' ),
			IAC_VERSION
		);

		wp_enqueue_script(
			'impact-accs-header-tools',
			IAC_URL . 'assets/js/header-tools.js',
			array(),
			IAC_VERSION,
			true
		);

		$bridge_deps = array( 'impact-accs-gsap', 'impact-accs-lenis', 'impact-accs-header-tools', 'impact-accs-accounts-dropdown', 'impact-accs-footer-interactive', 'impact-accs-mobile-menu' );
		if ( is_front_page() ) {
			wp_enqueue_script(
				'impact-accs-homepage-i18n',
				IAC_URL . 'assets/js/homepage-i18n.js',
				array(),
				IAC_VERSION,
				true
			);
			$bridge_deps[] = 'impact-accs-homepage-i18n';
		}

		wp_enqueue_script(
			'impact-accs-bridge',
			IAC_URL . 'assets/js/bridge.js',
			$bridge_deps,
			IAC_VERSION,
			true
		);

		wp_localize_script(
			'impact-accs-bridge',
			'iacData',
			array(
				'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'iac_access' ),
				'homeUrl'         => home_url( '/' ),
				'aboutUrl'        => class_exists( 'IAC_About_Page' ) ? IAC_About_Page::url() : home_url( '/about/' ),
				'blogUrl'         => class_exists( 'IAC_Blog_Page' ) ? IAC_Blog_Page::url() : home_url( '/blog/' ),
				'applicationUrl'  => class_exists( 'IAC_Application_Page' ) ? IAC_Application_Page::url() : home_url( '/application/' ),
				'contactUrl'      => class_exists( 'IAC_Contact_Page' ) ? IAC_Contact_Page::url() : home_url( '/contact/' ),
				'isFront'         => is_front_page() ? '1' : '0',
				'isApplication'   => class_exists( 'IAC_Application_Page' ) && IAC_Application_Page::is_application_page() ? '1' : '0',
				'isContact'       => class_exists( 'IAC_Contact_Page' ) && IAC_Contact_Page::is_contact_page() ? '1' : '0',
				'isBlog'          => class_exists( 'IAC_Blog_Page' ) && ( IAC_Blog_Page::is_blog_index() || IAC_Blog_Page::is_blog_post() ) ? '1' : '0',
				'isAbout'         => class_exists( 'IAC_About_Page' ) && IAC_About_Page::is_about_page() ? '1' : '0',
				'isFeature'       => class_exists( 'IAC_Feature_Page' ) && IAC_Feature_Page::is_feature_page() ? '1' : '0',
				'featureUrls'     => class_exists( 'IAC_Feature_Page' ) ? IAC_Feature_Page::urls_for_js() : array(),
				'assetBase'       => trailingslashit( IAC_URL . 'assets/site/' ),
				'pluginUrl'       => trailingslashit( IAC_URL ),
				'lang'            => IAC_I18n::lang(),
				'strings'         => IAC_I18n::js_strings(),
				'htmlReplacements' => IAC_I18n::is_ru() ? IAC_I18n::html_map() : (object) array(),
			)
		);
	}

	/**
	 * Remove Hello Elementor global styles that override chrome colors.
	 */
	public function dequeue_theme_conflicts() {
		if ( ! $this->should_render() ) {
			return;
		}

		$handles = array(
			'hello-elementor',
			'hello-elementor-theme-style',
			'hello-elementor-header-footer',
		);

		foreach ( $handles as $handle ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	}

	/**
	 * Original html classes on body.
	 *
	 * @param array<int,string> $classes Body classes.
	 * @return array<int,string>
	 */
	public function body_class( $classes ) {
		if ( ! $this->should_render() ) {
			return $classes;
		}
		$classes[] = 'iac-chrome-active';
		$classes[] = is_front_page() ? 'iac-chrome-home' : 'iac-chrome-inner';
		if ( class_exists( 'IAC_I18n' ) && IAC_I18n::is_ru() ) {
			$classes[] = 'iac-lang-ru';
		}
		if ( class_exists( 'IAC_Application_Page' ) && IAC_Application_Page::is_application_page() ) {
			$classes[] = 'iac-chrome-application';
			$classes[] = 'iac-waitlist-open';
		}
		if ( class_exists( 'IAC_Contact_Page' ) && IAC_Contact_Page::is_contact_page() ) {
			$classes[] = 'iac-chrome-contact';
			$classes[] = 'iac-waitlist-open';
		}
		if ( class_exists( 'IAC_Blog_Page' ) && ( IAC_Blog_Page::is_blog_index() || IAC_Blog_Page::is_blog_post() ) ) {
			$classes[] = 'iac-chrome-blog';
		}
		if ( class_exists( 'IAC_About_Page' ) && IAC_About_Page::is_about_page() ) {
			$classes[] = 'iac-chrome-about';
		}
		if ( class_exists( 'IAC_Feature_Page' ) && IAC_Feature_Page::is_feature_page() ) {
			$classes[] = 'iac-chrome-feature';
		}
		if ( class_exists( 'IAC_Not_Found_Page' ) && IAC_Not_Found_Page::is_not_found() ) {
			$classes[] = 'iac-not-found-active';
		}
		return $classes;
	}

	/**
	 * Original html font classes.
	 *
	 * @param string $output Language attributes.
	 * @return string
	 */
	public function language_attributes( $output ) {
		if ( ! $this->should_render() ) {
			return $output;
		}
		$extra = '';
		if ( class_exists( 'IAC_Application_Page' ) && IAC_Application_Page::is_application_page() ) {
			$extra = ' iac-waitlist-open';
		} elseif ( class_exists( 'IAC_Contact_Page' ) && IAC_Contact_Page::is_contact_page() ) {
			$extra = ' iac-waitlist-open';
		}
		return ' class="tekosans_992e9a10-module__7AQ5jq__variable spline_sans_fa99ba16-module__B2jdcW__variable eurostile_7a299062-module__JdaVzq__variable jetbrains_mono_7d65b77b-module__VxV-Ta__variable antialiased iac-chrome-active' . $extra . '" lang="' . esc_attr( IAC_I18n::lang() ) . '"' . $output;
	}

	/**
	 * Open wrapper + header + frame.
	 */
	public function render_opening() {
		if ( ! $this->should_render() || $this->opened ) {
			return;
		}
		$this->opened = true;

		$root_class = 'iac-root';
		if ( $this->uses_grid_background() ) {
			$root_class .= ' iac-frame-visible';
		}
		if (
			( class_exists( 'IAC_Application_Page' ) && IAC_Application_Page::is_application_page() ) ||
			( class_exists( 'IAC_Contact_Page' ) && IAC_Contact_Page::is_contact_page() )
		) {
			$root_class .= ' iac-frame-visible iac-waitlist-open';
		}

		echo '<div id="iac-root" class="' . esc_attr( $root_class ) . '">';
		echo IAC_I18n::bootstrap_script(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $this->uses_grid_background() ) {
			echo $this->render_template( 'grid-overlay.html' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo $this->render_template( 'frame.html' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->render_template( 'header.html' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div id="iac-content" class="iac-content">';
	}

	/**
	 * Footer (request access included) + close wrapper.
	 */
	public function render_closing() {
		if ( ! $this->should_render() ) {
			return;
		}
		if ( ! $this->opened ) {
			$this->render_opening();
		}

		echo '</div><!-- /#iac-content -->';

		if ( class_exists( 'IAC_Application_Page' ) && IAC_Application_Page::is_application_page() ) {
			$modal = $this->render_template( 'waitlist-modal.html' );
			$modal = str_replace(
				'id="iac-waitlist-modal" class="iac-waitlist-modal" aria-hidden="true"',
				'id="iac-waitlist-modal" class="iac-waitlist-modal iac-waitlist-open" aria-hidden="false"',
				$modal
			);
			echo $modal; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( class_exists( 'IAC_Contact_Page' ) && IAC_Contact_Page::is_contact_page() ) {
			$modal = $this->render_template( 'contact-modal.html' );
			$modal = str_replace(
				'id="iac-contact-modal" class="iac-waitlist-modal iac-contact-modal" aria-hidden="true"',
				'id="iac-contact-modal" class="iac-waitlist-modal iac-contact-modal iac-waitlist-open" aria-hidden="false"',
				$modal
			);
			echo $modal; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo $this->render_template( 'footer.html' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '</div><!-- /#iac-root -->';
	}

	/**
	 * Shortcode: only request access block extracted from footer template.
	 *
	 * @return string
	 */
	public function shortcode_request_access() {
		$footer = $this->render_template( 'footer.html' );
		if ( preg_match('/<div class="relative flex h-full flex-col items-center justify-center.*?<\/p>\s*<\/div>\s*<\/div>\s*<\/div>/s', $footer, $m ) ) {
			return '<section class="iac-request-shortcode">' . $m[0] . '</section>';
		}
		return $footer;
	}

	/**
	 * Waitlist modal markup for mirrored homepage (no full chrome).
	 *
	 * @return string
	 */
	public function get_waitlist_host_markup() {
		$modal = $this->render_template( 'waitlist-modal.html' );
		if ( '' === $modal ) {
			return '';
		}

		return '<div id="iac-waitlist-host" class="iac-waitlist-host">' . $modal . '</div>';
	}

	/**
	 * Script config for waitlist bridge.js.
	 *
	 * @param bool $is_front Is front page.
	 * @return array<string, string>
	 */
	public function get_waitlist_script_data( $is_front = false ) {
		return array(
			'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
			'nonce'          => wp_create_nonce( 'iac_access' ),
			'homeUrl'        => home_url( '/' ),
			'applicationUrl' => class_exists( 'IAC_Application_Page' ) ? IAC_Application_Page::url() : home_url( '/application/' ),
			'contactUrl'     => class_exists( 'IAC_Contact_Page' ) ? IAC_Contact_Page::url() : home_url( '/contact/' ),
			'isFront'        => $is_front ? '1' : '0',
			'standalone'     => '1',
		);
	}

	/**
	 * Full-page application form markup.
	 *
	 * @return string
	 */
	public function render_application_page() {
		return $this->render_template( 'application-page.html' );
	}

	/**
	 * Full-page contact form markup.
	 *
	 * @return string
	 */
	public function render_contact_page() {
		return $this->render_template( 'contact-page.html' );
	}

	/**
	 * Blog index markup.
	 *
	 * @return string
	 */
	public function render_blog_page() {
		$html = $this->render_template( 'blog-page.html' );
		return apply_filters( 'iac_blog_page_html', $html );
	}

	/**
	 * Single blog post markup.
	 *
	 * @param string $slug Post slug.
	 * @return string
	 */
	public function render_blog_post( $slug ) {
		$map = array(
			'manifesto' => 'blog-post-manifesto.html',
			'markets'   => 'blog-post-yc-p26.html',
		);

		if ( isset( $map[ $slug ] ) ) {
			$html = $this->render_template( $map[ $slug ] );
			return apply_filters( 'iac_blog_post_html', $html, $slug );
		}

		$html = apply_filters( 'iac_blog_render_dynamic_post', '', $slug );
		if ( '' !== $html ) {
			$html = $this->render_html_string( $html );
		}

		return apply_filters( 'iac_blog_post_html', $html, $slug );
	}

	/**
	 * Process dynamic HTML through the same rewrite/i18n pipeline as templates.
	 *
	 * @param string $html Raw markup.
	 * @return string
	 */
	public function render_html_string( $html ) {
		if ( '' === $html ) {
			return '';
		}
		$html = $this->rewrite_html( $html );
		return IAC_I18n::localize_html( $html );
	}

	/**
	 * About page markup.
	 *
	 * @return string
	 */
	public function render_about_page() {
		return $this->render_template( 'about-page.html' );
	}

	/**
	 * 404 arcade page markup.
	 *
	 * @return string
	 */
	public function render_not_found_page() {
		return $this->render_template( 'not-found-page.html' );
	}

	/**
	 * Feature page markup.
	 *
	 * @param string $slug Feature slug.
	 * @return string
	 */
	public function render_feature_page( $slug ) {
		if ( ! class_exists( 'IAC_Feature_Page' ) ) {
			return '';
		}

		$features = IAC_Feature_Page::FEATURES;
		if ( ! isset( $features[ $slug ] ) ) {
			return '';
		}

		return $this->render_template( $features[ $slug ]['template'] );
	}

	/**
	 * Pages that use the square grid background + visible frame.
	 *
	 * @return bool
	 */
	public function uses_grid_background() {
		if ( class_exists( 'IAC_Blog_Page' ) && ( IAC_Blog_Page::is_blog_index() || IAC_Blog_Page::is_blog_post() ) ) {
			return true;
		}
		if ( class_exists( 'IAC_About_Page' ) && IAC_About_Page::is_about_page() ) {
			return true;
		}
		if ( class_exists( 'IAC_Feature_Page' ) && IAC_Feature_Page::is_feature_page() ) {
			return true;
		}
		if ( class_exists( 'IAC_Not_Found_Page' ) && IAC_Not_Found_Page::is_not_found() ) {
			return true;
		}
		return false;
	}

	/**
	 * AJAX form handler.
	 */
	public function ajax_submit_access() {
		check_ajax_referer( 'iac_access', 'nonce' );
		$is_ru = class_exists( 'IAC_I18n' ) && IAC_I18n::is_ru();

		$first     = isset( $_POST['firstName'] ) ? sanitize_text_field( wp_unslash( $_POST['firstName'] ) ) : '';
		$last      = isset( $_POST['lastName'] ) ? sanitize_text_field( wp_unslash( $_POST['lastName'] ) ) : '';
		$email     = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$message   = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		$form_type = isset( $_POST['formType'] ) ? sanitize_text_field( wp_unslash( $_POST['formType'] ) ) : 'access';
		$topic     = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';

		if ( '' === $message && isset( $_POST['companyName'] ) ) {
			$message = sanitize_text_field( wp_unslash( $_POST['companyName'] ) );
		}

		if ( 'contact' === $form_type ) {
			if ( '' === $first || '' === $last || ! is_email( $email ) || '' === $topic || '' === $message ) {
				wp_send_json_error(
					array( 'message' => $is_ru ? 'Заполните все обязательные поля.' : __( 'Please fill in all required fields.', 'impact-accs-chrome' ) ),
					400
				);
			}

			$admin   = get_option( 'admin_email' );
			$subject = sprintf(
				/* translators: 1: site name, 2: contact topic */
				__( '[%1$s] impact.accs contact: %2$s', 'impact-accs-chrome' ),
				wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
				$topic
			);
			$body    = "First name: {$first}\nLast name: {$last}\nEmail: {$email}\nSubject: {$topic}\nMessage: {$message}\n";

			wp_mail( $admin, $subject, $body );

			wp_send_json_success(
				array(
					'message' => $is_ru ? 'Сообщение отправлено. Владелец impact. скоро ответит.' : __( "Message sent. We'll get back to you shortly.", 'impact-accs-chrome' ),
				)
			);
		}

		if ( '' === $first || '' === $last || ! is_email( $email ) ) {
			wp_send_json_error(
				array( 'message' => $is_ru ? 'Заполните все обязательные поля.' : __( 'Please fill in all required fields.', 'impact-accs-chrome' ) ),
				400
			);
		}

		$admin   = get_option( 'admin_email' );
		$subject = sprintf(
			/* translators: %s: site name */
			__( '[%s] impact.accs access request', 'impact-accs-chrome' ),
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
		);
		$body    = "First name: {$first}\nLast name: {$last}\nEmail: {$email}\nMessage: {$message}\n";

		wp_mail( $admin, $subject, $body );

		wp_send_json_success(
			array(
				'message' => $is_ru ? 'Заявка отправлена. Владелец impact. скоро ответит.' : __( "You're in. We'll be in touch when it's your turn.", 'impact-accs-chrome' ),
			)
		);
	}

	/**
	 * Load template and rewrite asset/internal URLs.
	 *
	 * @param string $file Template filename.
	 * @return string
	 */
	public function render_template( $file ) {
		$path = IAC_I18n::template_path( $file );
		if ( ! is_file( $path ) ) {
			return '';
		}
		$html = (string) file_get_contents( $path );
		$html = $this->rewrite_html( $html );
		return IAC_I18n::localize_html( $html );
	}

	/**
	 * Rewrite original absolute paths to WordPress/plugin URLs.
	 *
	 * @param string $html Raw template HTML.
	 * @return string
	 */
	private function rewrite_html( $html ) {
		$home = home_url( '/' );
		$site = trailingslashit( IAC_URL . 'assets/site' );

		$map = array(
			'/about#careers'                     => class_exists( 'IAC_Contact_Page' ) ? IAC_Contact_Page::url() : $home . 'contact/',
			'/contact'                           => class_exists( 'IAC_Contact_Page' ) ? IAC_Contact_Page::url() : $home . 'contact/',
			'/blog/manifesto'                    => class_exists( 'IAC_Blog_Page' ) ? IAC_Blog_Page::post_url( 'manifesto' ) : $this->page_url( 'blog/manifesto', $home . 'blog/manifesto/' ),
			'/blog/markets'                      => class_exists( 'IAC_Blog_Page' ) ? IAC_Blog_Page::post_url( 'markets' ) : $this->page_url( 'blog/markets', $home . 'blog/markets/' ),
			'/accounts/platform-access'          => class_exists( 'IAC_Feature_Page' ) ? IAC_Feature_Page::url( 'platform-access' ) : $this->page_url( 'accounts/platform-access', $home . 'accounts/platform-access/' ),
			'/accounts/agency-accounts'          => class_exists( 'IAC_Feature_Page' ) ? IAC_Feature_Page::url( 'agency-accounts' ) : $this->page_url( 'accounts/agency-accounts', $home . 'accounts/agency-accounts/' ),
			'/accounts/team-supply'              => class_exists( 'IAC_Feature_Page' ) ? IAC_Feature_Page::url( 'team-supply' ) : $this->page_url( 'accounts/team-supply', $home . 'accounts/team-supply/' ),
			'/features/conversational-debugging' => class_exists( 'IAC_Feature_Page' ) ? IAC_Feature_Page::url( 'platform-access' ) : $this->page_url( 'accounts/platform-access', $home . 'accounts/platform-access/' ),
			'/features/autonomous-alerts'        => class_exists( 'IAC_Feature_Page' ) ? IAC_Feature_Page::url( 'agency-accounts' ) : $this->page_url( 'accounts/agency-accounts', $home . 'accounts/agency-accounts/' ),
			'/features/coding-agents'            => class_exists( 'IAC_Feature_Page' ) ? IAC_Feature_Page::url( 'team-supply' ) : $this->page_url( 'accounts/team-supply', $home . 'accounts/team-supply/' ),
			'/features/coding-agents-welcome'    => class_exists( 'IAC_Feature_Page' ) ? IAC_Feature_Page::url( 'team-supply' ) : $this->page_url( 'accounts/team-supply', $home . 'accounts/team-supply/' ),
			'/legal/data-processing-addendum'    => esc_url( IAC_URL . 'assets/legal/data-processing-addendum.pdf' ),
			'/legal/privacy-policy'              => esc_url( IAC_URL . 'assets/legal/privacy-policy.pdf' ),
			'/legal/terms-of-service'            => esc_url( IAC_URL . 'assets/legal/terms-of-service.pdf' ),
			'?waitlist=true'                     => class_exists( 'IAC_Application_Page' ) ? IAC_Application_Page::url() : home_url( '/application/' ),
			'/application'                       => class_exists( 'IAC_Application_Page' ) ? IAC_Application_Page::url() : home_url( '/application/' ),
			'?contact=true'                      => class_exists( 'IAC_Contact_Page' ) ? IAC_Contact_Page::url() : $home . 'contact/',
			'https://t.me/impactaccs'             => 'https://t.me/founderads',
			'https://wa.me/'                      => 'https://t.me/founderads',
			'/about'                             => class_exists( 'IAC_About_Page' ) ? IAC_About_Page::url() : $this->page_url( 'about', $home . 'about/' ),
			'/blog'                              => class_exists( 'IAC_Blog_Page' ) ? IAC_Blog_Page::url() : $this->page_url( 'blog', $home . 'blog/' ),
			'/'                                  => $home,
		);

		$html = preg_replace(
			'#(src|href)="/assets/(?!site/)([^"]+)"#',
			'$1="' . esc_url( IAC_URL . 'assets/site/assets/' ) . '$2"',
			$html
		);

		$html = preg_replace_callback(
			'#src="/_next/[^"?]+\?(?:amp;)?url=([^"&]+)(?:[^"]*)"#',
			function ( $m ) use ( $site ) {
				$decoded = urldecode( $m[1] );
				if ( 0 === strpos( $decoded, '/assets/' ) ) {
					return 'src="' . esc_url( $site . 'assets/' . ltrim( substr( $decoded, 8 ), '/' ) ) . '"';
				}
				if ( 0 === strpos( $decoded, '/_next/static/media/' ) ) {
					return 'src="' . esc_url( $site . ltrim( $decoded, '/' ) ) . '"';
				}
				return $m[0];
			},
			$html
		);

		$html = preg_replace(
			'#src="/_next/[^"?]+\?(?:amp;)?url=%2Fassets%2Fteam%2F([^"&]+)\.(webp|png|jpg)(?:[^"]*)"#',
			'src="' . esc_url( IAC_URL . 'assets/site/assets/team/' ) . '$1.$2"',
			$html
		);

		$html = preg_replace( '#srcSet="[^"]*"#', '', $html );

		$html = preg_replace_callback(
			'/(src|href)="(\/_next\/image\?url=)([^"&]+)([^"]*)"/',
			function ( $m ) use ( $site ) {
				$decoded = urldecode( $m[3] );
				if ( 0 === strpos( $decoded, '/_next/static/media/' ) ) {
					return $m[1] . '="' . esc_url( $site . ltrim( $decoded, '/' ) ) . '"';
				}
				return $m[0];
			},
			$html
		);

		$html = preg_replace(
			'#(src|href)="/assets/site/([^"]+)"#',
			'$1="' . esc_url( IAC_URL . 'assets/site/' ) . '$2"',
			$html
		);

		$html = str_replace( '%2F', '/', $html );

		$application_url = class_exists( 'IAC_Application_Page' ) ? IAC_Application_Page::url() : home_url( '/application/' );
		$html              = preg_replace(
			'#href="[^"]*(?:conversational-debugging|autonomous-alerts|coding-agents)[^"]*\.html[^"]*"#',
			'href="' . esc_url( $application_url ) . '"',
			$html
		);

		$html = preg_replace(
			'#(src|href)="assets/blog/([^"]+)"#',
			'$1="' . esc_url( IAC_URL . 'assets/site/assets/blog/' ) . '$2"',
			$html
		);

		$html = preg_replace(
			'#src="_next/onsen[^"]*"#',
			'src="' . esc_url( IAC_URL . 'assets/site/assets/onsen.png' ) . '"',
			$html
		);

		$html = preg_replace(
			'#(src|href)="/(_next/[^"]+)"#',
			'$1="' . esc_url( $site ) . '$2"',
			$html
		);

		foreach ( $map as $from => $to ) {
			$html = str_replace( 'href="' . $from . '"', 'href="' . esc_url( $to ) . '"', $html );
		}

		$html = str_replace( 'id="waitlist-error"', 'id="iac-waitlist-error"', $html );
		$html = str_replace( '<form noValidate="">', '<form class="iac-access-form" noValidate="">', $html );
		$html = str_replace(
			'<h3 class="text-title text-glow text-h1 text-center">REQUEST ACCESS</h3>',
			'<h3 id="iac-request-access" class="text-title text-glow text-h1 text-center">REQUEST ACCESS</h3>',
			$html
		);

		$html = str_replace(
			'class="relative h-fit w-max max-w-full"',
			'class="iac-footer-col relative h-fit w-max max-w-full"',
			$html
		);

		$html = str_replace(
			'relative flex flex-col pt-6 pb-5 pl-6',
			'iac-footer-links relative flex flex-col pt-6 pb-5 pl-6',
			$html
		);

		$html = str_replace(
			'grid grid-cols-2 gap-6 min-[900px]:flex',
			'iac-footer-menu-grid grid grid-cols-2 gap-6 min-[900px]:flex',
			$html
		);

		$html = str_replace(
			'class="px-sides absolute bottom-0 left-0 z-10 mt-auto w-full mix-blend-overlay md:px-12"',
			'class="iac-footer-logo-wrap px-sides absolute bottom-0 left-0 z-10 mt-auto w-full mix-blend-overlay md:px-12"',
			$html
		);

		$html = str_replace(
			'class="px-sides relative z-20 flex flex-col gap-8 pb-[max(30vw,40px)] sm:pb-[40vw]',
			'class="iac-footer-menu-row px-sides relative z-20 flex flex-col gap-8 pb-16 sm:pb-20',
			$html
		);

		$html = str_replace(
			'class="px-sides relative bottom-0 left-0 z-20 flex w-full justify-center gap-4 pt-6 pb-2 md:absolute md:justify-between md:px-12 md:pt-10 xl:pb-[max(calc(--spacing(10)*var(--scale)),8px)] 2xl:pb-[calc(--spacing(6)*var(--scale))]"',
			'class="iac-footer-bottom-bar px-sides relative z-20 flex w-full justify-center gap-4 pt-4 pb-3 md:justify-between md:px-12"',
			$html
		);

		$html = str_replace( '&amp;', '&', $html );

		return $html;
	}

	/**
	 * Resolve WP page URL.
	 *
	 * @param string $slug Page slug.
	 * @param string $fallback Fallback URL.
	 * @return string
	 */
	private function page_url( $slug, $fallback ) {
		$page = get_page_by_path( $slug );
		return $page ? get_permalink( $page ) : $fallback;
	}
}
