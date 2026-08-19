<?php
/**
 * WordPress admin UI.
 *
 * @package ImpactAccsContentEditor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once IACCE_DIR . 'includes/class-labels.php';
require_once IACCE_DIR . 'includes/class-blocks.php';

/**
 * Admin screens.
 */
class IACCE_Admin {

	const PER_PAGE = 40;

	/** @var IACCE_Admin|null */
	private static $instance = null;

	/**
	 * @return IACCE_Admin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_iacce_save', array( $this, 'handle_save' ) );
		add_action( 'admin_post_iacce_rescan', array( $this, 'handle_rescan' ) );
		add_action( 'admin_post_iacce_backup', array( $this, 'handle_backup' ) );
		add_action( 'admin_post_iacce_restore', array( $this, 'handle_restore' ) );
		add_action( 'admin_post_iacce_reset_item', array( $this, 'handle_reset_item' ) );
	}

	public function register_menu() {
		add_menu_page(
			'Тексты impact.accs',
			'Тексты сайта',
			'edit_pages',
			'iacce-editor',
			array( $this, 'render_page' ),
			'dashicons-edit-page',
			58
		);
	}

	/**
	 * @param string $hook Hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_iacce-editor' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'iacce-admin', IACCE_URL . 'assets/admin.css', array(), IACCE_VERSION );
		wp_enqueue_script( 'iacce-admin', IACCE_URL . 'assets/admin.js', array(), IACCE_VERSION, true );
	}

	public function render_page() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'impact-accs-content-editor' ) );
		}

		$tab       = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'texts'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$registry  = get_option( IACCE_OPTION_REGISTRY, array() );
		$overrides = get_option( IACCE_OPTION_OVERRIDES, array() );
		$links     = get_option( IACCE_OPTION_LINKS, array() );
		$all_texts = isset( $registry['texts'] ) && is_array( $registry['texts'] ) ? $registry['texts'] : array();
		$link_reg  = isset( $registry['links'] ) && is_array( $registry['links'] ) ? $registry['links'] : array();
		$q         = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$section   = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$block     = isset( $_GET['block'] ) ? sanitize_key( wp_unslash( $_GET['block'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$modified  = ! empty( $_GET['modified'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$paged     = max( 1, isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$all_texts = array_map( array( $this, 'ensure_block_meta' ), $all_texts );

		$notice    = isset( $_GET['iacce_notice'] ) ? sanitize_text_field( wp_unslash( $_GET['iacce_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$sections  = IACCE_Labels::dashboard_sections();
		$stats     = $this->build_section_stats( $all_texts, $overrides );
		$mod_count = $this->count_modified( $all_texts, $overrides );

		$show_dashboard    = 'texts' === $tab && '' === $section && '' === $q && ! $modified;
		$show_block_picker = 'texts' === $tab && '' !== $section && '' === $block && '' === $q && ! $modified;
		$block_stats       = '' !== $section ? $this->build_block_stats( $all_texts, $overrides, $section ) : array();

		$texts = $all_texts;
		if ( $modified ) {
			$texts = array_values(
				array_filter(
					$texts,
					static function ( $item ) use ( $overrides ) {
						$id = $item['id'] ?? '';
						return $id && ! empty( $overrides[ $id ] );
					}
				)
			);
		}
		$texts = $this->filter_texts( $texts, $q, $section, $block );
		$total = count( $texts );
		$pages = max( 1, (int) ceil( $total / self::PER_PAGE ) );
		$paged = min( $paged, $pages );
		$slice = ( $show_dashboard || $show_block_picker ) ? array() : array_slice( $texts, ( $paged - 1 ) * self::PER_PAGE, self::PER_PAGE );

		?>
		<div class="wrap iacce-wrap">
			<div class="iacce-topbar">
				<div>
					<h1>Тексты сайта</h1>
					<p class="iacce-intro">Выберите раздел → блок на странице → измените текст → <strong>Сохранить</strong> → на сайте <kbd>Ctrl</kbd>+<kbd>F5</kbd>.</p>
				</div>
				<a class="button iacce-open-site" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">↗ Открыть сайт</a>
			</div>

			<?php if ( $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
			<?php endif; ?>

			<nav class="nav-tab-wrapper iacce-tabs">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=iacce-editor&tab=texts' ) ); ?>" class="nav-tab <?php echo 'texts' === $tab ? 'nav-tab-active' : ''; ?>">Тексты</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=iacce-editor&tab=links' ) ); ?>" class="nav-tab <?php echo 'links' === $tab ? 'nav-tab-active' : ''; ?>">Ссылки и кнопки</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=iacce-editor&tab=backups' ) ); ?>" class="nav-tab <?php echo 'backups' === $tab ? 'nav-tab-active' : ''; ?>">Бекапы</a>
			</nav>

			<?php if ( 'backups' === $tab ) : ?>
				<?php $this->render_backups_tab(); ?>
			<?php elseif ( 'links' === $tab ) : ?>
				<?php $this->render_links_tab( $link_reg, $links ); ?>
			<?php elseif ( $show_dashboard ) : ?>
				<?php $this->render_steps( 1 ); ?>
				<?php $this->render_dashboard( $sections, $stats, $mod_count ); ?>
			<?php elseif ( $show_block_picker ) : ?>
				<?php $this->render_steps( 2 ); ?>
				<?php $this->render_block_picker( $sections, $section, $block_stats ); ?>
			<?php else : ?>
				<?php $this->render_steps( $modified || $q ? 0 : 3 ); ?>
				<?php $this->render_texts_tab( $slice, $overrides, $q, $section, $block, $sections, $paged, $pages, $total, $modified, $mod_count ); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * @param array<int,array<string,mixed>> $texts     All texts.
	 * @param array<string,mixed>            $overrides Overrides.
	 * @return array<string,array{total:int,modified:int}>
	 */
	private function build_section_stats( $texts, $overrides ) {
		$stats = array();
		foreach ( IACCE_Labels::dashboard_sections() as $slug => $meta ) {
			$stats[ $slug ] = array(
				'total'    => 0,
				'modified' => 0,
			);
		}
		foreach ( $texts as $item ) {
			$cat = $item['category'] ?? 'other';
			if ( ! isset( $stats[ $cat ] ) ) {
				$cat = 'other';
			}
			++$stats[ $cat ]['total'];
			$id = $item['id'] ?? '';
			if ( $id && ! empty( $overrides[ $id ] ) ) {
				++$stats[ $cat ]['modified'];
			}
		}
		return $stats;
	}

	/**
	 * @param array<int,array<string,mixed>> $texts     Texts.
	 * @param array<string,mixed>            $overrides Overrides.
	 * @return int
	 */
	private function count_modified( $texts, $overrides ) {
		$n = 0;
		foreach ( $texts as $item ) {
			$id = $item['id'] ?? '';
			if ( $id && ! empty( $overrides[ $id ] ) ) {
				++$n;
			}
		}
		return $n;
	}

	/**
	 * Effective on-site value for a language.
	 *
	 * @param array<string,mixed> $item      Registry row.
	 * @param array<string,mixed> $overrides All overrides.
	 * @param string              $lang      en|ru.
	 * @return string
	 */
	private function effective_text( $item, $overrides, $lang ) {
		$id = $item['id'] ?? '';
		if ( $id && isset( $overrides[ $id ][ $lang ] ) && '' !== (string) $overrides[ $id ][ $lang ] ) {
			return (string) $overrides[ $id ][ $lang ];
		}
		$base = (string) ( $item[ $lang ] ?? '' );
		if ( 'ru' === $lang && '' === $base && ! empty( $item['en'] ) ) {
			$base = IACCE_Scanner::lookup_ru( (string) $item['en'] );
		}
		return $base;
	}

