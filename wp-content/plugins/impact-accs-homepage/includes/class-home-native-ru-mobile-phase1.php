<?php
/**
 * Homepage native Russian transition layer for the f7f1 homepage UI chunk.
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Localizes the client-only conversation/homepage module used by the hero and
 * the main content sections. The original chunk remains untouched on disk.
 */
class IAH_Home_Native_Ru_Mobile_Phase1 {

	/** Homepage UI chunk that owns hero conversations and main content copy. */
	private const MOBILE_HERO_CHUNK = 'f7f1c59a71681025.js';

	/** Shared native-RU endpoint. */
	private const ENDPOINT = 'iah-home-native-ru';

	/**
	 * Register the transition layer.
	 */
	public static function boot() {
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_serve_mobile_hero_chunk' ), -997 );
		add_action( 'template_redirect', array( __CLASS__, 'start_document_buffer' ), -999 );
	}

	/**
	 * Serve the localized f7f1 homepage chunk.
	 */
	public static function maybe_serve_mobile_hero_chunk() {
		if ( ! self::is_mobile_chunk_request() ) {
			return;
		}

		$path = IAH_DIR . 'assets/site/_next/static/chunks/' . self::MOBILE_HERO_CHUNK;
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
		$js = self::localize_visible_home_copy( $js );

		if ( ! headers_sent() ) {
			header( 'Content-Type: application/javascript; charset=utf-8' );
			header( 'Cache-Control: public, max-age=31536000, immutable' );
			header( 'X-IAH-Native-RU: phase2-home-content' );
		}

		/* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
		echo $js;
		exit;
	}

	/**
	 * Wrap the homepage response so only the f7f1 parser script src is changed.
	 */
	public static function start_document_buffer() {
		if ( ! self::is_home_request() ) {
			return;
		}

		ob_start( array( __CLASS__, 'patch_document' ) );
	}

	/**
	 * Route only the initial f7f1 script through the RU endpoint. Flight metadata
	 * and every other script reference remain byte-for-byte untouched here.
	 *
	 * @param string $html Rendered homepage document.
	 * @return string
	 */
	public static function patch_document( $html ) {
		if ( ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		$url = trailingslashit( home_url( self::ENDPOINT ) ) . self::MOBILE_HERO_CHUNK . '?v=' . rawurlencode( IAH_VERSION );
		$url = esc_url( $url );
		if ( '' === $url ) {
			return $html;
		}

		$chunk   = preg_quote( self::MOBILE_HERO_CHUNK, '#' );
		$pattern = "#(<script\\b[^>]*\\bsrc=)([\"'])([^\"']*/_next/static/chunks/" . $chunk . "(?:\\?[^\"']*)?)\\2#i";

		return preg_replace_callback(
			$pattern,
			static function ( $matches ) use ( $url ) {
				return $matches[1] . '"' . $url . '"';
			},
			$html,
			1
		);
	}

	/**
	 * Preserve the original Turbopack id while bytes are served from WordPress.
	 *
	 * @param string $js Original chunk.
	 * @return string
	 */
	private static function normalize_turbopack_chunk_identity( $js ) {
		$dynamic = '"object"==typeof document?document.currentScript:void 0';
		$stable  = wp_json_encode( 'static/chunks/' . self::MOBILE_HERO_CHUNK, JSON_UNESCAPED_SLASHES );
		$offset  = strpos( $js, $dynamic );

		if ( false === $offset || ! is_string( $stable ) ) {
			return $js;
		}

		return substr_replace( $js, $stable, $offset, strlen( $dynamic ) );
	}

	/**
	 * Apply the approved v2 homepage copy to exact quoted literals only, while
	 * retaining the few safe hero fragments that predate v2. State identifiers
	 * such as VolumeRequestPending and AgencyAccounts are never rewritten.
	 *
	 * @param string $js Original chunk.
	 * @return string
	 */
	private static function localize_visible_home_copy( $js ) {
		$merged   = class_exists( 'IAH_Home_Js_Localizer' ) ? IAH_Home_Js_Localizer::map() : array();
		$approved = class_exists( 'IAH_Home_Native_Ru_Phase2' ) ? IAH_Home_Native_Ru_Phase2::approved_map() : array();
		$keys     = array_unique( array_merge( array_keys( $approved ), self::legacy_visible_keys() ) );
		$unsafe   = class_exists( 'IAH_Home_Native_Ru_Phase2' ) ? array_fill_keys( IAH_Home_Native_Ru_Phase2::unsafe_keys(), true ) : array();

		foreach ( $keys as $en ) {
			if ( isset( $unsafe[ $en ] ) ) {
				continue;
			}
			$ru = isset( $approved[ $en ] ) && is_string( $approved[ $en ] ) ? $approved[ $en ] : ( isset( $merged[ $en ] ) && is_string( $merged[ $en ] ) ? $merged[ $en ] : null );
			if ( ! is_string( $ru ) ) {
				continue;
			}
			$js = self::replace_quoted_literal( $js, $en, $ru );
		}

		/* Split React children that cannot be represented by a full-sentence key. */
		$fragments = array(
			'Terms confirmed at '       => 'Параметры подтверждены в ',
			'. Preparing delivery now.' => '. Готовим передачу.',
			'Request '                  => 'Запрос: ',
			'9:11 AM'                   => '09:11',
			'9:16 AM'                   => '09:16',
			'9:17 AM'                   => '09:17',
			'9:18 AM'                   => '09:18',
			'9:19 AM'                   => '09:19',
			'9:20 AM'                   => '09:20',
			'9:22 AM'                   => '09:22',
		);
		foreach ( $fragments as $en => $ru ) {
			$js = self::replace_quoted_literal( $js, $en, $ru );
		}

		return $js;
	}

	/**
	 * Safe hero literals that are not all present in the canonical v2 map.
	 *
	 * @return array<int,string>
	 */
	private static function legacy_visible_keys() {
		return array(
			'Need EU accounts before launch.',
			' terms and delivery for EU launch',
			'confirm availability for EU and volume terms.',
			' lock terms and confirm delivery for this volume',
			'Repeat order confirmed — ',
			' supply stable',
			'EU · 50 agency accounts — terms confirmed.',
			'Delivery scheduled before the launch window.',
			'Matching supply for AgencyAccounts.RequestEU().',
			'Terms confirmed. Delivery scheduled before the launch window.',
			'Volume request — GEO: EU',
			'Buyer desk needs agency accounts before traffic goes live. GEO: EU.',
			'Terms draft ready for EU · 50 accounts. Volume and GEO locked.',
			'50 accounts · EU · delivery before 18:00.',
			'Working resource — request logged, supply matching.',
			'Posted to #requests — volume logged.',
			'Request logged — matching supply and terms.',
			'Desk notified @team. Delivery queued.',
			'5 years supplying access — repeat orders active',
			'Repeat order channel active. Terms unchanged.',
			'Working resource ready for the next launch.',
			'Active channels: ',
			'Request status',
			'Volume terms',
			'Supply status',
			'Request',
			'Next step',
			'High',
			'Open',
			'Request access',
			'Contact team',
			'You',
			'Send',
			'Denis A.',
			'Elena M.',
		);
	}

	/**
	 * Replace an exact JS string literal without touching identifiers/substrings.
	 *
	 * @param string $js Source JS.
	 * @param string $from English literal.
	 * @param string $to Russian literal.
	 * @return string
	 */
	private static function replace_quoted_literal( $js, $from, $to ) {
		if ( class_exists( 'IAH_Home_Native_Ru_Phase2' ) ) {
			return IAH_Home_Native_Ru_Phase2::replace_quoted_literal( $js, $from, $to );
		}

		$from_double = wp_json_encode( $from, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		$to_double   = wp_json_encode( $to, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( is_string( $from_double ) && is_string( $to_double ) ) {
			$js = str_replace( $from_double, $to_double, $js );
		}

		$from_single = "'" . self::escape_single_quoted_js( $from ) . "'";
		$to_single   = "'" . self::escape_single_quoted_js( $to ) . "'";
		return str_replace( $from_single, $to_single, $js );
	}

	/**
	 * @param string $value Value.
	 * @return string
	 */
	private static function escape_single_quoted_js( $value ) {
		return str_replace(
			array( '\\', "'", "\r", "\n", "\xE2\x80\xA8", "\xE2\x80\xA9" ),
			array( '\\\\', "\\'", '\\r', '\\n', '\\u2028', '\\u2029' ),
			$value
		);
	}

	/**
	 * Root homepage detection before the normal WP query is available.
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

		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path = is_string( $uri ) && '' !== $uri ? wp_parse_url( $uri, PHP_URL_PATH ) : null;
		return '/' === $path || '' === $path;
	}

	/**
	 * Match only the exact f7f1 native-RU endpoint.
	 *
	 * @return bool
	 */
	private static function is_mobile_chunk_request() {
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path = is_string( $uri ) && '' !== $uri ? wp_parse_url( $uri, PHP_URL_PATH ) : null;
		return '/' . self::ENDPOINT . '/' . self::MOBILE_HERO_CHUNK === $path;
	}
}

IAH_Home_Native_Ru_Mobile_Phase1::boot();
