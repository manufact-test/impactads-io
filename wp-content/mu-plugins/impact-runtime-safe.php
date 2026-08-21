<?php
/**
 * Plugin Name: Impact Runtime Safe Fixes
 * Description: Small WordPress/Hostinger compatibility fixes that do not reorder or defer Next.js application scripts.
 * Version: 1.1.0
 * Author: Impact
 *
 * @package ImpactRuntimeSafe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the current request path without the query string.
 *
 * @return string
 */
function impact_runtime_safe_request_path() {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( ! is_string( $uri ) || '' === $uri ) {
		return '';
	}

	$path = wp_parse_url( $uri, PHP_URL_PATH );
	return is_string( $path ) ? $path : '';
}

/**
 * Return the WordPress Site Icon with a cache-busting query string.
 *
 * Chrome caches favicons separately from the normal page cache, so changing
 * only the link markup can leave the generic globe visible for a long time.
 *
 * @param int $size Requested icon size.
 * @return string
 */
function impact_runtime_safe_site_icon_url( $size = 32 ) {
	$url = function_exists( 'get_site_icon_url' ) ? get_site_icon_url( (int) $size ) : '';

	if ( ! $url && defined( 'IAH_FAVICON_URL' ) ) {
		$url = IAH_FAVICON_URL;
	}
	if ( ! $url ) {
		return '';
	}

	$site_icon_id = (int) get_option( 'site_icon', 0 );
	$version      = $site_icon_id > 0 ? (string) $site_icon_id : 'impact';

	return add_query_arg( 'v', rawurlencode( $version ), $url );
}

/**
 * Let the WordPress Site Icon setting own favicon markup on regular pages.
 */
function impact_runtime_safe_restore_site_icon() {
	if ( class_exists( 'IAC_Chrome' ) && method_exists( 'IAC_Chrome', 'instance' ) ) {
		$chrome = IAC_Chrome::instance();

		remove_action( 'wp_head', array( $chrome, 'render_favicon' ), 0 );
		remove_filter( 'get_site_icon_url', array( $chrome, 'filter_site_icon_url' ), 99 );
		remove_filter( 'site_icon_meta_tags', array( $chrome, 'filter_site_icon_meta_tags' ), 99 );
	}

	// Render one deterministic, cache-busted favicon set on WordPress pages.
	remove_action( 'wp_head', 'wp_site_icon', 99 );
	add_action( 'wp_head', 'impact_runtime_safe_render_site_icon', 0 );
}
add_action( 'plugins_loaded', 'impact_runtime_safe_restore_site_icon', 100 );

/**
 * Render favicon tags for normal WordPress/Elementor pages.
 */