	/**
	 * Original RU from registry, with i18n map fallback.
	 *
	 * @param array<string,mixed> $item Registry row.
	 * @return string
	 */
	private function original_ru( $item ) {
		$ru = (string) ( $item['ru'] ?? '' );
		if ( '' !== $ru ) {
			return $ru;
		}
		$en = (string) ( $item['en'] ?? '' );
		return '' !== $en ? IACCE_Scanner::lookup_ru( $en ) : '';
	}

	/**
	 * Ensure block metadata exists (works without rescan).
	 *
	 * @param array<string,mixed> $item Text row.
	 * @return array<string,mixed>
	 */
	public function ensure_block_meta( $item ) {
		$item = IACCE_Scanner::apply_nav_menu_meta( $item );
		$item = IACCE_Scanner::apply_contact_meta( $item );
		if ( ! empty( $item['nav_menu'] ) || ! empty( $item['contact_info'] ) ) {
			if ( ! empty( $item['nav_menu'] ) ) {
				$item['category'] = 'header';
				$item['section']  = 'Шапка и меню (везде)';
			}
			if ( ! empty( $item['contact_info'] ) ) {
				$item['category'] = 'contact';
				$item['section']  = 'Окно «Связаться»';
			}
		} elseif ( empty( $item['category'] ) ) {
			$meta             = IACCE_Labels::resolve( $item['group'] ?? '', $item['source'] ?? '' );
			$item['section']  = $meta['section'];
			$item['category'] = $meta['category'];
		}
		if ( ! empty( $item['block'] ) && ! empty( $item['block_title'] ) && empty( $item['nav_menu'] ) && empty( $item['contact_info'] ) ) {
			return $item;
		}
		return IACCE_Blocks::enrich( $item );
	}

	/**
	 * @param array<int,array<string,mixed>> $texts     All texts.
	 * @param array<string,mixed>            $overrides Overrides.
	 * @param string                         $section   Section slug.
	 * @return array<string,array<string,mixed>>
	 */
	private function build_block_stats( $texts, $overrides, $section ) {
		$stats = array();
		foreach ( $texts as $item ) {
			if ( ( $item['category'] ?? '' ) !== $section ) {
				continue;
			}
			$bid = $item['block'] ?? ( $section . '-misc' );
			if ( ! isset( $stats[ $bid ] ) ) {
				$stats[ $bid ] = array(
					'total'    => 0,
					'modified' => 0,
					'examples' => array(),
					'meta'     => array(
						'id'    => $bid,
						'title' => $item['block_title'] ?? $bid,
						'desc'  => $item['block_desc'] ?? '',
						'where' => $item['block_where'] ?? '',
						'order' => $item['block_order'] ?? 999,
						'icon'  => 'dashicons-editor-alignleft',
					),
				);
			}
			++$stats[ $bid ]['total'];
			$id = $item['id'] ?? '';
			if ( $id && ! empty( $overrides[ $id ] ) ) {
				++$stats[ $bid ]['modified'];
			}
			if ( count( $stats[ $bid ]['examples'] ) < 2 && ! empty( $item['en'] ) ) {
				$stats[ $bid ]['examples'][] = IACCE_Labels::preview( $item['en'], 48 );
			}
		}

		$catalog = IACCE_Blocks::catalog();
		foreach ( $stats as $bid => &$row ) {
			if ( isset( $catalog[ $bid ] ) ) {
				$row['meta'] = $catalog[ $bid ];
			}
		}
		unset( $row );

		uasort(
			$stats,
			static function ( $a, $b ) {
				return ( $a['meta']['order'] ?? 999 ) <=> ( $b['meta']['order'] ?? 999 );
			}
		);

		return $stats;
	}

	/**
	 * Full navigation tree: section → blocks.
	 *
	 * @param array<int,array<string,mixed>>                            $texts     Texts.
	 * @param array<string,mixed>                                       $overrides Overrides.
	 * @param array<string,array{label:string,desc:string,icon:string}> $sections  Sections.
	 * @return array<string,array<string,mixed>>
	 */
	private function build_nav_tree( $texts, $overrides, $sections ) {
		$tree = array();
		foreach ( $sections as $slug => $meta ) {
			$tree[ $slug ] = array(
				'meta'   => $meta,
				'blocks' => array(),
				'total'  => 0,
			);
		}

		$catalog = IACCE_Blocks::catalog();
		foreach ( $texts as $item ) {
			$cat = $item['category'] ?? 'other';
			if ( ! isset( $tree[ $cat ] ) ) {
				$cat = 'other';
			}
			$bid = $item['block'] ?? ( $cat . '-misc' );
			if ( ! isset( $tree[ $cat ]['blocks'][ $bid ] ) ) {
				$bmeta = $catalog[ $bid ] ?? array(
					'id'    => $bid,
					'title' => $item['block_title'] ?? $bid,
					'where' => $item['block_where'] ?? '',
					'order' => $item['block_order'] ?? 999,
					'icon'  => 'dashicons-editor-alignleft',
				);
				$tree[ $cat ]['blocks'][ $bid ] = array(
					'meta'     => $bmeta,
					'total'    => 0,
					'modified' => 0,
				);
			}
			++$tree[ $cat ]['total'];
			++$tree[ $cat ]['blocks'][ $bid ]['total'];
			$id = $item['id'] ?? '';
			if ( $id && ! empty( $overrides[ $id ] ) ) {
				++$tree[ $cat ]['blocks'][ $bid ]['modified'];
			}
		}

		foreach ( $tree as &$sec ) {
			uasort(
				$sec['blocks'],
				static function ( $a, $b ) {
					return ( $a['meta']['order'] ?? 999 ) <=> ( $b['meta']['order'] ?? 999 );
				}
			);
		}
		unset( $sec );

		return $tree;
	}

	/**
	 * Short label for sidebar.
	 *
	 * @param string $title Title.
	 * @return string
	 */
	private function sidebar_label( $title ) {
		$title = trim( (string) $title );
		if ( mb_strlen( $title ) <= 34 ) {
			return $title;
		}
		return mb_substr( $title, 0, 31 ) . '…';
	}

