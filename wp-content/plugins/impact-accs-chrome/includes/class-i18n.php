<?php
/**
 * Native EN/RU localization (no Google Translate).
 *
 * @package ImpactAccsChrome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site localization helper.
 */
class IAC_I18n {

	/** @var string|null */
	private static $lang = null;

	/** @var array<string,string>|null */
	private static $html_map = null;

	/** @var array<string,string>|null */
	private static $js_map = null;

	/**
	 * Active language code.
	 *
	 * Russian is the primary/default language. An explicit EN cookie or saved
	 * preference still wins for returning visitors.
	 *
	 * @return string 'en'|'ru'
	 */
	public static function lang() {
		if ( null !== self::$lang ) {
			return self::$lang;
		}

		$cookie = 'ru';
		if ( isset( $_COOKIE['iac_lang4'] ) ) {
			$cookie = sanitize_key( wp_unslash( $_COOKIE['iac_lang4'] ) );
		} elseif ( isset( $_COOKIE['iac_lang3'] ) ) {
			$cookie = sanitize_key( wp_unslash( $_COOKIE['iac_lang3'] ) );
		} elseif ( isset( $_COOKIE['iac_lang'] ) ) {
			$cookie = sanitize_key( wp_unslash( $_COOKIE['iac_lang'] ) );
		}
		self::$lang = in_array( $cookie, array( 'en', 'ru' ), true ) ? $cookie : 'ru';
		return self::$lang;
	}

	/**
	 * @return bool
	 */
	public static function is_ru() {
		return 'ru' === self::lang();
	}

	/**
	 * HTML string replacements (EN => RU).
	 *
	 * @return array<string,string>
	 */
	public static function html_map() {
		if ( null !== self::$html_map ) {
			return self::$html_map;
		}

		if ( ! self::is_ru() ) {
			self::$html_map = array();
			return self::$html_map;
		}

		$file = IAC_DIR . 'includes/i18n/ru-map.php';
		self::$html_map = is_readable( $file ) ? require $file : array();
		if ( ! is_array( self::$html_map ) ) {
			self::$html_map = array();
		}

		return self::$html_map;
	}

	/**
	 * JS UI strings for wp_localize_script.
	 *
	 * @return array<string,string>
	 */
	public static function js_strings() {
		if ( null !== self::$js_map ) {
			return self::$js_map;
		}

		if ( ! self::is_ru() ) {
			self::$js_map = array();
			return self::$js_map;
		}

		$file = IAC_DIR . 'includes/i18n/ru-js.php';
		self::$js_map = is_readable( $file ) ? require $file : array();
		if ( ! is_array( self::$js_map ) ) {
			self::$js_map = array();
		}

		return self::$js_map;
	}

	/**
	 * Is the current request the Platform Access account page?
	 *
	 * @return bool
	 */
	private static function is_platform_access_request() {
		if ( class_exists( 'IAC_Feature_Page' ) && method_exists( 'IAC_Feature_Page', 'get_slug' ) ) {
			$slug = IAC_Feature_Page::get_slug();
			if ( '' !== $slug ) {
				return 'platform-access' === $slug;
			}
		}

		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		if ( ! is_string( $request ) || '' === $request ) {
			return false;
		}

		$path = trim( (string) parse_url( $request, PHP_URL_PATH ), '/' );
		return in_array(
			$path,
			array(
				'accounts/platform-access',
				'features/conversational-debugging',
			),
			true
		);
	}

	/**
	 * Apply page-specific Platform Access copy before the global RU map.
	 *
	 * Kept in a separate file so identical English labels can be translated
	 * differently by screen without touching React, hydration or live DOM.
	 *
	 * @param string $html Template HTML.
	 * @return string
	 */
	private static function localize_platform_access_html( $html ) {
		static $loaded    = false;
		static $localizer = null;

		if ( ! $loaded ) {
			$file = IAC_DIR . 'includes/i18n/ru-platform-access.php';
			$localizer = is_readable( $file ) ? require $file : null;
			$loaded = true;
		}

		if ( is_callable( $localizer ) ) {
			return $localizer( $html );
		}

		return $html;
	}

