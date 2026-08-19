<?php
/**
 * Impact.accs 404 template.
 *
 * @package ImpactAccsChrome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

status_header( 404 );
nocache_headers();

get_header();

if ( class_exists( 'IAC_Chrome' ) ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo IAC_Chrome::instance()->render_not_found_page();
}

get_footer();
