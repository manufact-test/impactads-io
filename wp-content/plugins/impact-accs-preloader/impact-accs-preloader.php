<?php
/**
 * Plugin Name:       Impact.accs Preloader
 * Plugin URI:        https://cu59725-wordpress-9vuvh.tw1.ru/
 * Description:       Прелоадер impact.accs (Initializing + прогресс) как на оригинальном сайте.
 * Version:           1.0.3
 * Author:            Impact
 * Text Domain:       impact-accs-preloader
 * Requires at least: 5.8
 * Requires PHP:      7.4
 *
 * @package ImpactAccsPreloader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IAP_VERSION', '1.0.3' );
define( 'IAP_FILE', __FILE__ );
define( 'IAP_DIR', plugin_dir_path( __FILE__ ) );
define( 'IAP_URL', plugin_dir_url( __FILE__ ) );
define( 'IAP_TEMPLATES', IAP_DIR . 'templates/' );

require_once IAP_DIR . 'includes/class-preloader.php';

/**
 * Bootstrap plugin.
 */
function iap_init() {
	IAP_Preloader::instance();
}
add_action( 'plugins_loaded', 'iap_init' );
