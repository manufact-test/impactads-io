<?php
/**
 * Narrow source-level fixes for the homepage hero and the purchase-format block.
 *
 * This layer owns only three known UI chunks. It preserves Turbopack logical
 * chunk identity and never mutates the live React/3D DOM.
 *
 * @package ImpactAccsHomepage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IAH_Home_First_Two_Fixes {

	/** Direct endpoint for the narrowly patched chunks. */
	private const ENDPOINT = 'iah-home-first-two-fixes';

	/** Hero dialogue components shared by desktop/mobile scene. */
	private const DIALOGUE_CHUNK = 'f7f1c59a71681025.js';

	/** Scroll indicator + chat primitives + batch card. */
	private const SHARED_UI_CHUNK = '692acfebb5322696.js';

	/** Second homepage block with the three purchase-format scenes. */
	private const HOME_CONTENT_CHUNK = '827ff3490ba1793e.js';

	/**
	 * Register before the existing phase-2 endpoint and outside its document
	 * buffer, so this layer receives the final already-assembled homepage HTML.
	 */
	public static function boot() {
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_serve_chunk' ), -997 );
		add_action( 'template_redirect', array( __CLASS__, 'start_document_buffer' ), -1002 );
	}

	/**
	 * Serve one explicitly owned chunk through the source-level patch.
	 */
	public static function maybe_serve_chunk() {
		$chunk = self::requested_chunk();
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

		if ( self::DIALOGUE_CHUNK === $chunk ) {
			$js = self::patch_dialogue_source( $js );
			$js = self::apply_existing_ru_map( $js, true );
			$js = self::patch_dialogue_after_map( $js );
		} elseif ( self::SHARED_UI_CHUNK === $chunk ) {
			$js = self::patch_shared_ui( $js );
		} elseif ( self::HOME_CONTENT_CHUNK === $chunk ) {
			$js = self::apply_existing_ru_map( $js, false );
			$js = self::patch_media_buying_animation( $js );
		}

		if ( ! headers_sent() ) {
			header( 'Content-Type: application/javascript; charset=utf-8' );
			header( 'Cache-Control: public, max-age=31536000, immutable' );
			header( 'X-IAH-Homepage-Fix: first-two-blocks' );
		}

		/* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
		echo $js;
		exit;
	}

	/**
	 * Start as the outer document buffer. Existing native-RU buffers start later
	 * and therefore finish first; this callback receives their final HTML.
	 */
	public static function start_document_buffer() {
		if ( ! self::is_home_request() ) {
			return;
		}

		ob_start( array( __CLASS__, 'patch_document' ) );
	}

	/**
	 * Point only the three confirmed UI scripts to this endpoint.
	 *
	 * @param string $html Homepage document.
	 * @return string
	 */
	public static function patch_document( $html ) {
		if ( ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		foreach ( self::owned_chunks() as $chunk ) {
			$html = self::rewrite_parser_chunk_src( $html, $chunk );
		}

		/* Match the interactive button classes rendered by the patched client chunk. */
		$html = str_replace( 'pointer-events-none min-w-auto', 'pointer-events-auto min-w-auto', $html );
		$html = str_replace(
			'pointer-events-none min-w-0 shrink-0 border-[#1F2328] bg-[#1F2328] px-4 text-white hover:bg-[#32383f]',
			'pointer-events-auto min-w-0 shrink-0 border-[#1F2328] bg-[#1F2328] px-4 text-white hover:bg-[#32383f]',
			$html
		);

		/* Keep homepage header SSR, Flight/client source and existing click guards on the same contact target. */
		$contact_url = esc_url( home_url( '/contact/' ) );
		if ( '' !== $contact_url ) {
			$header_start = stripos( $html, '<header' );
			$header_end   = false !== $header_start ? stripos( $html, '</header>', $header_start ) : false;
			if ( false !== $header_start && false !== $header_end ) {
				$header_end += strlen( '</header>' );
				$header      = substr( $html, $header_start, $header_end - $header_start );
				$app_url     = esc_url( home_url( '/application/' ) );
				$header      = str_replace( 'href="?waitlist=true"', 'href="' . $contact_url . '"', $header );
				if ( '' !== $app_url ) {
					$header = str_replace( 'href="' . $app_url . '"', 'href="' . $contact_url . '"', $header );
				}
				$html = substr_replace( $html, $header, $header_start, $header_end - $header_start );
			}

			$contact_js = esc_js( home_url( '/contact/' ) );
			$old_cta_js = esc_js( home_url( '/#iac-final-cta' ) );
			$html       = str_replace( 'var U="#iac-final-cta";function hit', 'var U="' . $contact_js . '";function hit', $html );
			foreach ( array( '?waitlist=true', 'request access', 'get access', 'запросить доступ', 'получить доступ', 'связаться' ) as $label ) {
				$html = str_replace( '"' . $label . '":"' . $old_cta_js . '"', '"' . $label . '":"' . $contact_js . '"', $html );
			}
		}

		return $html;
	}

	/**
	 * @return array<int,string>
	 */
	private static function owned_chunks() {
		return array(
			self::DIALOGUE_CHUNK,
			self::SHARED_UI_CHUNK,
			self::HOME_CONTENT_CHUNK,
		);
	}

	/**
	 * Preserve the existing RU copy in chunks already handled by the native RU
	 * layer. Program-state identifiers stay untouched here and are changed only
	 * in exact visible JSX structures below.
	 *
	 * @param string $js Chunk source.
	 * @param bool   $full_map Use the complete existing hero map.
	 * @return string
	 */
	private static function apply_existing_ru_map( $js, $full_map ) {
		if ( ! class_exists( 'IAH_Home_Native_Ru_Phase2' ) ) {
			return $js;
		}

		if ( $full_map && class_exists( 'IAH_Home_Js_Localizer' ) ) {
			$map = IAH_Home_Js_Localizer::map();
		} else {
			$map = IAH_Home_Native_Ru_Phase2::presentation_map();
		}

		if ( empty( $map ) || ! is_array( $map ) ) {
			return $js;
		}

		$unsafe = array_fill_keys( IAH_Home_Native_Ru_Phase2::unsafe_keys(), true );
		foreach ( $map as $from => $to ) {
			if ( isset( $unsafe[ $from ] ) || ! is_string( $from ) || ! is_string( $to ) || '' === $from ) {
				continue;
			}
			$js = IAH_Home_Native_Ru_Phase2::replace_quoted_literal( $js, $from, $to );
		}

		return $js;
	}

	/**
	 * Fix first-screen strings that are split across nested JSX spans/code nodes
	 * and therefore cannot be translated correctly by a plain string dictionary.
	 *
	 * @param string $js Dialogue chunk.
	 * @return string
	 */
	private static function patch_dialogue_source( $js ) {
		$red_first_old = '(0,C.jsxs)(r.SlackMessage,{name:"Denis A.",time:"9:16 AM",avatar:"/assets/ia-logo.svg",children:[(0,C.jsx)("span",{className:"text-primary font-bold",children:"@impact.accs"}),(0,C.jsx)("span",{"data-typewriter":!0,children:" Need EU accounts before launch."})]})';
		$red_first_new = '(0,C.jsx)(r.SlackMessage,{name:"Denis A.",time:"9:16 AM",avatar:"/assets/ia-logo.svg",children:(0,C.jsx)("span",{"data-typewriter":!0,children:"Нужен аккаунт под залив? Подберём по спенду, гео и валюте. Проверите до оплаты."})})';
		$js = str_replace( $red_first_old, $red_first_new, $js );

		$red_status_old = '(0,C.jsx)("code",{className:"rounded bg-white/10 px-1 py-0.5 text-[12px]",children:"VolumeRequestPending"})," in"," ",(0,C.jsx)("code",{className:"rounded bg-white/10 px-1 py-0.5 text-[12px]",children:"AgencyAccounts"})';
		$red_status_new = '(0,C.jsx)("code",{className:"rounded bg-white/10 px-1 py-0.5 text-[12px]",children:"Аккаунт найден"})," · ",(0,C.jsx)("code",{className:"rounded bg-white/10 px-1 py-0.5 text-[12px]",children:"готов к проверке"})';
		$js = str_replace( $red_status_old, $red_status_new, $js );

		$red_details_old = 'children:["Terms confirmed at ",(0,C.jsx)("strong",{className:"text-white/80",children:"9:11 AM"}),". Preparing delivery now."]';
		$red_details_new = 'children:"Трастовая история. Спенд $2 000–3 000. Цена — $400."';
		$js = str_replace( $red_details_old, $red_details_new, $js );

		$red_input_old = 'children:[(0,C.jsx)("span",{className:"text-primary font-bold",children:"@impact.accs"}),(0,C.jsxs)("span",{"data-type-text":!0,children:[" ","Request ",(0,C.jsx)("span",{className:"text-primary font-bold",children:"@impact.accs"})," terms and delivery for EU launch"]})]';
		$red_input_new = 'children:(0,C.jsx)("span",{"data-type-text":!0,children:"@founderads Проверил: спенд, гео и валюта совпадают. Готов оплатить."})';
		$js = str_replace( $red_input_old, $red_input_new, $js );

		$red_final_old = 'children:(0,C.jsxs)("div",{className:"flex flex-col gap-1",children:[(0,C.jsxs)("p",{children:["Matching supply for"," ",(0,C.jsx)("code",{className:"rounded bg-white/10 px-1 py-0.5 text-[12px]",children:"AgencyAccounts.RequestEU()"}),"."]}),(0,C.jsx)("p",{children:"Terms confirmed. Delivery scheduled before the launch window."})]})';
		$red_final_new = 'children:(0,C.jsxs)("div",{className:"flex flex-col gap-1",children:[(0,C.jsx)("p",{children:"Оплата получена"}),(0,C.jsx)("p",{children:"Админ-доступ передан. Замена действует, пока аккаунт не тронут. Поддержка 24/7, напрямую с владельцем."})]})';
		$js = str_replace( $red_final_old, $red_final_new, $js );

		$yellow_body_old = 'children:"Buyer desk needs agency accounts before traffic goes live. GEO: EU."';
		$yellow_body_new = 'children:"Нужен стабильный объём под регулярные заливы. Подбор по спенду, гео и валюте. Условия — под объём."';
		$js = str_replace( $yellow_body_old, $yellow_body_new, $js );

		$yellow_input_old = 'children:[(0,C.jsx)("span",{className:"text-primary font-bold",children:"@impact.accs"}),(0,C.jsxs)("span",{"data-type-text":!0,children:[" ",(0,C.jsx)("span",{className:"text-primary font-bold",children:"@impact.accs"})," lock terms and confirm delivery for this volume"]})]';
		$yellow_input_new = 'children:(0,C.jsx)("span",{"data-type-text":!0,children:"@founderads Условия подходят. Зафиксируйте объём и график продаж."})';
		$js = str_replace( $yellow_input_old, $yellow_input_new, $js );

		$green_intro_old = '(0,C.jsxs)("p",{children:["Active channels: ",(0,C.jsx)("code",{className:"rounded bg-white/10 px-1 py-0.5 text-[12px]",children:"EU desk"}),","," ",(0,C.jsx)("code",{className:"rounded bg-white/10 px-1 py-0.5 text-[12px]",children:"Agency pool"}),", +3 more"]})';
		$green_intro_new = '(0,C.jsx)("p",{children:"Нужны аккаунты для медиабаинга? Подбор трастовых аккаунтов для медиабаинга готов"})';
		$js = str_replace( $green_intro_old, $green_intro_new, $js );

		$green_input_old = 'children:(0,C.jsxs)("span",{"data-type-text":!0,children:["Repeat order confirmed — ",(0,C.jsx)("span",{className:"text-primary font-bold",children:"@impact.accs"})," supply stable"]})';
		$green_input_new = 'children:(0,C.jsx)("span",{"data-type-text":!0,children:"@founderads Закрепите 50 аккаунтов и пришлите их на проверку."})';
		$js = str_replace( $green_input_old, $green_input_new, $js );

		$js = self::replace_in_segment(
			$js,
			'function u({active:e,onSend:t})',
			'function f({active:e,onSend:t})',
			'children:"Send"',
			'children:"ПОДТВЕРДИТЬ"'
		);
		$js = self::replace_in_segment(
			$js,
			'function f({active:e,onSend:t})',
			'function j({active:e,onSend:t})',
			'children:"Send"',
			'children:"ЗАФИКСИРОВАТЬ"'
		);
		$js = self::replace_in_segment(
			$js,
			'function j({active:e,onSend:t})',
			'function y({errorChartVisible:e,inputActive:t,onSend:a,wrapper:i=m})',
			'children:"Send"',
			'children:"ЗАРЕЗЕРВИРОВАТЬ"'
		);

		return $js;
	}

	/**
	 * Finish visible brand/system cleanup after the approved map is applied.
	 *
	 * @param string $js Dialogue chunk.
	 * @return string
	 */
	private static function patch_dialogue_after_map( $js ) {
		$js = str_replace( '"impact.accs"', '"impact."', $js );
		$js = str_replace( '"@impact.accs"', '"impact."', $js );
		$js = str_replace( 'children:"You"', 'children:"Вы"', $js );
		$js = str_replace( 'children:"#requests"', 'children:"заявки"', $js );
		$js = str_replace( 'children:"@team"', 'children:"команда"', $js );
		$js = str_replace( '"Posted to "', '"Запрос принят владельцем · "', $js );
		$js = str_replace( '" — volume logged."', '""', $js );
		$js = str_replace( '"Desk notified "', '"Условия сохранены для следующих поставок · "', $js );
		$js = str_replace( '". Delivery queued."', '""', $js );

		return $js;
	}

	/**
	 * Translate the first-screen scroll cue and shared visible chrome. The action
	 * buttons in the second block become real contact buttons.
	 *
	 * @param string $js Shared UI chunk.
	 * @return string
	 */
	private static function patch_shared_ui( $js ) {
		$js = str_replace( 'children:"SCROLL DOWN"', 'children:"ПРОКРУТИТЕ ВНИЗ"', $js );
		$js = str_replace( 'children:"APP"', 'children:"24/7"', $js );
		$js = str_replace( 'children:"Impact"', 'children:"impact."', $js );
		$js = str_replace( '"impact.accs"', '"impact."', $js );
		$js = str_replace( 'children:"Severity:"', 'children:""', $js );
		$js = str_replace( 'children:"Status:"', 'children:""', $js );

		$old_alert_action = 'className:"pointer-events-none min-w-auto",variant:"primary"===e.variant?"primary":"outline",children:e.label';
		$new_alert_action = 'className:"pointer-events-auto min-w-auto",variant:"primary"===e.variant?"primary":"outline",onClick:()=>{window.location.href="https://impactads.io/contact/"},children:e.label';
		$js = str_replace( $old_alert_action, $new_alert_action, $js );

		$old_button = '(0,t.jsx)(o.Button,{size:"sm",variant:"outline",className:"pointer-events-none min-w-0 shrink-0 border-[#1F2328] bg-[#1F2328] px-4 text-white hover:bg-[#32383f]","data-slot":"action-button",children:"View batch"})';
		$new_button = '(0,t.jsx)(o.Button,{size:"sm",variant:"outline",className:"pointer-events-auto min-w-0 shrink-0 border-[#1F2328] bg-[#1F2328] px-4 text-white hover:bg-[#32383f]","data-slot":"action-button",onClick:()=>{window.location.href="https://impactads.io/contact/"},children:"ПОЛУЧИТЬ НА ПРОВЕРКУ"})';
		$js = str_replace( $old_button, $new_button, $js );

		return $js;
	}

	/**
	 * Keep the media-buying card readable: reveal its parts without dimming
	 * previous lines and without pushing the whole overlay upwards.
	 *
	 * @param string $js Main homepage content chunk.
	 * @return string
	 */
	private static function patch_media_buying_animation( $js ) {
		$old = '{enter:(e,[t,r,a,s],n)=>{e.fromTo(t,{opacity:0,y:20},{opacity:1,y:0,duration:.4,ease:"power2.out"},">+0.2"),e.to(t,{opacity:.6,duration:.2,ease:"power2.out"},">+0.35"),e.fromTo(r,{opacity:0,y:20},{opacity:1,y:0,duration:.45,ease:"power2.out"},"<"),e.to(n,{y:-20,duration:.45,ease:"power2.out"},"<"),e.to([t,r],{opacity:.6,duration:.2,ease:"power2.out"},">+0.35"),e.fromTo(a,{opacity:0,y:20},{opacity:1,y:0,duration:.4,ease:"power2.out"},"<"),e.to(n,{y:-40,duration:.4,ease:"power2.out"},"<"),e.to([t,r,a],{opacity:.6,duration:.2,ease:"power2.out"},">+0.3"),e.fromTo(s,{opacity:0,y:20},{opacity:1,y:0,duration:.45,ease:"power2.out"},"<"),e.to(n,{y:-60,duration:.45,ease:"power2.out"},"<")},exit:N}';
		$new = '{enter:(e,t,n)=>{e.set(n,{y:0}),e.fromTo(t,{opacity:0,y:0},{opacity:1,y:0,duration:.35,ease:"power2.out",stagger:.06})},exit:N}';

		return str_replace( $old, $new, $js );
	}

	/**
	 * Replace a literal only inside one known minified function segment.
	 *
	 * @param string $js Source.
	 * @param string $start Start marker.
	 * @param string $end End marker.
	 * @param string $from Source literal.
	 * @param string $to Replacement literal.
	 * @return string
	 */
	private static function replace_in_segment( $js, $start, $end, $from, $to ) {
		$start_pos = strpos( $js, $start );
		if ( false === $start_pos ) {
			return $js;
		}

		$end_pos = strpos( $js, $end, $start_pos + strlen( $start ) );
		if ( false === $end_pos || $end_pos <= $start_pos ) {
			return $js;
		}

		$segment = substr( $js, $start_pos, $end_pos - $start_pos );
		$segment = str_replace( $from, $to, $segment );

		return substr_replace( $js, $segment, $start_pos, $end_pos - $start_pos );
	}

	/**
	 * Keep Turbopack's logical identity equal to the mirrored static chunk.
	 *
	 * @param string $js Chunk source.
	 * @param string $chunk Chunk basename.
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
	 * Rewrite a parser-created script regardless of whether an earlier native-RU
	 * layer has already changed its endpoint URL.
	 *
	 * @param string $html Document.
	 * @param string $chunk Chunk basename.
	 * @return string
	 */
	private static function rewrite_parser_chunk_src( $html, $chunk ) {
		$url = trailingslashit( home_url( self::ENDPOINT ) ) . $chunk . '?v=' . rawurlencode( IAH_VERSION );
		$url = esc_url( $url );
		if ( '' === $url ) {
			return $html;
		}

		$quoted  = preg_quote( $chunk, '#' );
		$pattern = "#(<script\\b[^>]*\\bsrc=)([\"'])([^\"']*" . $quoted . "(?:\\?[^\"']*)?)\\2#i";

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
	 * Detect the narrow endpoint without rewrite rules or rewrite flushing.
	 *
	 * @return string|null
	 */
	private static function requested_chunk() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		if ( ! is_string( $uri ) || '' === $uri ) {
			return null;
		}

		$path   = wp_parse_url( $uri, PHP_URL_PATH );
		$prefix = '/' . self::ENDPOINT . '/';
		if ( ! is_string( $path ) || 0 !== strpos( $path, $prefix ) ) {
			return null;
		}

		$chunk = basename( substr( $path, strlen( $prefix ) ) );
		return in_array( $chunk, self::owned_chunks(), true ) ? $chunk : null;
	}

	/**
	 * Detect the public root homepage before the WordPress query is built.
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
}

IAH_Home_First_Two_Fixes::boot();
