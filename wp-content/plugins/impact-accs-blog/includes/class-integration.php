<?php
/**
 * Hooks into impact-accs-chrome blog rendering.
 *
 * @package ImpactAccsBlog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Chrome integration.
 */
class IAB_Integration {

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_filter( 'iac_blog_resolve_post_slug', array( __CLASS__, 'resolve_slug' ), 10, 2 );
		add_filter( 'iac_blog_render_dynamic_post', array( __CLASS__, 'render_dynamic_post' ), 10, 2 );
		add_filter( 'iac_blog_page_html', array( __CLASS__, 'inject_blog_index' ), 10, 1 );
		add_filter( 'iac_blog_post_html', array( __CLASS__, 'normalize_blog_post_html' ), 20, 2 );
	}

	/**
	 * @param string  $slug Empty default.
	 * @param WP_Post $page Current page.
	 * @return string
	 */
	public static function resolve_slug( $slug, $page ) {
		unset( $slug );
		return IAB_Sync::slug_from_page( $page );
	}

	/**
	 * Replace the retired brand only in visible HTML text nodes.
	 *
	 * Attributes such as href/src/mailto are deliberately left untouched.
	 *
	 * @param string $html Rendered blog HTML.
	 * @return string
	 */
	private static function replace_legacy_brand_text( $html ) {
		$parts = preg_split( '#(<[^>]+>)#s', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
		if ( ! is_array( $parts ) ) {
			return $html;
		}

		foreach ( $parts as $index => $part ) {
			if ( '' === $part || '<' === $part[0] ) {
				continue;
			}
			$parts[ $index ] = str_ireplace( 'impact.accs', 'impact.', $part );
		}

		return implode( '', $parts );
	}

	/**
	 * Finalize blog markup after dynamic content has been injected.
	 *
	 * Dynamic blog cards are added after the chrome template's first i18n pass,
	 * so run the native localization once more and only then normalize the
	 * retired public brand name. This stays entirely server-side and does not
	 * mutate the browser DOM.
	 *
	 * @param string $html Rendered blog HTML.
	 * @return string
	 */
	private static function finalize_blog_html( $html ) {
		if ( ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		if ( class_exists( 'IAC_I18n' ) && method_exists( 'IAC_I18n', 'localize_html' ) ) {
			$html = IAC_I18n::localize_html( $html );
		}

		return self::replace_legacy_brand_text( $html );
	}

	/**
	 * Final cleanup for every rendered blog article, including reserved static
	 * posts such as /blog/markets/ and /blog/manifesto/.
	 *
	 * @param string $html Rendered post HTML.
	 * @param string $slug Post slug.
	 * @return string
	 */
	public static function normalize_blog_post_html( $html, $slug ) {
		unset( $slug );

		if ( ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		return self::replace_legacy_brand_text( $html );
	}

	/**
	 * @param string $html Empty default.
	 * @param string $slug Post slug.
	 * @return string
	 */
	public static function render_dynamic_post( $html, $slug ) {
		unset( $html );

		$post = IAB_CPT::get_by_slug( $slug );
		if ( ! $post ) {
			return '';
		}

		return self::finalize_blog_html( IAB_Render::single( $post ) );
	}

	/**
	 * @param string $html Blog index HTML.
	 * @return string
	 */
	public static function inject_blog_index( $html ) {
		return self::finalize_blog_html( IAB_Render::inject_index( $html ) );
	}
}
