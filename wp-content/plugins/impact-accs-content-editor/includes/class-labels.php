<?php
/**
 * Human-readable section labels for admin UI.
 *
 * @package ImpactAccsContentEditor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Friendly labels.
 */
class IACCE_Labels {

	/**
	 * Map filename / path fragment to readable section name.
	 *
	 * @return array<string,string>
	 */
	public static function section_map() {
		return array(
			'index.html'                            => 'Главная страница',
			'header.html'                           => 'Шапка и меню (везде)',
			'footer.html'                           => 'Подвал сайта (везде)',
			'about-page.html'                       => 'Страница «О нас»',
			'blog-page.html'                        => 'Страница «Блог»',
			'blog-post-manifesto.html'              => 'Статья «Манифест»',
			'blog-post-yc-p26.html'                 => 'Статья «5 лет на рынке»',
			'contact-page.html'                     => 'Страница «Контакты»',
			'contact-modal.html'                    => 'Окно «Связаться»',
			'application-page.html'                 => 'Форма «Запросить доступ»',
			'waitlist-modal.html'                   => 'Окно «Request Access»',
			'feature-autonomous-alerts.html'          => 'Agency Accounts',
			'feature-conversational-debugging.html' => 'Platform Access',
			'feature-coding-agents.html'            => 'Team Supply',
			'not-found-page.html'                   => 'Страница 404',
			'404-impact.php'                        => 'Страница 404',
			'translations.json'                     => 'Переводы · все страницы',
			'ru-map.php'                            => 'Переводы · шапка, страницы',
			'ru-js.php'                             => 'Переводы · кнопки и формы',
			'ru-home-extra.php'                     => 'Главная · 3D-сцена и анимации',
			'ru-home-exact.php'                     => 'Главная · кнопки и заголовки',
		);
	}

	/**
	 * Main site areas for dashboard cards.
	 *
	 * @return array<string,array{label:string,desc:string,icon:string}>
	 */
	public static function dashboard_sections() {
		return array(
			'home'     => array(
				'label' => 'Главная',
				'desc'  => 'Слайдер, 3D-здания, чат, манифест, анимации',
				'icon'  => 'dashicons-admin-home',
			),
			'header'   => array(
				'label' => 'Шапка и меню',
				'desc'  => 'Логотип, пункты меню, кнопка Request Access, звук',
				'icon'  => 'dashicons-menu-alt3',
			),
			'footer'   => array(
				'label' => 'Подвал',
				'desc'  => 'Ссылки внизу, копирайт, соцсети',
				'icon'  => 'dashicons-arrow-down-alt',
			),
			'about'    => array(
				'label' => 'О нас',
				'desc'  => 'Страница About',
				'icon'  => 'dashicons-groups',
			),
			'blog'     => array(
				'label' => 'Блог',
				'desc'  => 'Список статей и тексты постов',
				'icon'  => 'dashicons-admin-post',
			),
			'accounts' => array(
				'label' => 'Accounts (услуги)',
				'desc'  => 'Agency Accounts, Platform Access, Team Supply',
				'icon'  => 'dashicons-portfolio',
			),
			'contact'  => array(
				'label' => 'Контакты и формы',
				'desc'  => 'Contact, заявки, модальные окна',
				'icon'  => 'dashicons-email',
			),
			'404'      => array(
				'label' => 'Страница 404',
				'desc'  => 'Текст «страница не найдена»',
				'icon'  => 'dashicons-warning',
			),
			'other'    => array(
				'label' => 'Прочее',
				'desc'  => 'Остальные тексты',
				'icon'  => 'dashicons-editor-alignleft',
			),
		);
	}

	/**
	 * Ordered sections for filter dropdown.
	 *
	 * @return array<string,string> slug => label
	 */
	public static function filter_sections() {
		$out = array();
		foreach ( self::dashboard_sections() as $slug => $meta ) {
			$out[ $slug ] = $meta['label'];
		}
		return $out;
	}

	/**
	 * Resolve friendly section + category from raw group/source.
	 *
	 * @param string $group  Raw group.
	 * @param string $source Source file.
	 * @return array{section:string,category:string}
	 */
	public static function resolve( $group, $source ) {
		$map      = self::section_map();
		$section  = 'Прочее';
		$category = 'other';

		foreach ( $map as $needle => $label ) {
			if ( false !== stripos( $source, $needle ) || false !== stripos( $group, $needle ) ) {
				$section = $label;
				break;
			}
		}

		if ( false !== stripos( $source, 'index.html' ) || false !== stripos( $group, 'ru-home' ) ) {
			$category = 'home';
		} elseif ( false !== stripos( $source, 'header' ) ) {
			$category = 'header';
		} elseif ( false !== stripos( $source, 'footer' ) ) {
			$category = 'footer';
		} elseif ( false !== stripos( $source, 'about' ) ) {
			$category = 'about';
		} elseif ( false !== stripos( $source, 'blog' ) ) {
			$category = 'blog';
		} elseif ( false !== stripos( $source, 'feature-' ) || false !== stripos( $group, 'accounts' ) ) {
			$category = 'accounts';
		} elseif ( false !== stripos( $source, 'contact' ) || false !== stripos( $source, 'application' ) || false !== stripos( $source, 'waitlist' ) ) {
			$category = 'contact';
		} elseif ( false !== stripos( $source, 'not-found' ) || false !== stripos( $source, '404' ) ) {
			$category = '404';
		} elseif ( false !== stripos( $group, 'Главная' ) || false !== stripos( $group, 'Переводы' ) ) {
			$category = 'home';
		}

		return array(
			'section'  => $section,
			'category' => $category,
		);
	}

	/**
	 * Short preview for table row.
	 *
	 * @param string $text Text.
	 * @param int    $max  Max length.
	 * @return string
	 */
	public static function preview( $text, $max = 120 ) {
		$text = trim( (string) $text );
		if ( mb_strlen( $text ) <= $max ) {
			return $text;
		}
		return mb_substr( $text, 0, $max - 1 ) . '…';
	}
}
