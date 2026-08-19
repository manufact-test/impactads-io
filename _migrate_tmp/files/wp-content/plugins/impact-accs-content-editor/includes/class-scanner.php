<?php
/**
 * Scan templates and i18n files for editable strings.
 *
 * @package ImpactAccsContentEditor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once IACCE_DIR . 'includes/class-labels.php';
require_once IACCE_DIR . 'includes/class-blocks.php';

/**
 * Content scanner.
 */
class IACCE_Scanner {

	/**
	 * Build full text + link registry.
	 *
	 * @return array{texts: array<int,array<string,mixed>>, links: array<int,array<string,mixed>>}
	 */
	public static function build_registry() {
		$texts  = array();
		$links  = array();
		$seen   = array();
		$ru_map = self::load_ru_map();

		$sources = self::content_sources();
		foreach ( $sources as $source ) {
			$group = $source['group'];
			$path  = $source['path'];
			if ( ! is_readable( $path ) ) {
				continue;
			}

			$content  = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$ext      = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
			$basename = basename( $path );

			if ( 'json' === $ext ) {
				self::scan_json_pairs( $content, $group, $basename, $texts, $seen, $ru_map );
				continue;
			}

			if ( 'php' === $ext ) {
				self::scan_php_map( $path, $group, $basename, $texts, $seen, $ru_map );
				continue;
			}

			// Minified homepage SSR — only clean template HTML files, not index.html DOM.
			if ( 'index.html' === $basename ) {
				continue;
			}

			self::scan_html( $content, $group, $basename, $texts, $links, $seen, $ru_map );
		}

		// Homepage texts live in i18n maps — ensure they are included.
		self::scan_homepage_i18n( $texts, $seen, $ru_map );

		// Nav labels often appear first in ru-map (category "other") — re-assign to header menu.
		self::ensure_nav_menu( $texts, $seen, $ru_map );

		// Phone numbers and contact lines skipped by is_valid_string (no letters).
		self::ensure_contact_info( $texts, $seen, $ru_map );

		foreach ( $texts as &$item ) {
			if ( empty( $item['ru'] ) && ! empty( $item['en'] ) ) {
				$item['ru'] = self::lookup_ru( (string) $item['en'], $ru_map );
			}
		}
		unset( $item );

		foreach ( $texts as &$item ) {
			$meta             = IACCE_Labels::resolve( $item['group'] ?? '', $item['source'] ?? '' );
			$item['section']  = $meta['section'];
			$item['category'] = $meta['category'];
			$item['preview']  = IACCE_Labels::preview( $item['en'] ?? '' );
			$item             = IACCE_Blocks::enrich( $item );
		}
		unset( $item );

		usort(
			$texts,
			static function ( $a, $b ) {
				$c = strcmp( $a['category'] ?? '', $b['category'] ?? '' );
				if ( 0 !== $c ) {
					return $c;
				}
				$bo = ( $a['block_order'] ?? 999 ) <=> ( $b['block_order'] ?? 999 );
				if ( 0 !== $bo ) {
					return $bo;
				}
				$bt = strcmp( $a['block_title'] ?? '', $b['block_title'] ?? '' );
				if ( 0 !== $bt ) {
					return $bt;
				}
				return strcmp( $a['en'] ?? '', $b['en'] ?? '' );
			}
		);

		usort(
			$links,
			static function ( $a, $b ) {
				return strcmp( $a['group'] ?? '', $b['group'] ?? '' );
			}
		);

		foreach ( $links as &$link ) {
			$meta             = IACCE_Labels::resolve( $link['group'] ?? '', $link['source'] ?? '' );
			$link['section']  = $meta['section'];
			$link['category'] = $meta['category'];
		}
		unset( $link );

		return array(
			'texts'   => array_values( $texts ),
			'links'   => array_values( $links ),
			'scanned' => gmdate( 'c' ),
			'version' => IACCE_VERSION,
		);
	}

	/**
	 * Known header / mobile menu labels (SSR + JS fallbacks).
	 *
	 * @return array<int,string>
	 */
	public static function nav_menu_catalog() {
		return array(
			'About',
			'Blog',
			'Contact',
			'ACCOUNTS',
			'Accounts',
			'Request access',
			'Platform Access',
			'Agency Accounts',
			'Team Supply',
			'Menu',
		);
	}

