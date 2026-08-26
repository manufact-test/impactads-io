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
 * Keeps the server-rendered homepage and its known UI chunks in the same RU
 * state before hydration, without touching WebGPU, scene state or Turbopack
 * bootstrap code outside the original logical chunk id.
 */
class IAH_Home_Native_Ru_Phase2 {

	/** Shared header/mobile-menu/preloader chunk. */
	private const COMMON_CHUNK = '1e7f2c52e84d02fd.js';

	/** Main lower-page UI/content chunk. */
	private const HOME_CONTENT_CHUNK = '827ff3490ba1793e.js';

	/** Timeline data + original Next footer/form chunk. */
	private const FOOTER_CONTENT_CHUNK = 'd53e27b68750e6f9.js';

	/** Shared native-RU endpoint used by phase 1/2. */
	private const ENDPOINT = 'iah-home-native-ru';

	/**
	 * Register before the mirrored homepage renderer.
	 */
	public static function boot() {
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_serve_phase2_chunk' ), -996 );
		add_action( 'template_redirect', array( __CLASS__, 'start_document_buffer' ), -1001 );
	}

	/**
	 * Serve one of the three explicitly owned phase-2 UI chunks.
	 */
	public static function maybe_serve_phase2_chunk() {
		$chunk = self::requested_phase2_chunk();
		if ( null === $chunk ) {
			return;
		}

		$path = IAH_DIR . 'assets/site/_next/static/chunks/' . $chunk;
		if ( ! is_readable( $path ) ) {
			status_header( 404 );
			exit;
		}

		$js = file_get_contents( $path );
		if ( ! is_string( $js ) ) {
			status_header( 500 );
			exit;
		}

		$js = self::normalize_turbopack_chunk_identity( $js, $chunk );
		$js = self::localize_owned_chunk( $js, $chunk );

		if ( ! headers_sent() ) {
			header( 'Content-Type: application/javascript; charset=utf-8' );
			header( 'Cache-Control: public, max-age=31536000, immutable' );
			header( 'X-IAH-Native-RU: phase2-' . substr( $chunk, 0, 8 ) );
		}

		/* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
		echo $js;
		exit;
	}

	/**
	 * Capture the final mirrored document. This starts outside the existing phase
	 * buffers so the callback sees the fully assembled page at flush time.
	 */
	public static function start_document_buffer() {
		if ( ! self::is_home_request() ) {
			return;
		}

		ob_start( array( __CLASS__, 'patch_document' ) );
	}

	/**
	 * Route only the three confirmed parser-created scripts through the native RU
	 * endpoint, then align exact SSR/Flight presentation literals with the same
	 * client-side RU copy. Flight dependency filenames remain original.
	 *
	 * @param string $html Rendered homepage document.
	 * @return string
	 */
	public static function patch_document( $html ) {
		if ( ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		foreach ( self::phase2_chunks() as $chunk ) {
			$html = self::rewrite_parser_chunk_src( $html, $chunk );
		}

		return self::localize_body_and_flight( $html );
	}

	/**
	 * Explicit phase-2 ownership list. f7f1 and 5308 remain on their existing,
	 * separately tested phase-1 paths.
	 *
	 * @return array<int,string>
	 */
	private static function phase2_chunks() {
		return array(
			self::COMMON_CHUNK,
			self::HOME_CONTENT_CHUNK,
			self::FOOTER_CONTENT_CHUNK,
		);
	}

	/**
	 * Replace only the real parser-created script src for one known chunk.
	 * Escaped script references inside Next Flight strings do not match this
	 * pattern and therefore retain their original dependency filename.
	 *
	 * @param string $html Document.
	 * @param string $chunk Original chunk basename.
	 * @return string
	 */
	private static function rewrite_parser_chunk_src( $html, $chunk ) {
		$url = trailingslashit( home_url( self::ENDPOINT ) ) . $chunk . '?v=' . rawurlencode( IAH_VERSION );
		$url = esc_url( $url );
		if ( '' === $url ) {
			return $html;
		}

		$quoted  = preg_quote( $chunk, '#' );
		$pattern = "#(<script\\b[^>]*\\bsrc=)([\"'])([^\"']*/_next/static/chunks/" . $quoted . "(?:\\?[^\"']*)?)\\2#i";

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
	 * Translate only the document body. iacData and SEO dictionaries are injected
	 * into <head> and remain untouched, so their English lookup keys stay intact.
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
	 * Exact presentation copy whose owning client code is now covered by 1e7,
	 * 827, d53, f7f1 or the existing 5308 phase-1 endpoint. Server-only children
	 * (for example the final footer CTA) are also safe because Flight supplies the
	 * translated prop to the localized client boundary.
	 *
	 * @return array<string,string>
	 */
	private static function document_map() {
		$map = self::presentation_map();

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
	 * Client-approved v2 copy.
	 *
	 * @return array<string,string>
	 */
	public static function approved_map() {
		$file = IAH_DIR . 'includes/i18n/ru-home-v2.php';
		$map  = is_readable( $file ) ? require $file : array();
		return is_array( $map ) ? $map : array();
	}

	/**
	 * Canonical v2 copy plus a deliberately small exact-label supplement for the
	 * header, mobile menu, footer and preloader. No broad legacy dictionary is
	 * applied to arbitrary bundle strings.
	 *
	 * @return array<string,string>
	 */
	public static function presentation_map() {
		$map    = self::approved_map();
		$merged = class_exists( 'IAH_Home_Js_Localizer' ) ? IAH_Home_Js_Localizer::map() : array();

		foreach ( self::supplemental_visible_keys() as $key ) {
			if ( isset( $merged[ $key ] ) && is_string( $merged[ $key ] ) ) {
				$map[ $key ] = $merged[ $key ];
			}
		}

		/* Native preloader copy did not exist in the legacy dictionaries. */
		$map['Initializing']                    = 'ЗАГРУЗКА';
		$map['Loading']                         = 'Загрузка';
		$map['Sound muted']                     = 'Звук выключен';
		$map['Sound enabled']                   = 'Звук включён';
		$map['Click anywhere to enable sound']  = 'Нажмите, чтобы включить звук';
		$map['Impact System']                   = 'Система Impact';

		return $map;
	}

	/**
	 * Values known to be program state/identifiers, or too generic to rewrite in
	 * the Flight payload. EU-agency-50 is not listed: in 827 it is confirmed as a
	 * visible PR-card title and is intentionally translated by v2.
	 *
	 * @return array<int,string>
	 */
	public static function unsafe_keys() {
		return array(
			'VolumeRequestPending',
			'AgencyAccounts',
			'and',
		);
	}

	/**
	 * Small exact-label supplement shared by the owned chunks.
	 *
	 * @return array<int,string>
	 */
	private static function supplemental_visible_keys() {
		return array(
			'About',
			'ABOUT',
			'Blog',
			'BLOG',
			'Contact',
			'CONTACT',
			'Accounts',
			'ACCOUNTS',
			'FEATURES',
			'Menu',
			'MENU',
			'Close',
			'CLOSE',
			'Back to list',
			'BACK TO LIST',
			'Waitlist',
			'Impact starts with access',
			'Enable sound',
			'Unmute sound',
			'Mute sound',
			'SCROLL DOWN',
			'Scroll down',
		);
	}

	/**
	 * Header/mobile-menu/preloader values confirmed inside 1e7. The live
	 * Sound: off/on/locked suffix is intentionally not rewritten here; the
	 * existing first-paint CSS presentation remains until final visual QA.
	 *
	 * @return array<int,string>
	 */
	private static function common_visible_keys() {
		return array_merge(
			array(
				'Platform Access',
				'Agency Accounts',
				'Team Supply',
				'Request access',
				'REQUEST ACCESS',
				'Get access',
				'GET ACCESS',
				'impact.corp®',
				'RIGHTS RESERVED',
				'all rights reserved',
				'Initializing',
				'Loading',
				'Sound muted',
				'Sound enabled',
				'Click anywhere to enable sound',
				'Impact System',
			),
			self::supplemental_visible_keys()
		);
	}

	/**
	 * Localize one known UI chunk using exact quoted literals only.
	 *
	 * @param string $js Chunk source.
	 * @param string $chunk Original chunk basename.
	 * @return string
	 */
	private static function localize_owned_chunk( $js, $chunk ) {
		$map = self::presentation_map();

		if ( self::COMMON_CHUNK === $chunk ) {
			$keys = self::common_visible_keys();
		} else {
			$keys = array_keys( $map );
		}

		$unsafe = array_fill_keys( self::unsafe_keys(), true );
		foreach ( $keys as $en ) {
			if ( isset( $unsafe[ $en ] ) || ! isset( $map[ $en ] ) || ! is_string( $map[ $en ] ) ) {
				continue;
			}
			$js = self::replace_quoted_literal( $js, $en, $map[ $en ] );
		}

		return $js;
	}

	/**
	 * Replace one presentation value in normal HTML and in escaped Next Flight
	 * strings, never as an unbounded substring.
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
	 * Escape a JSON string literal one level deeper for a JS string carrying a
	 * Next Flight payload.
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
	 * Preserve the original Turbopack logical id while bytes are served through
	 * WordPress. Only the bootstrap identity expression is replaced.
	 *
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
	 * Return the requested phase-2 filename only for the exact native-RU route.
	 *
	 * @return string|null
	 */
	private static function requested_phase2_chunk() {
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path = is_string( $uri ) && '' !== $uri ? wp_parse_url( $uri, PHP_URL_PATH ) : null;
		if ( ! is_string( $path ) ) {
			return null;
		}

		$prefix = '/' . self::ENDPOINT . '/';
		if ( 0 !== strpos( $path, $prefix ) ) {
			return null;
		}

		$chunk = substr( $path, strlen( $prefix ) );
		return in_array( $chunk, self::phase2_chunks(), true ) ? $chunk : null;
	}
}

IAH_Home_Native_Ru_Phase2::boot();
