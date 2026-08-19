<?php
/**
 * Admin UI.
 *
 * @package ImpactAccsMigrate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migration admin screens.
 */
class IASM_Admin {

	/** @var IASM_Admin|null */
	private static $instance = null;

	/**
	 * @return IASM_Admin
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
		add_action( 'admin_post_iasm_export', array( $this, 'handle_export' ) );
		add_action( 'admin_post_iasm_import_upload', array( $this, 'handle_import_upload' ) );
		add_action( 'admin_post_iasm_import_from_server', array( $this, 'handle_import_from_server' ) );
		add_action( 'admin_post_iasm_import_run', array( $this, 'handle_import_run' ) );
		add_action( 'admin_post_iasm_download', array( $this, 'handle_download' ) );
	}

	public function register_menu() {
		add_management_page(
			'Перенос сайта',
			'Перенос сайта',
			'export',
			'iasm-migrate',
			array( $this, 'render_page' )
		);
	}

	/**
	 * @param string $hook Hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'tools_page_iasm-migrate' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'iasm-admin', IASM_URL . 'assets/admin.css', array(), IASM_VERSION );
		wp_enqueue_script( 'iasm-admin', IASM_URL . 'assets/admin.js', array(), IASM_VERSION, true );
	}

	public function render_page() {
		if ( ! current_user_can( 'export' ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'impact-accs-migrate' ) );
		}

		$last      = IASM_Exporter::last_job();
		$pending   = IASM_Importer::pending_job();
		$notice    = isset( $_GET['iasm_notice'] ) ? sanitize_text_field( wp_unslash( $_GET['iasm_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$error     = isset( $_GET['iasm_error'] ) ? sanitize_text_field( wp_unslash( $_GET['iasm_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$on_server = IASM_Importer::server_packages();
		$upload_max = ini_get( 'upload_max_filesize' );
		$post_max   = ini_get( 'post_max_size' );
		?>
		<div class="wrap iasm-wrap">
			<h1>Перенос сайта Impact</h1>
			<p class="iasm-intro">Создайте пакет на <strong>старом</strong> сайте и восстановите его на <strong>новом</strong> домене — база, плагины, темы, uploads, скрипты.</p>

			<?php if ( $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
			<?php endif; ?>
			<?php if ( $error ) : ?>
				<div class="notice notice-error is-dismissible"><p><?php echo esc_html( $error ); ?></p></div>
			<?php endif; ?>

			<div class="iasm-grid">
				<section class="iasm-panel">
					<h2>1. Экспорт (текущий сайт)</h2>
					<p>Соберёт <code>database.sql</code> + весь <code>wp-content</code> в один файл <code>.iamigrate.zip</code>.</p>
					<ul class="iasm-list">
						<li>Плагины (impact-accs-* и все остальные)</li>
						<li>Темы и uploads</li>
						<li>База данных WordPress</li>
						<li>.htaccess (если доступен)</li>
					</ul>
					<p class="iasm-warn">Экспорт может занять несколько минут. Не закрывайте вкладку.</p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'iasm_export' ); ?>
						<input type="hidden" name="action" value="iasm_export" />
						<button type="submit" class="button button-primary button-hero" id="iasm-export-btn">Создать пакет переноса</button>
					</form>
					<?php if ( ! empty( $last['done'] ) && ! empty( $last['path'] ) && file_exists( $last['path'] ) ) : ?>
						<p class="iasm-success">
							Последний пакет: <strong><?php echo esc_html( size_format( (int) ( $last['size'] ?? 0 ) ) ); ?></strong>
							· <?php echo esc_html( (string) ( $last['manifest']['site_url'] ?? '' ) ); ?>
							<br>
							<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=iasm_download' ), 'iasm_download' ) ); ?>">Скачать .iamigrate.zip</a>
						</p>
					<?php endif; ?>
				</section>

				<section class="iasm-panel">
					<h2>2. Импорт (новый сайт / домен)</h2>
					<ol class="iasm-steps">
						<li>Установите чистый WordPress на новом хостинге</li>
						<li>Установите этот же плагин <strong>Impact Site Migrate</strong></li>
						<li>Загрузите файл пакета и нажмите «Восстановить»</li>
					</ol>
					<p class="iasm-warn"><strong>Внимание:</strong> импорт заменит базу и файлы на этом сайте. Сделайте бекап, если здесь уже что-то было.</p>

					<p class="iasm-hint">Лимит PHP: <code>upload_max_filesize=<?php echo esc_html( $upload_max ); ?></code>, <code>post_max_size=<?php echo esc_html( $post_max ); ?></code>. Если zip больше — залейте через FTP (см. ниже).</p>

					<form id="iasm-import-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'iasm_import_upload' ); ?>
						<input type="hidden" name="action" value="iasm_import_upload" />
						<label class="iasm-file-label">
							<span>Файл пакета (.iamigrate.zip)</span>
							<input type="file" name="package" id="iasm-package-file" accept=".zip,.iamigrate.zip" required />
						</label>
						<button type="submit" class="button button-primary" id="iasm-upload-btn">Загрузить пакет</button>
						<p class="iasm-hint">Не закрывайте вкладку — распаковка может занять несколько минут.</p>
					</form>

					<?php if ( ! empty( $on_server ) ) : ?>
						<div class="iasm-ftp-box">
							<h3>Или пакет уже на сервере (FTP)</h3>
							<p>Залейте <code>.iamigrate.zip</code> в:<br><code>wp-content/plugins/impact-accs-migrate/packages/</code></p>
							<?php foreach ( $on_server as $pkg_path ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:8px 0">
									<?php wp_nonce_field( 'iasm_import_from_server' ); ?>
									<input type="hidden" name="action" value="iasm_import_from_server" />
									<input type="hidden" name="package_path" value="<?php echo esc_attr( basename( $pkg_path ) ); ?>" />
									<button type="submit" class="button">Использовать <?php echo esc_html( basename( $pkg_path ) ); ?> (<?php echo esc_html( size_format( (int) filesize( $pkg_path ) ) ); ?>)</button>
								</form>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<details class="iasm-ftp-help">
							<summary>Файл слишком большой? Загрузка через FTP</summary>
							<ol>
								<li>Залейте <code>.iamigrate.zip</code> в <code>wp-content/plugins/impact-accs-migrate/packages/</code></li>
								<li>Обновите эту страницу — появится кнопка «Использовать …»</li>
							</ol>
						</details>
					<?php endif; ?>

					<?php if ( ! empty( $pending['ready'] ) && ! empty( $pending['manifest'] ) ) : ?>
						<div class="iasm-import-ready" id="iasm-import-ready">
							<h3>Пакет загружен</h3>
							<p>Источник: <code><?php echo esc_html( (string) ( $pending['manifest']['site_url'] ?? '' ) ); ?></code></p>
							<p>Новый URL: <code><?php echo esc_html( untrailingslashit( home_url() ) ); ?></code></p>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<?php wp_nonce_field( 'iasm_import_run' ); ?>
								<input type="hidden" name="action" value="iasm_import_run" />
								<label>Старый URL
									<input type="url" class="large-text" name="old_url" value="<?php echo esc_attr( untrailingslashit( (string) ( $pending['manifest']['site_url_raw'] ?? $pending['manifest']['site_url'] ?? '' ) ) ); ?>" />
								</label>
								<label>Новый URL
									<input type="url" class="large-text" name="new_url" value="<?php echo esc_attr( untrailingslashit( home_url() ) ); ?>" />
								</label>
								<button type="submit" class="button button-primary button-hero" onclick="return confirm('Импорт заменит базу и wp-content. Продолжить?');">Восстановить сайт 1:1</button>
							</form>
						</div>
					<?php endif; ?>
				</section>
			</div>

			<details class="iasm-faq">
				<summary>Частые вопросы</summary>
				<dl>
					<dt>Ошибка «Failed to fetch»?</dt>
					<dd>Файл слишком большой для браузера. Обновите плагин до 1.0.1 или залейте zip через FTP в <code>packages/</code>.</dd>
					<dt>Нужно ли копировать wp-config.php?</dt>
					<dd>Нет. Оставьте <code>wp-config.php</code> с данными БД <strong>нового</strong> хостинга.</dd>
					<dt>После импорта не пускает в админку?</dt>
					<dd>Войдите логином/паролем <strong>со старого</strong> сайта.</dd>
				</dl>
			</details>
		</div>
		<?php
	}

	public function handle_export() {
		if ( ! current_user_can( 'export' ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'impact-accs-migrate' ) );
		}
		check_admin_referer( 'iasm_export' );
		@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@ini_set( 'memory_limit', '512M' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		$result = IASM_Exporter::start();
		if ( is_wp_error( $result ) ) {
			$this->redirect_notice( '', $result->get_error_message() );
		}

		$size = size_format( (int) ( $result['size'] ?? 0 ) );
		$this->redirect_notice( 'Пакет создан (' . $size . '). Нажмите «Скачать .iamigrate.zip» ниже.' );
	}

	public function handle_import_upload() {
		if ( ! current_user_can( 'export' ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'impact-accs-migrate' ) );
		}
		check_admin_referer( 'iasm_import_upload' );
		@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( empty( $_FILES['package'] ) ) {
			$this->redirect_notice( '', 'Файл не выбран.' );
		}

		$result = IASM_Importer::upload( $_FILES['package'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( is_wp_error( $result ) ) {
			$this->redirect_notice( '', $result->get_error_message() );
		}

		$this->redirect_notice( 'Пакет загружен. Проверьте URL и нажмите «Восстановить сайт 1:1».' );
	}

	public function handle_import_from_server() {
		if ( ! current_user_can( 'export' ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'impact-accs-migrate' ) );
		}
		check_admin_referer( 'iasm_import_from_server' );
		@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		$name = isset( $_POST['package_path'] ) ? sanitize_file_name( wp_unslash( $_POST['package_path'] ) ) : '';
		if ( '' === $name || preg_match( '/[\\\\\\/]/', $name ) ) {
			$this->redirect_notice( '', 'Некорректное имя файла.' );
		}

		$path = IASM_PACKAGES_DIR . $name;
		if ( ! is_readable( $path ) ) {
			$this->redirect_notice( '', 'Файл не найден в packages/.' );
		}

		$result = IASM_Importer::upload_from_path( $path );
		if ( is_wp_error( $result ) ) {
			$this->redirect_notice( '', $result->get_error_message() );
		}

		$this->redirect_notice( 'Пакет с сервера принят. Нажмите «Восстановить сайт 1:1».' );
	}

	public function handle_import_run() {
		if ( ! current_user_can( 'export' ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'impact-accs-migrate' ) );
		}
		check_admin_referer( 'iasm_import_run' );
		@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		$old = isset( $_POST['old_url'] ) ? esc_url_raw( wp_unslash( $_POST['old_url'] ) ) : '';
		$new = isset( $_POST['new_url'] ) ? esc_url_raw( wp_unslash( $_POST['new_url'] ) ) : '';

		$result = IASM_Importer::run( $old, $new );
		if ( is_wp_error( $result ) ) {
			$this->redirect_notice( '', $result->get_error_message() );
		}

		$this->redirect_notice(
			sprintf(
				'Готово! %s → %s. Войдите логином со старого сайта.',
				$result['old_url'] ?? '',
				$result['new_url'] ?? ''
			)
		);
	}

	/**
	 * @param string $notice Success message.
	 * @param string $error  Error message.
	 */
	private function redirect_notice( $notice = '', $error = '' ) {
		$args = array( 'page' => 'iasm-migrate' );
		if ( '' !== $notice ) {
			$args['iasm_notice'] = rawurlencode( $notice );
		}
		if ( '' !== $error ) {
			$args['iasm_error'] = rawurlencode( $error );
		}
		wp_safe_redirect( add_query_arg( $args, admin_url( 'tools.php' ) ) );
		exit;
	}

	public function handle_download() {
		if ( ! current_user_can( 'export' ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'impact-accs-migrate' ) );
		}
		check_admin_referer( 'iasm_download' );

		$job = IASM_Exporter::last_job();
		if ( empty( $job['path'] ) || ! file_exists( $job['path'] ) ) {
			wp_die( esc_html__( 'Файл пакета не найден. Создайте экспорт заново.', 'impact-accs-migrate' ) );
		}

		$path = (string) $job['path'];
		$name = 'impact-site-' . gmdate( 'Y-m-d' ) . IASM_PACKAGE_EXT;

		nocache_headers();
		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="' . $name . '"' );
		header( 'Content-Length: ' . filesize( $path ) );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		readfile( $path );
		exit;
	}
}