	/**
	 * Tag registry row as header nav menu (works without rescan).
	 *
	 * @param array<string,mixed> $item Text row.
	 * @return array<string,mixed>
	 */
	public static function apply_nav_menu_meta( $item ) {
		$en = self::normalize_text( (string) ( $item['en'] ?? '' ) );
		if ( ! in_array( $en, self::nav_menu_catalog(), true ) ) {
			return $item;
		}

		$item['block_hint'] = 'Header nav';
		$item['nav_menu']   = true;
		$item['group']      = 'Шапка · меню';
		$item['source']     = 'header-nav';
		if ( empty( $item['ru'] ) ) {
			$item['ru'] = self::lookup_ru( $en );
		}

		return $item;
	}

	/**
	 * Ensure all nav menu strings exist and belong to header-nav block.
	 *
	 * @param array<int,array<string,mixed>> $texts  Texts.
	 * @param array<string,bool>             $seen   Seen keys.
	 * @param array<string,string>           $ru_map RU map.
	 */
	private static function ensure_nav_menu( &$texts, &$seen, $ru_map ) {
		$by_id = array();
		foreach ( $texts as $idx => $item ) {
			if ( ! empty( $item['id'] ) ) {
				$by_id[ $item['id'] ] = $idx;
			}
		}

		foreach ( self::nav_menu_catalog() as $en ) {
			$en = self::normalize_text( $en );
			$id = md5( $en );

			if ( isset( $by_id[ $id ] ) ) {
				$texts[ $by_id[ $id ] ] = self::apply_nav_menu_meta( $texts[ $by_id[ $id ] ] );
				continue;
			}

			self::add_text( $en, 'Шапка · меню', 'header-nav', $texts, $seen, $ru_map, 'Header nav' );
			$last = count( $texts ) - 1;
			if ( $last >= 0 ) {
				$texts[ $last ] = self::apply_nav_menu_meta( $texts[ $last ] );
				$by_id[ $id ]   = $last;
			}
		}
	}

	/**
	 * Contact modal / page copy (phone, email, address).
	 *
	 * @return array<int,string>
	 */
	public static function contact_info_catalog() {
		return array(
			'+7 (495) 312-84-67',
			'contact@impact.accs',
			'partners@impact.accs',
			'12 Lenin Street,',
			'123112 Moscow, Russia',
		);
	}

	/**
	 * Tag registry row as contact modal content (works without rescan).
	 *
	 * @param array<string,mixed> $item Text row.
	 * @return array<string,mixed>
	 */
	public static function apply_contact_meta( $item ) {
		$en = self::normalize_text( (string) ( $item['en'] ?? '' ) );
		if ( ! in_array( $en, self::contact_info_catalog(), true ) && ! self::is_phone_number( $en ) ) {
			return $item;
		}

		$item['block_hint']  = 'Contact modal';
		$item['contact_info'] = true;
		$item['group']       = 'Контакты · модалка';
		$item['source']      = 'contact-modal';
		if ( empty( $item['ru'] ) ) {
			$item['ru'] = self::lookup_ru( $en );
		}

		return $item;
	}

	/**
	 * Ensure contact phone/email/address strings exist in registry.
	 *
	 * @param array<int,array<string,mixed>> $texts  Texts.
	 * @param array<string,bool>             $seen   Seen keys.
	 * @param array<string,string>           $ru_map RU map.
	 */
	private static function ensure_contact_info( &$texts, &$seen, $ru_map ) {
		$by_id = array();
		foreach ( $texts as $idx => $item ) {
			if ( ! empty( $item['id'] ) ) {
				$by_id[ $item['id'] ] = $idx;
			}
		}

		foreach ( self::contact_info_catalog() as $en ) {
			$en = self::normalize_text( $en );
			$id = md5( $en );

			if ( isset( $by_id[ $id ] ) ) {
				$texts[ $by_id[ $id ] ] = self::apply_contact_meta( $texts[ $by_id[ $id ] ] );
				continue;
			}

			self::add_text( $en, 'Контакты · модалка', 'contact-modal.html', $texts, $seen, $ru_map, 'Contact modal', true );
			$last = count( $texts ) - 1;
			if ( $last >= 0 ) {
				$texts[ $last ] = self::apply_contact_meta( $texts[ $last ] );
				$by_id[ $id ]   = $last;
			}
		}
	}