	/**
	 * Unified texts UI: sidebar + editor.
	 *
	 * @param array<string,array<string,mixed>>                         $nav_tree  Nav tree.
	 * @param array<int,array<string,mixed>>                            $texts     Texts slice.
	 * @param array<string,mixed>                                       $overrides Overrides.
	 * @param string                                                    $q         Search.
	 * @param string                                                    $section   Section.
	 * @param string                                                    $block     Block.
	 * @param array<string,array{label:string,desc:string,icon:string}> $sections  Sections.
	 * @param int                                                       $paged     Page.
	 * @param int                                                       $pages     Pages.
	 * @param int                                                       $total     Total.
	 * @param bool                                                      $modified  Modified filter.
	 * @param int                                                       $mod_count Modified count.
	 * @param bool                                                      $has_panel Has editor panel.
	 */
	private function render_texts_workspace( $nav_tree, $texts, $overrides, $q, $section, $block, $sections, $paged, $pages, $total, $modified, $mod_count, $has_panel ) {
		$base = admin_url( 'admin.php?page=iacce-editor&tab=texts' );
		?>
		<div class="iacce-layout">
			<aside class="iacce-sidebar" id="iacce-sidebar">
				<div class="iacce-sidebar__head">
					<strong>Где на сайте</strong>
					<button type="button" class="iacce-sidebar__close" id="iacce-sidebar-close" aria-label="Закрыть">×</button>
				</div>

				<form method="get" class="iacce-sidebar-search">
					<input type="hidden" name="page" value="iacce-editor" />
					<input type="hidden" name="tab" value="texts" />
					<input type="search" name="q" value="<?php echo esc_attr( $q ); ?>" placeholder="Поиск…" class="iacce-search" />
				</form>

				<nav class="iacce-nav">
					<?php foreach ( $nav_tree as $slug => $sec ) :
						if ( ( $sec['total'] ?? 0 ) < 1 ) {
							continue;
						}
						$is_open = ( $section === $slug ) || ( $block && isset( $sec['blocks'][ $block ] ) );
						if ( ! $section && ! $block && 'home' === $slug ) {
							$is_open = true;
						}
						?>
						<details class="iacce-nav__section" <?php echo $is_open ? 'open' : ''; ?>>
							<summary class="iacce-nav__section-title">
								<span class="dashicons <?php echo esc_attr( $sec['meta']['icon'] ?? 'dashicons-admin-page' ); ?>"></span>
								<?php echo esc_html( $sec['meta']['label'] ?? $slug ); ?>
								<span class="iacce-nav__count"><?php echo (int) $sec['total']; ?></span>
							</summary>
							<ul class="iacce-nav__blocks">
								<?php
								$num = 0;
								foreach ( $sec['blocks'] as $bid => $brow ) :
									if ( ( $brow['total'] ?? 0 ) < 1 ) {
										continue;
									}
									++$num;
									$active = ( $block === $bid );
									$url    = add_query_arg(
										array(
											'section' => $slug,
											'block'   => $bid,
										),
										$base
									);
									?>
									<li>
										<a class="iacce-nav__block <?php echo $active ? 'iacce-nav__block--active' : ''; ?>"
											href="<?php echo esc_url( $url ); ?>"
											data-block="<?php echo esc_attr( $bid ); ?>">
											<span class="iacce-nav__num"><?php echo (int) $num; ?></span>
											<span class="iacce-nav__block-label"><?php echo esc_html( $this->sidebar_label( $brow['meta']['title'] ?? $bid ) ); ?></span>
											<?php if ( ( $brow['modified'] ?? 0 ) > 0 ) : ?>
												<span class="iacce-nav__dot" title="Есть изменения"></span>
											<?php endif; ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</details>
					<?php endforeach; ?>
				</nav>

				<div class="iacce-sidebar__foot">
					<?php if ( $mod_count > 0 ) : ?>
						<a class="iacce-sidebar__link <?php echo $modified ? 'iacce-sidebar__link--active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'modified', '1', $base ) ); ?>">
							Изменённые (<?php echo (int) $mod_count; ?>)
						</a>
					<?php endif; ?>
				</div>
			</aside>

			<div class="iacce-main">
				<button type="button" class="button iacce-sidebar-toggle" id="iacce-sidebar-toggle">☰ Меню сайта</button>

				<?php if ( $has_panel ) : ?>
					<?php $this->render_editor_panel( $texts, $overrides, $q, $section, $block, $sections, $paged, $pages, $total, $modified ); ?>
				<?php else : ?>
					<?php $this->render_welcome_panel( $nav_tree, $mod_count ); ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Empty state — pick from sidebar.
	 *
	 * @param array<string,array<string,mixed>> $nav_tree  Tree.
	 * @param int                               $mod_count Modified.
	 */
	private function render_welcome_panel( $nav_tree, $mod_count ) {
		$base     = admin_url( 'admin.php?page=iacce-editor&tab=texts' );
		$shortcuts = array(
			array( 'section' => 'home', 'block' => 'home-interfaces', 'label' => '«Chaos is optional» — слайдер 3D' ),
			array( 'section' => 'header', 'block' => 'header-cta', 'label' => 'Кнопка Request Access' ),
			array( 'section' => 'home', 'block' => 'home-hero-chat', 'label' => 'Чат Impact APP' ),
			array( 'section' => 'footer', 'block' => 'footer-cta', 'label' => 'REQUEST ACCESS в подвале' ),
		);
		?>
		<div class="iacce-welcome">
			<div class="iacce-welcome__arrow">←</div>
			<h2>Выберите блок слева</h2>
			<p>Откройте раздел (Главная, Шапка…) и нажмите нужный блок.<br>Цифры <strong>1, 2, 3…</strong> — порядок сверху вниз на странице.</p>

			<h3>Быстрый переход</h3>
			<div class="iacce-quick">
				<?php foreach ( $shortcuts as $link ) : ?>
					<a class="iacce-quick__btn" href="<?php echo esc_url( add_query_arg( array( 'section' => $link['section'], 'block' => $link['block'] ), $base ) ); ?>">
						<?php echo esc_html( $link['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>

			<?php if ( $mod_count > 0 ) : ?>
				<p class="iacce-welcome__mod">
					<a href="<?php echo esc_url( add_query_arg( 'modified', '1', $base ) ); ?>">→ Посмотреть <?php echo (int) $mod_count; ?> изменённых текстов</a>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Editor panel (right side).
	 *
	 * @param array<int,array<string,mixed>>                            $texts     Texts.
	 * @param array<string,mixed>                                       $overrides Overrides.
	 * @param string                                                    $q         Search.
	 * @param string                                                    $section   Section.
	 * @param string                                                    $block     Block.
	 * @param array<string,array{label:string,desc:string,icon:string}> $sections  Sections.
	 * @param int                                                       $paged     Page.
	 * @param int                                                       $pages     Pages.
	 * @param int                                                       $total     Total.
	 * @param bool                                                      $modified  Modified.
	 */
	private function render_editor_panel( $texts, $overrides, $q, $section, $block, $sections, $paged, $pages, $total, $modified ) {
		$base_url   = admin_url( 'admin.php?page=iacce-editor&tab=texts' );
		$catalog    = IACCE_Blocks::catalog();
		$block_meta = $block && isset( $catalog[ $block ] ) ? $catalog[ $block ] : null;
		$sec_label  = isset( $sections[ $section ] ) ? $sections[ $section ]['label'] : '';

		if ( $modified ) {
			$panel_title = 'Изменённые тексты';
		} elseif ( $q ) {
			$panel_title = 'Поиск: «' . $q . '»';
		} elseif ( $block_meta ) {
			$panel_title = $block_meta['title'];
		} else {
			$panel_title = 'Тексты';
		}
		?>
		<div class="iacce-panel-head">
			<?php if ( $sec_label && $block_meta && ! $modified && ! $q ) : ?>
				<div class="iacce-panel-path">
					<span><?php echo esc_html( $sec_label ); ?></span>
					<span class="iacce-panel-path__sep">→</span>
					<strong><?php echo esc_html( $block_meta['title'] ); ?></strong>
				</div>
			<?php else : ?>
				<h2 class="iacce-panel-title"><?php echo esc_html( $panel_title ); ?></h2>
			<?php endif; ?>

			<?php if ( $block_meta && ! $q && ! $modified ) : ?>
				<p class="iacce-panel-where"><span>📍</span> <?php echo esc_html( $block_meta['where'] ); ?></p>
			<?php endif; ?>

			<?php if ( $q || $modified ) : ?>
				<p class="iacce-panel-where"><a href="<?php echo esc_url( $base_url ); ?>">← Очистить фильтр</a></p>
			<?php endif; ?>
		</div>

		<p class="iacce-count"><?php echo (int) $total; ?> фраз<?php echo $pages > 1 ? ' · стр. ' . (int) $paged . '/' . (int) $pages : ''; ?></p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="iacce-text-form">
			<?php wp_nonce_field( 'iacce_save_texts' ); ?>
			<input type="hidden" name="action" value="iacce_save" />
			<input type="hidden" name="iacce_type" value="texts" />
			<input type="hidden" name="iacce_return_section" value="<?php echo esc_attr( $section ); ?>" />
			<input type="hidden" name="iacce_return_block" value="<?php echo esc_attr( $block ); ?>" />
			<?php if ( $modified ) : ?><input type="hidden" name="iacce_return_modified" value="1" /><?php endif; ?>
			<?php if ( $q ) : ?><input type="hidden" name="iacce_return_q" value="<?php echo esc_attr( $q ); ?>" /><?php endif; ?>

			<?php if ( empty( $texts ) ) : ?>
				<div class="iacce-empty"><p>Ничего не найдено.</p></div>
			<?php else : ?>
				<div class="iacce-cards iacce-cards--flat">
					<?php foreach ( $texts as $item ) :
						$this->render_text_card( $item, $overrides, $base_url, (bool) $q );
					endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $pages > 1 ) : ?>
				<div class="iacce-pagination tablenav">
					<div class="tablenav-pages">
						<?php
						echo wp_kses_post(
							paginate_links(
								array(
									'base'      => add_query_arg( 'paged', '%#%' ),
									'format'    => '',
									'prev_text' => '←',
									'next_text' => '→',
									'total'     => $pages,
									'current'   => $paged,
								)
							)
						);
						?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $texts ) ) : ?>
				<div class="iacce-sticky-bar">
					<button type="submit" class="button button-primary button-hero">Сохранить</button>
					<span class="iacce-hint">Потом на сайте: <kbd>Ctrl</kbd>+<kbd>F5</kbd></span>
					<span class="iacce-dirty-hint" hidden>● Не сохранено</span>
				</div>
			<?php endif; ?>
		</form>
		<?php
	}

	/**
	 * Single text edit card.
	 *
	 * @param array<string,mixed> $item      Text row.
	 * @param array<string,mixed> $overrides Overrides.
	 * @param string              $base_url  Base URL.
	 * @param bool                $show_block Show block link.
	 */
	private function render_text_card( $item, $overrides, $base_url, $show_block = false ) {
		$id         = $item['id'];
		$ov         = isset( $overrides[ $id ] ) && is_array( $overrides[ $id ] ) ? $overrides[ $id ] : array();
		$is_mod     = ! empty( $ov['en'] ) || ! empty( $ov['ru'] );
		$orig_en    = (string) ( $item['en'] ?? '' );
		$orig_ru    = $this->original_ru( $item );
		$current_en = $this->effective_text( $item, $overrides, 'en' );
		$current_ru = $this->effective_text( $item, $overrides, 'ru' );
		$ru_hint    = '' === (string) ( $item['ru'] ?? '' ) && '' === $orig_ru;
		?>
		<div class="iacce-card <?php echo $is_mod ? 'iacce-card--modified' : ''; ?>"
			data-id="<?php echo esc_attr( $id ); ?>"
			data-orig-en="<?php echo esc_attr( $orig_en ); ?>"
			data-orig-ru="<?php echo esc_attr( $orig_ru ); ?>">
			<div class="iacce-card__head">
				<?php if ( $show_block && ! empty( $item['block_title'] ) ) : ?>
					<a class="iacce-badge iacce-badge--block" href="<?php echo esc_url( add_query_arg( array( 'section' => $item['category'] ?? '', 'block' => $item['block'] ?? '' ), $base_url ) ); ?>"><?php echo esc_html( $item['block_title'] ); ?></a>
				<?php endif; ?>
				<?php if ( $is_mod ) : ?><span class="iacce-badge iacce-badge--changed">изм.</span><?php endif; ?>
				<a class="iacce-reset" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=iacce_reset_item&id=' . rawurlencode( $id ) ), 'iacce_reset_' . $id ) ); ?>" onclick="return confirm('Вернуть как было?');">↩</a>
			</div>
			<div class="iacce-lang-tabs" role="tablist">
				<button type="button" class="iacce-lang-tab iacce-lang-tab--active" data-lang="en" role="tab">EN</button>
				<button type="button" class="iacce-lang-tab" data-lang="ru" role="tab">RU</button>
			</div>
			<div class="iacce-lang-panel iacce-lang-panel--active" data-lang="en">
				<input type="text" class="large-text iacce-input" name="texts[<?php echo esc_attr( $id ); ?>][en]" value="<?php echo esc_attr( $current_en ); ?>" data-lang="en" autocomplete="off" placeholder="English" />
			</div>
			<div class="iacce-lang-panel" data-lang="ru">
				<input type="text" class="large-text iacce-input" name="texts[<?php echo esc_attr( $id ); ?>][ru]" value="<?php echo esc_attr( $current_ru ); ?>" data-lang="ru" autocomplete="off" placeholder="<?php echo esc_attr( $ru_hint ? 'Введите перевод' : 'Русский' ); ?>" />
				<?php if ( $ru_hint ) : ?>
					<p class="iacce-orig-hint">Перевод не найден в файлах — можно ввести вручную.</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * @param array<int,array<string,mixed>> $texts   Texts.
	 * @param string                         $q       Query.
	 * @param string                         $section Section slug.
	 * @param string                         $block   Block slug.
	 * @return array<int,array<string,mixed>>
	 */
	private function filter_texts( $texts, $q, $section, $block = '' ) {
		if ( '' !== $q ) {
			$texts = array_values(
				array_filter(
					$texts,
					static function ( $item ) use ( $q ) {
						$hay = strtolower(
							( $item['en'] ?? '' ) . ' ' .
							( $item['ru'] ?? '' ) . ' ' .
							( $item['section'] ?? '' ) . ' ' .
							( $item['block_title'] ?? '' ) . ' ' .
							( $item['block_desc'] ?? '' ) . ' ' .
							( $item['block_where'] ?? '' ) . ' ' .
							( $item['preview'] ?? '' )
						);
						return false !== strpos( $hay, strtolower( $q ) );
					}
				)
			);
		}
		if ( '' !== $section ) {
			$texts = array_values(
				array_filter(
					$texts,
					static function ( $item ) use ( $section ) {
						return ( $item['category'] ?? '' ) === $section;
					}
				)
			);
		}
		if ( '' !== $block ) {
			$texts = array_values(
				array_filter(
					$texts,
					static function ( $item ) use ( $block ) {
						return ( $item['block'] ?? '' ) === $block;
					}
				)
			);
		}
		return $texts;
	}

	/**
	 * Step indicator: 1 = section, 2 = block, 3 = edit.
	 *
	 * @param int $active Active step (0 = hidden).
	 */
	private function render_steps( $active ) {
		if ( $active < 1 ) {
			return;
		}
		$steps = array(
			1 => 'Раздел',
			2 => 'Блок',
			3 => 'Тексты',
		);
		?>
		<div class="iacce-steps">
			<?php foreach ( $steps as $num => $label ) : ?>
				<span class="iacce-steps__item <?php echo $num === $active ? 'iacce-steps__item--active' : ( $num < $active ? 'iacce-steps__item--done' : '' ); ?>">
					<span class="iacce-steps__num"><?php echo (int) $num; ?></span>
					<?php echo esc_html( $label ); ?>
				</span>
				<?php if ( $num < 3 ) : ?>
					<span class="iacce-steps__arrow">→</span>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * @param array<string,array{label:string,desc:string,icon:string}> $sections Sections.
	 * @param array<string,array{total:int,modified:int}>               $stats    Stats.
	 * @param int                                                       $mod_count Modified total.
	 */
	private function render_dashboard( $sections, $stats, $mod_count ) {
		$base = admin_url( 'admin.php?page=iacce-editor&tab=texts' );
		?>
		<form method="get" class="iacce-search-bar">
			<input type="hidden" name="page" value="iacce-editor" />
			<input type="hidden" name="tab" value="texts" />
			<span class="dashicons dashicons-search"></span>
			<input type="search" name="q" value="" placeholder="Или сразу найти текст — Chaos, Request access, Telegram…" class="iacce-search iacce-search--hero" />
			<button type="submit" class="button button-primary">Найти</button>
		</form>

		<?php if ( $mod_count > 0 ) : ?>
			<a class="iacce-modified-banner" href="<?php echo esc_url( add_query_arg( 'modified', '1', $base ) ); ?>">
				<strong><?php echo (int) $mod_count; ?></strong> изменённых текстов
			</a>
		<?php endif; ?>

		<div class="iacce-quick iacce-quick--dash">
			<span class="iacce-quick__label">Популярное:</span>
			<a class="iacce-quick__btn" href="<?php echo esc_url( add_query_arg( array( 'section' => 'home', 'block' => 'home-interfaces' ), $base ) ); ?>">Chaos is optional (слайдер)</a>
			<a class="iacce-quick__btn" href="<?php echo esc_url( add_query_arg( array( 'section' => 'header', 'block' => 'header-cta' ), $base ) ); ?>">Request Access</a>
			<a class="iacce-quick__btn" href="<?php echo esc_url( add_query_arg( array( 'section' => 'home', 'block' => 'home-hero-chat' ), $base ) ); ?>">Чат на главной</a>
		</div>

		<h2 class="iacce-h2">Выберите раздел сайта</h2>
		<div class="iacce-dashboard">
			<?php foreach ( $sections as $slug => $meta ) :
				$total = $stats[ $slug ]['total'] ?? 0;
				if ( $total < 1 ) {
					continue;
				}
				$mod = $stats[ $slug ]['modified'] ?? 0;
				$url = add_query_arg( 'section', $slug, $base );
				?>
				<a class="iacce-dash-card" href="<?php echo esc_url( $url ); ?>">
					<span class="iacce-dash-card__icon dashicons <?php echo esc_attr( $meta['icon'] ); ?>"></span>
					<span class="iacce-dash-card__title"><?php echo esc_html( $meta['label'] ); ?></span>
					<span class="iacce-dash-card__desc"><?php echo esc_html( $meta['desc'] ); ?></span>
					<span class="iacce-dash-card__meta">
						<?php echo (int) $total; ?> текстов
						<?php if ( $mod > 0 ) : ?>
							· <em><?php echo (int) $mod; ?> изменено</em>
						<?php endif; ?>
					</span>
				</a>
			<?php endforeach; ?>
		</div>

		<div class="iacce-tips">
			<h3>Как пользоваться</h3>
			<ol>
				<li>Выберите <strong>раздел</strong> (Главная, Шапка…)</li>
				<li>Выберите <strong>блок на странице</strong> — там написано, где именно на сайте</li>
				<li>Измените текст → <strong>Сохранить</strong> → на сайте <kbd>Ctrl</kbd>+<kbd>F5</kbd></li>
			</ol>
		</div>
		<?php
	}

	/**
	 * Block picker inside a section.
	 *
	 * @param array<string,array{label:string,desc:string,icon:string}> $sections    Sections.
	 * @param string                                                    $section     Section slug.
	 * @param array<string,array<string,mixed>>                         $block_stats Block stats.
	 */
	private function render_block_picker( $sections, $section, $block_stats ) {
		$base     = admin_url( 'admin.php?page=iacce-editor&tab=texts' );
		$sec_meta = $sections[ $section ] ?? array( 'label' => $section, 'desc' => '' );
		$sec_url  = add_query_arg( 'section', $section, $base );
		?>
		<div class="iacce-breadcrumb iacce-breadcrumb--big">
			<a class="button" href="<?php echo esc_url( $base ); ?>">← Назад к разделам</a>
		</div>

		<div class="iacce-section-hero">
			<span class="iacce-section-hero__icon dashicons <?php echo esc_attr( $sec_meta['icon'] ?? 'dashicons-admin-page' ); ?>"></span>
			<div>
				<h2 class="iacce-h2 iacce-h2--flush"><?php echo esc_html( $sec_meta['label'] ?? $section ); ?></h2>
				<p><?php echo esc_html( $sec_meta['desc'] ?? '' ); ?></p>
			</div>
		</div>

		<form method="get" class="iacce-search-bar">
			<input type="hidden" name="page" value="iacce-editor" />
			<input type="hidden" name="tab" value="texts" />
			<input type="hidden" name="section" value="<?php echo esc_attr( $section ); ?>" />
			<span class="dashicons dashicons-search"></span>
			<input type="search" name="q" value="" placeholder="Поиск в разделе «<?php echo esc_attr( $sec_meta['label'] ?? $section ); ?>»…" class="iacce-search" />
			<button type="submit" class="button button-primary">Найти</button>
		</form>

		<h3 class="iacce-h3">Какой блок менять?</h3>
		<p class="iacce-h3-sub">Сверху вниз — как при прокрутке страницы</p>

		<div class="iacce-block-grid">
			<?php
			$position = 0;
			foreach ( $block_stats as $bid => $row ) :
				if ( ( $row['total'] ?? 0 ) < 1 ) {
					continue;
				}
				++$position;
				$meta = $row['meta'] ?? array();
				$url  = add_query_arg(
					array(
						'section' => $section,
						'block'   => $bid,
					),
					$base
				);
				?>
				<a class="iacce-block-card" href="<?php echo esc_url( $url ); ?>">
					<div class="iacce-block-card__top">
						<span class="iacce-block-card__num"><?php echo (int) $position; ?></span>
						<span class="iacce-block-card__icon dashicons <?php echo esc_attr( $meta['icon'] ?? 'dashicons-editor-alignleft' ); ?>"></span>
						<?php if ( ( $row['modified'] ?? 0 ) > 0 ) : ?>
							<span class="iacce-block-card__mod"><?php echo (int) $row['modified']; ?> изм.</span>
						<?php endif; ?>
					</div>
					<span class="iacce-block-card__title"><?php echo esc_html( $meta['title'] ?? $bid ); ?></span>
					<span class="iacce-block-card__where"><?php echo esc_html( $meta['where'] ?? '' ); ?></span>
					<span class="iacce-block-card__meta"><?php echo (int) $row['total']; ?> текстов →</span>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * @param array<int,array<string,mixed>>                          $texts     Page slice.
	 * @param array<string,mixed>                                     $overrides Overrides.
	 * @param string                                                  $q         Query.
	 * @param string                                                  $section   Section.
	 * @param string                                                  $block     Block slug.
	 * @param array<string,array{label:string,desc:string,icon:string}> $sections  Sections.
	 * @param int                                                     $paged     Page.
	 * @param int                                                     $pages     Total pages.
	 * @param int                                                     $total     Total items.
	 * @param bool                                                    $modified  Modified filter.
	 * @param int                                                     $mod_count Modified count.
	 */
	private function render_texts_tab( $texts, $overrides, $q, $section, $block, $sections, $paged, $pages, $total, $modified = false, $mod_count = 0 ) {
		$base_url = admin_url( 'admin.php?page=iacce-editor&tab=texts' );
		$title    = 'Все тексты';
		$block_meta = null;

		if ( $modified ) {
			$title = 'Изменённые тексты';
		} elseif ( $block ) {
			$catalog    = IACCE_Blocks::catalog();
			$block_meta = $catalog[ $block ] ?? null;
			$title      = $block_meta['title'] ?? $block;
		} elseif ( $section && isset( $sections[ $section ] ) ) {
			$title = $sections[ $section ]['label'];
		} elseif ( '' !== $q ) {
			$title = 'Поиск: «' . $q . '»';
		}

		$use_block_group = '' === $block && '' === $q && ! $modified;
		$grouped         = array();
		foreach ( $texts as $item ) {
			if ( $use_block_group ) {
				$key = $item['block_title'] ?? ( $item['section'] ?? 'Прочее' );
			} elseif ( $q && ! $block ) {
				$key = $item['block_title'] ?? 'Прочее';
			} else {
				$key = '_flat';
			}
			if ( ! isset( $grouped[ $key ] ) ) {
				$grouped[ $key ] = array();
			}
			$grouped[ $key ][] = $item;
		}
		?>
		<div class="iacce-breadcrumb iacce-breadcrumb--big">
			<a class="button" href="<?php echo esc_url( $base_url ); ?>">← Разделы</a>
			<?php if ( $section && isset( $sections[ $section ] ) ) : ?>
				<a class="button" href="<?php echo esc_url( add_query_arg( 'section', $section, $base_url ) ); ?>">← <?php echo esc_html( $sections[ $section ]['label'] ); ?></a>
			<?php endif; ?>
		</div>

		<?php if ( $block_meta ) : ?>
			<div class="iacce-block-banner iacce-block-banner--compact">
				<strong><?php echo esc_html( $block_meta['title'] ); ?></strong>
				<p class="iacce-block-banner__where">📍 <?php echo esc_html( $block_meta['where'] ); ?></p>
			</div>
		<?php elseif ( $modified || $q ) : ?>
			<h2 class="iacce-panel-title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>

		<?php if ( $q || $modified ) : ?>
		<form method="get" class="iacce-search-bar">
			<input type="hidden" name="page" value="iacce-editor" />
			<input type="hidden" name="tab" value="texts" />
			<?php if ( $section ) : ?><input type="hidden" name="section" value="<?php echo esc_attr( $section ); ?>" /><?php endif; ?>
			<?php if ( $block ) : ?><input type="hidden" name="block" value="<?php echo esc_attr( $block ); ?>" /><?php endif; ?>
			<?php if ( $modified ) : ?><input type="hidden" name="modified" value="1" /><?php endif; ?>
			<span class="dashicons dashicons-search"></span>
			<input type="search" name="q" value="<?php echo esc_attr( $q ); ?>" placeholder="Поиск…" class="iacce-search" />
			<button type="submit" class="button">Найти</button>
			<a class="button" href="<?php echo esc_url( $base_url ); ?>">Сбросить</a>
		</form>
		<?php endif; ?>

		<p class="iacce-count"><?php echo (int) $total; ?> фраз<?php echo $pages > 1 ? ' · стр. ' . (int) $paged . '/' . (int) $pages : ''; ?></p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="iacce-text-form">
			<?php wp_nonce_field( 'iacce_save_texts' ); ?>
			<input type="hidden" name="action" value="iacce_save" />
			<input type="hidden" name="iacce_type" value="texts" />
			<input type="hidden" name="iacce_return_section" value="<?php echo esc_attr( $section ); ?>" />
			<input type="hidden" name="iacce_return_block" value="<?php echo esc_attr( $block ); ?>" />
			<?php if ( $modified ) : ?><input type="hidden" name="iacce_return_modified" value="1" /><?php endif; ?>
			<?php if ( $q ) : ?><input type="hidden" name="iacce_return_q" value="<?php echo esc_attr( $q ); ?>" /><?php endif; ?>

			<?php if ( empty( $texts ) ) : ?>
				<div class="iacce-empty">
					<p>Ничего не найдено.</p>
					<p><a href="<?php echo esc_url( $base_url ); ?>">← Вернуться к разделам</a></p>
				</div>
			<?php else : ?>
				<?php foreach ( $grouped as $group_name => $items ) : ?>
					<?php if ( '_flat' !== $group_name ) : ?>
					<details class="iacce-group" <?php echo count( $grouped ) === 1 ? 'open' : ''; ?>>
						<summary class="iacce-group__title">
							<?php echo esc_html( $group_name ); ?>
							<span class="iacce-group__count"><?php echo count( $items ); ?></span>
						</summary>
						<div class="iacce-cards">
					<?php else : ?>
						<div class="iacce-cards iacce-cards--flat">
					<?php endif; ?>
							<?php foreach ( $items as $item ) :
								$id         = $item['id'];
								$ov         = isset( $overrides[ $id ] ) && is_array( $overrides[ $id ] ) ? $overrides[ $id ] : array();
								$is_mod     = ! empty( $ov['en'] ) || ! empty( $ov['ru'] );
								$orig_en    = (string) ( $item['en'] ?? '' );
								$orig_ru    = $this->original_ru( $item );
								$current_en = $this->effective_text( $item, $overrides, 'en' );
								$current_ru = $this->effective_text( $item, $overrides, 'ru' );
								$ru_hint    = '' === (string) ( $item['ru'] ?? '' ) && '' === $orig_ru;
								?>
								<div class="iacce-card <?php echo $is_mod ? 'iacce-card--modified' : ''; ?>"
									data-id="<?php echo esc_attr( $id ); ?>"
									data-orig-en="<?php echo esc_attr( $orig_en ); ?>"
									data-orig-ru="<?php echo esc_attr( $orig_ru ); ?>">
									<div class="iacce-card__head">
										<?php if ( $q && ! empty( $item['block_title'] ) ) : ?>
											<a class="iacce-badge iacce-badge--block" href="<?php echo esc_url( add_query_arg( array( 'section' => $item['category'] ?? '', 'block' => $item['block'] ?? '' ), $base_url ) ); ?>"><?php echo esc_html( $item['block_title'] ); ?></a>
										<?php endif; ?>
										<?php if ( $is_mod ) : ?>
											<span class="iacce-badge iacce-badge--changed">Изменено</span>
										<?php endif; ?>
										<a class="iacce-reset" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=iacce_reset_item&id=' . rawurlencode( $id ) ), 'iacce_reset_' . $id ) ); ?>" onclick="return confirm('Вернуть оригинальный текст?');">↩ Вернуть как было</a>
									</div>

									<div class="iacce-lang-tabs" role="tablist">
										<button type="button" class="iacce-lang-tab iacce-lang-tab--active" data-lang="en" role="tab">🇬🇧 English</button>
										<button type="button" class="iacce-lang-tab" data-lang="ru" role="tab">🇷🇺 Русский</button>
									</div>

									<div class="iacce-lang-panel iacce-lang-panel--active" data-lang="en">
										<label class="iacce-field-label" for="iacce-en-<?php echo esc_attr( $id ); ?>">Текст на сайте (English)</label>
										<input type="text" id="iacce-en-<?php echo esc_attr( $id ); ?>" class="large-text iacce-input" name="texts[<?php echo esc_attr( $id ); ?>][en]" value="<?php echo esc_attr( $current_en ); ?>" data-lang="en" autocomplete="off" />
										<?php if ( $is_mod && ! empty( $ov['en'] ) ) : ?>
											<p class="iacce-orig-hint">Было: <button type="button" class="iacce-restore-one" data-lang="en"><?php echo esc_html( $orig_en ); ?></button></p>
										<?php endif; ?>
									</div>

									<div class="iacce-lang-panel" data-lang="ru">
										<label class="iacce-field-label" for="iacce-ru-<?php echo esc_attr( $id ); ?>">Текст на сайте (Русский)</label>
										<input type="text" id="iacce-ru-<?php echo esc_attr( $id ); ?>" class="large-text iacce-input" name="texts[<?php echo esc_attr( $id ); ?>][ru]" value="<?php echo esc_attr( $current_ru ); ?>" data-lang="ru" autocomplete="off" placeholder="<?php echo esc_attr( $ru_hint ? 'Введите перевод' : '' ); ?>" />
										<?php if ( $ru_hint ) : ?>
											<p class="iacce-orig-hint">Перевод не найден в файлах — можно ввести вручную.</p>
										<?php elseif ( $is_mod && ! empty( $ov['ru'] ) ) : ?>
											<p class="iacce-orig-hint">Было: <button type="button" class="iacce-restore-one" data-lang="ru"><?php echo esc_html( $orig_ru ); ?></button></p>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php if ( '_flat' !== $group_name ) : ?>
					</details>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if ( $pages > 1 ) : ?>
				<div class="iacce-pagination tablenav">
					<div class="tablenav-pages">
						<?php
						echo wp_kses_post(
							paginate_links(
								array(
									'base'      => add_query_arg( 'paged', '%#%' ),
									'format'    => '',
									'prev_text' => '← Назад',
									'next_text' => 'Далее →',
									'total'     => $pages,
									'current'   => $paged,
								)
							)
						);
						?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $texts ) ) : ?>
				<div class="iacce-sticky-bar">
					<button type="submit" class="button button-primary button-hero">Сохранить</button>
					<span class="iacce-hint">После сохранения откройте сайт и нажмите <kbd>Ctrl</kbd>+<kbd>F5</kbd></span>
					<span class="iacce-dirty-hint" hidden>Есть несохранённые изменения</span>
				</div>
			<?php endif; ?>
		</form>

		<details class="iacce-advanced">
			<summary>Служебное</summary>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="iacce-rescan">
				<?php wp_nonce_field( 'iacce_rescan' ); ?>
				<input type="hidden" name="action" value="iacce_rescan" />
				<button type="submit" class="button">Обновить список текстов</button>
				<span class="iacce-hint">Только если после обновления плагина список выглядит странно.</span>
			</form>
		</details>
		<?php
	}

	/**
	 * @param array<int,array<string,mixed>> $link_reg Links registry.
	 * @param array<string,string>           $links    Overrides.
	 */
	private function render_links_tab( $link_reg, $links ) {
		$grouped = array();
		foreach ( $link_reg as $link ) {
			$key = $link['category'] ?? 'other';
			$sections = IACCE_Labels::dashboard_sections();
			$label = isset( $sections[ $key ] ) ? $sections[ $key ]['label'] : 'Прочее';
			if ( ! isset( $grouped[ $label ] ) ) {
				$grouped[ $label ] = array();
			}
			$grouped[ $label ][] = $link;
		}
		?>
		<div class="iacce-help">
			<strong>Ссылки:</strong> Telegram, WhatsApp, телефон <code>tel:+7…</code>, email <code>mailto:…</code>. Текст телефона меняется во вкладке «Тексты» → Контакты → модалка; здесь — куда ведёт клик.
		</div>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="iacce-links-form">
			<?php wp_nonce_field( 'iacce_save_links' ); ?>
			<input type="hidden" name="action" value="iacce_save" />
			<input type="hidden" name="iacce_type" value="links" />

			<?php foreach ( $grouped as $group_name => $items ) : ?>
				<details class="iacce-group" open>
					<summary class="iacce-group__title">
						<?php echo esc_html( $group_name ); ?>
						<span class="iacce-group__count"><?php echo count( $items ); ?></span>
					</summary>
					<div class="iacce-cards">
						<?php foreach ( $items as $link ) :
							$orig = (string) ( $link['href'] ?? '' );
							$cur  = isset( $links[ $link['id'] ] ) && '' !== $links[ $link['id'] ] ? $links[ $link['id'] ] : $orig;
							$changed = $cur !== $orig;
							$kind = 'Ссылка';
							if ( 0 === stripos( $orig, 'tel:' ) ) {
								$kind = 'Телефон (tel:)';
							} elseif ( 0 === stripos( $orig, 'mailto:' ) ) {
								$kind = 'Email (mailto:)';
							}
							?>
							<div class="iacce-card iacce-card--link <?php echo $changed ? 'iacce-card--modified' : ''; ?>"
								data-orig-href="<?php echo esc_attr( $orig ); ?>">
								<div class="iacce-link-label">
									<span class="iacce-link-label__text"><?php echo esc_html( $link['label'] ?: '(кнопка без текста)' ); ?></span>
									<span class="iacce-badge"><?php echo esc_html( $kind ); ?></span>
								</div>
								<label class="iacce-field-label">Куда ведёт клик</label>
								<input type="text" class="large-text iacce-link-input" name="links[<?php echo esc_attr( $link['id'] ); ?>]" value="<?php echo esc_attr( $cur ); ?>" placeholder="https://… или tel:+7… или mailto:…" />
								<?php if ( $changed ) : ?>
									<p class="iacce-orig-hint">Было: <button type="button" class="iacce-restore-link"><?php echo esc_html( $orig ); ?></button></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</details>
			<?php endforeach; ?>

			<div class="iacce-sticky-bar">
				<button type="submit" class="button button-primary button-hero">Сохранить ссылки</button>
			</div>
		</form>
		<?php
	}

	private function render_backups_tab() {
		$backups = IACCE_Backup::list_snapshots();
		?>
		<div class="iacce-help">
			Перед крупными правками создайте бекап. «Восстановить» вернёт тексты и ссылки к сохранённому состоянию.
		</div>
		<div class="iacce-backups">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:1em">
				<?php wp_nonce_field( 'iacce_backup' ); ?>
				<input type="hidden" name="action" value="iacce_backup" />
				<button type="submit" class="button button-primary">Создать бекап сейчас</button>
			</form>
			<table class="widefat striped">
				<thead><tr><th>Дата</th><th>Описание</th><th></th></tr></thead>
				<tbody>
				<?php if ( empty( $backups ) ) : ?>
					<tr><td colspan="3">Бекапов пока нет — создаются автоматически при сохранении.</td></tr>
				<?php else : ?>
					<?php foreach ( array_reverse( $backups ) as $row ) : ?>
						<tr>
							<td><?php echo esc_html( $row['created'] ?? '' ); ?></td>
							<td><?php echo esc_html( $row['label'] ?? '' ); ?></td>
							<td>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
									<?php wp_nonce_field( 'iacce_restore_' . ( $row['id'] ?? '' ) ); ?>
									<input type="hidden" name="action" value="iacce_restore" />
									<input type="hidden" name="backup_id" value="<?php echo esc_attr( $row['id'] ?? '' ); ?>" />
									<button type="submit" class="button" onclick="return confirm('Восстановить этот бекап?');">Восстановить</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function handle_save() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'impact-accs-content-editor' ) );
		}

