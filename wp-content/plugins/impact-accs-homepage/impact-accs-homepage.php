<?php
/**
 * Plugin Name:       Impact.accs Homepage
 * Plugin URI:        https://cu59725-wordpress-9vuvh.tw1.ru/
 * Description:       Создаёт страницу главной 1:1 как на impact.accs (Next.js, 3D/WebGPU, анимации).
 * Version:           1.5.57
 * Author:            Impact
 * Text Domain:       impact-accs-homepage
 * Requires at least: 5.8
 * Requires PHP:      7.4
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IAH_VERSION', '1.5.57' );
define( 'IAH_FILE', __FILE__ );
define( 'IAH_DIR', plugin_dir_path( __FILE__ ) );
define( 'IAH_URL', plugin_dir_url( __FILE__ ) );
define( 'IAH_PAGE_SLUG', 'impact-home' );
define( 'IAH_TEMPLATE', 'page-impact-home.php' );

$iah_favicon_url = function_exists( 'get_site_icon_url' ) ? get_site_icon_url( 32 ) : '';
if ( ! $iah_favicon_url ) {
	$iah_favicon_url = 'https://impactads.io/wp-content/uploads/2026/06/cropped-IA.png';
}
define( 'IAH_FAVICON_URL', $iah_favicon_url );

require_once IAH_DIR . 'includes/class-homepage.php';
require_once IAH_DIR . 'includes/class-asset-proxy.php';
require_once IAH_DIR . 'includes/class-home-js-localizer.php';
require_once IAH_DIR . 'includes/class-home-native-ru-phase1.php';
require_once IAH_DIR . 'includes/class-home-native-ru-mobile-phase1.php';
require_once IAH_DIR . 'includes/class-home-native-ru-phase2.php';

/**
 * Bootstrap.
 */
function iah_init() {
	IAH_Homepage::instance();
}
add_action( 'plugins_loaded', 'iah_init' );

add_action(
	'init',
	static function () {
		iah_register_rewrites();
	},
	99
);

register_activation_hook( __FILE__, 'iah_activate' );

/**
 * Activation: create page with custom template.
 */
function iah_activate() {
	$page = get_page_by_path( IAH_PAGE_SLUG );
	if ( ! $page ) {
		$page_id = wp_insert_post(
			array(
				'post_title'   => 'Impact Home',
				'post_name'    => IAH_PAGE_SLUG,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			),
			true
		);
		if ( ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', IAH_TEMPLATE );
			update_option( 'iah_page_id', (int) $page_id );
		}
	} else {
		update_post_meta( $page->ID, '_wp_page_template', IAH_TEMPLATE );
		update_option( 'iah_page_id', (int) $page->ID );
	}
	set_transient( 'iah_activation_notice', 1, MINUTE_IN_SECONDS );
	iah_register_rewrites();
	flush_rewrite_rules();
}

/**
 * Register front-controller rewrites.
 */
function iah_register_rewrites() {
	add_rewrite_rule( '^_next/image/?', 'index.php?iah_next_image=1', 'top' );
	add_rewrite_rule( '^models/(.+)$', 'index.php?iah_static=models/$1', 'top' );
	add_rewrite_rule( '^textures/(.+)$', 'index.php?iah_static=textures/$1', 'top' );
	add_rewrite_rule( '^audio/(.+)$', 'index.php?iah_static=audio/$1', 'top' );
	add_rewrite_rule( '^sequences/(.+)$', 'index.php?iah_static=sequences/$1', 'top' );
	add_rewrite_rule( '^basis/(.+)$', 'index.php?iah_static=basis/$1', 'top' );
	add_rewrite_rule( '^draco/(.+)$', 'index.php?iah_static=draco/$1', 'top' );
	add_rewrite_rule( '^assets/(.+)$', 'index.php?iah_static=assets/$1', 'top' );
}

register_deactivation_hook( __FILE__, 'iah_deactivate' );

/**
 * Deactivation cleanup (keep page).
 */
function iah_deactivate() {
	delete_option( 'iah_page_id' );
}
