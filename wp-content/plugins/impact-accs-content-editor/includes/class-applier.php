<?php
/**
 * Apply text/link overrides to rendered HTML safely.
 *
 * @package ImpactAccsContentEditor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output filter.
 */
class IACCE_Applier {

	/** @var IACCE_Applier|null */
	private static $instance = null;

	/** @var bool */
	private $buffering = false;

	/**
	 * @return IACCE_Applier
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'template_redirect', array( $this, 'start_buffer' ), -999 );
	}

	/**
	 * Start full-page output buffer (covers homepage exit()).
	 */
	public function start_buffer() {
		if ( $this->buffering || is_admin() || wp_doing_ajax() ) {
			return;
		}
		// The mirrored React/WebGPU homepage has a dedicated, versioned RU copy
		// source. Database overrides must not rewrite its HTML or live React DOM.
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$path = is_string( $uri ) ? (string) wp_parse_url( $uri, PHP_URL_PATH ) : '';
		if ( '/' === $path || '' === $path ) {
			return;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}
		if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$this->buffering = true;
		ob_start(
			function ( $html ) {
				return $this->apply( (string) $html );
			}
		);
	}

	/**
	 * Apply all overrides to HTML.
	 *
	 * @param string $html HTML.
	 * @return string
	 */
	public function apply( $html ) {
		if ( '' === $html || is_admin() ) {
			return $html;
		}

		$lang   = class_exists( 'IAC_I18n' ) ? IAC_I18n::lang() : 'en';
		$map    = $this->build_replacement_map( $lang );
		$links  = $this->link_map();
		$client = self::build_client_pairs( $lang );
		$link_pairs = $this->link_pairs_for_client( $links );

		if ( ! empty( $map ) ) {
			$html = $this->safe_replace( $html, $map );
		}
		if ( ! empty( $links ) ) {
			$html = $this->apply_link_overrides( $html, $links );
		}
		$html = $this->patch_home_i18n_payload( $html, $lang, $client );
		$html = $this->inject_live_script( $html, $client, $lang, $link_pairs );

		return $html;
	}

	/**
	 * Client-side replacement pairs (header, footer, 3D, React CSR).
	 *
	 * @param string $lang Language.
	 * @return array<int,array{from:string,to:string}>
	 */
	public static function build_client_pairs( $lang ) {
		$registry  = get_option( IACCE_OPTION_REGISTRY, array() );
		$overrides = get_option( IACCE_OPTION_OVERRIDES, array() );
		$texts     = isset( $registry['texts'] ) && is_array( $registry['texts'] ) ? $registry['texts'] : array();
		$pairs     = array();
		$seen      = array();

		foreach ( $texts as $item ) {
			if ( empty( $item['id'] ) || empty( $item['en'] ) ) {
				continue;
			}
			$id = $item['id'];
			$ov = isset( $overrides[ $id ] ) && is_array( $overrides[ $id ] ) ? $overrides[ $id ] : array();
			$en = (string) $item['en'];
			$ru = ! empty( $item['ru'] ) ? (string) $item['ru'] : '';

			if ( 'ru' === $lang ) {
				if ( ! empty( $ov['ru'] ) && $ov['ru'] !== $ru && '' !== $ru ) {
					self::add_pair( $pairs, $seen, $ru, (string) $ov['ru'] );
				}
				if ( ! empty( $ov['ru'] ) && $ov['ru'] !== $en ) {
					self::add_pair( $pairs, $seen, $en, (string) $ov['ru'] );
				}
				if ( ! empty( $ov['en'] ) && $ov['en'] !== $en ) {
					self::add_pair( $pairs, $seen, $en, (string) $ov['en'] );
				}
			} elseif ( ! empty( $ov['en'] ) && $ov['en'] !== $en ) {
				self::add_pair( $pairs, $seen, $en, (string) $ov['en'] );
			}
		}

		usort(
			$pairs,
			static function ( $a, $b ) {
				return strlen( $b['from'] ) - strlen( $a['from'] );
			}
		);

		return self::expand_case_variants( $pairs, $texts );
	}