		$type = isset( $_POST['iacce_type'] ) ? sanitize_key( wp_unslash( $_POST['iacce_type'] ) ) : '';

		if ( 'links' === $type ) {
			check_admin_referer( 'iacce_save_links' );
			$incoming = isset( $_POST['links'] ) && is_array( $_POST['links'] ) ? wp_unslash( $_POST['links'] ) : array();
			$registry = get_option( IACCE_OPTION_REGISTRY, array() );
			$link_reg = isset( $registry['links'] ) && is_array( $registry['links'] ) ? $registry['links'] : array();
			$orig_by_id = array();
			foreach ( $link_reg as $row ) {
				if ( ! empty( $row['id'] ) ) {
					$orig_by_id[ $row['id'] ] = (string) ( $row['href'] ?? '' );
				}
			}

			$clean = array();
			foreach ( $incoming as $id => $url ) {
				$id  = sanitize_key( $id );
				$url = $this->sanitize_link_override( (string) $url );
				$orig = $orig_by_id[ $id ] ?? '';
				if ( '' === $url || $url === $orig ) {
					continue;
				}
				$clean[ $id ] = $url;
			}
			IACCE_Backup::create_snapshot( 'before-save-links' );
			update_option( IACCE_OPTION_LINKS, $clean, false );
			$this->redirect( 'Ссылки сохранены.', 'links' );
		}