	/**
	 * Pull homepage animation / block copy from i18n PHP files.
	 *
	 * @param array<int,array<string,mixed>> $texts  Texts.
	 * @param array<string,bool>             $seen   Seen.
	 * @param array<string,string>           $ru_map RU.
	 */
	private static function scan_homepage_i18n( &$texts, &$seen, $ru_map ) {
		$home_dir = defined( 'IAH_DIR' ) ? IAH_DIR : WP_PLUGIN_DIR . '/impact-accs-homepage/';
		$files    = array(
			$home_dir . 'includes/i18n/ru-home-extra.php'  => 'Главная · анимации и блоки',
			$home_dir . 'includes/i18n/ru-home-exact.php'  => 'Главная · кнопки и заголовки',
		);

		foreach ( $files as $path => $label ) {
			if ( ! is_readable( $path ) ) {
				continue;
			}
			self::scan_php_map( $path, $label, basename( $path ), $texts, $seen, $ru_map );
		}
	}

	/**
	 * @return array<int,array{group:string,path:string}>
	 */
	private static function content_sources() {
		$sources    = array();
		$chrome_dir = defined( 'IAC_DIR' ) ? IAC_DIR : WP_PLUGIN_DIR . '/impact-accs-chrome/';
		$home_dir   = defined( 'IAH_DIR' ) ? IAH_DIR : WP_PLUGIN_DIR . '/impact-accs-homepage/';

		$map = array(
			array( $chrome_dir . 'templates/', 'Шаблоны' ),
			array( $home_dir . 'content/', 'Главная' ),
			array( $chrome_dir . 'includes/i18n/', 'Переводы' ),
			array( $home_dir . 'includes/i18n/', 'Главная · переводы' ),
		);

		foreach ( $map as $item ) {
			$dir = $item[0];
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				if ( ! $file instanceof SplFileInfo || ! $file->isFile() ) {
					continue;
				}
				$ext = strtolower( $file->getExtension() );
				if ( ! in_array( $ext, array( 'html', 'php', 'json' ), true ) ) {
					continue;
				}
				$sources[] = array(
					'group' => $item[1],
					'path'  => $file->getPathname(),
				);
			}
		}

