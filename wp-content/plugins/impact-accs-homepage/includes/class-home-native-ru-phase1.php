<?php
/**
 * Homepage native Russian transition layer, phase 1.
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Makes RU authoritative on the homepage and localizes the client-only hero
 * dialogue chunk before React creates its first visible UI state.
 */
class IAH_Home_Native_Ru_Phase1 {

	/** Client-only chunk that owns the animated hero dialogue/alert copy. */
	private const HERO_CHUNK = '5308d2f8d20274da.js';

	/** Direct WordPress endpoint used only for the localized hero chunk. */
	private const ENDPOINT = 'iah-home-native-ru';

	/**
	 * Register the transition layer before the normal homepage bootstrap.
	 */
	public static function boot() {
		add_action( 'plugins_loaded', array( __CLASS__, 'force_home_ru' ), -999 );
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_serve_hero_chunk' ), -998 );
		add_action( 'template_redirect', array( __CLASS__, 'start_document_buffer' ), -1000 );
	}

	/**
	 * Homepage is Russian-only in phase 1, including returning visitors with
	 * stale EN cookies left by the previous language switcher.
	 */
	public static function force_home_ru() {
		if ( ! self::is_home_request() ) {
			return;
		}

		foreach ( array( 'iac_lang4', 'iac_lang3', 'iac_lang' ) as $name ) {
			$_COOKIE[ $name ] = 'ru';
			self::persist_ru_cookie( $name );
		}
	}

