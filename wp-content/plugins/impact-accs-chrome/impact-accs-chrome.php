<?php
/**
 * Plugin Name:       Impact.accs Chrome for Elementor
 * Plugin URI:        https://cu59725-wordpress-9vuvh.tw1.ru/
 * Description:       1:1 хедер, футер, рамки, скроллбар и Request Access с сайта impact.accs для Hello Elementor + Elementor.
 * Version:           2.4.71
 * Author:            Impact
 * Text Domain:       impact-accs-chrome
 * Requires at least: 5.8
 * Requires PHP:      7.4
 *
 * @package ImpactAccsChrome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IAC_VERSION', '2.4.71' );
define( 'IAC_FILE', __FILE__ );
define( 'IAC_DIR', plugin_dir_path( __FILE__ ) );
define( 'IAC_URL', plugin_dir_url( __FILE__ ) );
define( 'IAC_TEMPLATES', IAC_DIR . 'templates/' );
define( 'IAC_SITE_DIR', IAC_DIR . 'assets/site/' );

require_once IAC_DIR . 'includes/class-application-page.php';
require_once IAC_DIR . 'includes/class-contact-page.php';
require_once IAC_DIR . 'includes/class-blog-page.php';
require_once IAC_DIR . 'includes/class-about-page.php';
require_once IAC_DIR . 'includes/class-feature-page.php';
require_once IAC_DIR . 'includes/class-not-found-page.php';
require_once IAC_DIR . 'includes/class-seo.php';
require_once IAC_DIR . 'includes/class-i18n.php';
require_once IAC_DIR . 'includes/class-chrome.php';

/**
 * Bootstrap plugin.
 */
function iac_init() {
	IAC_Application_Page::ensure_page();
	IAC_Contact_Page::ensure_page();
	IAC_Blog_Page::ensure_pages();
	IAC_About_Page::ensure_page();
	IAC_Application_Page::instance();
	IAC_Contact_Page::instance();
	IAC_Blog_Page::instance();
	IAC_About_Page::instance();
	IAC_Feature_Page::instance();
	IAC_Not_Found_Page::instance();
	IAC_SEO::instance();
	IAC_Chrome::instance();
}
add_action( 'plugins_loaded', 'iac_init' );

/**
 * Account pages + one-time URL migration (needs init).
 */
function iac_init_accounts_pages() {
	IAC_Feature_Page::ensure_pages();
}
add_action( 'init', 'iac_init_accounts_pages', 5 );

/**
 * Register Elementor widget.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
 */
function iac_register_elementor_widgets( $widgets_manager ) {
	require_once IAC_DIR . 'includes/elementor/widget-request-access.php';
	$widgets_manager->register( new IAC_Elementor_Request_Access() );
}
add_action( 'elementor/widgets/register', 'iac_register_elementor_widgets' );

register_activation_hook( __FILE__, 'iac_activate' );

/**
 * Activation hook.
 */
function iac_activate() {
	if ( '' === get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}

	$page_id = IAC_Application_Page::ensure_page();
	if ( $page_id ) {
		set_transient( 'iac_application_page_created', 1, 60 );
	}

	$contact_id = IAC_Contact_Page::ensure_page();
	if ( $contact_id ) {
		set_transient( 'iac_contact_page_created', 1, 60 );
	}

	$blog_id = IAC_Blog_Page::ensure_pages();
	if ( $blog_id ) {
		set_transient( 'iac_blog_pages_created', 1, 60 );
	}

	$about_id = IAC_About_Page::ensure_page();
	if ( $about_id ) {
		set_transient( 'iac_about_page_created', 1, 60 );
	}

	$features_id = IAC_Feature_Page::ensure_pages();
	if ( $features_id ) {
		set_transient( 'iac_feature_pages_created', 1, 60 );
	}

	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'iac_deactivate' );

/**
 * Deactivation hook.
 */
function iac_deactivate() {
	flush_rewrite_rules();
}