		return $sources;
	}

	/**
	 * Full EN→RU map for admin + scanner.
	 *
	 * @return array<string,string>
	 */
	public static function get_ru_map() {
		static $cache = null;
		if ( null === $cache ) {
			$cache = self::load_ru_map();
		}
		return $cache;
	}

	/**
	 * Resolve Russian translation for an English string.
	 *
	 * @param string               $en     English text.
	 * @param array<string,string> $ru_map Optional map.
	 * @return string
	 */
	public static function lookup_ru( $en, $ru_map = null ) {
		if ( null === $ru_map ) {
			$ru_map = self::get_ru_map();
		}
		$en = self::normalize_text( $en );
		if ( '' === $en ) {
			return '';
		}
		if ( isset( $ru_map[ $en ] ) ) {
			return self::normalize_text( (string) $ru_map[ $en ] );
		}
		return '';
	}

	/**
	 * @return array<string,string>
	 */
	private static function load_ru_map() {
		$chrome_dir = defined( 'IAC_DIR' ) ? IAC_DIR : WP_PLUGIN_DIR . '/impact-accs-chrome/';
		$home_dir   = defined( 'IAH_DIR' ) ? IAH_DIR : WP_PLUGIN_DIR . '/impact-accs-homepage/';
		$maps       = array();
		$files      = array(
			$chrome_dir . 'includes/i18n/ru-map.php',
			$chrome_dir . 'includes/i18n/translations.json',
			$chrome_dir . 'includes/i18n/ru-js.php',
			$home_dir . 'includes/i18n/ru-home-extra.php',
			$home_dir . 'includes/i18n/ru-home-exact.php',
		);

		foreach ( $files as $file ) {
			if ( ! $file || ! is_readable( $file ) ) {
				continue;
			}
			if ( substr( $file, -5 ) === '.json' ) {
				$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				if ( is_array( $data ) ) {
					$maps = array_merge( $maps, $data );
				}
				continue;
			}
			$data = require $file;
			if ( is_array( $data ) ) {
				$maps = array_merge( $maps, $data );
			}
		}

		return $maps;
	}

	/**
	 * @param array<int,array<string,mixed>> $texts Texts.
	 * @param array<string,bool>             $seen  Seen keys.
	 * @param array<string,string>           $ru_map RU map.
	 */
	private static function add_text( $en, $group, $source, &$texts, &$seen, $ru_map, $block_hint = '', $force = false ) {
		$en = self::normalize_text( $en );
		if ( ! $force && ! self::is_valid_string( $en ) ) {
			return;
		}
		if ( $force && '' === $en ) {
			return;
		}

		$key = md5( $en );
		if ( isset( $seen[ $key ] ) ) {
			return;
		}
		$seen[ $key ] = true;

		$ru = self::lookup_ru( $en, $ru_map );

		$row = array(
			'id'     => $key,
			'en'     => $en,
			'ru'     => $ru,
			'group'  => $group,
			'source' => $source,
		);
		if ( '' !== $block_hint ) {
			$row['block_hint'] = $block_hint;
		}
		$texts[] = $row;
	}

	/**
	 * @param array<int,array<string,mixed>> $texts Texts.
	 * @param array<int,array<string,mixed>> $links Links.
	 * @param array<string,bool>             $seen  Seen.
	 * @param array<string,string>           $ru_map RU.
	 */
	private static function scan_html( $html, $group, $source, &$texts, &$links, &$seen, $ru_map ) {
		if ( '' === trim( $html ) ) {
			return;
		}

		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR );
		libxml_clear_errors();

		$xpath = new DOMXPath( $dom );

		foreach ( $xpath->query( '//text()' ) as $node ) {
			if ( ! $node instanceof DOMText ) {
				continue;
			}
			$parent = $node->parentNode;
			if ( ! $parent instanceof DOMElement ) {
				continue;
			}
			if ( in_array( strtolower( $parent->nodeName ), array( 'script', 'style', 'noscript', 'template' ), true ) ) {
				continue;
			}
			self::add_text( $node->wholeText, $group, $source, $texts, $seen, $ru_map );
		}

		foreach ( $xpath->query( '//*[@placeholder]' ) as $node ) {
			if ( $node instanceof DOMElement ) {
				self::add_text( $node->getAttribute( 'placeholder' ), $group, $source, $texts, $seen, $ru_map );
			}
		}

		foreach ( $xpath->query( '//*[@aria-label]' ) as $node ) {
			if ( $node instanceof DOMElement ) {
				self::add_text( $node->getAttribute( 'aria-label' ), $group, $source, $texts, $seen, $ru_map );
			}
		}

		foreach ( $xpath->query( '//*[@alt]' ) as $node ) {
			if ( $node instanceof DOMElement ) {
				self::add_text( $node->getAttribute( 'alt' ), $group, $source, $texts, $seen, $ru_map );
			}
		}

		$link_seen = array();
		foreach ( $xpath->query( '//a[@href]' ) as $node ) {
			if ( ! $node instanceof DOMElement ) {
				continue;
			}
			$href = trim( $node->getAttribute( 'href' ) );
			if ( '' === $href || '#' === $href || 0 === strpos( $href, 'javascript:' ) ) {
				continue;
			}
			$label = self::normalize_text( $node->textContent );
			$lid   = md5( $href . '|' . $label );
			if ( isset( $link_seen[ $lid ] ) ) {
				continue;
			}
			$link_seen[ $lid ] = true;
			$links[]           = array(
				'id'     => $lid,
				'href'   => $href,
				'label'  => $label,
				'group'  => $group,
				'source' => $source,
			);
		}
	}

	/**
	 * @param array<int,array<string,mixed>> $texts Texts.
	 * @param array<string,bool>             $seen  Seen.
	 * @param array<string,string>           $ru_map RU.
	 */
	private static function scan_json_pairs( $json, $group, $source, &$texts, &$seen, $ru_map ) {
		$data = json_decode( $json, true );
		if ( ! is_array( $data ) ) {
			return;
		}
		foreach ( $data as $en => $ru ) {
			if ( ! is_string( $en ) ) {
				continue;
			}
			self::add_text( $en, $group, $source, $texts, $seen, $ru_map );
		}
	}

	/**
	 * @param array<int,array<string,mixed>> $texts Texts.
	 * @param array<string,bool>             $seen  Seen.
	 * @param array<string,string>           $ru_map RU.
	 */
	private static function scan_php_map( $path, $group, $source, &$texts, &$seen, $ru_map ) {
		if ( ! is_readable( $path ) ) {
			return;
		}

		$lines      = file( $path, FILE_IGNORE_NEW_LINES );
		$block_hint = '';
		$parsed     = 0;

		if ( is_array( $lines ) ) {
			foreach ( $lines as $line ) {
				if ( preg_match( '/^\s*\/\/\s*(.+?)\s*$/', $line, $comment ) ) {
					$block_hint = trim( $comment[1] );
					continue;
				}
				if ( preg_match( "/^\s*'((?:\\\\'|[^'])*)'\s*=>/", $line, $match ) ) {
					self::add_text( stripcslashes( $match[1] ), $group, $source, $texts, $seen, $ru_map, $block_hint );
					++$parsed;
					continue;
				}
				if ( preg_match( '/^\s*"((?:\\\\.|[^"\\\\])*)"\s*=>/', $line, $match ) ) {
					self::add_text( stripcslashes( $match[1] ), $group, $source, $texts, $seen, $ru_map, $block_hint );
					++$parsed;
				}
			}
		}

		if ( $parsed > 0 ) {
			return;
		}

		$data = require $path;
		if ( ! is_array( $data ) ) {
			return;
		}
		foreach ( $data as $en => $ru ) {
			if ( ! is_string( $en ) ) {
				continue;
			}
			self::add_text( $en, $group, $source, $texts, $seen, $ru_map );
		}
	}

	/**
	 * @param string $text Text.
	 * @return string
	 */
	private static function normalize_text( $text ) {
		$text = html_entity_decode( (string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$text = preg_replace( '/\s+/u', ' ', $text );
		return trim( (string) $text );
	}

	/**
	 * @param string $text Text.
	 * @return bool
	 */
	private static function is_valid_string( $text ) {
		if ( '' === $text ) {
			return false;
		}
		if ( mb_strlen( $text ) < 2 ) {
			return false;
		}
		if ( mb_strlen( $text ) > 500 ) {
			return false;
		}
		if ( self::is_phone_number( $text ) ) {
			return true;
		}
		// Must contain letters (any language).
		if ( ! preg_match( '/[\p{L}]/u', $text ) ) {
			return false;
		}
		// Reject HTML / code fragments.
		if ( preg_match( '/<[^>]+>/', $text ) ) {
			return false;
		}
		if ( preg_match( '/<\/?(div|span|canvas|template|section|svg|script|style|path|rect|button)\b/i', $text ) ) {
			return false;
		}
		if ( preg_match( '/^\s*[<>]/', $text ) ) {
			return false;
		}
		if ( preg_match( '/\bclass\s*=/i', $text ) ) {
			return false;
		}
		if ( preg_match( '/\b(data-|aria-|xmlns|viewBox|fill=|stroke=)/i', $text ) ) {
			return false;
		}
		if ( preg_match( '/^\$[A-Za-z0-9_,\[\]\{\}\s"\':\\\\]+$/', $text ) ) {
			return false;
		}
		if ( preg_match( '/^[\d\s\W]+$/u', $text ) ) {
			return false;
		}
		if ( self::looks_like_code( $text ) ) {
			return false;
		}
		if ( preg_match( '/^https?:\/\//i', $text ) ) {
			return false;
		}
		if ( preg_match( '/\.(js|css|png|jpg|webp|woff2?|svg|ico|mp4)(\?|$)/i', $text ) ) {
			return false;
		}
		// Mostly punctuation / brackets.
		if ( preg_match( '/^[\s\W<>="\']+$/u', $text ) ) {
			return false;
		}
		return true;
	}

	/**
	 * @param string $text Text.
	 * @return bool
	 */
	private static function looks_like_code( $text ) {
		if ( preg_match( '/^(function|var |const |let |import |export |\/\*|<\?php|<script|webpack|__NEXT|\$\$)/i', $text ) ) {
			return true;
		}
		if ( substr_count( $text, '{' ) > 1 || substr_count( $text, '}' ) > 1 ) {
			return true;
		}
		if ( substr_count( $text, ';' ) > 1 ) {
			return true;
		}
		if ( preg_match( '/\\\\n|\\\\t|\\\\u[0-9a-f]{4}/i', $text ) ) {
			return true;
		}
		return false;
	}

	/**
	 * @param string $text Text.
	 * @return bool
	 */
	private static function is_phone_number( $text ) {
		return (bool) preg_match( '/^\+?[\d\s\(\)\-\.]{7,}$/u', trim( $text ) );
	}
}
