<?php
/**
 * Sync published posts to /blog/{slug}/ WordPress pages.
 *
 * @package ImpactAccsBlog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page sync for blog routes.
 */
class IAB_Sync {

	const META_PAGE_ID = '_iab_wp_page_id';

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_action( 'save_post_' . IAB_CPT::POST_TYPE, array( __CLASS__, 'sync_on_save' ), 20, 3 );
		add_action( 'before_delete_post', array( __CLASS__, 'delete_synced_page' ) );
		add_action( 'trashed_post', array( __CLASS__, 'trash_synced_page' ) );
	}

	/**
	 * Slugs used by static chrome blog posts.
	 *
	 * @return string[]
	 */
	public static function reserved_slugs() {
		return array( 'manifesto', 'markets' );
	}

	/**
	 * @param string $slug Post slug.
	 * @return bool
	 */
	public static function is_reserved_slug( $slug ) {
		return in_array( $slug, self::reserved_slugs(), true );
	}

	/**
	 * @param int $post_id Blog post ID.
	 * @return string
	 */
	public static function marker( $post_id ) {
		return '<!-- iab-blog-' . (int) $post_id . ' -->';
	}

	/**
	 * @return int Blog index page ID.
	 */
	public static function blog_parent_id() {
		if ( class_exists( 'IAC_Blog_Page' ) ) {
			return (int) IAC_Blog_Page::ensure_pages();
		}

		$page = get_page_by_path( 'blog' );
		return $page instanceof WP_Post ? (int) $page->ID : 0;
	}

	/**
	 * Public URL for a blog post.
	 *
	 * @param int|WP_Post $post Post.
	 * @return string
	 */
	public static function post_public_url( $post ) {
		$post = get_post( $post );
		if ( ! $post || 'publish' !== $post->post_status ) {
			return '';
		}

		$page_id = (int) get_post_meta( $post->ID, self::META_PAGE_ID, true );
		if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
			$link = get_permalink( $page_id );
			if ( is_string( $link ) && '' !== $link ) {
				return $link;
			}
		}

		if ( class_exists( 'IAC_Blog_Page' ) ) {
			return IAC_Blog_Page::post_url( $post->post_name );
		}

		return home_url( '/blog/' . $post->post_name . '/' );
	}

	/**
	 * Resolve slug from synced WP page.
	 *
	 * @param WP_Post $page Page object.
	 * @return string
	 */
	public static function slug_from_page( $page ) {
		if ( ! $page instanceof WP_Post || 'page' !== $page->post_type ) {
			return '';
		}

		if ( ! preg_match( '/<!--\s*iab-blog-(\d+)\s*-->/', $page->post_content, $matches ) ) {
			return '';
		}

		$post = get_post( (int) $matches[1] );
		if ( ! $post || IAB_CPT::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			return '';
		}

		$blog_id = self::blog_parent_id();
		if ( $blog_id && (int) $page->post_parent !== $blog_id ) {
			return '';
		}

		return $post->post_name;
	}

	/**
	 * Sync child page on publish/update.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Is update.
	 */
	public static function sync_on_save( $post_id, $post, $update ) {
		unset( $update );

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		$parent_id = self::blog_parent_id();
		if ( ! $parent_id ) {
			return;
		}

		if ( 'publish' === $post->post_status ) {
			if ( self::is_reserved_slug( $post->post_name ) ) {
				return;
			}
			self::upsert_page( $post, $parent_id );
			return;
		}

		self::set_page_status( $post_id, 'draft' );
	}

	/**
	 * Create or update synced page.
	 *
	 * @param WP_Post $post      Blog post.
	 * @param int     $parent_id Blog index page ID.
	 */
	private static function upsert_page( $post, $parent_id ) {
		$page_id = (int) get_post_meta( $post->ID, self::META_PAGE_ID, true );
		$data    = array(
			'post_title'   => $post->post_title,
			'post_name'    => $post->post_name,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_parent'  => $parent_id,
			'post_content' => self::marker( $post->ID ),
		);

		if ( $page_id && get_post( $page_id ) ) {
			$data['ID'] = $page_id;
			wp_update_post( $data );
		} else {
			$page_id = wp_insert_post( $data, true );
			if ( ! is_wp_error( $page_id ) ) {
				update_post_meta( $post->ID, self::META_PAGE_ID, (int) $page_id );
			}
		}

		if ( is_admin() ) {
			set_transient( 'iab_published_notice', 1, 30 );
		}
	}

	/**
	 * @param int    $post_id Blog post ID.
	 * @param string $status  Page status.
	 */
	private static function set_page_status( $post_id, $status ) {
		$page_id = (int) get_post_meta( $post_id, self::META_PAGE_ID, true );
		if ( ! $page_id || ! get_post( $page_id ) ) {
			return;
		}

		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => $status,
			)
		);
	}

	/**
	 * @param int $post_id Post ID.
	 */
	public static function delete_synced_page( $post_id ) {
		if ( ! IAB_CPT::is_blog_post( $post_id ) ) {
			return;
		}

		$page_id = (int) get_post_meta( $post_id, self::META_PAGE_ID, true );
		if ( $page_id ) {
			wp_delete_post( $page_id, true );
		}
	}

	/**
	 * @param int $post_id Post ID.
	 */
	public static function trash_synced_page( $post_id ) {
		if ( ! IAB_CPT::is_blog_post( $post_id ) ) {
			return;
		}
		self::set_page_status( $post_id, 'trash' );
	}
}
