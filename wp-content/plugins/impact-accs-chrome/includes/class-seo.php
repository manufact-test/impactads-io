<?php
/**
 * SEO / meta tags for impact.accs chrome pages.
 *
 * @package ImpactAccsChrome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Document title + description + Open Graph + Twitter cards.
 */
class IAC_SEO {

	/**
	 * Default meta description.
	 */
	const DEFAULT_DESCRIPTION = 'Closed access infrastructure for media buying teams. Platform access, agency accounts, team supply, clear terms, and direct handoff.';

	/**
	 * Default document title.
	 */
	const DEFAULT_TITLE = 'impact.accs | Closed Access Infrastructure';

	/**
	 * Singleton.
	 *
	 * @var IAC_SEO|null
	 */
	private static $instance = null;

	/**
	 * Instance getter.
	 *
	 * @return IAC_SEO
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
		add_filter( 'pre_get_document_title', array( $this, 'filter_document_title' ), 20 );
		add_action( 'wp_head', array( $this, 'render_meta_tags' ), 1 );
	}

	/**
	 * Should output chrome SEO overrides?
	 *
	 * @return bool
	 */
	public function should_render() {
		if ( ! class_exists( 'IAC_Chrome' ) ) {
			return false;
		}
		return IAC_Chrome::instance()->should_render();
	}

	/**
	 * Resolve SEO config for the current request.
	 *
	 * @return array{title:string,description:string}|null
	 */
	public function get_page_seo() {
		if ( class_exists( 'IAC_Not_Found_Page' ) && IAC_Not_Found_Page::is_not_found() ) {
			return array(
				'title'       => 'Access Route Not Found | impact.accs',
				'description' => self::DEFAULT_DESCRIPTION,
			);
		}

		if ( class_exists( 'IAC_Feature_Page' ) && IAC_Feature_Page::is_feature_page() ) {
			$slug = IAC_Feature_Page::get_slug();
			if ( isset( IAC_Feature_Page::FEATURES[ $slug ]['title'] ) ) {
				return array(
					'title'       => IAC_Feature_Page::FEATURES[ $slug ]['title'] . ' | impact.accs',
					'description' => self::DEFAULT_DESCRIPTION,
				);
			}
		}

		if ( class_exists( 'IAC_About_Page' ) && IAC_About_Page::is_about_page() ) {
			return array(
				'title'       => 'About | impact.accs',
				'description' => self::DEFAULT_DESCRIPTION,
			);
		}

		if ( class_exists( 'IAC_Blog_Page' ) && IAC_Blog_Page::is_blog_index() ) {
			return array(
				'title'       => 'Blog | impact.accs',
				'description' => self::DEFAULT_DESCRIPTION,
			);
		}

		if ( class_exists( 'IAC_Blog_Page' ) && IAC_Blog_Page::is_blog_post() ) {
			$post = get_queried_object();
			$name = ( $post instanceof WP_Post ) ? get_the_title( $post ) : 'Blog';
			return array(
				'title'       => wp_strip_all_tags( $name ) . ' | impact.accs',
				'description' => self::DEFAULT_DESCRIPTION,
			);
		}

		if ( class_exists( 'IAC_Contact_Page' ) && IAC_Contact_Page::is_contact_page() ) {
			return array(
				'title'       => 'Contact | impact.accs',
				'description' => self::DEFAULT_DESCRIPTION,
			);
		}

		if ( class_exists( 'IAC_Application_Page' ) && IAC_Application_Page::is_application_page() ) {
			return array(
				'title'       => 'Request Access | impact.accs',
				'description' => self::DEFAULT_DESCRIPTION,
			);
		}

		if ( is_front_page() ) {
			return array(
				'title'       => self::DEFAULT_TITLE,
				'description' => self::DEFAULT_DESCRIPTION,
			);
		}

		return null;
	}

