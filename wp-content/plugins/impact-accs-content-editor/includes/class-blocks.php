<?php
/**
 * Visual page blocks — where each text lives on the site.
 *
 * @package ImpactAccsContentEditor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block catalog and resolver.
 */
class IACCE_Blocks {

	/**
	 * @return array<string,array{id:string,category:string,title:string,desc:string,where:string,order:int,icon:string,examples:array<int,string>}>
	 */
	public static function catalog() {
		static $catalog = null;
		if ( null !== $catalog ) {
			return $catalog;
		}

		$catalog = array(
			// ── Главная (сверху вниз) ──────────────────────────────
			'home-preloader'       => array(
				'id'       => 'home-preloader',
				'category' => 'home',
				'title'    => 'Прелоader при загрузке',
				'desc'     => 'Экран «impact.accs Initializing…» и «Click anywhere to enable sound»',
				'where'    => 'Появляется первым, до загрузки сайта',
				'order'    => 10,
				'icon'     => 'dashicons-update',
				'examples' => array( 'Initializing', 'enable sound' ),
			),
			'home-hero-cards'      => array(
				'id'       => 'home-hero-cards',
				'category' => 'home',
				'title'    => 'Три услуги вверху',
				'desc'     => 'Agency Accounts · Platform Access · Team Supply',
				'where'    => 'Самый верх главной, три карточки под шапкой',
				'order'    => 20,
				'icon'     => 'dashicons-grid-view',
				'examples' => array( 'Agency Accounts', 'Platform Access', 'Team Supply' ),
			),
			'home-hero-alerts'     => array(
				'id'       => 'home-hero-alerts',
				'category' => 'home',
				'title'    => 'Панель ALERTS',
				'desc'     => 'Уведомления слева: «Launch blocked», «Supply stable»…',
				'where'    => 'Левая колонка hero-блока, вкладка ALERTS',
				'order'    => 30,
				'icon'     => 'dashicons-bell',
				'examples' => array( 'Launch blocked', 'Supply stable', 'Access confirmed' ),
			),
			'home-hero-chat'       => array(
				'id'       => 'home-hero-chat',
				'category' => 'home',
				'title'    => 'Чат Impact APP',
				'desc'     => 'Сообщения в чате справа, статусы запросов',
				'where'    => 'Правая колонка hero-блока, вкладки CHAT / AGENTS',
				'order'    => 40,
				'icon'     => 'dashicons-format-chat',
				'examples' => array( 'Request status', 'Working resource', 'EU · 50 agency' ),
			),
			'home-ready'           => array(
				'id'       => 'home-ready',
				'category' => 'home',
				'title'    => 'Ready for Action',
				'desc'     => 'Заголовок «READY FOR ACTION» и текст про resource layer',
				'where'    => 'Секция сразу под hero, перед двумя колонками',
				'order'    => 50,
				'icon'     => 'dashicons-megaphone',
				'examples' => array( 'READY FOR ACTION', 'With investors from' ),
			),
			'home-two-columns'     => array(
				'id'       => 'home-two-columns',
				'category' => 'home',
				'title'    => 'Две колонки преимуществ',
				'desc'     => '«ACCESS IN MINUTES» и «WORKS WITH YOUR TEAMS»',
				'where'    => 'Две большие колонки по центру страницы',
				'order'    => 60,
				'icon'     => 'dashicons-columns',
				'examples' => array( 'ACCESS IN MINUTES', 'WORKS WITH YOUR TEAMS' ),
			),
			'home-manifesto'       => array(
				'id'       => 'home-manifesto',
				'category' => 'home',
				'title'    => 'Манифест',
				'desc'     => 'Блок MANIFESTO — «impact.accs is more than a shop»',
				'where'    => 'Секция с крупным заголовком MANIFESTO',
				'order'    => 70,
				'icon'     => 'dashicons-book',
				'examples' => array( 'MANIFESTO', 'more than a shop' ),
			),
			'home-interfaces'      => array(
				'id'       => 'home-interfaces',
				'category' => 'home',
				'title'    => 'Слайдер INTERFACES (3D)',
				'desc'     => 'Карусель с 3D-зданием: «Chaos is optional», «Resource over noise»…',
				'where'    => 'Большая карточка по центру, 3D-сцена, кнопки ← →',
				'order'    => 80,
				'icon'     => 'dashicons-images-alt2',
				'examples' => array( 'Chaos is optional', 'Resource over noise', 'Read Blog' ),
			),
			'home-closed'          => array(
				'id'       => 'home-closed',
				'category' => 'home',
				'title'    => 'Closed Infrastructure',
				'desc'     => '«CLOSED INFRASTRUCTURE» — закрытый доступ',
				'where'    => 'Секция под слайдером',
				'order'    => 90,
				'icon'     => 'dashicons-lock',
				'examples' => array( 'CLOSED INFRASTRUCTURE', 'Closed infrastructure' ),
			),
			'home-process-cards'   => array(
				'id'       => 'home-process-cards',
				'category' => 'home',
				'title'    => '4 карточки процесса',
				'desc'     => 'Closed Access · Verified Supply · Controlled Delivery · Desk Control',
				'where'    => 'Ряд из 4 карточек с иконками',
				'order'    => 100,
				'icon'     => 'dashicons-index-card',
				'examples' => array( 'CLOSED ACCESS', 'VERIFIED SUPPLY', 'DESK CONTROL' ),
			),
			'home-features-list'   => array(
				'id'       => 'home-features-list',
				'category' => 'home',
				'title'    => 'Список преимуществ (01, 02, 03…)',
				'desc'     => 'Geo & vertical match, Verified supply, Volume terms…',
				'where'    => 'Нумерованный список с описаниями',
				'order'    => 110,
				'icon'     => 'dashicons-list-view',
				'examples' => array( 'Geo & vertical match', 'Verified supply' ),
			),
			'home-investors'       => array(
				'id'       => 'home-investors',
				'category' => 'home',
				'title'    => 'Инвесторы / Backed by',
				'desc'     => '«BACKED BY BUILDERS», карточки инвесторов',
				'where'    => 'Карусель логотипов и имён инвесторов',
				'order'    => 120,
				'icon'     => 'dashicons-groups',
				'examples' => array( 'BACKED BY', 'INVESTORS' ),
			),
			'home-footer-cta'      => array(
				'id'       => 'home-footer-cta',
				'category' => 'home',
				'title'    => 'SAVE THE DAY (низ главной)',
				'desc'     => '«SAVE THE DAY» перед подвалом',
				'where'    => 'Нижняя часть главной, перед footer',
				'order'    => 130,
				'icon'     => 'dashicons-arrow-down-alt',
				'examples' => array( 'SAVE THE DAY', 'Closed access infrastructure' ),
			),
			'home-ui-labels'       => array(
				'id'       => 'home-ui-labels',
				'category' => 'home',
				'title'    => 'Мелкие подписи на главной',
				'desc'     => 'Learn more, Next step, Sound ON/OFF, LinkedIn…',
				'where'    => 'Кнопки и подписи по всей главной',
				'order'    => 140,
				'icon'     => 'dashicons-tag',
				'examples' => array( 'LEARN MORE', 'SOUND: ON', 'How it works' ),
			),
			'home-services-copy'   => array(
				'id'       => 'home-services-copy',
				'category' => 'home',
				'title'    => 'Описания услуг (длинные тексты)',
				'desc'     => 'Параграфы про Agency accounts, Facebook/Google/TikTok…',
				'where'    => 'Тексты описаний услуг на главной и в блоках',
				'order'    => 150,
				'icon'     => 'dashicons-text-page',
				'examples' => array( 'Random sellers fail', 'Facebook, Google, TikTok' ),
			),
			'home-misc'            => array(
				'id'       => 'home-misc',
				'category' => 'home',
				'title'    => 'Прочее на главной',
				'desc'     => 'Тексты без отдельного блока',
				'where'    => 'Главная страница',
				'order'    => 999,
				'icon'     => 'dashicons-editor-alignleft',
				'examples' => array(),
			),

			// ── Шапка ────────────────────────────────────────────────
			'header-nav'           => array(
				'id'       => 'header-nav',
				'category' => 'header',
				'title'    => 'Пункты меню',
				'desc'     => 'About · Blog · Contact · ACCOUNTS ▾ · Request access · dropdown',
				'where'    => 'Верхняя навигация и мобильное меню',
				'order'    => 10,
				'icon'     => 'dashicons-menu',
				'examples' => array( 'About', 'Blog', 'Contact', 'ACCOUNTS', 'Platform Access', 'Agency Accounts', 'Team Supply' ),
			),
			'header-sound'         => array(
				'id'       => 'header-sound',
				'category' => 'header',
				'title'    => 'Кнопка звука',
				'desc'     => 'SOUND: ON / OFF, Enable sound',
				'where'    => 'Левый верхний угол шапки',
				'order'    => 20,
				'icon'     => 'dashicons-controls-volumeon',
				'examples' => array( 'SOUND: ON', 'Enable sound' ),
			),
			'header-cta'           => array(
				'id'       => 'header-cta',
				'category' => 'header',
				'title'    => 'Кнопка Request Access',
				'desc'     => 'Красная кнопка в шапке',
				'where'    => 'Правый верхний угол',
				'order'    => 30,
				'icon'     => 'dashicons-unlock',
				'examples' => array( 'Request access', 'REQUEST ACCESS' ),
			),
			'header-lang'          => array(
				'id'       => 'header-lang',
				'category' => 'header',
				'title'    => 'Переключатель EN / RU',
				'desc'     => 'Языковой переключатель',
				'where'    => 'Справа в шапке, рядом с кнопкой',
				'order'    => 40,
				'icon'     => 'dashicons-translation',
				'examples' => array( 'EN', 'RU' ),
			),
			'header-misc'          => array(
				'id'       => 'header-misc',
				'category' => 'header',
				'title'    => 'Прочее в шапке',
				'desc'     => 'Остальные тексты шапки',
				'where'    => 'Шапка сайта',
				'order'    => 999,
				'icon'     => 'dashicons-editor-alignleft',
				'examples' => array(),
			),

			// ── Подвал ─────────────────────────────────────────────
			'footer-cta'           => array(
				'id'       => 'footer-cta',
				'category' => 'footer',
				'title'    => 'REQUEST ACCESS (подвал)',
				'desc'     => 'Большой заголовок и текст в footer',
				'where'    => 'Центр подвала, над городским силуэтом',
				'order'    => 10,
				'icon'     => 'dashicons-megaphone',
				'examples' => array( 'REQUEST ACCESS', 'Closed access infrastructure' ),
			),
			'footer-links'         => array(
				'id'       => 'footer-links',
				'category' => 'footer',
				'title'    => 'Ссылки в подвале',
				'desc'     => 'Privacy, Terms, Telegram, WhatsApp…',
				'where'    => 'Нижняя строка подвала',
				'order'    => 20,
				'icon'     => 'dashicons-admin-links',
				'examples' => array( 'Privacy', 'Terms', 'Telegram' ),
			),
			'footer-misc'          => array(
				'id'       => 'footer-misc',
				'category' => 'footer',
				'title'    => 'Прочее в подвале',
				'desc'     => 'Копирайт и прочие тексты',
				'where'    => 'Подвал сайта',
				'order'    => 999,
				'icon'     => 'dashicons-editor-alignleft',
				'examples' => array(),
			),

			// ── About ──────────────────────────────────────────────
			'about-hero'           => array(
				'id'       => 'about-hero',
				'category' => 'about',
				'title'    => 'Hero «Meet impact.accs»',
				'desc'     => 'Заголовок и вступление',
				'where'    => 'Верх страницы About',
				'order'    => 10,
				'icon'     => 'dashicons-welcome-view-site',
				'examples' => array( 'Meet impact.accs' ),
			),
			'about-content'        => array(
				'id'       => 'about-content',
				'category' => 'about',
				'title'    => 'Основной текст About',
				'desc'     => 'Параграфы на странице',
				'where'    => 'Середина страницы About',
				'order'    => 20,
				'icon'     => 'dashicons-text',
				'examples' => array(),
			),
			'about-misc'           => array(
				'id'       => 'about-misc',
				'category' => 'about',
				'title'    => 'Прочее на About',
				'desc'     => 'Остальные тексты',
				'where'    => 'Страница About',
				'order'    => 999,
				'icon'     => 'dashicons-editor-alignleft',
				'examples' => array(),
			),

			// ── Блог ───────────────────────────────────────────────
			'blog-list'            => array(
				'id'       => 'blog-list',
				'category' => 'blog',
				'title'    => 'Список статей',
				'desc'     => 'Заголовок журнала, превью постов',
				'where'    => 'Страница /blog/',
				'order'    => 10,
				'icon'     => 'dashicons-admin-post',
				'examples' => array( 'impact.accs journal' ),
			),
			'blog-post-manifesto'  => array(
				'id'       => 'blog-post-manifesto',
				'category' => 'blog',
				'title'    => 'Статья «Манифест»',
				'desc'     => 'Текст поста /blog/manifesto/',
				'where'    => 'Страница статьи',
				'order'    => 20,
				'icon'     => 'dashicons-media-document',
				'examples' => array( 'Access Is Infrastructure' ),
			),
			'blog-post-yc'         => array(
				'id'       => 'blog-post-yc',
				'category' => 'blog',
				'title'    => 'Статья «5 лет на рынке»',
				'desc'     => 'Текст второго поста',
				'where'    => 'Страница статьи',
				'order'    => 30,
				'icon'     => 'dashicons-media-document',
				'examples' => array(),
			),
			'blog-misc'            => array(
				'id'       => 'blog-misc',
				'category' => 'blog',
				'title'    => 'Прочее в блоге',
				'desc'     => 'Остальные тексты блога',
				'where'    => 'Блог',
				'order'    => 999,
				'icon'     => 'dashicons-editor-alignleft',
				'examples' => array(),
			),

			// ── Accounts / услуги ──────────────────────────────────
			'accounts-agency'      => array(
				'id'       => 'accounts-agency',
				'category' => 'accounts',
				'title'    => 'Agency Accounts',
				'desc'     => 'Страница /accounts/agency-accounts/',
				'where'    => 'Заголовок, описание, кнопки',
				'order'    => 10,
				'icon'     => 'dashicons-building',
				'examples' => array( 'agency', 'accounts' ),
			),
			'accounts-platform'    => array(
				'id'       => 'accounts-platform',
				'category' => 'accounts',
				'title'    => 'Platform Access',
				'desc'     => 'Страница platform access',
				'where'    => 'Заголовок, описание, кнопки',
				'order'    => 20,
				'icon'     => 'dashicons-desktop',
				'examples' => array( 'platform', 'access' ),
			),
			'accounts-team'        => array(
				'id'       => 'accounts-team',
				'category' => 'accounts',
				'title'    => 'Team Supply',
				'desc'     => 'Страница team supply',
				'where'    => 'Заголовок, описание, кнопки',
				'order'    => 30,
				'icon'     => 'dashicons-groups',
				'examples' => array( 'team', 'supply' ),
			),
			'accounts-misc'        => array(
				'id'       => 'accounts-misc',
				'category' => 'accounts',
				'title'    => 'Прочее в Accounts',
				'desc'     => 'Остальные тексты услуг',
				'where'    => 'Страницы Accounts',
				'order'    => 999,
				'icon'     => 'dashicons-editor-alignleft',
				'examples' => array(),
			),

			// ── Контакты и формы ───────────────────────────────────
			'contact-page'         => array(
				'id'       => 'contact-page',
				'category' => 'contact',
				'title'    => 'Страница Contact',
				'desc'     => 'Тексты на /contact/',
				'where'    => 'Страница контактов',
				'order'    => 10,
				'icon'     => 'dashicons-email',
				'examples' => array(),
			),
			'contact-modal'        => array(
				'id'       => 'contact-modal',
				'category' => 'contact',
				'title'    => 'Окно «Связаться»',
				'desc'     => 'Модальное окно Contact us',
				'where'    => 'Всплывающее окно при клике Contact',
				'order'    => 20,
				'icon'     => 'dashicons-format-status',
				'examples' => array( 'Email us' ),
			),
			'application-form'     => array(
				'id'       => 'application-form',
				'category' => 'contact',
				'title'    => 'Форма «Запросить доступ»',
				'desc'     => 'Страница /application/ — поля, кнопки',
				'where'    => 'Страница заявки',
				'order'    => 30,
				'icon'     => 'dashicons-forms',
				'examples' => array( 'Request access', 'Access' ),
			),
			'waitlist-modal'       => array(
				'id'       => 'waitlist-modal',
				'category' => 'contact',
				'title'    => 'Окно Request Access',
				'desc'     => 'Модалка при клике на красную кнопку',
				'where'    => 'Всплывающее окно поверх сайта',
				'order'    => 40,
				'icon'     => 'dashicons-unlock',
				'examples' => array( 'Request access', 'Waitlist' ),
			),
			'contact-misc'         => array(
				'id'       => 'contact-misc',
				'category' => 'contact',
				'title'    => 'Прочее в формах',
				'desc'     => 'Остальные тексты',
				'where'    => 'Контакты и формы',
				'order'    => 999,
				'icon'     => 'dashicons-editor-alignleft',
				'examples' => array(),
			),

			// ── 404 ────────────────────────────────────────────────
			'404-page'             => array(
				'id'       => '404-page',
				'category' => '404',
				'title'    => 'Страница не найдена',
				'desc'     => 'Текст 404',
				'where'    => 'Когда страница не существует',
				'order'    => 10,
				'icon'     => 'dashicons-warning',
				'examples' => array( 'not found', '404' ),
			),

			// ── Прочее ─────────────────────────────────────────────
			'other-translations'   => array(
				'id'       => 'other-translations',
				'category' => 'other',
				'title'    => 'Общие переводы',
				'desc'     => 'Тексты из translations.json и ru-map.php',
				'where'    => 'Используются на нескольких страницах',
				'order'    => 10,
				'icon'     => 'dashicons-translation',
				'examples' => array(),
			),
			'other-misc'           => array(
				'id'       => 'other-misc',
				'category' => 'other',
				'title'    => 'Прочее',
				'desc'     => 'Неклассифицированные тексты',
				'where'    => 'Сайт',
				'order'    => 999,
				'icon'     => 'dashicons-editor-alignleft',
				'examples' => array(),
			),
		);

		return $catalog;
	}

