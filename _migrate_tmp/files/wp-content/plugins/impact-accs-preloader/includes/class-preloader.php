<?php
/**
 * Impact.accs page loader.
 *
 * @package ImpactAccsPreloader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Preloader renderer.
 */
class IAP_Preloader {

	/**
	 * Singleton.
	 *
	 * @var IAP_Preloader|null
	 */
	private static $instance = null;

	/**
	 * Instance getter.
	 *
	 * @return IAP_Preloader
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 5 );
		add_filter( 'language_attributes', array( $this, 'language_attributes' ) );
		add_action( 'wp_head', array( $this, 'render_head_bootstrap' ), 0 );
		add_action( 'wp_body_open', array( $this, 'render_loader' ), 0 );
	}

	/**
	 * Should render preloader?
	 *
	 * @return bool
	 */
	public function should_render() {
		if ( is_admin() || wp_doing_ajax() ) {
			return false;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}
		if ( is_feed() || is_embed() || is_robots() ) {
			return false;
		}
		if ( $this->is_elementor_editor() ) {
			return false;
		}
		if ( class_exists( 'IAH_Homepage' ) && IAH_Homepage::is_home_page() ) {
			return false;
		}
		if ( class_exists( 'IAH_Homepage' ) && is_front_page() ) {
			return false;
		}
		$iah_page = (int) get_option( 'iah_page_id', 0 );
		if ( $iah_page && get_queried_object_id() === $iah_page ) {
			return false;
		}
		return (bool) apply_filters( 'iap_render_preloader', true );
	}

	/**
	 * Elementor editor detection.
	 *
	 * @return bool
	 */
	private function is_elementor_editor() {
		if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}
		if ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$p = \Elementor\Plugin::instance();
			if ( isset( $p->preview ) && method_exists( $p->preview, 'is_preview_mode' ) && $p->preview->is_preview_mode() ) {
				return true;
			}
			if ( isset( $p->editor ) && method_exists( $p->editor, 'is_edit_mode' ) && $p->editor->is_edit_mode() ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Enqueue styles and script.
	 */
	public function enqueue_assets() {
		if ( ! $this->should_render() ) {
			return;
		}

		wp_enqueue_style(
			'impact-accs-preloader-site-1',
			IAP_URL . 'assets/site/_next/static/chunks/e404d10d1b589fe4.css',
			array(),
			IAP_VERSION
		);
		wp_enqueue_style(
			'impact-accs-preloader-site-2',
			IAP_URL . 'assets/site/_next/static/chunks/507126d1be67e5b2.css',
			array( 'impact-accs-preloader-site-1' ),
			IAP_VERSION
		);
		wp_enqueue_style(
			'impact-accs-preloader',
			IAP_URL . 'assets/css/preloader-overrides.css',
			array( 'impact-accs-preloader-site-2' ),
			IAP_VERSION
		);

		wp_enqueue_script(
			'impact-accs-preloader',
			IAP_URL . 'assets/js/preloader.js',
			array(),
			IAP_VERSION,
			true
		);
	}

	/**
	 * Font CSS variables on html (matches original site).
	 *
	 * @param string $output Language attributes.
	 * @return string
	 */
	public function language_attributes( $output ) {
		if ( ! $this->should_render() ) {
			return $output;
		}

		if ( false !== strpos( $output, 'tekosans_992e9a10-module__7AQ5jq__variable' ) ) {
			return $output;
		}

		return ' class="tekosans_992e9a10-module__7AQ5jq__variable spline_sans_fa99ba16-module__B2jdcW__variable eurostile_7a299062-module__JdaVzq__variable jetbrains_mono_7d65b77b-module__VxV-Ta__variable antialiased"' . $output;
	}

	/**
	 * Early class + lock scroll before paint.
	 */
	public function render_head_bootstrap() {
		if ( ! $this->should_render() ) {
			return;
		}
		?>
		<style id="iap-preloader-critical">
		html.iap-preloader-active{background:#010401!important}
		html.iap-preloader-active body{overflow:hidden!important;background:#010401!important;margin:0}
		html.iap-preloader-active body::before{
			content:"";position:fixed;inset:0;z-index:2147483646;background:#010401;pointer-events:none
		}
		#iap-loader{
			position:fixed;inset:0;z-index:2147483647;display:grid;place-items:center;
			overflow:hidden;color:#ff0027;background:#010401
		}
		#iap-loader [data-loader-bg]{position:absolute;inset:0;background:#010401}
		</style>
		<script>
		document.documentElement.classList.add('iap-preloader-active');
		document.documentElement.dataset.muted = 'true';
		</script>
		<?php
	}

	/**
	 * Output loader markup at body open.
	 */
	public function render_loader() {
		if ( ! $this->should_render() ) {
			return;
		}

		$template = IAP_TEMPLATES . 'loader.html';
		if ( ! is_readable( $template ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo file_get_contents( $template );
	}
}