	/**
	 * Same override for REQUEST ACCESS / Request access etc.
	 *
	 * @param array<int,array{from:string,to:string}> $pairs Pairs.
	 * @param array<int,array<string,mixed>>          $texts Registry texts.
	 * @return array<int,array{from:string,to:string}>
	 */
	private static function expand_case_variants( $pairs, $texts ) {
		$seen_keys = array();
		foreach ( $pairs as $pair ) {
			$seen_keys[ md5( ( $pair['from'] ?? '' ) . '|' . ( $pair['to'] ?? '' ) ) ] = true;
		}

		$all = array();
		foreach ( $texts as $item ) {
			if ( ! empty( $item['en'] ) ) {
				$all[] = (string) $item['en'];
			}
			if ( ! empty( $item['ru'] ) ) {
				$all[] = (string) $item['ru'];
			}
		}

		foreach ( $pairs as $pair ) {
			$from = (string) ( $pair['from'] ?? '' );
			$to   = (string) ( $pair['to'] ?? '' );
			if ( '' === $from || '' === $to ) {
				continue;
			}
			foreach ( $all as $candidate ) {
				if ( $candidate === $from || strcasecmp( $candidate, $from ) !== 0 ) {
					continue;
				}
				$key = md5( $candidate . '|' . $to );
				if ( isset( $seen_keys[ $key ] ) ) {
					continue;
				}
				$seen_keys[ $key ] = true;
				$pairs[]           = array(
					'from' => $candidate,
					'to'   => $to,
				);
			}
		}

		usort(
			$pairs,
			static function ( $a, $b ) {
				return strlen( $b['from'] ) - strlen( $a['from'] );
			}
		);

		return $pairs;
	}

	/**
	 * @param array<int,array{from:string,to:string}> $pairs Pairs.
	 * @param array<string,bool>                      $seen  Seen.
	 * @param string                                  $from  From.
	 * @param string                                  $to    To.
	 */
	private static function add_pair( &$pairs, &$seen, $from, $to ) {
		$from = trim( $from );
		$to   = trim( $to );
		if ( '' === $from || '' === $to || $from === $to ) {
			return;
		}
		$key = md5( $from . '|' . $to );
		if ( isset( $seen[ $key ] ) ) {
			return;
		}
		$seen[ $key ] = true;
		$pairs[]        = array(
			'from' => $from,
			'to'   => $to,
		);
	}

	/**
	 * @param string $lang Language.
	 * @return array<string,string>
	 */
	private function build_replacement_map( $lang ) {
		$registry  = get_option( IACCE_OPTION_REGISTRY, array() );
		$overrides = get_option( IACCE_OPTION_OVERRIDES, array() );
		$texts     = isset( $registry['texts'] ) && is_array( $registry['texts'] ) ? $registry['texts'] : array();
		$map       = array();

		foreach ( $texts as $item ) {
			if ( empty( $item['id'] ) || empty( $item['en'] ) ) {
				continue;
			}
			$id = $item['id'];
			$ov = isset( $overrides[ $id ] ) && is_array( $overrides[ $id ] ) ? $overrides[ $id ] : array();
			$en = (string) $item['en'];
			$ru = ! empty( $item['ru'] ) ? (string) $item['ru'] : $en;

			if ( 'ru' === $lang ) {
				if ( ! empty( $ov['ru'] ) && $ov['ru'] !== $ru ) {
					$map[ $ru ] = (string) $ov['ru'];
				}
				if ( ! empty( $ov['en'] ) && $ov['en'] !== $en ) {
					$map[ $en ] = (string) $ov['en'];
				}
			} elseif ( ! empty( $ov['en'] ) && $ov['en'] !== $en ) {
				$map[ $en ] = (string) $ov['en'];
			}
		}

		uksort(
			$map,
			static function ( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			}
		);

		return $this->expand_map_case_variants( $map, $texts );
	}