	/**
	 * Blocks for a category, sorted by scroll order.
	 *
	 * @param string $category Category slug.
	 * @return array<int,array<string,mixed>>
	 */
	public static function blocks_for_category( $category ) {
		$out = array();
		foreach ( self::catalog() as $block ) {
			if ( ( $block['category'] ?? '' ) === $category ) {
				$out[] = $block;
			}
		}
		usort(
			$out,
			static function ( $a, $b ) {
				return ( $a['order'] ?? 999 ) <=> ( $b['order'] ?? 999 );
			}
		);
		return $out;
	}

	/**
	 * Map PHP file comment to block id.
	 *
	 * @param string $comment Comment text.
	 * @return string
	 */
	public static function hint_to_block( $comment ) {
		$map = array(
			'Hero / alerts'              => 'home-hero-alerts',
			'Hero chat overlays'         => 'home-hero-chat',
			'Investors / ready block'    => 'home-ready',
			'Feature columns'            => 'home-two-columns',
			'Manifesto'                  => 'home-manifesto',
			'Interfaces carousel'        => 'home-interfaces',
			'Closed infrastructure'      => 'home-closed',
			'Footer CTA block'           => 'home-footer-cta',
			'Process cards'              => 'home-process-cards',
			'Numbered features'          => 'home-features-list',
			'Platform / agency sections' => 'home-services-copy',
			'UI labels'                  => 'home-ui-labels',
			'Header nav'                 => 'header-nav',
			'Contact modal'              => 'contact-modal',
			'Section headings'           => 'home-interfaces',
			'CTAs / credits'             => 'home-ui-labels',
		);

		$comment = trim( $comment );
		if ( isset( $map[ $comment ] ) ) {
			return $map[ $comment ];
		}

		foreach ( $map as $needle => $block_id ) {
			if ( false !== stripos( $comment, $needle ) ) {
				return $block_id;
			}
		}

		return '';
	}

