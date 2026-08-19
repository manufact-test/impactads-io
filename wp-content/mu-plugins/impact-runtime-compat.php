<?php
/**
 * Plugin Name: Impact Runtime Compatibility
 * Description: Keeps the migrated Next.js homepage compatible with WordPress/Hostinger runtime behavior.
 * Version: 1.1.0
 * Author: Impact
 *
 * @package ImpactRuntimeCompatibility
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Let WordPress own the Site Icon on regular WordPress-rendered pages.
 *
 * The chrome plugin ships a static favicon and deliberately removes the
 * WordPress Site Icon tags. Remove only those overrides; all other chrome
 * behavior stays untouched.
 */
function impact_runtime_restore_wordpress_site_icon() {
	if ( ! class_exists( 'IAC_Chrome' ) || ! method_exists( 'IAC_Chrome', 'instance' ) ) {
		return;
	}

	$chrome = IAC_Chrome::instance();

	remove_action( 'wp_head', array( $chrome, 'render_favicon' ), 0 );
	remove_filter( 'get_site_icon_url', array( $chrome, 'filter_site_icon_url' ), 99 );
	remove_filter( 'site_icon_meta_tags', array( $chrome, 'filter_site_icon_meta_tags' ), 99 );
}
add_action( 'plugins_loaded', 'impact_runtime_restore_wordpress_site_icon', 100 );

/**
 * Return the request path without query parameters.
 *
 * @return string
 */
function impact_runtime_request_path() {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( ! is_string( $uri ) || '' === $uri ) {
		return '';
	}

	$path = wp_parse_url( $uri, PHP_URL_PATH );
	return is_string( $path ) ? $path : '';
}

/**
 * Serve a harmless local stub for the Vercel Analytics loader left in the
 * exported Next.js bundle. The site is hosted on Hostinger, so the Vercel
 * endpoint does not exist and otherwise produces a guaranteed 404.
 */
function impact_runtime_serve_vercel_analytics_stub() {
	if ( '/_vercel/insights/script.js' !== impact_runtime_request_path() ) {
		return;
	}

	status_header( 200 );
	if ( ! headers_sent() ) {
		header( 'Content-Type: application/javascript; charset=UTF-8' );
		header( 'Cache-Control: public, max-age=86400' );
		header( 'X-Robots-Tag: noindex, nofollow', true );
	}

	echo 'window.va=function(){};window.vaq=[];'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'template_redirect', 'impact_runtime_serve_vercel_analytics_stub', -20000 );

/**
 * Remove existing favicon tags from mirrored HTML.
 *
 * @param string $html HTML document.
 * @return string
 */
