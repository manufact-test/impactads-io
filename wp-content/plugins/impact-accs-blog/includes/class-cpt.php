<?php
/**
 * Custom post type for admin-managed blog posts.
 *
 * @package ImpactAccsBlog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blog post CPT.
 */
class IAB_CPT {

	const POST_TYPE = 'iac_blog_post';

	/**
	 * Register post type.
	 */
	public static function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'               => 'Посты блога',
					'singular_name'      => 'Пост блога',
					'add_new'            => 'Добавить пост',
					'add_new_item'       => 'Новый пост блога',
					'edit_item'          => 'Редактировать пост',
					'new_item'           => 'Новый пост',
					'view_item'          => 'Открыть пост',
					'search_items'       => 'Искать посты',
					'not_found'          => 'Постов нет',
					'not_found_in_trash' => 'В корзине пусто',
					'all_items'          => 'Все посты',
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_icon'           => 'dashicons-welcome-write-blog',
				'menu_position'       => 26,
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'exclude_from_search' => true,
			)
		);
	}

	/**
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_blog_post( $post_id ) {
		return self::POST_TYPE === get_post_type( $post_id );
	}

	/**
	 * Published posts newest first.
	 *
	 * @return WP_Post[]
	 */
	public static function get_published_posts() {
		$posts = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		return is_array( $posts ) ? $posts : array();
	}

	/**
	 * @param string $slug Post slug.
	 * @return WP_Post|null
	 */
	public static function get_by_slug( $slug ) {
		if ( '' === $slug ) {
			return null;
		}

		$posts = get_posts(
			array(
				'name'           => $slug,
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
			)
		);

		return ! empty( $posts ) ? $posts[0] : null;
	}
}
