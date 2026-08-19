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

		return IAB_Render::single( $post );
	}

	/**
	 * @param string $html Blog index HTML.
	 * @return string
	 */
	public static function inject_blog_index( $html ) {
		return IAB_Render::inject_index( $html );
	}
}
