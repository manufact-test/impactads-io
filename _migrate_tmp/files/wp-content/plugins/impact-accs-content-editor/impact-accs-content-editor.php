<?php
/**
 * Plugin Name:       Impact.accs Content Editor
 * Description:       Админка для безопасного редактирования текстов и ссылок на сайте impact.accs (EN/RU).
 * Version:           1.5.4
 * Author:            Impact
 * Requires at least: 5.8
 * Requires PHP:      7.4
 *
 * @package ImpactAccsContentEditor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IACCE_VERSION', '1.5.4' );
define( 'IACCE_FILE', __FILE__ );
define( 'IACCE_DIR', plugin_dir_path( __FILE__ ) );
define( 'IACCE_URL', plugin_dir_url( __FILE__ ) );
define( 'IACCE_OPTION_REGISTRY', 'iacce_text_registry' );
define( 'IACCE_OPTION_OVERRIDES', 'iacce_text_overrides' );
define( 'IACCE_OPTION_LINKS', 'iacce_link_overrides' );
define( 'IACCE_OPTION_BACKUPS', 'iacce_backup_index' );

require_once IACCE_DIR . 'includes/class-scanner.php';
require_once IACCE_DIR . 'includes/class-blocks.php';
require_once IACCE_DIR . 'includes/class-applier.php';
require_once IACCE_DIR . 'includes/class-backup.php';
require_once IACCE_DIR . 'includes/class-admin.php';

/**
 * Bootstrap.
 */
function iacce_init() {
	IACCE_Admin::instance();
	IACCE_Applier::instance();
}
add_action( 'plugins_loaded', 'iacce_init' );

register_activation_hook( __FILE__, 'iacce_activate' );

/**
 * Activation: backup + defer heavy scan.
 */
function iacce_activate() {
	IACCE_Backup::create_snapshot( 'before-content-editor-v' . IACCE_VERSION );

	if ( false === get_option( IACCE_OPTION_REGISTRY, false ) ) {
		update_option( IACCE_OPTION_REGISTRY, array( 'texts' => array(), 'links' => array(), 'pending_scan' => 1 ), false );
	}
	if ( false === get_option( IACCE_OPTION_OVERRIDES, false ) ) {
		update_option( IACCE_OPTION_OVERRIDES, array(), false );
	}
	if ( false === get_option( IACCE_OPTION_LINKS, false ) ) {
		update_option( IACCE_OPTION_LINKS, array(), false );
	}
}

/**
 * Run initial scan once in admin.
 */
function iacce_maybe_initial_scan() {
	if ( ! is_admin() || ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$registry = get_option( IACCE_OPTION_REGISTRY, array() );
	if ( empty( $registry['pending_scan'] ) ) {
		return;
	}
	$registry = IACCE_Scanner::build_registry();
	unset( $registry['pending_scan'] );
	update_option( IACCE_OPTION_REGISTRY, $registry, false );
}
add_action( 'admin_init', 'iacce_maybe_initial_scan', 5 );