		check_admin_referer( 'iacce_save_texts' );
		$incoming = isset( $_POST['texts'] ) && is_array( $_POST['texts'] ) ? wp_unslash( $_POST['texts'] ) : array();
		$registry = get_option( IACCE_OPTION_REGISTRY, array() );
		$registry_texts = isset( $registry['texts'] ) && is_array( $registry['texts'] ) ? $registry['texts'] : array();
		$orig_by_id = array();
		foreach ( $registry_texts as $row ) {
			if ( ! empty( $row['id'] ) ) {
				$orig_by_id[ $row['id'] ] = $row;
			}
		}

		$existing = get_option( IACCE_OPTION_OVERRIDES, array() );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}

		foreach ( $incoming as $id => $row ) {
			$id = sanitize_key( $id );
			if ( ! is_array( $row ) || ! isset( $orig_by_id[ $id ] ) ) {
				continue;
			}
			$orig    = $orig_by_id[ $id ];
			$en      = isset( $row['en'] ) ? sanitize_textarea_field( $row['en'] ) : '';
			$ru      = isset( $row['ru'] ) ? sanitize_textarea_field( $row['ru'] ) : '';
			$orig_en = (string) ( $orig['en'] ?? '' );
			$orig_ru = (string) ( $orig['ru'] ?? '' );
			if ( '' === $orig_ru && '' !== $orig_en ) {
				$orig_ru = IACCE_Scanner::lookup_ru( $orig_en );
			}

			$entry = array();
			if ( '' !== $en && $en !== $orig_en ) {
				$entry['en'] = $en;
			}
			if ( '' !== $ru && $ru !== $orig_ru ) {
				$entry['ru'] = $ru;
			}

			if ( empty( $entry ) ) {
				unset( $existing[ $id ] );
			} else {
				$existing[ $id ] = $entry;
			}
		}

		IACCE_Backup::create_snapshot( 'before-save' );
		update_option( IACCE_OPTION_OVERRIDES, $existing, false );

		$extra = array();
		if ( ! empty( $_POST['iacce_return_section'] ) ) {
			$extra['section'] = sanitize_key( wp_unslash( $_POST['iacce_return_section'] ) );
		}
		if ( ! empty( $_POST['iacce_return_block'] ) ) {
			$extra['block'] = sanitize_key( wp_unslash( $_POST['iacce_return_block'] ) );
		}
		if ( ! empty( $_POST['iacce_return_modified'] ) ) {
			$extra['modified'] = '1';
		}
		if ( ! empty( $_POST['iacce_return_q'] ) ) {
			$extra['q'] = sanitize_text_field( wp_unslash( $_POST['iacce_return_q'] ) );
		}
		$this->redirect( 'Сохранено! Обновите сайт (Ctrl+F5).', 'texts', $extra );
	}

	public function handle_rescan() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'impact-accs-content-editor' ) );
		}
		check_admin_referer( 'iacce_rescan' );

		IACCE_Backup::create_snapshot( 'before-rescan' );
		$new = IACCE_Scanner::build_registry();
		update_option( IACCE_OPTION_REGISTRY, $new, false );
		$this->redirect( 'Список обновлён.' );
	}

	public function handle_backup() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'impact-accs-content-editor' ) );
		}
		check_admin_referer( 'iacce_backup' );
		$result = IACCE_Backup::create_snapshot( 'manual' );
		if ( is_wp_error( $result ) ) {
			$this->redirect( 'Ошибка бекапа: ' . $result->get_error_message(), 'backups' );
		}
		$this->redirect( 'Бекап создан.', 'backups' );
	}

	public function handle_restore() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'impact-accs-content-editor' ) );
		}
		$id = isset( $_POST['backup_id'] ) ? sanitize_file_name( wp_unslash( $_POST['backup_id'] ) ) : '';
		check_admin_referer( 'iacce_restore_' . $id );
		$result = IACCE_Backup::restore_snapshot( $id );
		if ( is_wp_error( $result ) ) {
			$this->redirect( 'Ошибка: ' . $result->get_error_message(), 'backups' );
		}
		$this->redirect( 'Бекап восстановлен.', 'backups' );
	}

	public function handle_reset_item() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'impact-accs-content-editor' ) );
		}
		$id = isset( $_REQUEST['id'] ) ? sanitize_key( wp_unslash( $_REQUEST['id'] ) ) : '';
		check_admin_referer( 'iacce_reset_' . $id );

		$existing = get_option( IACCE_OPTION_OVERRIDES, array() );
		if ( is_array( $existing ) && isset( $existing[ $id ] ) ) {
			unset( $existing[ $id ] );
			update_option( IACCE_OPTION_OVERRIDES, $existing, false );
		}
		$this->redirect( 'Текст возвращён к оригиналу.' );
	}

	/**
	 * @param string $url Raw link from admin form.
	 * @return string
	 */
	private function sanitize_link_override( $url ) {
		$url = trim( wp_strip_all_tags( (string) $url ) );
		if ( '' === $url ) {
			return '';
		}
		if ( '#' === $url[0] ) {
			return $url;
		}
		if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) {
			return sanitize_text_field( $url );
		}
		if ( preg_match( '/^(mailto|tel|sms):/i', $url ) ) {
			$clean = esc_url_raw( $url, array( 'mailto', 'tel', 'sms', 'http', 'https' ) );
			return is_string( $clean ) ? $clean : '';
		}
		return esc_url_raw( $url );
	}

	/**
	 * @param string               $message Message.
	 * @param string               $tab     Tab.
	 * @param array<string,string> $extra   Extra query args.
	 */
	private function redirect( $message, $tab = 'texts', $extra = array() ) {
		wp_safe_redirect(
			add_query_arg(
				array_merge(
					array(
						'page'         => 'iacce-editor',
						'tab'          => $tab,
						'iacce_notice' => rawurlencode( $message ),
					),
					$extra
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}