function impact_runtime_strip_favicon_tags( $html ) {
	$html = preg_replace_callback(
		'#<link\b[^>]*>#i',
		static function ( $matches ) {
			$tag = $matches[0];
			if ( preg_match( '#\brel\s*=\s*(["\'])([^"\']*(?:apple-touch-icon|icon)[^"\']*)\1#i', $tag ) ) {
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
 * Build favicon tags from the Site Icon selected in WordPress settings.
 *
 * @return string
 */
function impact_runtime_wordpress_site_icon_tags() {
	if ( ! function_exists( 'has_site_icon' ) || ! has_site_icon() ) {
		return '';
	}

	$icon_32  = get_site_icon_url( 32 );
	$icon_192 = get_site_icon_url( 192 );
	$icon_180 = get_site_icon_url( 180 );
	$icon_270 = get_site_icon_url( 270 );
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
	if ( $icon_270 ) {
		$tags[] = '<meta name="msapplication-TileImage" content="' . esc_url( $icon_270 ) . '" />';
	}

	return implode( "\n", $tags );
}

/**
 * Mark only the two compatibility scripts that mutate React-owned text/DOM so
 * they are loaded after the Next.js document has finished browser hydration.
 *
 * The mirrored app uses async Next.js chunks. These classic WordPress bridge
 * scripts are appended at the end of the body and can otherwise run first,
 * which is the source of React hydration error #418 (text mismatch).
 *
 * @param string $html HTML document.
 * @return string
 */
function impact_runtime_defer_home_dom_mutators( $html ) {
	return preg_replace_callback(
		'#<script\b([^>]*)\bsrc\s*=\s*(["\'])([^"\']*(?:homepage-i18n|home-chrome)\.js(?:\?[^"\']*)?)\2([^>]*)>\s*</script>#i',
		static function ( $matches ) {
			return '<script data-impact-hydration-src="' . esc_attr( html_entity_decode( $matches[3], ENT_QUOTES, 'UTF-8' ) ) . '"></script>';
		},
		$html
	);
}

/**
 * Small compatibility bootstrap for warnings and runtime races inherited from
 * the exported Vercel/Next.js build.
 *
 * @return string
 */
function impact_runtime_home_compat_script() {
	return <<<'JS'
<script id="impact-runtime-compat">
(function () {
  'use strict';

  // Suppress only known non-actionable build/runtime warnings. Real console
  // errors (including React hydration errors) remain fully visible.
  var originalWarn = console.warn;
  console.warn = function () {
    var first = arguments.length ? arguments[0] : '';
    if (typeof first === 'string') {
      if (first.indexOf('[posthog] NEXT_PUBLIC_POSTHOG_KEY is not set') === 0) {
        return;
      }
      if (first === 'THREE.Clock: This module has been deprecated. Please use THREE.Timer instead.') {
        return;
      }
      if (first.indexOf('THREE.TSL: Vertex attribute "normal" not found on geometry.') === 0) {
        return;
      }
    }
    return originalWarn.apply(console, arguments);
  };

  // GSAP's exported app contains animations for optional responsive elements.
  // Keep those no-target cases silent while preserving every other GSAP warning.
  var attempts = 0;
  var gsapTimer = window.setInterval(function () {
    attempts += 1;
    if (window.gsap && typeof window.gsap.config === 'function') {
      window.gsap.config({ nullTargetWarn: false });
      window.clearInterval(gsapTimer);
    } else if (attempts >= 400) {
      window.clearInterval(gsapTimer);
    }
  }, 25);

  // WordPress compatibility scripts can modify body/header text while async
  // Next.js chunks are still hydrating. Load them in original order only after
  // window.load, the React loader is gone, and a conservative quiet period has
  // elapsed. This removes the actual #418 race instead of hiding the error.
  function loadHydrationScripts() {
    var pending = Array.prototype.slice.call(
      document.querySelectorAll('script[data-impact-hydration-src]')
    );
    if (!pending.length) {
      return;
    }

    var index = 0;
    function next() {
      if (index >= pending.length) {
        return;
      }
      var placeholder = pending[index++];
      var src = placeholder.getAttribute('data-impact-hydration-src');
      if (!src) {
        next();
        return;
      }
      var script = document.createElement('script');
      script.src = src;
      script.async = false;
      script.onload = next;
      script.onerror = next;
      placeholder.parentNode.insertBefore(script, placeholder.nextSibling);
      placeholder.parentNode.removeChild(placeholder);
    }
    next();
  }

  function scheduleHydrationScripts() {
    var quietSince = 0;
    var startedAt = Date.now();

    function tick() {
      var loader = document.querySelector('[data-loader-phase][role="status"]');
      if (loader) {
        quietSince = 0;
      } else if (!quietSince) {
        quietSince = Date.now();
      }

      // 2.5s with no loader after window.load is deliberately conservative;
      // the fallback avoids ever blocking the compatibility layer permanently.
      if ((quietSince && Date.now() - quietSince >= 2500) || Date.now() - startedAt >= 12000) {
        loadHydrationScripts();
        return;
      }
      window.setTimeout(tick, 100);
    }

    tick();
  }

  if (document.readyState === 'complete') {
    scheduleHydrationScripts();
  } else {
    window.addEventListener('load', scheduleHydrationScripts, { once: true });
  }
})();
</script>
JS;
}

/**
 * Patch only the mirrored site-root document after the homepage renderer has
 * produced its final HTML. This also works for cached homepage HTML.
 *
 * @param string $html HTML document.
 * @return string
 */
function impact_runtime_patch_home_document( $html ) {
	if ( ! is_string( $html ) || '' === $html || false === stripos( $html, '<head' ) ) {
		return $html;
	}

	// Chromium on Windows currently ignores powerPreference and logs a warning.
	// Keep the original high-performance hint on other platforms.
	$adapter_options = "(/Win/i.test((navigator.userAgentData&&navigator.userAgentData.platform)||navigator.platform||navigator.userAgent||''))?{}:{powerPreference:'high-performance'}";
	$html            = str_replace(
		array(
			"navigator.gpu.requestAdapter({ powerPreference: 'high-performance' })",
			'navigator.gpu.requestAdapter({ powerPreference: "high-performance" })',
			'navigator.gpu.requestAdapter({powerPreference:"high-performance"})',
			"navigator.gpu.requestAdapter({powerPreference:'high-performance'})",
		),
		'navigator.gpu.requestAdapter(' . $adapter_options . ')',
		$html
	);

	$html = impact_runtime_defer_home_dom_mutators( $html );

	$site_icon_tags = impact_runtime_wordpress_site_icon_tags();
	if ( '' !== $site_icon_tags ) {
		$html = impact_runtime_strip_favicon_tags( $html );
	}

	$head_inject = impact_runtime_home_compat_script();
	if ( '' !== $site_icon_tags ) {
		$head_inject .= "\n" . $site_icon_tags . "\n";
	}

	$html = preg_replace_callback(
		'#<head\b[^>]*>#i',
		static function ( $matches ) use ( $head_inject ) {
			return $matches[0] . "\n" . $head_inject;
		},
		$html,
		1
	);

	return is_string( $html ) ? $html : '';
}

/**
 * Start a final-output patch only for the site root (including ?_lc=2).
 */
function impact_runtime_start_home_output_buffer() {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	$path = impact_runtime_request_path();
	if ( '/' !== $path && '' !== $path ) {
		return;
	}

	ob_start( 'impact_runtime_patch_home_document' );
}
add_action( 'template_redirect', 'impact_runtime_start_home_output_buffer', -10000 );