	/**
	 * Resolve block for a registry text row.
	 *
	 * @param array<string,mixed> $item Text item.
	 * @return array<string,mixed>
	 */
	public static function resolve( $item ) {
		$catalog  = self::catalog();
		$category = $item['category'] ?? 'other';
		$source   = strtolower( (string) ( $item['source'] ?? '' ) );
		$en       = (string) ( $item['en'] ?? '' );
		$en_low   = strtolower( $en );

		if ( ! empty( $item['nav_menu'] ) ) {
			return $catalog['header-nav'];
		}

		if ( ! empty( $item['contact_info'] ) ) {
			return $catalog['contact-modal'];
		}

		$block_id = '';

		if ( ! empty( $item['block_hint'] ) ) {
			$block_id = self::hint_to_block( (string) $item['block_hint'] );
		}

		if ( '' === $block_id && ! empty( $item['block'] ) && isset( $catalog[ $item['block'] ] ) ) {
			return $catalog[ $item['block'] ];
		}

		// By source file.
		if ( '' === $block_id ) {
			$file_map = array(
				'header.html'                          => 'header-nav',
				'footer.html'                          => 'footer-cta',
				'about-page.html'                      => 'about-hero',
				'blog-page.html'                       => 'blog-list',
				'blog-post-manifesto.html'             => 'blog-post-manifesto',
				'blog-post-yc-p26.html'                => 'blog-post-yc',
				'contact-page.html'                    => 'contact-page',
				'contact-modal.html'                   => 'contact-modal',
				'application-page.html'                => 'application-form',
				'waitlist-modal.html'                  => 'waitlist-modal',
				'feature-autonomous-alerts.html'       => 'accounts-agency',
				'feature-conversational-debugging.html' => 'accounts-platform',
				'feature-coding-agents.html'           => 'accounts-team',
				'not-found-page.html'                  => '404-page',
				'404-impact.php'                       => '404-page',
				'translations.json'                    => 'other-translations',
				'ru-map.php'                           => 'other-translations',
				'ru-js.php'                            => 'contact-misc',
			);
			foreach ( $file_map as $file => $bid ) {
				if ( false !== strpos( $source, $file ) ) {
					$block_id = $bid;
					break;
				}
			}
		}

		// Keyword heuristics (home).
		if ( '' === $block_id && 'home' === $category ) {
			$block_id = self::guess_home_block( $en, $en_low );
		}

		if ( '' === $block_id && 'header' === $category ) {
			if ( preg_match( '/sound|audio|muted/i', $en ) ) {
				$block_id = 'header-sound';
			} elseif ( preg_match( '/request access/i', $en ) ) {
				$block_id = 'header-cta';
			} elseif ( in_array( $en_low, array( 'en', 'ru', 'english', 'russian' ), true ) ) {
				$block_id = 'header-lang';
			} elseif ( in_array( $en, array( 'About', 'Blog', 'Contact', 'Menu', 'MENU', 'FEATURES', 'ACCOUNTS', 'Accounts', 'Impact', 'Platform Access', 'Agency Accounts', 'Team Supply' ), true ) ) {
				$block_id = 'header-nav';
			}
		}

		if ( '' === $block_id ) {
			$block_id = $category . '-misc';
			if ( ! isset( $catalog[ $block_id ] ) ) {
				$block_id = 'other-misc';
			}
		}

		return $catalog[ $block_id ] ?? $catalog['other-misc'];
	}

