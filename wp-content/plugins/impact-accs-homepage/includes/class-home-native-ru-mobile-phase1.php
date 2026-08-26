<?php
/**
 * Homepage native Russian transition layer for the mobile/touch hero, phase 1.
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Localizes the client-only conversation module used by the mobile/touch hero.
 *
 * The module is an initial parser-created script, so it cannot be caught by the
 * narrow HTMLScriptElement.src interceptor used for the dynamically loaded 3D
 * hero chunk. We rewrite only this one script tag in the final document and
 * keep its original Turbopack logical chunk id.
 */
class IAH_Home_Native_Ru_Mobile_Phase1 {

	/** Client-only chunk that owns the Slack-like hero conversations. */
	private const MOBILE_HERO_CHUNK = 'f7f1c59a71681025.js';

	/** Shared phase-1 endpoint. */
	private const ENDPOINT = 'iah-home-native-ru';

	/**
	 * Register the mobile transition layer.
	 */
	public static function boot() {
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_serve_mobile_hero_chunk' ), -997 );
		add_action( 'template_redirect', array( __CLASS__, 'start_document_buffer' ), -999 );
	}

	/**
	 * Serve the localized mobile/touch conversation chunk.
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
		$js = self::localize_visible_mobile_copy( $js );

		if ( ! headers_sent() ) {
			header( 'Content-Type: application/javascript; charset=utf-8' );
			header( 'Cache-Control: public, max-age=31536000, immutable' );
			header( 'X-IAH-Native-RU: phase1-mobile-hero' );
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
	 * Route only the initial mobile conversation script through the RU endpoint.
	 * Flight metadata and every other script reference remain byte-for-byte as
	 * rendered by the existing homepage plugin.
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

		$chunk = preg_quote( self::MOBILE_HERO_CHUNK, '#' );
		$pattern = '#<script\\b(?=[^>]*\\bsrc=)(?P<tag>[^>]*)>?#i';

		return preg_replace_callback(
			$pattern,
			static function ( $matches ) use ( $chunk, $url ) {
				$tag = '<script' . $matches['tag'] . '>';
				$src_pattern = "#\\bsrc=([\"'])([^\"']*/_next/static/chunks/" . $chunk . "(?:\\?[^\"']*)?)\\1#i";
				if ( ! preg_match( $src_pattern, $tag ) ) {
					return $tag;
				}

				return preg_replace( $src_pattern, 'src="' . $url . '"', $tag, 1 );
			},
			$html
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
	 * Replace only confirmed user-facing literals from module 24684. Both quote
	 * styles are supported because this compiled chunk contains a mix of JSON-
	 * style double quotes and minifier-produced single-quoted children values.
	 * Code/state tokens such as VolumeRequestPending and AgencyAccounts remain
	 * untouched deliberately.
	 *
	 * @param string $js Original chunk.
	 * @return string
	 */
	private static function localize_visible_mobile_copy( $js ) {
		$map = class_exists( 'IAH_Home_Js_Localizer' ) ? IAH_Home_Js_Localizer::map() : array();

		foreach ( self::visible_keys() as $en ) {
			if ( ! isset( $map[ $en ] ) || ! is_string( $map[ $en ] ) ) {
				continue;
			}
			$js = self::replace_quoted_literal( $js, $en, $map[ $en ] );
		}

		// These phrases are split into separate React children in the source, so
		// the approved full-sentence map cannot match them as one literal.
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
	 * User-facing strings confirmed inside the mobile conversation module.
	 *
	 * @return array<int,string>
	 */
	private static function visible_keys() {
		return array(
			'Need EU accounts before launch.',
			' terms and delivery for EU launch',
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
	 * Escape a value for an already-quoted single-quoted JS literal.
	 *
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

		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path = is_string( $uri ) && '' !== $uri ? wp_parse_url( $uri, PHP_URL_PATH ) : null;
		return '/' === $path || '' === $path;
	}

	/**
	 * Match only the exact f7f1 phase-1 endpoint.
	 *
	 * @return bool
	 */
	private static function is_mobile_chunk_request() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path = is_string( $uri ) && '' !== $uri ? wp_parse_url( $uri, PHP_URL_PATH ) : null;
		return '/' . self::ENDPOINT . '/' . self::MOBILE_HERO_CHUNK === $path;
	}
}

IAH_Home_Native_Ru_Mobile_Phase1::boot();
