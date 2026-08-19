<?php
/**
 * Localize mirrored homepage Next.js chunks when RU is active.
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Homepage JS chunk localizer.
 */
class IAH_Home_Js_Localizer {

	/** @var array<string,string>|null */
	private static $map = null;

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_action( 'plugins_loaded', array( __CLASS__, 'early_serve' ), 0 );
		add_action( 'init', array( __CLASS__, 'register_rewrite' ) );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_serve' ), 0 );
	}

	/**
	 * @return bool
	 */
	public static function is_ru() {
		return class_exists( 'IAC_I18n' ) && IAC_I18n::is_ru();
	}

	/**
	 * Merged EN→RU map for homepage chunks.
	 *
	 * @return array<string,string>
	 */
	public static function map() {
		if ( null !== self::$map ) {
			return self::$map;
		}

		self::$map = array();

		$extra = IAH_DIR . 'includes/i18n/ru-home-extra.php';
		if ( is_readable( $extra ) ) {
			$more = require $extra;
			if ( is_array( $more ) ) {
				self::$map = $more;
			}
		}

		return self::$map;
	}

	/**
	 * Rewrite rules.
	 */
	public static function register_rewrite() {
		add_rewrite_rule( '^iah-home-js/([a-z0-9]+\.js)$', 'index.php?iah_home_js=$matches[1]', 'top' );
	}

	/**
	 * @param array<int,string> $vars Vars.
	 * @return array<int,string>
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'iah_home_js';
		return $vars;
	}

	/**
	 * Early serve before WP routing.
	 */
	public static function early_serve() {
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		$file = self::detect_request_file();
		if ( $file ) {
			self::serve( $file );
		}
	}

	/**
	 * Serve via query var.
	 */
	public static function maybe_serve() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$file = get_query_var( 'iah_home_js' );
		if ( $file ) {
			self::serve( sanitize_file_name( wp_unslash( $file ) ) );
		}
	}

	/**
	 * @return string|null
	 */
	private static function detect_request_file() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! is_string( $uri ) || false === strpos( $uri, 'iah-home-js/' ) ) {
			return null;
		}
		if ( ! preg_match( '#/iah-home-js/([a-z0-9]+\.js)(?:\?|$)#i', $uri, $matches ) ) {
			return null;
		}
		return sanitize_file_name( $matches[1] );
	}

	/**
	 * @param string $file Chunk filename.
	 */
	private static function serve( $file ) {
		if ( ! preg_match( '/^[a-z0-9]+\.js$/', $file ) ) {
			status_header( 400 );
			exit;
		}

		$path = IAH_DIR . 'assets/site/_next/static/chunks/' . $file;
		if ( ! is_readable( $path ) ) {
			status_header( 404 );
			exit;
		}

		if ( self::is_ru() ) {
			$cached = IAH_DIR . 'cache/chunks-ru/' . $file;
			if ( is_readable( $cached ) ) {
				header( 'Content-Type: application/javascript; charset=utf-8' );
				header( 'Cache-Control: public, max-age=31536000, immutable' );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				readfile( $cached );
				exit;
			}
		}

		$js = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! is_string( $js ) ) {
			status_header( 500 );
			exit;
		}

		if ( self::is_ru() ) {
			$js = self::localize_js( $js );
		}

		header( 'Content-Type: application/javascript; charset=utf-8' );
		header( 'Cache-Control: private, max-age=3600' );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $js;
		exit;
	}

	/**
	 * Replace quoted UI literals inside JS bundles.
	 *
	 * @param string $js Raw chunk source.
	 * @return string
	 */
	public static function localize_js( $js ) {
		$map = self::map();
		if ( empty( $map ) ) {
			return $js;
		}

		uksort(
			$map,
			static function ( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			}
		);

		foreach ( $map as $en => $ru ) {
			if ( ! is_string( $en ) || ! is_string( $ru ) || '' === $en ) {
				continue;
			}
			if ( strlen( $en ) < 12 ) {
				if ( ! preg_match( '/^[A-Z0-9\s:/@.\-–—]+$/', $en ) && false === strpos( $en, ' ' ) ) {
					continue;
				}
			}

			$en_json = wp_json_encode( $en, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			$ru_json = wp_json_encode( $ru, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			if ( ! is_string( $en_json ) || ! is_string( $ru_json ) || '""' === $en_json ) {
				continue;
			}
			$js = str_replace( $en_json, $ru_json, $js );
		}

		return $js;
	}

	/**
	 * Rewrite chunk URLs in homepage HTML for RU visitors.
	 *
	 * @param string $html Document HTML.
	 * @param string $prefix Asset prefix.
	 * @return string
	 */
	public static function rewrite_html_chunk_urls( $html, $prefix ) {
		if ( ! self::is_ru() ) {
			return $html;
		}

		$local         = trailingslashit( home_url( 'iah-home-js' ) );
		$quoted_prefix = preg_quote( $prefix, '#' );

		$html = preg_replace(
			'#(<script\b[^>]*\bsrc=(["\']))' . $quoted_prefix . '_next/static/chunks/([a-z0-9]+\.js)\2#i',
			'$1' . $local . '$3$2',
			$html
		);

		return preg_replace(
			'#(<link\b[^>]*\bhref=(["\']))' . $quoted_prefix . '_next/static/chunks/([a-z0-9]+\.js)\2#i',
			'$1' . $local . '$3$2',
			$html
		);
	}

	/**
	 * Intercept dynamic chunk loads (Turbopack) for RU homepage.
	 *
	 * @return string
	 */
	public static function intercept_script() {
		if ( ! self::is_ru() ) {
			return '';
		}
		$local = esc_js( trailingslashit( home_url( 'iah-home-js' ) ) );
		return '<script>(function(){var L="' . $local . '",P="/wp-content/plugins/impact-accs-homepage/assets/site/_next/static/chunks/";function rw(u){if(typeof u!=="string")return u;var p=u;try{if(u.indexOf("http")===0){var o=location.origin;if(u.indexOf(o)===0)p=u.slice(o.length);else return u}}catch(e){}if(p.indexOf(P)===0)return L+p.slice(P.length);return u}var f=window.fetch;if(f)window.fetch=function(i,n){if(typeof i==="string")i=rw(i);else if(i&&i.url)i=new Request(rw(i.url),i);return f.call(this,i,n)};var X=XMLHttpRequest.prototype.open;XMLHttpRequest.prototype.open=function(){arguments[1]=rw(arguments[1]);return X.apply(this,arguments)};var ps=Object.getOwnPropertyDescriptor(HTMLScriptElement.prototype,"src");if(ps&&ps.set)Object.defineProperty(HTMLScriptElement.prototype,"src",{set:function(v){ps.set.call(this,rw(v))},get:ps.get})})();</script>';
	}

	/**
	 * @return bool
	 */
	public static function should_localize_html() {
		return self::is_ru();
	}
}

IAH_Home_Js_Localizer::boot();
