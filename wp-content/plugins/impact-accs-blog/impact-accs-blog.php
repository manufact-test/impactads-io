<?php
/**
 * Plugin Name:       Impact.accs Blog Manager
 * Description:       Создание постов блога из админки — публикация на /blog/ в стиле impact.accs.
 * Version:           1.0.0
 * Author:            Impact
 * Text Domain:       impact-accs-blog
 * Requires at least: 5.8
 * Requires PHP:      7.4
 *
 * @package ImpactAccsBlog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IAB_VERSION', '1.0.0' );
define( 'IAB_FILE', __FILE__ );
define( 'IAB_DIR', plugin_dir_path( __FILE__ ) );
define( 'IAB_URL', plugin_dir_url( __FILE__ ) );

require_once IAB_DIR . 'includes/class-cpt.php';
require_once IAB_DIR . 'includes/class-admin.php';
require_once IAB_DIR . 'includes/class-sync.php';
require_once IAB_DIR . 'includes/class-render.php';
require_once IAB_DIR . 'includes/class-integration.php';

/**
 * Bootstrap plugin.
 */
function iab_init() {
	IAB_CPT::register();
	IAB_Admin::boot();
	IAB_Sync::boot();
	IAB_Integration::boot();
}
add_action( 'plugins_loaded', 'iab_init' );

register_activation_hook(
	__FILE__,
	static function () {
		IAB_CPT::register();
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	static function () {
		flush_rewrite_rules();
	}
);
