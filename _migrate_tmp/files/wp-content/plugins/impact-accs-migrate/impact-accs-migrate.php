<?php
/**
 * Plugin Name:       Impact Site Migrate
 * Description:       Полный перенос WordPress-сайта 1:1 — база данных, плагины, темы, uploads. Экспорт на текущем сайте, импорт на новом домене.
 * Version:           1.0.2
 * Author:            Impact
 * Requires at least: 5.8
 * Requires PHP:      7.4
 *
 * @package ImpactAccsMigrate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IASM_VERSION', '1.0.2' );
define( 'IASM_FILE', __FILE__ );
define( 'IASM_DIR', plugin_dir_path( __FILE__ ) );
define( 'IASM_URL', plugin_dir_url( __FILE__ ) );
define( 'IASM_PACKAGES_DIR', IASM_DIR . 'packages/' );
define( 'IASM_PACKAGE_EXT', '.iamigrate.zip' );

require_once IASM_DIR . 'includes/class-package.php';
require_once IASM_DIR . 'includes/class-database.php';
require_once IASM_DIR . 'includes/class-replace.php';
require_once IASM_DIR . 'includes/class-files.php';
require_once IASM_DIR . 'includes/class-exporter.php';
require_once IASM_DIR . 'includes/class-importer.php';
require_once IASM_DIR . 'includes/class-admin.php';

/**
 * Bootstrap.
 */
function iasm_init() {
	IASM_Admin::instance();
}
add_action( 'plugins_loaded', 'iasm_init' );

register_activation_hook(
	IASM_FILE,
	static function () {
		if ( ! is_dir( IASM_PACKAGES_DIR ) ) {
			wp_mkdir_p( IASM_PACKAGES_DIR );
		}
		$htaccess = IASM_PACKAGES_DIR . '.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			file_put_contents( $htaccess, "Deny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
		$index = IASM_PACKAGES_DIR . 'index.php';
		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, "<?php\n// Silence.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
	}
);