	/**
	 * Serve only the client-side hero dialogue chunk through a strict visible
	 * copy whitelist. The original source chunk is never modified on disk.
	 */
	public static function maybe_serve_hero_chunk() {
		if ( ! self::is_native_chunk_request() ) {
			return;
		}

		$path = IAH_DIR . 'assets/site/_next/static/chunks/' . self::HERO_CHUNK;
		if ( ! is_readable( $path ) ) {
			status_header( 404 );
			exit;
		}

		$js = file_get_contents( $path );
		if ( ! is_string( $js ) ) {
			status_header( 500 );
			exit;
		}

		$js = self::normalize_turbopack_chunk_identity( $js );
		$js = self::localize_visible_hero_copy( $js );

		if ( ! headers_sent() ) {
			header( 'Content-Type: application/javascript; charset=utf-8' );
			header( 'Cache-Control: public, max-age=31536000, immutable' );
			header( 'X-IAH-Native-RU: phase1-hero' );
		}

		/* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
		echo $js;
		exit;
	}

	/**
	 * Start before IAH_Homepage::maybe_render_front_page() so the early chunk
	 * interceptor becomes the first script in <head> without changing body DOM.
	 */
	public static function start_document_buffer() {
		if ( ! self::is_home_request() ) {
			return;
		}

		ob_start( array( __CLASS__, 'patch_document' ) );
	}

	/**
	 * Inject the narrow chunk redirect before the mirrored Next.js scripts.
	 *
	 * @param string $html Rendered homepage document.
	 * @return string
	 */
	public static function patch_document( $html ) {
		if ( ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		$script = self::early_interceptor_script();
		if ( '' === $script || false !== strpos( $html, 'data-iah-native-ru-phase1' ) ) {
			return $html;
		}

		return preg_replace( '/<head>/i', '<head>' . $script, $html, 1 );
	}

	/**
	 * Keep Turbopack's logical chunk ID equal to the original static path even
	 * though phase 1 serves the bytes from a WordPress endpoint. Turbopack
	 * explicitly supports a string chunk ID in this metadata position.
	 *
	 * @param string $js Original hero chunk.
	 * @return string
	 */
	private static function normalize_turbopack_chunk_identity( $js ) {
		$dynamic = '"object"==typeof document?document.currentScript:void 0';
		$stable  = wp_json_encode( 'static/chunks/' . self::HERO_CHUNK, JSON_UNESCAPED_SLASHES );
		$offset  = strpos( $js, $dynamic );

		if ( false === $offset || ! is_string( $stable ) ) {
			return $js;
		}

		return substr_replace( $js, $stable, $offset, strlen( $dynamic ) );
	}

	/**
	 * Replace exact quoted UI literals only. No identifiers, state names,
	 * shaders, debug labels, paths or component structure are touched.
	 *
	 * @param string $js Original hero chunk.
	 * @return string
	 */
	private static function localize_visible_hero_copy( $js ) {
		if ( ! class_exists( 'IAH_Home_Js_Localizer' ) ) {
			return $js;
		}

		$map = IAH_Home_Js_Localizer::map();
		if ( empty( $map ) ) {
			return $js;
		}

		foreach ( self::hero_visible_keys() as $en ) {
			if ( ! isset( $map[ $en ] ) || ! is_string( $map[ $en ] ) ) {
				continue;
			}

			$en_json = wp_json_encode( $en, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			$ru_json = wp_json_encode( $map[ $en ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			if ( ! is_string( $en_json ) || ! is_string( $ru_json ) ) {
				continue;
			}

			$js = str_replace( $en_json, $ru_json, $js );
		}

		return $js;
	}

	/**
	 * User-facing literals used by the animated hero dialogue branches.
	 * Deliberately excludes code-like values such as VolumeRequestPending and
	 * AgencyAccounts so translation cannot alter React state or scene logic.
	 *
	 * @return array<int,string>
	 */
	private static function hero_visible_keys() {
		return array(
			'Launch blocked — access needed',
			'Buyer desk needs agency accounts before traffic goes live.',
			'Buyer desk needs agency accounts before traffic goes live. GEO: EU.',
			'Supply stable',
			'Repeat order channel active — terms unchanged.',
			'Volume request — EU',
			'50 accounts · GEO locked. Terms needed before 18:00.',
			'@impact.accs Need EU accounts before launch.',
			'Need EU accounts before launch.',
			' terms and delivery for EU launch',
			'confirm availability for EU and volume terms.',
			' lock terms and confirm delivery for this volume',
			'Repeat order confirmed — ',
			' supply stable',
			'Terms confirmed at 9:11 AM. Preparing delivery now.',
			'EU · 50 agency accounts — terms confirmed.',
			'Delivery scheduled before the launch window.',
			'Matching supply for AgencyAccounts.RequestEU().',
			'Terms confirmed. Delivery scheduled before the launch window.',
			'Access confirmed',
			'Accounts delivered on agreed terms. Launch window open.',
			'Working resource — request logged, supply matching.',
			'Active channels: EU desk, Agency pool, +3 more',
			'5 years supplying access — repeat orders active',
			'Repeat order channel active. Terms unchanged.',
			'Working resource ready for the next launch.',
			'Supply confirmed',
			'Supply matched',
			'Terms confirmed. Delivery in progress.',
			'Volume request — GEO: EU',
			'Terms draft ready for EU · 50 accounts. Volume and GEO locked.',
			'50 accounts · EU · delivery before 18:00.',
			'Posted to #requests — volume logged.',
			'Request logged — matching supply and terms.',
			'Desk notified @team. Delivery queued.',
			'Denis A.',
			'Elena M.',
		);
	}

	/**
	 * Redirect only requests for the known client-only hero chunk. This keeps
	 * all bootstrap, hydration and WebGPU chunks on their untouched originals.
	 *
	 * @return string
	 */
	private static function early_interceptor_script() {
		$target = self::HERO_CHUNK;
		$url    = trailingslashit( home_url( self::ENDPOINT ) ) . self::HERO_CHUNK . '?v=' . rawurlencode( IAH_VERSION );
		$target = wp_json_encode( $target, JSON_UNESCAPED_SLASHES );
		$url    = wp_json_encode( $url, JSON_UNESCAPED_SLASHES );

		if ( ! is_string( $target ) || ! is_string( $url ) ) {
			return '';
		}

		return '<script data-iah-native-ru-phase1>(function(){"use strict";var T=' . $target . ',R=' . $url . ';try{localStorage.setItem("iac-lang","ru")}catch(e){}function rw(u){if(typeof u!=="string"||u.indexOf(T)===-1||u.indexOf("/' . self::ENDPOINT . '/")!==-1)return u;return R}var p=Object.getOwnPropertyDescriptor(HTMLScriptElement.prototype,"src");if(p&&p.set)Object.defineProperty(HTMLScriptElement.prototype,"src",{configurable:p.configurable,enumerable:p.enumerable,get:p.get,set:function(v){p.set.call(this,rw(v))}})})();</script>';
	}

	/**
	 * Persist RU so client fallback code does not stop on a stale EN cookie.
	 *
	 * @param string $name Cookie name.
	 */
	private static function persist_ru_cookie( $name ) {
		if ( headers_sent() ) {
			return;
		}

		$path   = '/';
		$domain = defined( 'COOKIE_DOMAIN' ) && COOKIE_DOMAIN ? COOKIE_DOMAIN : '';

		setcookie(
			$name,
			'ru',
			array(
				'expires'  => time() + YEAR_IN_SECONDS,
				'path'     => $path,
				'domain'   => $domain,
				'secure'   => is_ssl(),
				'httponly' => false,
				'samesite' => 'Lax',
			)
		);
	}

	/**
	 * Detect root homepage without relying on query setup at plugins_loaded.
	 *
	 * @return bool
	 */
	private static function is_home_request() {
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return false;
		}
		if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
			return false;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}

		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		if ( ! is_string( $uri ) || '' === $uri ) {
			return false;
		}

		$path = wp_parse_url( $uri, PHP_URL_PATH );
		return '/' === $path || '' === $path;
	}

	/**
	 * Detect the exact phase-1 chunk endpoint before WordPress routing.
	 *
	 * @return bool
	 */
	private static function is_native_chunk_request() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		if ( ! is_string( $uri ) || '' === $uri ) {
			return false;
		}

		$path     = wp_parse_url( $uri, PHP_URL_PATH );
		$expected = '/' . self::ENDPOINT . '/' . self::HERO_CHUNK;
		return $expected === $path;
	}
}

IAH_Home_Native_Ru_Phase1::boot();