	/**
	 * @param array<string,string>           $map   Replacement map.
	 * @param array<int,array<string,mixed>> $texts Registry texts.
	 * @return array<string,string>
	 */
	private function expand_map_case_variants( $map, $texts ) {
		$all = array();
		foreach ( $texts as $item ) {
			if ( ! empty( $item['en'] ) ) {
				$all[] = (string) $item['en'];
			}
			if ( ! empty( $item['ru'] ) ) {
				$all[] = (string) $item['ru'];
			}
		}

		foreach ( $map as $from => $to ) {
			foreach ( $all as $candidate ) {
				if ( $candidate === $from || strcasecmp( $candidate, $from ) !== 0 || isset( $map[ $candidate ] ) ) {
					continue;
				}
				$map[ $candidate ] = $to;
			}
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
	 * @param array<string,string> $map Link href map.
	 * @return array<int,array{from:string,to:string}>
	 */
	private function link_pairs_for_client( $map ) {
		$pairs = array();
		foreach ( $map as $from => $to ) {
			if ( '' === $from || '' === $to || $from === $to ) {
				continue;
			}
			$pairs[] = array(
				'from' => (string) $from,
				'to'   => (string) $to,
			);
		}
		return $pairs;
	}

	/**
	 * @return array<string,string>
	 */
	private function link_map() {
		$registry = get_option( IACCE_OPTION_REGISTRY, array() );
		$stored   = get_option( IACCE_OPTION_LINKS, array() );
		$links    = isset( $registry['links'] ) && is_array( $registry['links'] ) ? $registry['links'] : array();
		$map      = array();

		foreach ( $links as $link ) {
			if ( empty( $link['id'] ) || empty( $link['href'] ) ) {
				continue;
			}
			$override = isset( $stored[ $link['id'] ] ) ? trim( (string) $stored[ $link['id'] ] ) : '';
			if ( '' === $override || $override === $link['href'] ) {
				continue;
			}
			if ( ! $this->is_safe_url( $override ) ) {
				continue;
			}
			$map[ (string) $link['href'] ] = $override;
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
	 * @param string               $html HTML.
	 * @param array<string,string> $map  Link map.
	 * @return string
	 */
	private function apply_link_overrides( $html, $map ) {
		if ( empty( $map ) ) {
			return $html;
		}

		return preg_replace_callback(
			'/(\shref=)(["\'])((?:\\\\.|(?!\2).)*)\2/i',
			static function ( $m ) use ( $map ) {
				$href = html_entity_decode( $m[3], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				if ( isset( $map[ $href ] ) ) {
					return $m[1] . $m[2] . esc_attr( $map[ $href ] ) . $m[2];
				}
				return $m[0];
			},
			$html
		);
	}

	/**
	 * @param string                            $html   HTML.
	 * @param array<int,array{from:string,to:string}> $client Client pairs.
	 * @param string                            $lang   Lang.
	 * @return string
	 */
	private function patch_home_i18n_payload( $html, $lang, $client ) {
		if ( false === stripos( $html, 'iacData' ) ) {
			return $html;
		}

		return preg_replace_callback(
			'/var\s+iacData\s*=\s*(\{[\s\S]*?\});/i',
			function ( $m ) use ( $lang, $client ) {
				$data = json_decode( $m[1], true );
				if ( ! is_array( $data ) ) {
					return $m[0];
				}

				$extra_exact = array();
				$extra_html  = array();

				foreach ( $client as $pair ) {
					if ( empty( $pair['from'] ) || empty( $pair['to'] ) ) {
						continue;
					}
					$extra_exact[ $pair['from'] ] = $pair['to'];
					$extra_html[ $pair['from'] ]  = $pair['to'];
				}

				if ( ! empty( $extra_exact ) ) {
					$current = isset( $data['exactReplacements'] ) && is_array( $data['exactReplacements'] ) ? $data['exactReplacements'] : array();
					$data['exactReplacements'] = array_merge( $current, $extra_exact );
				}
				if ( ! empty( $extra_html ) ) {
					$current = isset( $data['htmlReplacements'] ) && is_array( $data['htmlReplacements'] ) ? $data['htmlReplacements'] : array();
					$data['htmlReplacements'] = array_merge( $current, $extra_html );
				}

				$data['iacceConfig'] = array(
					'lang'  => $lang,
					'pairs' => $client,
				);

				$encoded = wp_json_encode( $data );
				if ( ! is_string( $encoded ) ) {
					return $m[0];
				}

				return 'var iacData=' . $encoded . ';';
			},
			$html,
			1
		);
	}

	/**
	 * @param string                            $html   HTML.
	 * @param array<int,array{from:string,to:string}> $client Pairs.
	 * @param string                            $lang   Lang.
	 * @return string
	 */
	private function inject_live_script( $html, $client, $lang, $link_pairs = array() ) {
		if ( empty( $client ) && empty( $link_pairs ) ) {
			return $html;
		}

		if ( false !== stripos( $html, 'iacce-live-overrides.js' ) ) {
			return $html;
		}

		$config  = wp_json_encode(
			array(
				'lang'  => $lang,
				'pairs' => $client,
				'links' => $link_pairs,
			)
		);
		$src     = esc_url( IACCE_URL . 'assets/live-overrides.js?v=' . rawurlencode( IACCE_VERSION ) );
		$snippet = '<script>window.iacceConfig=' . $config . ';</script>'
			. '<script src="' . $src . '" defer></script>';

		if ( false !== stripos( $html, '</body>' ) ) {
			return preg_replace( '/<\/body>/i', $snippet . '</body>', $html, 1 );
		}

		return $html . $snippet;
	}

	/**
	 * Safe str_replace with protected regions.
	 *
	 * @param string               $html HTML.
	 * @param array<string,string> $map  Map.
	 * @return string
	 */
	private function safe_replace( $html, $map ) {
		if ( empty( $map ) ) {
			return $html;
		}

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
					$key            = '%%IACCE' . $index . '%%';
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
				$key            = '%%IACCE' . $index . '%%';
				$tokens[ $key ] = $matches[0];
				$index++;
				return $key;
			},
			$html
		);

		foreach ( $map as $from => $to ) {
			$html = self::safe_str_replace( $html, (string) $from, (string) $to );
		}

		if ( ! empty( $tokens ) ) {
			$html = str_replace( array_keys( $tokens ), array_values( $tokens ), $html );
		}

		return $html;
	}

	/**
	 * One replacement without prefix-extension loops (optional → optionals).
	 *
	 * @param string $haystack Haystack.
	 * @param string $from     From.
	 * @param string $to       To.
	 * @return string
	 */
	private static function safe_str_replace( $haystack, $from, $to ) {
		if ( '' === $from || $from === $to || false === strpos( $haystack, $from ) ) {
			return $haystack;
		}
		// "optional" → "optionals": replace only whole phrase, not inside already-changed text.
		if ( 0 === strpos( $to, $from ) && strlen( $to ) > strlen( $from ) ) {
			$pattern = '/' . preg_quote( $from, '/' ) . '(?![\p{L}])/u';
			return (string) preg_replace( $pattern, $to, $haystack );
		}
		$next = str_replace( $from, $to, $haystack );
		if ( $next !== $to && false !== strpos( $next, $from ) ) {
			return $haystack;
		}
		return $next;
	}

	/**
	 * @param string $url URL.
	 * @return bool
	 */
	private function is_safe_url( $url ) {
		if ( '' === $url ) {
			return false;
		}
		if ( '#' === $url[0] ) {
			return true;
		}
		if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) {
			return true;
		}
		if ( preg_match( '/^(mailto|tel|sms):/i', $url ) ) {
			return (bool) preg_match( '/^(mailto:[^\s<>"\']+|tel:\+?[\d\s\-\(\)\.]+|sms:\+?[\d\s\-\(\)\.]+)$/i', $url );
		}
		return (bool) wp_http_validate_url( $url );
	}
}
