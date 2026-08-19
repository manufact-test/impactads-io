<?php
/**
 * Elementor widget: original Request Access block.
 *
 * @package ImpactAccsChrome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor widget.
 */
class IAC_Elementor_Request_Access extends \Elementor\Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'impact_request_access';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Impact Request Access', 'impact-accs-chrome' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	/**
	 * Categories.
	 *
	 * @return array<int,string>
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Render.
	 */
	protected function render() {
		echo do_shortcode( '[impact_request_access]' );
	}
}
