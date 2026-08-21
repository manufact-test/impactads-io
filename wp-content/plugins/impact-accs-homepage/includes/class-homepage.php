<?php
/**
 * Homepage installer + renderer helpers.
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 */
class IAH_Homepage {

	/**
	 * Singleton.
	 *
	 * @var IAH_Homepage|null
	 */
	private static $instance = null;

	/**
	 * Instance getter.
	 *
	 * @return IAH_Homepage
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
		add_filter( 'theme_page_templates', array( $this, 'register_template' ) );
		add_filter( 'template_include', array( $this, 'load_template' ), 99 );
		add_action( 'template_redirect', array( $this, 'maybe_render_front_page' ), -999 );
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );

		add_filter( 'iac_render_chrome', array( $this, 'disable_chrome' ), 100 );
		add_filter( 'iap_render_preloader', array( $this, 'disable_preloader' ), 100 );
	}

	/**
	 * Serve mirrored homepage at site root regardless of Reading settings.
	 */
	public function maybe_render_front_page() {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}
		if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		if ( ! self::is_home_request() ) {
			return;
		}

		self::send_front_page_nocache_headers();
		self::render_document();
	}

	/**
	 * True for site root even when rewrite rules / Reading settings are temporarily wrong.
	 *
	 * @return bool
	 */
	private static function is_home_request() {
		if ( is_front_page() ) {
			return true;
		}

		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! is_string( $uri ) || '' === $uri ) {
			return false;
		}

		$path = strtok( $uri, '?' );
		return '/' === $path || '' === $path;
	}

	/**
	 * Never cache mirrored homepage at CDN/browser (stale cache served Hello world for RU).
	 */
	private static function send_front_page_nocache_headers() {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}

		if ( headers_sent() ) {
			return;
		}

		nocache_headers();
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private' );
		header( 'CDN-Cache-Control: no-store' );
		header( 'X-LiteSpeed-Cache-Control: no-cache' );
		header( 'X-LiteSpeed-Tag: iah_home_nocache' );
	}

	/**
	 * Register template label in page editor.
	 *
	 * @param array<string, string> $templates Templates.
	 * @return array<string, string>
	 */
	public function register_template( $templates ) {
		$templates[ IAH_TEMPLATE ] = 'Impact.accs Home (1:1)';
		return $templates;
	}

	/**
	 * Load plugin template for impact home page.
	 *
	 * @param string $template Current template.
	 * @return string
	 */
	public function load_template( $template ) {
		if ( ! is_page() ) {
			return $template;
		}

		$slug = get_page_template_slug( get_queried_object_id() );
		if ( IAH_TEMPLATE !== $slug ) {
			return $template;
		}

		$plugin_template = IAH_DIR . 'templates/' . IAH_TEMPLATE;
		if ( is_readable( $plugin_template ) ) {
			return $plugin_template;
		}

		return $template;
	}

	/**
	 * Is current request the mirrored homepage?
	 *
	 * @return bool
	 */
	public static function is_home_page() {
		if ( is_front_page() ) {
			return true;
		}

		$page_id = (int) get_option( 'iah_page_id', 0 );
		if ( $page_id && get_queried_object_id() === $page_id ) {
			return true;
		}

		return is_page() && IAH_TEMPLATE === get_page_template_slug( get_queried_object_id() );
	}

	/**
	 * Is a given page ID the mirrored homepage?
	 *
	 * @param int $page_id Page ID.
	 * @return bool
	 */
	public static function is_home_page_id( $page_id ) {
		$iah_id = (int) get_option( 'iah_page_id', 0 );
		if ( $iah_id && $iah_id === (int) $page_id ) {
			return true;
		}

		return IAH_TEMPLATE === get_page_template_slug( $page_id );
	}

	/**
	 * Disable chrome plugin overlay on mirrored page.
	 *
	 * @param bool $render Render flag.
	 * @return bool
	 */
	public function disable_chrome( $render ) {
		if ( self::is_home_page() ) {
			return false;
		}
		return $render;
	}

	/**
	 * Disable separate preloader — mirrored HTML includes its own.
	 *
	 * @param bool $render Render flag.
	 * @return bool
	 */
	public function disable_preloader( $render ) {
		if ( self::is_home_page() ) {
			return false;
		}
		return $render;
	}

	/**
	 * Admin notice with link to created page.
	 */
	public function admin_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! get_transient( 'iah_activation_notice' ) ) {
			return;
		}
		delete_transient( 'iah_activation_notice' );

		$page_id = (int) get_option( 'iah_page_id', 0 );
		if ( ! $page_id ) {
			return;
		}

		$url = get_permalink( $page_id );
		if ( ! $url ) {
			return;
		}

		echo '<div class="notice notice-success is-dismissible"><p>';
		echo esc_html__( 'Impact.accs Homepage: страница создана.', 'impact-accs-homepage' );
		echo ' <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Открыть Impact Home', 'impact-accs-homepage' ) . '</a>';
		echo ' — ' . esc_html__( 'Настройки → Чтение → статическая главная, чтобы назначить её home.', 'impact-accs-homepage' );
		echo '</p></div>';
	}

	/**
	 * Root-relative asset prefix (must match mirrored JS chunks).
	 *
	 * @return string
	 */
	public static function asset_prefix() {
		$plugin_path = wp_parse_url( IAH_URL, PHP_URL_PATH );
		if ( ! is_string( $plugin_path ) ) {
			$plugin_path = '/wp-content/plugins/impact-accs-homepage/';
		}
		return trailingslashit( untrailingslashit( $plugin_path ) . '/assets/site' );
	}

	/**
	 * Canonical front URL (cache-busted — Hostinger CDN poisons bare "/").
	 *
	 * @return string
	 */
	public static function front_url() {
		return home_url( '/?_lc=2' );
	}

	public static function waitlist_click_guard_script() {
		$url = class_exists( 'IAC_Application_Page' ) ? IAC_Application_Page::url() : home_url( '/application/' );
		$url = esc_js( $url );
		return '<script>(function(){var U="' . $url . '";function hit(t){if(!t||!t.closest)return false;var hdr=t.closest("header");if(!hdr)return false;var wrap=t.closest(\'header [class*="scale-95"], header .pointer-events-auto.absolute.top-0.right-0\');if(wrap&&/request access|get access|запросить доступ|получить доступ|связаться/i.test((wrap.textContent||"").replace(/\\s+/g," ").trim()))return true;var el=t.closest("header button, button[data-slot=button]");if(!el||!hdr.contains(el))return false;return /request access|get access|запросить доступ|получить доступ|связаться/i.test((el.textContent||"").replace(/\\s+/g," ").trim())}function stop(e){if(!hit(e.target))return;e.preventDefault();e.stopPropagation();if(e.stopImmediatePropagation)e.stopImmediatePropagation();window.location.href=U}document.addEventListener("pointerdown",stop,true);document.addEventListener("click",stop,true);window.__iahOpenWaitlist=function(){window.location.href=U}})();</script>';
	}

	/**
	 * Rewrite legacy ?contact=true links to /contact/ (React menu + footer).
	 *
	 * @return string
	 */
	public static function contact_link_patch_script() {
		$url = class_exists( 'IAC_Contact_Page' ) ? IAC_Contact_Page::url() : home_url( '/contact/' );
		$url = esc_js( $url );
		return '<script>(function(){var U="' . $url . '";function isContactHref(h){if(!h)return false;h=String(h);return h.indexOf("contact=true")!==-1||h==="/contact"||h==="/contact/"}function patch(){document.querySelectorAll("a[href]").forEach(function(a){var h=a.getAttribute("href")||"";if(isContactHref(h))a.setAttribute("href",U)})}function go(e){var a=e.target.closest&&e.target.closest("a[href]");if(!a||!isContactHref(a.getAttribute("href")||""))return;e.preventDefault();e.stopPropagation();if(e.stopImmediatePropagation)e.stopImmediatePropagation();window.location.href=U}patch();document.addEventListener("DOMContentLoaded",patch);document.addEventListener("click",go,true);document.addEventListener("pointerdown",go,true);if(window.MutationObserver){new MutationObserver(patch).observe(document.documentElement,{childList:true,subtree:true,attributes:true,attributeFilter:["href"]})}})();</script>';
	}

	/**
	 * Mobile menu: force full-page navigation on first tap (Next.js Link SPA breaks on WP mirror).
	 *
	 * @return string
	 */
	public static function mobile_menu_nav_script() {
		$about    = esc_js( class_exists( 'IAC_About_Page' ) ? IAC_About_Page::url() : home_url( '/about/' ) );
		$blog     = esc_js( class_exists( 'IAC_Blog_Page' ) ? IAC_Blog_Page::url() : home_url( '/blog/' ) );
		$contact  = esc_js( class_exists( 'IAC_Contact_Page' ) ? IAC_Contact_Page::url() : home_url( '/contact/' ) );
		$app      = esc_js( class_exists( 'IAC_Application_Page' ) ? IAC_Application_Page::url() : home_url( '/application/' ) );
		$agency   = esc_js( class_exists( 'IAC_Feature_Page' ) ? IAC_Feature_Page::urls_for_js()['agency'] : home_url( '/accounts/agency-accounts/' ) );
		$platform = esc_js( class_exists( 'IAC_Feature_Page' ) ? IAC_Feature_Page::urls_for_js()['platform'] : home_url( '/accounts/platform-access/' ) );
		$team     = esc_js( class_exists( 'IAC_Feature_Page' ) ? IAC_Feature_Page::urls_for_js()['team'] : home_url( '/accounts/team-supply/' ) );

		return '<script>(function(){var R={"/about":"' . $about . '","/blog":"' . $blog . '","/blog/":"' . $blog . '","?contact=true":"' . $contact . '","/contact":"' . $contact . '","/contact/":"' . $contact . '","?waitlist=true":"' . $app . '","/application":"' . $app . '","/application/":"' . $app . '","/accounts/agency-accounts":"' . $agency . '","/accounts/agency-accounts/":"' . $agency . '","/accounts/platform-access":"' . $platform . '","/accounts/platform-access/":"' . $platform . '","/accounts/team-supply":"' . $team . '","/accounts/team-supply/":"' . $team . '"};var L={"about":"' . $about . '","о нас":"' . $about . '","blog":"' . $blog . '","блог":"' . $blog . '","contact":"' . $contact . '","контакты":"' . $contact . '","request access":"' . $app . '","get access":"' . $app . '","запросить доступ":"' . $app . '","получить доступ":"' . $app . '","связаться":"' . $app . '","platform access":"' . $platform . '","платформа доступ":"' . $platform . '","agency accounts":"' . $agency . '","агентские аккаунты":"' . $agency . '","team supply":"' . $team . '","командные поставки":"' . $team . '"};function norm(h){if(!h)return"";try{if(h.indexOf("http")===0){var u=new URL(h);h=u.pathname+u.search}}catch(e){}var q=h.split("?")[0].split("#")[0];return q.replace(/\\/+$/, "")||"/"}function openMenu(){var m=document.querySelector("[data-main-menu]");if(!m)return false;var o=m.closest(".fixed.inset-0");if(!o)return true;var d=o.style.display||window.getComputedStyle(o).display;return d&&d!=="none"}function resolve(a){var h=a.getAttribute("href")||"";var label=(a.textContent||"").replace(/\\s+/g," ").trim().toLowerCase();if(L[label])return L[label];var key=norm(h);if(R[key])return R[key];if(h.indexOf("contact")!==-1)return"' . $contact . '";if(h.indexOf("waitlist")!==-1)return"' . $app . '";if(/^https?:\\/\\//i.test(h)||h.indexOf("/")===0)return h;return""}function go(e){if(window.matchMedia("(min-width:1024px)").matches)return;if(!openMenu())return;var m=document.querySelector("[data-main-menu]");if(!m)return;var a=e.target&&e.target.closest?e.target.closest("a[href]"):null;if(!a||!m.contains(a))return;if(a.closest(".iac-lang-switch,.notranslate"))return;var url=resolve(a);if(!url)return;e.preventDefault();e.stopPropagation();if(e.stopImmediatePropagation)e.stopImmediatePropagation();window.location.href=url}document.addEventListener("pointerdown",go,true);document.addEventListener("click",go,true)})();</script>';
	}

	/**
	 * Homepage: redirect script config only (no modal).
	 *
	 * @return string
	 */
	public static function chrome_waitlist_snippet() {
		if ( ! class_exists( 'IAC_Chrome' ) || ! class_exists( 'IAC_Application_Page' ) ) {
			return '';
		}

		$out = '';

		if ( method_exists( 'IAC_Chrome', 'instance' ) ) {
			$footer_markup = IAC_Chrome::instance()->render_template( 'footer.html' );
			if ( is_string( $footer_markup ) && '' !== $footer_markup ) {
				$out .= '<script>window.__iahChromeFooter=' . wp_json_encode( $footer_markup ) . ';</script>';
			}
		}

		$js = esc_url( IAC_URL . 'assets/js/waitlist-home.js?v=' . rawurlencode( IAH_VERSION ) );
		$out .= '<script src="' . $js . '"></script>';

		return $out;
	}

	/**
	 * Inline script: rewrite root asset paths before Next.js chunks load.
	 *
	 * @param string $prefix Plugin assets/site prefix.
	 * @return string
	 */
	public static function asset_rewrite_script( $prefix ) {
		$base = esc_js( $prefix );
		return '<script>(function(){var B="' . $base . '",LEG=["https://cu59725-wordpress-9tad1.tw1.ru","https://cu59725-wordpress-9vuvh.tw1.ru","http://cu59725-wordpress-9tad1.tw1.ru","http://cu59725-wordpress-9vuvh.tw1.ru"],R=["/models/","/textures/","/audio/","/sequences/","/basis/","/draco/","/assets/"];function rw(u){if(typeof u!=="string")return u;var j;for(j=0;j<LEG.length;j++){if(u.indexOf(LEG[j])===0)return location.origin+u.slice(LEG[j].length)}var p=u,a=false;try{if(u.indexOf("http")===0){var o=location.origin;if(u.indexOf(o)===0){p=u.slice(o.length);a=true}else return u}}catch(e){}if(p.indexOf(B)===0)return(a?location.origin:"")+p;if(p.indexOf("/wp-content/plugins/impact-accs-homepage/assets/site/")===0)return(a?location.origin:"")+p;for(j=0;j<R.length;j++){if(p.indexOf(R[j])===0)return(a?location.origin:"")+B+p.slice(1)}return u}var f=window.fetch;if(f)window.fetch=function(i,n){if(typeof i==="string")i=rw(i);else if(i&&i.url)i=new Request(rw(i.url),i);return f.call(this,i,n)};var X=XMLHttpRequest.prototype.open;XMLHttpRequest.prototype.open=function(){arguments[1]=rw(arguments[1]);return X.apply(this,arguments)};var A=window.Audio;if(A){window.Audio=function(s){var a=new A();if(s)a.src=rw(s);return a};window.Audio.prototype=A.prototype}var ps=Object.getOwnPropertyDescriptor(HTMLScriptElement.prototype,"src");if(ps&&ps.set)Object.defineProperty(HTMLScriptElement.prototype,"src",{set:function(v){ps.set.call(this,rw(v))},get:ps.get});var pi=Object.getOwnPropertyDescriptor(HTMLImageElement.prototype,"src");if(pi&&pi.set)Object.defineProperty(HTMLImageElement.prototype,"src",{set:function(v){pi.set.call(this,rw(v))},get:pi.get});var pg=Object.getOwnPropertyDescriptor(HTMLImageElement.prototype,"srcset");if(pg&&pg.set)Object.defineProperty(HTMLImageElement.prototype,"srcset",{set:function(v){pg.set.call(this,rw(v))},get:pg.get});var W=window.Worker;if(W)window.Worker=function(u,o){return new W(typeof u==="string"?rw(u):u,o)}})();</script>';
	}

	/**
	 * Prevent external scripts from replacing Next.js GSAP (breaks ScrollTrigger).
	 *
	 * @return string
	 */
	public static function gsap_guard_script() {
		return '<script>(function(){var g;try{Object.defineProperty(window,"gsap",{configurable:true,enumerable:true,get:function(){return g},set:function(v){if(g&&g.registerPlugin&&g.scrollTrigger&&v&&v!==g&&!v.scrollTrigger)return;g=v}})}catch(e){}})();</script>';
	}

	/**
	 * Remove SSR chrome injected into mirrored HTML (breaks React hydration).
	 *
	 * @param string $html Document HTML.
	 * @return string
	 */
	private static function strip_ssr_chrome_conflicts( $html ) {
		$html = preg_replace(
			'/<li\b[^>]*\biac-lang-switch-li\b[^>]*>[\s\S]*?<\/li>/i',
			'',
			$html
		);

		$prefix = preg_quote( trim( self::asset_prefix(), '/' ), '#' );
		$html   = preg_replace(
			'#/' . $prefix . '/' . $prefix . '/#',
			'/' . trim( self::asset_prefix(), '/' ) . '/',
			$html
		);

		return $html;
	}

	/**
	 * Full EN→RU map for homepage client i18n (always embedded; applied when cookie is ru).
	 *
	 * @return array<string,string>|object
	 */
	private static function home_i18n_map() {
		if ( class_exists( 'IAH_Home_Js_Localizer' ) ) {
			return IAH_Home_Js_Localizer::map();
		}
		$file = IAH_DIR . 'includes/i18n/ru-home-extra.php';
		$map  = is_readable( $file ) ? require $file : array();
		return is_array( $map ) ? $map : (object) array();
	}

	/**
	 * Exact-match EN→RU labels for homepage (header nav, headings, buttons).
	 *
	 * @return array<string,string>|object
	 */
	private static function home_exact_i18n_map() {
		$file = IAH_DIR . 'includes/i18n/ru-home-exact.php';
		$map  = is_readable( $file ) ? require $file : array();
		return is_array( $map ) ? $map : (object) array();
	}

	/**
	 * Merge iah-home + lang into the mirrored <html> tag (avoid duplicate class/lang attrs).
	 *
	 * @param string $html Document HTML.
	 * @param string $lang Language code.
	 * @return string
	 */
	private static function patch_home_html_tag( $html, $lang ) {
		// The mirrored Next app hydrates the document root. Changing <html>
		// attributes on the server makes the browser DOM differ from the React
		// payload and triggers recoverable error #418. Runtime chrome adds its
		// marker class only after the native loader/hydration has completed.
		return $html;
	}

	/**
	 * Patch mirrored homepage header/footer for chrome parity.
	 *
	 * @param string $html Document HTML.
	 * @return string
	 */
	private static function patch_homepage_chrome( $html ) {
		// Lang switch is added client-side after React hydration (home-chrome.js).
		return self::strip_ssr_chrome_conflicts( $html );
	}

	/**
	 * Server-side full-page cache key for the rendered homepage.
	 *
	 * Keyed by language + plugin versions + content file mtime so any content,
	 * i18n or code change invalidates the cache automatically. CDN/browser still
	 * receive no-store headers, so this never reintroduces cross-language caching.
	 *
	 * @param string $lang Language code.
	 * @param string $file Content file path.
	 * @return string
	 */
	private static function cache_key( $lang, $file ) {
		$iac_ver = defined( 'IAC_VERSION' ) ? IAC_VERSION : '0';
		$mtime   = @filemtime( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return 'iah_home_' . md5( $lang . '|' . IAH_VERSION . '|' . $iac_ver . '|' . $mtime );
	}

	/**
	 * Should this request bypass the server-side homepage cache?
	 *
	 * @return bool
	 */
	private static function cache_bypass() {
		if ( isset( $_GET['iah_nocache'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}
		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Render mirrored HTML document.
	 */
	public static function render_document() {
		$file = IAH_DIR . 'content/index.html';
		if ( ! is_readable( $file ) ) {
			status_header( 500 );
			echo '<!DOCTYPE html><html><body><p>Impact home content missing.</p></body></html>';
			exit;
		}

		$lang      = class_exists( 'IAC_I18n' ) ? IAC_I18n::lang() : 'en';
		$cache_key = self::cache_key( $lang, $file );
		$bypass    = self::cache_bypass();

		if ( ! $bypass ) {
			$cached = get_transient( $cache_key );
			if ( is_string( $cached ) && '' !== $cached ) {
				status_header( 200 );
				nocache_headers();
				if ( ! headers_sent() ) {
					header( 'X-IAH-Cache: HIT' );
				}
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $cached;
				exit;
			}
		}

		$html   = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$prefix = self::asset_prefix();

		$legacy_hosts = array(
			'https://cu59725-wordpress-9tad1.tw1.ru',
			'https://cu59725-wordpress-9vuvh.tw1.ru',
			'http://cu59725-wordpress-9tad1.tw1.ru',
			'http://cu59725-wordpress-9vuvh.tw1.ru',
		);
		$site_host  = wp_parse_url( home_url( '/' ), PHP_URL_SCHEME ) . '://' . wp_parse_url( home_url( '/' ), PHP_URL_HOST );
		$html       = str_replace( $legacy_hosts, $site_host, $html );

		// Path-only prefix — same as JS bundles (/wp-content/plugins/.../assets/site/).
		$html = str_replace( '{{IAH_ASSET_BASE}}', $prefix, $html );

		// Rewrite Next image optimizer URLs to direct static files.
		$html = preg_replace_callback(
			'#/_next/image\?url=([^&"\']+)(?:&[^"\']*)?#',
			static function ( $matches ) use ( $prefix ) {
				$path = urldecode( $matches[1] );
				return $prefix . ltrim( $path, '/' );
			},
			$html
		);

		// Fix any previously broken double-prefixed URLs from older versions.
		$html = preg_replace(
			'#/' . preg_quote( trim( $prefix, '/' ), '#' ) . 'https?://[^/]+' . preg_quote( $prefix, '#' ) . '#',
			$prefix,
			$html
		);

		$home   = esc_url( self::front_url() );
		$html   = str_replace( 'href="/"', 'href="' . $home . '"', $html );
		$iac_url = class_exists( 'IAC_Chrome' ) ? IAC_URL : '';
		$lang    = class_exists( 'IAC_I18n' ) ? IAC_I18n::lang() : 'en';
		if ( $iac_url && class_exists( 'IAC_Feature_Page' ) ) {
			$iac_ver   = defined( 'IAC_VERSION' ) ? IAC_VERSION : '2.3.90';
			$wp_css    = esc_url( $iac_url . 'assets/css/wp-overrides.css?v=' . rawurlencode( $iac_ver ) );
			$tools_css = esc_url( $iac_url . 'assets/css/header-tools.css?v=' . rawurlencode( $iac_ver ) );
			$menu_css  = esc_url( $iac_url . 'assets/css/mobile-menu.css?v=' . rawurlencode( $iac_ver ) );
			$head_css  = '<link rel="stylesheet" href="' . $wp_css . '" />'
				. '<link rel="stylesheet" href="' . $tools_css . '" />'
				. '<link rel="stylesheet" href="' . $menu_css . '" />'
				. '<style id="iah-hide-investors">html.iah-home section:has([data-credits-badge="true"]),html.iah-home section:has([data-credits-col="0"]){display:none!important;visibility:hidden!important;height:0!important;max-height:0!important;overflow:hidden!important;margin:0!important;padding:0!important;pointer-events:none!important;opacity:0!important}</style>'
				. '<script>(function(){function h(){document.querySelectorAll("[data-credits-badge],[data-credits-col]").forEach(function(el){var s=el.closest("section");if(s&&!s.hasAttribute("data-iah-hidden-investors")){s.style.setProperty("display","none","important");s.setAttribute("data-iah-hidden-investors","1");}});}h();document.addEventListener("DOMContentLoaded",h);if(window.MutationObserver){new MutationObserver(h).observe(document.documentElement,{childList:true,subtree:true});}})();</script>';
			$html = preg_replace( '/<\/head>/i', $head_css . '</head>', $html, 1 );
			$html = self::patch_home_html_tag( $html, $lang );
		}

		$legal = array(
			'/legal/privacy-policy'              => $iac_url ? esc_url( $iac_url . 'assets/legal/privacy-policy.pdf' ) : '/legal/privacy-policy',
			'/legal/terms-of-service'            => $iac_url ? esc_url( $iac_url . 'assets/legal/terms-of-service.pdf' ) : '/legal/terms-of-service',
			'/legal/data-processing-addendum'    => $iac_url ? esc_url( $iac_url . 'assets/legal/data-processing-addendum.pdf' ) : '/legal/data-processing-addendum',
			'/features/autonomous-alerts'        => home_url( '/accounts/agency-accounts/' ),
			'/features/conversational-debugging' => home_url( '/accounts/platform-access/' ),
			'/features/coding-agents'            => home_url( '/accounts/team-supply/' ),
			'/accounts/agency-accounts/'         => home_url( '/accounts/agency-accounts/' ),
			'/accounts/platform-access/'         => home_url( '/accounts/platform-access/' ),
			'/accounts/team-supply/'             => home_url( '/accounts/team-supply/' ),
			'?contact=true'                      => class_exists( 'IAC_Contact_Page' ) ? IAC_Contact_Page::url() : home_url( '/contact/' ),
			'?waitlist=true'                     => class_exists( 'IAC_Application_Page' ) ? IAC_Application_Page::url() : home_url( '/application/' ),
			'/about#careers'                     => class_exists( 'IAC_Contact_Page' ) ? IAC_Contact_Page::url() : home_url( '/contact/' ),
		);
		foreach ( $legal as $from => $to ) {
			$html = str_replace( 'href="' . $from . '"', 'href="' . esc_url( $to ) . '"', $html );
		}

		$contact_url = class_exists( 'IAC_Contact_Page' ) ? IAC_Contact_Page::url() : home_url( '/contact/' );
		$html        = preg_replace( '#href="[^"]*\?contact=true[^"]*"#', 'href="' . esc_url( $contact_url ) . '"', $html );

		$favicon = esc_url( IAH_FAVICON_URL );

		$html = preg_replace(
			'#<link rel="(?:shortcut icon|icon|apple-touch-icon)"[^>]*>#',
			'',
			$html
		);
		$favicon_tags  = '<link rel="icon" href="' . $favicon . '" sizes="32x32" type="image/png" />';
		$favicon_tags .= '<link rel="shortcut icon" href="' . $favicon . '" />';
		$favicon_tags .= '<link rel="apple-touch-icon" href="' . $favicon . '" />';
		$html          = preg_replace( '/<head>/i', '<head>' . $favicon_tags, $html, 1 );

		// Never prepend custom nodes to <body>: Next hydrates the entire document
		// and expects its first body child to match the exported React payload.
		$inline  = self::gsap_guard_script();
		// Do not replaceState to bare "/" — Hostinger CDN caches "/" per cookie and may serve stale Hello world.
		$inline .= self::asset_rewrite_script( $prefix );
		$inline .= self::waitlist_click_guard_script();
		$inline .= self::contact_link_patch_script();
		$inline .= self::mobile_menu_nav_script();
		$html    = preg_replace( '/<head>/i', '<head>' . $inline, $html, 1 );


		$bridge = IAH_URL . 'assets/js/wp-bridge.js?v=' . rawurlencode( IAH_VERSION );
		$inject = '<script src="' . esc_url( $bridge ) . '"></script>';
		if ( $iac_url ) {
			$iac_ver   = defined( 'IAC_VERSION' ) ? IAC_VERSION : '2.3.90';
			$footer_js = esc_url( $iac_url . 'assets/js/footer-interactive.js?v=' . rawurlencode( $iac_ver ) );
			$tools_js  = esc_url( $iac_url . 'assets/js/header-tools.js?v=' . rawurlencode( $iac_ver ) );
			$home_js   = esc_url( $iac_url . 'assets/js/home-chrome.js?v=' . rawurlencode( $iac_ver ) );
			$i18n_js   = esc_url( $iac_url . 'assets/js/homepage-i18n.js?v=' . rawurlencode( $iac_ver ) );
			// Do NOT inject chrome gsap.min.js / mobile-menu.js — they overwrite window.gsap
			// and break Next.js ScrollTrigger (scroll city sections). Next bundles its own GSAP.
			$inject   .= '<script src="' . $footer_js . '"></script>';
			$inject   .= '<script src="' . $tools_js . '"></script>';
			$inject   .= '<script src="' . $i18n_js . '"></script>';
			$inject   .= '<script src="' . $home_js . '"></script>';
		}
		if ( false !== stripos( $html, '</body>' ) ) {
			$html = preg_replace( '/<\/body>/i', $inject . '</body>', $html, 1 );
		} else {
			$html .= $inject;
		}

		if ( class_exists( 'IAH_Home_Js_Localizer' ) ) {
			// Do NOT rewrite all chunk <script> tags — localize_js() breaks Turbopack bootstrap (preloader stuck at 0).
			// RU hero/header strings: hero_intercept_script() routes only f7f1/5308/1e7f2 chunks through iah-home-js.
		}

		$html = self::patch_homepage_chrome( $html );
		// No hero_intercept_script — RU chunk intercept breaks React hydration (#418) and 3D scene.
		if ( $iac_url && class_exists( 'IAC_Feature_Page' ) ) {
			$iac_payload = array(
				'homeUrl'          => self::front_url(),
				'aboutUrl'         => class_exists( 'IAC_About_Page' ) ? IAC_About_Page::url() : home_url( '/about/' ),
				'blogUrl'          => class_exists( 'IAC_Blog_Page' ) ? IAC_Blog_Page::url() : home_url( '/blog/' ),
				'contactUrl'       => class_exists( 'IAC_Contact_Page' ) ? IAC_Contact_Page::url() : home_url( '/contact/' ),
				'applicationUrl'   => class_exists( 'IAC_Application_Page' ) ? IAC_Application_Page::url() : home_url( '/application/' ),
				'featureUrls'      => IAC_Feature_Page::urls_for_js(),
				'assetBase'        => self::asset_prefix(),
				'pluginUrl'        => trailingslashit( IAH_URL ),
				'isHome'           => '1',
				'isFront'          => '1',
				'lang'             => $lang,
				'strings'          => class_exists( 'IAC_I18n' ) ? IAC_I18n::js_strings() : array(),
				'htmlReplacements' => self::home_i18n_map(),
				'exactReplacements' => self::home_exact_i18n_map(),
			);
			$iac_data   = wp_json_encode( $iac_payload );
			$head_data  = '<script>var iacData=' . $iac_data . ';</script>';
			$html       = preg_replace( '/<\/head>/i', $head_data . '</head>', $html, 1 );
		}
		if ( class_exists( 'IAC_SEO' ) ) {
			$html = IAC_SEO::patch_homepage_html( $html );
		}

		if ( ! $bypass && is_string( $html ) && '' !== $html ) {
			$ttl = (int) apply_filters( 'iah_home_cache_ttl', HOUR_IN_SECONDS );
			if ( $ttl > 0 ) {
				set_transient( $cache_key, $html, $ttl );
			}
		}

		status_header( 200 );
		nocache_headers();
		if ( ! headers_sent() ) {
			header( 'X-IAH-Cache: ' . ( $bypass ? 'BYPASS' : 'MISS' ) );
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
		exit;
	}
}
