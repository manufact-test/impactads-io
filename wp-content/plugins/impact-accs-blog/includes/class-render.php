<?php
/**
 * HTML rendering matching impact.accs blog templates.
 *
 * @package ImpactAccsBlog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blog markup renderer.
 */
class IAB_Render {

	/**
	 * Team badge SVG (inline from chrome templates).
	 *
	 * @return string
	 */
	private static function team_svg( $class = 'h-3.5 w-auto' ) {
		return '<svg viewBox="0 0 48 44" fill="none" xmlns="http://www.w3.org/2000/svg" class="' . esc_attr( $class ) . '"><path d="M6.92931 24.0949H21.6714L16.3681 15.3035C15.5516 13.9428 16.5174 12.2021 18.0979 12.2021H41.0671C41.7783 12.2021 42.4281 11.831 42.7969 11.2214L47.6962 3.10134C48.5129 1.74063 47.547 0 45.9666 0H22.4704C21.7593 0 21.1096 0.3711 20.7407 0.980766L14.7526 10.9033C14.3838 11.5129 13.7253 11.8841 13.0228 11.8841H2.0299C0.449443 11.8841 -0.525173 13.6247 0.300177 14.9854L5.19959 23.1054C5.56836 23.7151 6.22688 24.0861 6.92931 24.0861V24.0949Z" fill="currentColor"></path><path d="M41.0694 19.0519H26.3271L31.6305 27.8434C32.4471 29.2042 31.4812 30.9448 29.9008 30.9448H6.93146C6.22026 30.9448 5.57052 31.3159 5.20174 31.9255L0.302333 40.0367C-0.514235 41.3974 0.451599 43.138 2.03206 43.138H25.5281C26.2394 43.138 26.8891 42.767 27.258 42.1573L33.2462 32.2347C33.6148 31.625 34.2734 31.254 34.9758 31.254H45.9687C47.5493 31.254 48.5238 29.5133 47.6985 28.1526L42.7991 20.0327C42.4302 19.423 41.7718 19.0519 41.0694 19.0519Z" fill="currentColor"></path></svg>';
	}

	/**
	 * Frame corner dots markup.
	 *
	 * @return string
	 */
	private static function frame_corners() {
		$corner = '<div data-slot="frame-corner" class="pointer-events-none absolute z-10 %s" aria-hidden="true"><div class="absolute top-0 left-0 size-[var(--corner-dot,5px)] translate-x-[-2px] translate-y-[-2px] bg-current"></div></div>';
		return sprintf(
			$corner . $corner . $corner . $corner,
			'top-0 left-0 -translate-x-px -translate-y-px',
			'top-0 right-0 translate-x-px -translate-y-px rotate-90',
			'right-0 bottom-0 translate-x-px translate-y-px rotate-180',
			'bottom-0 left-0 -translate-x-px translate-y-px -rotate-90'
		);
	}

	/**
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public static function cover_url( $post ) {
		$url = get_the_post_thumbnail_url( $post, 'large' );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
		if ( defined( 'IAC_URL' ) ) {
			return IAC_URL . 'assets/site/assets/blog/manifesto-cover.png';
		}
		return '';
	}

	/**
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public static function excerpt( $post ) {
		$text = has_excerpt( $post ) ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( $post->post_content ), 28, '…' );
		return trim( (string) $text );
	}

	/**
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public static function card( $post ) {
		$url    = esc_url( IAB_Sync::post_public_url( $post ) );
		$title  = esc_html( get_the_title( $post ) );
		$excerpt = esc_html( self::excerpt( $post ) );
		$cover  = esc_url( self::cover_url( $post ) );
		$slug   = esc_attr( $post->post_name );

		ob_start();
		?>
<a class="iac-blog-card iac-blog-card-<?php echo $slug; ?> group text-card-foreground flex flex-col gap-y-4" href="<?php echo $url; ?>">
	<div class="relative border border-current text-muted group-hover:text-primary ease-out-expo transition-colors duration-400" style="--corner-size:12px">
		<?php echo self::frame_corners(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<div class="relative aspect-[4/2.8] overflow-hidden">
			<img alt="<?php echo $title; ?>" loading="lazy" width="800" height="600" decoding="async" class="ease-out-expo h-full w-full object-cover grayscale transition-transform duration-400 select-none group-hover:scale-105" src="<?php echo $cover; ?>" />
			<div class="bg-primary/0 ease-out-expo group-hover:bg-primary/80 pointer-events-none absolute inset-0 mix-blend-color transition-colors duration-400"></div>
		</div>
	</div>
	<h3 class="text-paragraph text-muted group-hover:text-foreground group-hover:text-glow ease-out-expo transition-colors-and-shadows text-lg duration-400 md:text-xl 2xl:text-2xl"><?php echo $title; ?></h3>
	<p class="text-muted/50 group-hover:text-muted ease-out-expo line-clamp-3 text-base leading-relaxed text-pretty transition-colors"><?php echo $excerpt; ?></p>
	<span class="group/cta font-misc w-fit text-base leading-[0.85] font-medium uppercase transition-colors border-current border-b text-muted group-hover:text-accent-foreground">Read more <span class="inline-block pl-0 transition-[padding] duration-300 group-hover:pr-0 group-hover:pl-1 group-hover/cta:pr-0 group-hover/cta:pl-1">&gt;</span></span>
</a>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * @param WP_Post $post   Post.
	 * @param bool    $active Active slide.
	 * @return string
	 */
	public static function mobile_slide( $post, $active = false ) {
		$card = self::card( $post );
		$flag = $active ? 'true' : 'false';
		return '<div data-active="' . esc_attr( $flag ) . '" class="[&amp;_[data-slot=frame-corner]]:ease-glitch min-w-0 flex-[0_0_100%] py-2 [--corner-dot:7px] [&amp;_[data-slot=frame-corner]]:opacity-0 [&amp;_[data-slot=frame-corner]]:transition-opacity [&amp;_[data-slot=frame-corner]]:duration-1000 [&amp;[data-active=true]_[data-slot=frame-corner]]:opacity-100">' . $card . '</div>';
	}

