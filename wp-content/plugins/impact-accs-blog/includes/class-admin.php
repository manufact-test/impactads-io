<?php
/**
 * Admin meta boxes and columns.
 *
 * @package ImpactAccsBlog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin UI for blog posts.
 */
class IAB_Admin {

	const META_FEATURED = '_iab_featured';
	const META_BADGE    = '_iab_badge';

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_' . IAB_CPT::POST_TYPE, array( __CLASS__, 'save_meta' ), 10, 2 );
		add_filter( 'manage_' . IAB_CPT::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . IAB_CPT::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_action( 'admin_notices', array( __CLASS__, 'publish_notice' ) );
	}

	/**
	 * Meta boxes.
	 */
	public static function add_meta_boxes() {
		add_meta_box(
			'iab-post-settings',
			'Настройки блога',
			array( __CLASS__, 'render_settings_box' ),
			IAB_CPT::POST_TYPE,
			'side',
			'high'
		);

		add_meta_box(
			'iab-post-help',
			'Как это работает',
			array( __CLASS__, 'render_help_box' ),
			IAB_CPT::POST_TYPE,
			'side',
			'low'
		);
	}

	/**
	 * Settings meta box.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render_settings_box( $post ) {
		wp_nonce_field( 'iab_save_post_meta', 'iab_post_meta_nonce' );

		$featured = (bool) get_post_meta( $post->ID, self::META_FEATURED, true );
		$badge    = (string) get_post_meta( $post->ID, self::META_BADGE, true );
		if ( '' === $badge ) {
			$badge = 'Blogpost';
		}

		$url = IAB_Sync::post_public_url( $post );
		?>
		<p>
			<label>
				<input type="checkbox" name="iab_featured" value="1" <?php checked( $featured ); ?> />
				Показать в hero-блоке на /blog/
			</label>
		</p>
		<p>
			<label for="iab_badge"><strong>Бейдж</strong></label><br />
			<input type="text" class="widefat" id="iab_badge" name="iab_badge" value="<?php echo esc_attr( $badge ); ?>" placeholder="Blogpost" />
		</p>
		<?php if ( 'publish' === $post->post_status && $url ) : ?>
			<p>
				<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">Открыть на сайте</a>
			</p>
		<?php endif; ?>
		<?php if ( IAB_Sync::is_reserved_slug( $post->post_name ) ) : ?>
			<p class="description" style="color:#b32d2e;">
				Слаг «<?php echo esc_html( $post->post_name ); ?>» занят системным постом. Измените URL (slug) перед публикацией.
			</p>
		<?php else : ?>
		<p class="description">
			Обложка = featured image. Краткое описание = поле «Отрывок» на карточках /blog/.
		</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Help meta box.
	 */
	public static function render_help_box() {
		echo '<p>Заполните заголовок, текст, отрывок и обложку. Нажмите «Опубликовать» — пост появится на /blog/ автоматически.</p>';
	}

	/**
	 * Save meta.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public static function save_meta( $post_id, $post ) {
		unset( $post );

		if ( ! isset( $_POST['iab_post_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['iab_post_meta_nonce'] ) ), 'iab_save_post_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$featured = isset( $_POST['iab_featured'] ) ? '1' : '';
		update_post_meta( $post_id, self::META_FEATURED, $featured );

		$badge = isset( $_POST['iab_badge'] ) ? sanitize_text_field( wp_unslash( $_POST['iab_badge'] ) ) : 'Blogpost';
		if ( '' === $badge ) {
			$badge = 'Blogpost';
		}
		update_post_meta( $post_id, self::META_BADGE, $badge );
	}

	/**
	 * @param string[] $columns Columns.
	 * @return string[]
	 */
	public static function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['iab_featured'] = 'Hero';
			}
		}
		return $new;
	}

	/**
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public static function column_content( $column, $post_id ) {
		if ( 'iab_featured' !== $column ) {
			return;
		}
		echo get_post_meta( $post_id, self::META_FEATURED, true ) ? '★' : '—';
	}

	/**
	 * Notice after first publish sync.
	 */
	public static function publish_notice() {
		if ( ! current_user_can( 'edit_posts' ) || ! get_transient( 'iab_published_notice' ) ) {
			return;
		}

		delete_transient( 'iab_published_notice' );

		$url = class_exists( 'IAC_Blog_Page' ) ? IAC_Blog_Page::url() : home_url( '/blog/' );
		echo '<div class="notice notice-success is-dismissible"><p>';
		echo 'Пост опубликован на сайте.';
		echo ' <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">Открыть /blog/</a>';
		echo '</p></div>';
	}

	/**
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_featured( $post_id ) {
		return (bool) get_post_meta( $post_id, self::META_FEATURED, true );
	}

	/**
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function badge( $post_id ) {
		$badge = (string) get_post_meta( $post_id, self::META_BADGE, true );
		return '' !== $badge ? $badge : 'Blogpost';
	}
}