	/**
	 * Replace English copy in rendered HTML.
	 *
	 * @param string $html Template HTML.
	 * @return string
	 */
	public static function localize_html( $html ) {
		if ( ! self::is_ru() || '' === $html ) {
			return $html;
		}

		if ( self::is_platform_access_request() ) {
			$html = self::localize_platform_access_html( $html );
		}

		$map = self::html_map();
		if ( empty( $map ) ) {
			return $html;
		}

		uksort(
			$map,
			static function ( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			}
		);

		$tokens = array();
		$index  = 0;

		$patterns = array(
			'/<script\b[^>]*>[\s\S]*?<\/script>/i',
			'/<style\b[^>]*>[\s\S]*?<\/style>/i',
			'/\s(?:src|href|srcset|action|poster|data-src|data-href|content)=(["\'])(?:\\\\.|(?!\1).)*\1/i',
			'/url\((["\']?)(?:\\\\.|(?!\\1\)).)*\\1\)/i',
		);

		foreach ( $patterns as $pattern ) {
			$html = preg_replace_callback(
				$pattern,
				static function ( $matches ) use ( &$tokens, &$index ) {
					$key            = '%%IACI18N' . $index . '%%';
					$tokens[ $key ] = $matches[0];
					$index++;
					return $key;
				},
				$html
			);
		}

		$html = preg_replace_callback(
			'/\sclass=(["\'])(?:\\\\.|(?!\1).)*\1/i',
			static function ( $matches ) use ( &$tokens, &$index ) {
				$key            = '%%IACI18N' . $index . '%%';
				$tokens[ $key ] = $matches[0];
				$index++;
				return $key;
			},
			$html
		);

		$html = str_replace( array_keys( $map ), array_values( $map ), $html );

		if ( ! empty( $tokens ) ) {
			$html = str_replace( array_keys( $tokens ), array_values( $tokens ), $html );
		}

		return $html;
	}

	/**
	 * Resolve template path (prefers templates/ru/ when available).
	 *
	 * @param string $file Template filename.
	 * @return string Absolute path.
	 */
	public static function template_path( $file ) {
		$file = ltrim( $file, '/' );
		if ( self::is_ru() ) {
			$ru = IAC_TEMPLATES . 'ru/' . $file;
			if ( is_file( $ru ) ) {
				return $ru;
			}
		}
		return IAC_TEMPLATES . $file;
	}

	/**
	 * Set lang cookie from JS (early bootstrap snippet).
	 *
	 * @return string Inline script.
	 */
	public static function bootstrap_script() {
		return '<script>(function(){try{var lang="ru",m4=document.cookie.match(/(?:^|;\\s*)iac_lang4=(en|ru)/),m3=document.cookie.match(/(?:^|;\\s*)iac_lang3=(en|ru)/),m=document.cookie.match(/(?:^|;\\s*)iac_lang=(en|ru)/);if(m4)lang=m4[1];else if(m3)lang=m3[1];else if(m)lang=m[1];else{var ls=localStorage.getItem("iac-lang");if(ls==="ru"||ls==="en")lang=ls}if(lang!=="en"&&lang!=="ru")lang="ru";document.documentElement.lang=lang;document.documentElement.classList.toggle("iac-lang-ru",lang==="ru");document.cookie="iac_lang=;path=/;max-age=0";document.cookie="iac_lang3=;path=/;max-age=0";document.cookie="iac_lang4="+lang+";path=/;max-age="+(60*60*24*365);localStorage.setItem("iac-lang",lang);var h=location.hostname;document.cookie="googtrans=;path=/;expires=Thu, 01 Jan 1970 00:00:00 GMT";document.cookie="googtrans=;path=/;domain=."+h+";expires=Thu, 01 Jan 1970 00:00:00 GMT";var p=location.pathname||"/";if((p==="/"||p==="")&&location.search.indexOf("_lc=")===-1){location.replace("/?_lc=2")}}catch(e){}})();</script>';
	}

	/**
	 * Canonical homepage URL (cache-busted for Hostinger CDN).
	 *
	 * @return string
	 */
	public static function home_url_busted() {
		return home_url( '/?_lc=2' );
	}
}