function impact_runtime_safe_render_site_icon() {
	$icon_32  = impact_runtime_safe_site_icon_url( 32 );
	$icon_180 = impact_runtime_safe_site_icon_url( 180 );
	$icon_192 = impact_runtime_safe_site_icon_url( 192 );

	if ( ! $icon_32 && ! $icon_180 && ! $icon_192 ) {
		return;
	}

	if ( $icon_32 ) {
		echo '<link rel="icon" href="' . esc_url( $icon_32 ) . '" sizes="32x32" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<link rel="shortcut icon" href="' . esc_url( $icon_32 ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( $icon_192 ) {
		echo '<link rel="icon" href="' . esc_url( $icon_192 ) . '" sizes="192x192" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( $icon_180 ) {
		echo '<link rel="apple-touch-icon" href="' . esc_url( $icon_180 ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * The static Next.js export still references Vercel Analytics, but production
 * now runs on Hostinger. Return a tiny local no-op instead of a guaranteed 404.
 */
function impact_runtime_safe_vercel_analytics_stub() {
	if ( '/_vercel/insights/script.js' !== impact_runtime_safe_request_path() ) {
		return;
	}

	status_header( 200 );
	if ( ! headers_sent() ) {
		header( 'Content-Type: application/javascript; charset=UTF-8' );
		header( 'Cache-Control: public, max-age=86400' );
		header( 'X-Robots-Tag: noindex, nofollow', true );
	}

	echo 'window.va=window.va||function(){};window.vaq=window.vaq||[];'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'template_redirect', 'impact_runtime_safe_vercel_analytics_stub', -20000 );

/**
 * Remove icon tags already present in the mirrored static document.
 *
 * @param string $html Document HTML.
 * @return string
 */
function impact_runtime_safe_strip_home_icons( $html ) {
	$html = preg_replace_callback(
		'#<link\b[^>]*>#i',
		static function ( $matches ) {
			$tag = $matches[0];
			if ( preg_match( '#\brel\s*=\s*(["\'])([^"\']*(?:apple-touch-icon|shortcut icon|icon)[^"\']*)\1#i', $tag ) ) {
				return '';
			}
			return $tag;
		},
		$html
	);

	$html = preg_replace(
		'#<meta\b[^>]*\bname\s*=\s*(["\'])msapplication-TileImage\1[^>]*>#i',
		'',
		$html
	);

	return is_string( $html ) ? $html : '';
}

/**
 * Build favicon markup for the mirrored homepage.
 *
 * @return string
 */
function impact_runtime_safe_home_icon_tags() {
	$icon_32  = impact_runtime_safe_site_icon_url( 32 );
	$icon_180 = impact_runtime_safe_site_icon_url( 180 );
	$icon_192 = impact_runtime_safe_site_icon_url( 192 );
	$tags     = array();

	if ( $icon_32 ) {
		$tags[] = '<link rel="icon" href="' . esc_url( $icon_32 ) . '" sizes="32x32" />';
		$tags[] = '<link rel="shortcut icon" href="' . esc_url( $icon_32 ) . '" />';
	}
	if ( $icon_192 ) {
		$tags[] = '<link rel="icon" href="' . esc_url( $icon_192 ) . '" sizes="192x192" />';
	}
	if ( $icon_180 ) {
		$tags[] = '<link rel="apple-touch-icon" href="' . esc_url( $icon_180 ) . '" />';
	}

	return implode( "\n", $tags );
}

/**
 * Early, non-DOM runtime guards for the migrated homepage.
 *
 * This script deliberately does not move, defer, replace, or delay any Next.js
 * application script. It only handles known non-actionable warnings and the
 * Chromium/Windows WebGPU option that Chromium itself ignores.
 *
 * @return string
 */
function impact_runtime_safe_home_runtime_script() {
	return <<<'JS'
<script id="impact-runtime-safe-v2">
(function () {
  'use strict';

  var originalWarn = console.warn;
  console.warn = function () {
    var first = arguments.length ? arguments[0] : '';
    if (typeof first === 'string') {
      if (first.indexOf('[posthog] NEXT_PUBLIC_POSTHOG_KEY is not set') === 0) {
        return;
      }
      if (first.indexOf('GSAP target') === 0 && first.indexOf('not found') !== -1) {
        return;
      }
      if (first.indexOf('THREE.Clock: This module has been deprecated') === 0 ||
          first.indexOf('THREE.THREE.Clock: This module has been deprecated') === 0) {
        return;
      }
      if (first.indexOf('THREE.TSL: Vertex attribute "normal" not found on geometry') === 0) {
        return;
      }
    }
    return originalWarn.apply(console, arguments);
  };

  // Also use GSAP's own supported setting once the exported bundle exposes it.
  var gsapAttempts = 0;
  var gsapTimer = window.setInterval(function () {
    gsapAttempts += 1;
    if (window.gsap && typeof window.gsap.config === 'function') {
      window.gsap.config({ nullTargetWarn: false });
      window.clearInterval(gsapTimer);
    } else if (gsapAttempts >= 400) {
      window.clearInterval(gsapTimer);
    }
  }, 25);

  // Chromium on Windows currently ignores powerPreference and prints a browser
  // warning. Strip only that ignored property while preserving every other
  // requestAdapter option and the original method/receiver.
  try {
    var platform = (navigator.userAgentData && navigator.userAgentData.platform) || navigator.platform || navigator.userAgent || '';
    if (/Win/i.test(platform) && navigator.gpu) {
      var proto = Object.getPrototypeOf(navigator.gpu);
      var originalRequestAdapter = proto && proto.requestAdapter;
      if (typeof originalRequestAdapter === 'function' && !originalRequestAdapter.__impactSafeWrapped) {
        var wrappedRequestAdapter = function (options) {
          if (options && Object.prototype.hasOwnProperty.call(options, 'powerPreference')) {
            var clean = {};
            Object.keys(options).forEach(function (key) {
              if (key !== 'powerPreference') {
                clean[key] = options[key];
              }
            });
            options = clean;
          }
          return originalRequestAdapter.call(this, options);
        };
        wrappedRequestAdapter.__impactSafeWrapped = true;
        try {
          proto.requestAdapter = wrappedRequestAdapter;
        } catch (ignore) {}
      }
    }
  } catch (ignore) {}
})();
</script>
JS;
}

/**
 * Patch the final mirrored homepage HTML without touching application script
 * order or React-owned DOM.
 *
 * @param string $html Document HTML.
 * @return string
 */
function impact_runtime_safe_patch_home_html( $html ) {
	if ( ! is_string( $html ) || '' === $html || false === stripos( $html, '<head' ) ) {
		return $html;
	}

	$html = impact_runtime_safe_strip_home_icons( $html );

	// The export preloads this image although the initial viewport does not use
	// it, which is why Chromium reports "preloaded but not used" repeatedly.
	$html = preg_replace_callback(
		'#<link\b[^>]*>#i',
		static function ( $matches ) {
			$tag = $matches[0];
			if ( false !== stripos( $tag, 'rel="preload"' ) && false !== stripos( $tag, 'city-outline' ) ) {
				return '';
			}
			if ( false !== stripos( $tag, "rel='preload'" ) && false !== stripos( $tag, 'city-outline' ) ) {
				return '';
			}
			return $tag;
		},
		$html
	);

	$inject = impact_runtime_safe_home_icon_tags() . "\n" . impact_runtime_safe_home_runtime_script();
	$html   = preg_replace_callback(
		'#<head\b[^>]*>#i',
		static function ( $matches ) use ( $inject ) {
			return $matches[0] . "\n" . $inject . "\n";
		},
		$html,
		1
	);

	return is_string( $html ) ? $html : '';
}

/**
 * Buffer only the mirrored site-root response. The callback changes head-only
 * metadata/runtime guards and leaves all Next.js script tags in place.
 */
function impact_runtime_safe_start_home_buffer() {
	// The mirrored Next.js app hydrates the whole document, including <head>.
	// Even metadata-only buffering changes that document before hydration and
	// produces React #418. Keep these compatibility helpers for normal
	// WordPress pages, but leave the exported homepage response untouched.
	return;
}
add_action( 'template_redirect', 'impact_runtime_safe_start_home_buffer', -2000 );
