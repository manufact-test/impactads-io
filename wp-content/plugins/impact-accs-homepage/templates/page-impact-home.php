<?php
/**
 * Template Name: Impact.accs Home (1:1)
 * Template Post Type: page
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'show_admin_bar', '__return_false' );

IAH_Homepage::render_document();