	/**
	 * Filter document title.
	 *
	 * @param string $title Current title.
	 * @return string
	 */
	public function filter_document_title( $title ) {
		if ( ! $this->should_render() ) {
			return $title;
		}

		$seo = $this->get_page_seo();
		if ( $seo ) {
			return $seo['title'];
		}

		return $title;
	}

	/**
	 * Output meta description + OG + Twitter tags.
	 */
	public function render_meta_tags() {
		if ( ! $this->should_render() ) {
			return;
		}

		$seo = $this->get_page_seo();
		if ( ! $seo ) {
			return;
		}

		$title       = $seo['title'];
		$description = $seo['description'];
		$url         = $this->current_url();
		$image       = $this->og_image_url();

		echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<meta property="og:type" content="website" />' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $image ) {
			echo '<meta property="og:image" content="' . esc_url( $image ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $image ) {
			echo '<meta name="twitter:image" content="' . esc_url( $image ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Canonical current URL.
	 *
	 * @return string
	 */
	private function current_url() {
		if ( is_singular() ) {
			return get_permalink();
		}
		if ( is_404() ) {
			return home_url( add_query_arg( array() ) );
		}
		return home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	/**
	 * OG image — reuse homepage asset when available.
	 *
	 * @return string
	 */
	private function og_image_url() {
		if ( defined( 'IAH_FAVICON_URL' ) ) {
			return IAH_FAVICON_URL;
		}
		return IAC_URL . 'assets/site/assets/favicon.png';
	}

	/**
	 * Patch mirrored homepage HTML meta + embedded RSC metadata.
	 *
	 * @param string $html Homepage document HTML.
	 * @return string
	 */
	public static function patch_homepage_html( $html ) {
		$title = self::DEFAULT_TITLE;
		$desc  = self::DEFAULT_DESCRIPTION;
		$home  = esc_url( home_url( '/' ) );

		$patterns = array(
			'#<title>[^<]*</title>#i'                                    => '<title>' . esc_html( $title ) . '</title>',
			'#<meta name="description" content="[^"]*"[^>]*/?>#i'        => '<meta name="description" content="' . esc_attr( $desc ) . '"/>',
			'#<meta property="og:title" content="[^"]*"[^>]*/?>#i'        => '<meta property="og:title" content="' . esc_attr( $title ) . '"/>',
			'#<meta property="og:description" content="[^"]*"[^>]*/?>#i' => '<meta property="og:description" content="' . esc_attr( $desc ) . '"/>',
			'#<meta name="twitter:title" content="[^"]*"[^>]*/?>#i'      => '<meta name="twitter:title" content="' . esc_attr( $title ) . '"/>',
			'#<meta name="twitter:description" content="[^"]*"[^>]*/?>#i'=> '<meta name="twitter:description" content="' . esc_attr( $desc ) . '"/>',
			'#<link rel="canonical" href="[^"]*"[^>]*/?>#i'              => '<link rel="canonical" href="' . $home . '"/>',
		);

		foreach ( $patterns as $pattern => $replacement ) {
			$html = preg_replace( $pattern, $replacement, $html, 1 );
		}

		$legacy_titles = array(
			'Impact | AI-Native Observability',
			'Sazabi | AI-Native Observability',
		);
		$html = str_replace( $legacy_titles, $title, $html );

		$desc_json = str_replace( '"', '\\"', $desc );
		$html      = preg_replace(
			'/Autonomous alerts\. Conversational debugging\. Coding agent integrations\.[^"]+Built for teams who ship fast\./',
			$desc_json,
			$html
		);

		$html = preg_replace(
			'/Agency accounts\. Media buying access\. Team supply\. Closed access infrastructure for teams who launch fast\./',
			$desc_json,
			$html
		);

		$html = str_replace(
			array(
				'The AI-native observability platform for fast-moving engineering teams. Ship with confidence.',
				'The AI-Native Observability platform for fast-moving engineering teams. Ship with confidence.',
			),
			$desc,
			$html
		);

		return $html;
	}
}
