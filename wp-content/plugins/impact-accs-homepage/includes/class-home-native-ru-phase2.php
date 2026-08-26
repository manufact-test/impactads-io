<?php
/**
 * Homepage native Russian layer, phase 2.
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keeps the shared React chrome and the server-rendered homepage copy in the
 * same RU state before hydration, without touching WebGPU/scene code.
 */
class IAH_Home_Native_Ru_Phase2 {

	/** Shared header/mobile-menu chunk. */
	private const COMMON_CHUNK = '1e7f2c52e84d02fd.js';

	/** Shared native-RU endpoint used by phase 1/2. */
	private const ENDPOINT = 'iah-home-native-ru';

	/**
	 * Register before the mirrored homepage renderer.
	 */
	public static function boot() {
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_serve_common_chunk' ), -996 );
		add_action( 'template_redirect', array( __CLASS__, 'start_document_buffer' ), -1001 );
	}

	/**
	 * Serve the localized shared header/menu chunk.
	 */
	public static function maybe_serve_common_chunk() {
		if ( ! self::is_common_chunk_request() ) {
			return;
		}

		$path = IAH_DIR . 'assets/site/_next/static/chunks/' . self::COMMON_CHUNK;
		if ( ! is_readable( $path ) ) {
			status_header( 404 );
			exit;
		}

		$js = file_get_contents( $path );
		if ( ! is_string( $js ) ) {
			status_header( 500 );
			exit;
		}

		$js = self::normalize_turbopack_chunk_identity( $js, self::COMMON_CHUNK );
		$js = self::localize_common_chunk( $js );

		if ( ! headers_sent() ) {
			header( 'Content-Type: application/javascript; charset=utf-8' );
			header( 'Cache-Control: public, max-age=31536000, immutable' );
			header( 'X-IAH-Native-RU: phase2-common' );
		}

		/* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
		echo $js;
		exit;
	}

	/**
	 * Capture the final mirrored document. This starts before all existing phase
	 * buffers, so the callback sees the fully assembled page at flush time.
	 */
	public static function start_document_buffer() {
		if ( ! self::is_home_request() ) {
			return;
		}

		ob_start( array( __CLASS__, 'patch_document' ) );
	}

	/**
	 * Keep initial HTML/Flight copy aligned with the localized client chunks and
	 * route only the known common chunk through the phase-2 endpoint.
	 *
	 * @param string $html Rendered homepage document.
	 * @return string
	 */
	public static function patch_document( $html ) {
		if ( ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		$html = self::rewrite_common_chunk_src( $html );
		$html = self::localize_body_and_flight( $html );
		return $html;
	}

	/**
	 * Replace only the parser-created script src for the shared chunk. Flight
	 * dependency metadata keeps the original filename/logical chunk id.
	 *
	 * @param string $html Document.
	 * @return string
	 */
	private static function rewrite_common_chunk_src( $html ) {
		$url = trailingslashit( home_url( self::ENDPOINT ) ) . self::COMMON_CHUNK . '?v=' . rawurlencode( IAH_VERSION );
		$url = esc_url( $url );
		if ( '' === $url ) {
			return $html;
		}

		$chunk   = preg_quote( self::COMMON_CHUNK, '#' );
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
	 * Translate only the body. iacData and SEO helpers are injected into <head>
	 * and intentionally stay untouched so dictionary keys are never rewritten.
	 *
	 * @param string $html Document.
	 * @return string
	 */
	private static function localize_body_and_flight( $html ) {
		$body_start = stripos( $html, '<body' );
		$body_end   = strripos( $html, '</body>' );
		if ( false === $body_start || false === $body_end || $body_end <= $body_start ) {
			return $html;
		}

		$open_end = strpos( $html, '>', $body_start );
		if ( false === $open_end || $open_end >= $body_end ) {
			return $html;
		}

		$prefix = substr( $html, 0, $open_end + 1 );
		$body   = substr( $html, $open_end + 1, $body_end - $open_end - 1 );
		$suffix = substr( $html, $body_end );

		foreach ( self::document_map() as $en => $ru ) {
			$body = self::replace_document_literal( $body, $en, $ru );
		}

		return $prefix . $body . $suffix;
	}

	/**
	 * Approved homepage copy plus the small shared-nav labels that live in the
	 * exact dictionary. Unsafe React state/identifier values are excluded.
	 *
	 * @return array<string,string>
	 */
	private static function document_map() {
		$map = self::approved_map();

		$merged = class_exists( 'IAH_Home_Js_Localizer' ) ? IAH_Home_Js_Localizer::map() : array();
		foreach ( self::common_visible_keys() as $key ) {
			if ( isset( $merged[ $key ] ) && is_string( $merged[ $key ] ) ) {
				$map[ $key ] = $merged[ $key ];
			}
		}

		foreach ( self::unsafe_keys() as $key ) {
			unset( $map[ $key ] );
		}

		uksort(
			$map,
			static function ( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			}
		);

		return $map;
	}

	/**
	 * Client-approved v2 copy only. Legacy maps are not bulk-applied to chunks.
	 *
	 * @return array<string,string>
	 */
	public static function approved_map() {
		$file = IAH_DIR . 'includes/i18n/ru-home-v2.php';
		$map  = is_readable( $file ) ? require $file : array();
		return is_array( $map ) ? $map : array();
	}

	/**
	 * Values known to be program state/identifiers rather than presentation.
	 *
	 * @return array<int,string>
	 */
	public static function unsafe_keys() {
		return array(
			'VolumeRequestPending',
			'AgencyAccounts',
			'EU-agency-50',
			'and',
		);
	}

	/**
	 * Shared navigation/menu copy owned by 1e7. Sound state text stays on the
	 * existing CSS presentation layer because its suffix is a live state enum.
	 *
	 * @return array<int,string>
	 */
	private static function common_visible_keys() {
		return array(
			'Platform Access',
			'Agency Accounts',
			'Team Supply',
			'About',
			'ABOUT',
			'Blog',
			'BLOG',
			'Contact',
			'CONTACT',
			'Accounts',
			'ACCOUNTS',
			'FEATURES',
			'Request access',
			'REQUEST ACCESS',
			'Get access',
			'GET ACCESS',
			'Menu',
			'MENU',
			'Close',
			'CLOSE',
			'Back to list',
			'BACK TO LIST',
			'impact.corp®',
			'RIGHTS RESERVED',
			'all rights reserved',
		);
	}

	/**
	 * Apply exact visible literals to the shared chunk only.
	 *
	 * @param string $js Chunk source.
	 * @return string
	 */
	private static function localize_common_chunk( $js ) {
		$map = class_exists( 'IAH_Home_Js_Localizer' ) ? IAH_Home_Js_Localizer::map() : array();
		foreach ( self::common_visible_keys() as $en ) {
			if ( ! isset( $map[ $en ] ) || ! is_string( $map[ $en ] ) ) {
				continue;
			}
			$js = self::replace_quoted_literal( $js, $en, $map[ $en ] );
		}
		return $js;
	}

	/**
	 * Replace one presentation value in normal HTML and in escaped Next Flight
	 * strings, but never as an unbounded substring such as Request inside a state
	 * token.
	 *
	 * @param string $body Body HTML.
	 * @param string $from English value.
	 * @param string $to Russian value.
	 * @return string
	 */
	private static function replace_document_literal( $body, $from, $to ) {
		if ( ! is_string( $from ) || ! is_string( $to ) || '' === $from ) {
			return $body;
		}

		$from_html = esc_html( $from );
		$to_html   = esc_html( $to );
		$body      = str_replace( '>' . $from_html . '<', '>' . $to_html . '<', $body );
		$body      = str_replace( '>' . $from . '<', '>' . $to . '<', $body );

		$from_attr = esc_attr( $from );
		$to_attr   = esc_attr( $to );
		$body      = str_replace( '="' . $from_attr . '"', '="' . $to_attr . '"', $body );
		$body      = str_replace( "='" . $from_attr . "'", "='" . $to_attr . "'", $body );

		$from_json = wp_json_encode( $from, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		$to_json   = wp_json_encode( $to, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( is_string( $from_json ) && is_string( $to_json ) ) {
			$body = str_replace( $from_json, $to_json, $body );
			$body = str_replace( self::escape_for_flight( $from_json ), self::escape_for_flight( $to_json ), $body );
		}

		return $body;
	}

	/**
	 * Escape a JSON string literal one level deeper for a JS string that carries
	 * the Next Flight payload.
	 *
	 * @param string $json JSON string literal including quotes.
	 * @return string
	 */
	private static function escape_for_flight( $json ) {
		return str_replace( array( '\\', '"' ), array( '\\\\', '\\"' ), $json );
	}

	/**
	 * Replace exact double/single-quoted JS literals.
	 *
	 * @param string $js Source.
	 * @param string $from English value.
	 * @param string $to Russian value.
	 * @return string
	 */
	public static function replace_quoted_literal( $js, $from, $to ) {
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
	 * @param string $js Chunk source.
	 * @param string $chunk Original basename.
	 * @return string
	 */
	private static function normalize_turbopack_chunk_identity( $js, $chunk ) {
		$dynamic = '"object"==typeof document?document.currentScript:void 0';
		$stable  = wp_json_encode( 'static/chunks/' . $chunk, JSON_UNESCAPED_SLASHES );
		$offset  = strpos( $js, $dynamic );
		if ( false === $offset || ! is_string( $stable ) ) {
			return $js;
		}
		return substr_replace( $js, $stable, $offset, strlen( $dynamic ) );
	}

	/**
	 * Root request detection before WP query setup.
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
	 * @return bool
	 */
	private static function is_common_chunk_request() {
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path = is_string( $uri ) && '' !== $uri ? wp_parse_url( $uri, PHP_URL_PATH ) : null;
		return '/' . self::ENDPOINT . '/' . self::COMMON_CHUNK === $path;
	}
}

IAH_Home_Native_Ru_Phase2::boot();