	/**
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public static function featured_hero( $post ) {
		$url     = esc_url( IAB_Sync::post_public_url( $post ) );
		$title   = esc_html( get_the_title( $post ) );
		$excerpt = esc_html( self::excerpt( $post ) );
		$cover   = esc_url( self::cover_url( $post ) );
		$badge   = esc_html( IAB_Admin::badge( $post->ID ) );

		ob_start();
		?>
<a class="group before:featured-hover-mask hover:before:bg-foreground/8 relative flex flex-col-reverse gap-4 max-md:before:rounded-tl-4xl md:flex-row md:before:rounded-br-4xl [--cut-size:120px] md:[--cut-size:255px] 2xl:[--cut-size:275px]" href="<?php echo $url; ?>">
	<article class="bg-card text-muted ease-out-expo group-hover:text-foreground flex flex-1 flex-col justify-between rounded-lg p-6 transition-[filter] duration-500 md:min-h-[480px] lg:p-8 2xl:min-h-[520px]">
		<div class="flex items-center gap-2">
			<span class="font-misc inline-flex items-center rounded-sm uppercase transition-colors-and-shadows px-1 py-0.5 pt-0.75 shadow-deep border border-border-dark bg-linear-to-b from-background/44 to-background/22 text-muted text-sm leading-[0.9]"><?php echo $badge; ?></span>
			<span class="font-misc inline-flex items-center rounded-sm uppercase transition-colors-and-shadows px-1 py-0.5 pt-0.75 shadow-deep border border-border-dark bg-linear-to-b from-background/44 to-background/22 text-muted text-sm leading-[0.9]">Blogpost</span>
			<div class="flex flex-col gap-1.5 flex-1 max-lg:hidden">
				<div class="text-card-foreground/30 h-px w-full bg-[repeating-linear-gradient(to_right,currentColor_0_3px,transparent_3px_7.5px)]"></div>
				<div class="text-card-foreground/30 h-px w-full bg-[repeating-linear-gradient(to_right,currentColor_0_3px,transparent_3px_7.5px)]"></div>
			</div>
			<span class="font-misc inline-flex items-center rounded-sm uppercase transition-colors-and-shadows px-1 py-0.5 pt-0.75 shadow-deep border border-border-dark bg-linear-to-b from-background/44 to-background/22 text-muted text-sm leading-[0.9] gap-1.5 max-lg:hidden">By impact.accs team <?php echo self::team_svg( 'h-2.5 w-auto' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</div>
		<div class="flex flex-col gap-y-6 pt-6">
			<h5 class="text-title group-hover:text-glow transition-colors-and-shadows text-5xl md:text-6xl"><?php echo $title; ?></h5>
			<p class="ease-out-expo transition-colors-and-shadows group-hover:text-muted-foreground text-sm duration-500 md:text-base 2xl:text-lg"><?php echo $excerpt; ?></p>
		</div>
	</article>
	<div class="relative h-[200px] w-full md:h-auto md:w-[280px] md:min-w-3xs 2xl:w-[300px]">
		<div style="--cut-radius:9px" class="blog-card-mask bg-primary h-full rounded-2xl p-px">
			<div style="--cut-radius:8px" class="blog-card-mask bg-background relative h-full overflow-hidden rounded-2xl">
				<img alt="<?php echo $title; ?>" loading="lazy" decoding="async" class="ease-out-expo pointer-events-none object-cover object-center transition-transform duration-700 group-hover:scale-110 absolute inset-0 h-full w-full" src="<?php echo $cover; ?>" />
			</div>
		</div>
	</div>
</a>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public static function single( $post ) {
		$title   = esc_html( get_the_title( $post ) );
		$cover   = esc_url( self::cover_url( $post ) );
		$badge   = esc_html( IAB_Admin::badge( $post->ID ) );
		$content = $post->post_content;
		if ( function_exists( 'do_blocks' ) ) {
			$content = do_blocks( $content );
		}
		$content = wptexturize( $content );
		$content = convert_smilies( $content );
		$content = shortcode_unautop( $content );
		$content = wp_filter_content_tags( $content );
		$content = wpautop( $content );
		$content = do_shortcode( $content );
		$content = wp_kses_post( $content );
		$date    = esc_html( gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $post->post_date_gmt ? $post->post_date_gmt : $post->post_date ) ) );
		$slug    = esc_attr( $post->post_name );

		ob_start();
		?>
<div class="iac-blog-post iac-blog-post-<?php echo $slug; ?> iab-dynamic-blog-post">
<div class="from-background/0 via-background/0 to-background bg-linear-to-b pt-24 pb-36 md:pb-48">
<article class="container-tight min-h-screen">
<header class="flex flex-col gap-y-6 md:gap-y-8 md:px-4">
<div class="flex flex-wrap gap-2">
<span class="font-misc inline-flex items-center rounded-sm uppercase transition-colors-and-shadows px-1 py-0.5 pt-0.75 border border-muted bg-muted text-background text-base leading-[0.9]"><?php echo $badge; ?></span>
</div>
<div class="flex flex-col gap-y-4 md:flex-row md:items-end md:justify-between md:gap-x-8">
<div><h1 class="text-title text-card-foreground text-glow text-h1 text-pretty"><?php echo $title; ?></h1></div>
<span class="font-misc inline-flex items-center rounded-sm uppercase transition-colors-and-shadows px-1 py-0.5 pt-0.75 border border-transparent text-muted text-base leading-[0.9] shrink-0 gap-1.5">By impact.accs team <?php echo self::team_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
</div>
</header>
<div class="mt-8 md:mt-14">
<div class="text-muted relative border border-current aspect-video" style="--corner-size:12px">
<?php echo self::frame_corners(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<img alt="<?php echo $title; ?>" width="1920" height="940" decoding="async" class="size-full object-cover" src="<?php echo $cover; ?>" />
</div>
</div>
<div class="mx-auto flex gap-7 pt-14 max-lg:justify-center lg:grid lg:grid-cols-[auto_auto] xl:grid-cols-[1fr_auto_1fr]">
<div></div>
<div class="prose prose-blog md:prose-lg container-prose w-full min-w-0 iab-blog-content">
<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
<div class="max-md:hidden"></div>
</div>
<div class="iac-article-footer mt-14 flex flex-col gap-4 md:mt-20">
<div data-orientation="horizontal" role="none" data-slot="separator" class="text-muted bg-current shrink-0 [--thickness:1px] h-(--thickness) w-full relative before:absolute before:bg-current after:absolute after:bg-current before:left-0 before:bottom-(--thickness) before:h-3 before:w-(--thickness) after:right-0 after:bottom-(--thickness) after:h-3 after:w-(--thickness)" style="--thickness:2px"></div>
<div class="flex items-center justify-between px-4">
<span class="font-misc text-muted text-sm uppercase"><?php echo $date; ?></span>
<div class="flex items-center gap-3"><span class="font-misc text-muted text-sm uppercase">Share</span></div>
</div>
</div>
</article>
</div>
<div class="bg-background"></div>
</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Inject dynamic cards into blog index HTML.
	 *
	 * @param string $html Blog index HTML.
	 * @return string
	 */
	public static function inject_index( $html ) {
		$posts = IAB_CPT::get_published_posts();
		if ( empty( $posts ) ) {
			return $html;
		}

		$featured = null;
		foreach ( $posts as $post ) {
			if ( IAB_Admin::is_featured( $post->ID ) ) {
				$featured = $post;
				break;
			}
		}

		$cards_html  = '';
		$mobile_html = '';
		$index       = 0;
		foreach ( $posts as $post ) {
			$cards_html  .= self::card( $post );
			$mobile_html .= self::mobile_slide( $post, 0 === $index );
			$index++;
		}

		if ( $featured ) {
			$hero = self::featured_hero( $featured );
			$html = preg_replace(
				'#<a class="group before:featured-hover-mask[^"]*" href="[^"]*">.*?</a>#s',
				$hero,
				$html,
				1
			);
		}

		$html = preg_replace(
			'#(<div class="iac-blog-grid">)#',
			'$1' . $cards_html,
			$html,
			1
		);

		$html = preg_replace(
			'#(<div class="iac-blog-mobile[^"]*">\s*<div[^>]*>\s*<div class="flex gap-4">)#s',
			'$1' . $mobile_html,
			$html,
			1
		);

		return is_string( $html ) ? $html : '';
	}
}