	/**
	 * @param string $en     Original EN.
	 * @param string $en_low Lowercase EN.
	 * @return string Block id.
	 */
	private static function guess_home_block( $en, $en_low ) {
		$rules = array(
			'home-preloader'     => '/initializ|enable sound|click anywhere/i',
			'home-hero-alerts'   => '/launch blocked|supply stable|volume request|access confirmed|supply confirmed|supply matched|delivery scheduled/i',
			'home-hero-chat'     => '/request status|supply status|volume terms|agency account batch|repeat order confirmed|working resource —|need eu accounts/i',
			'home-ready'         => '/^ready for action$|with investors from|resource layer/i',
			'home-two-columns'   => '/^access in minutes$|^works with your teams$/i',
			'home-manifesto'     => '/^manifesto$|more than a shop|infrastructure for teams that run traffic/i',
			'home-interfaces'    => '/^interfaces$|chaos is optional|resource over noise|read blog|account sellers have accumulated noise/i',
			'home-closed'        => '/^closed infrastructure$|closed infrastructure for teams/i',
			'home-process-cards' => '/^closed access$|^verified supply$|^controlled delivery$|^desk control$|private team-only flow|structured account sourcing|tracked handoff|one channel for requests/i',
			'home-features-list' => '/^geo & vertical match$|^verified supply$|encrypted in transit|clear terms for every team member/i',
			'home-investors'     => '/^backed by$|^investors$|^customer$|^reviews$/i',
			'home-footer-cta'    => '/^save the day$/i',
			'home-hero-cards'    => '/^agency accounts$|^platform access$|^team supply$|^media buying access$/i',
			'home-ui-labels'     => '/^learn more$|^read more|^next step$|^how it works$|^waitlist$|^linkedin$|^sound:|^platform$|^agency$/i',
		);

		foreach ( $rules as $block_id => $pattern ) {
			if ( preg_match( $pattern, $en ) ) {
				return $block_id;
			}
		}

		if ( mb_strlen( $en ) > 80 || preg_match( '/facebook, google, tiktok|random sellers|agency accounts prepared/i', $en ) ) {
			return 'home-services-copy';
		}

		return 'home-misc';
	}

	/**
	 * Attach block fields to a registry row.
	 *
	 * @param array<string,mixed> $item Item.
	 * @return array<string,mixed>
	 */
	public static function enrich( $item ) {
		$block = self::resolve( $item );
		$item['block']       = $block['id'];
		$item['block_title'] = $block['title'];
		$item['block_desc']  = $block['desc'];
		$item['block_where'] = $block['where'];
		$item['block_order'] = $block['order'];
		return $item;
	}
}
