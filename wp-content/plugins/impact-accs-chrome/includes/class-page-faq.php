<?php
/**
 * FAQ blocks for Russian account and about pages.
 *
 * @package ImpactAccsChrome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Appends page-specific FAQ sections from the approved RU copy.
 */
class IAC_Page_FAQ {

	/**
	 * Singleton.
	 *
	 * @var IAC_Page_FAQ|null
	 */
	private static $instance = null;

	/**
	 * Instance getter.
	 *
	 * @return IAC_Page_FAQ
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register hooks.
	 */
	private function __construct() {
		add_filter( 'the_content', array( $this, 'append_faq' ), 90 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ), 1001 );
	}

	/**
	 * FAQ is specified only for the Russian versions of these pages.
	 *
	 * @return bool
	 */
	private function should_render() {
		if ( ! class_exists( 'IAC_I18n' ) || ! IAC_I18n::is_ru() ) {
			return false;
		}

		if ( class_exists( 'IAC_Feature_Page' ) && IAC_Feature_Page::is_feature_page() ) {
			return true;
		}

		return class_exists( 'IAC_About_Page' ) && IAC_About_Page::is_about_page();
	}

	/**
	 * Load the shared visual layer only where FAQ is rendered.
	 */
	public function enqueue_style() {
		if ( ! $this->should_render() ) {
			return;
		}

		wp_enqueue_style(
			'impact-accs-page-faq',
			IAC_URL . 'assets/css/page-faq.css',
			array( 'impact-accs-wp' ),
			IAC_VERSION
		);
	}

	/**
	 * Append the FAQ as the last content block before the global footer.
	 *
	 * @param string $content Page content.
	 * @return string
	 */
	public function append_faq( $content ) {
		if ( ! in_the_loop() || ! is_main_query() || ! $this->should_render() ) {
			return $content;
		}

		$key = $this->current_key();
		if ( '' === $key ) {
			return $content;
		}

		$config = $this->configs();
		if ( ! isset( $config[ $key ] ) ) {
			return $content;
		}

		return $content . $this->render_section( $config[ $key ] );
	}

	/**
	 * Resolve FAQ config key for the current page.
	 *
	 * @return string
	 */
	private function current_key() {
		if ( class_exists( 'IAC_Feature_Page' ) ) {
			$slug = IAC_Feature_Page::get_slug();
			if ( in_array( $slug, array( 'platform-access', 'agency-accounts', 'team-supply' ), true ) ) {
				return $slug;
			}
		}

		if ( class_exists( 'IAC_About_Page' ) && IAC_About_Page::is_about_page() ) {
			return 'about';
		}

		return '';
	}

	/**
	 * Approved FAQ copy from the page specification.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function configs() {
		return array(
			'platform-access' => array(
				'items' => array(
					array(
						'q' => 'Какие аккаунты вы продаёте?',
						'a' => 'Трастовые Google Ads спенд-аккаунты с реальной историей открутки. Без авторегов и пустого фарма.',
					),
					array(
						'q' => 'Какое гео и валюта?',
						'a' => 'Основное гео — USA, валюта — USD. Наличие нужного тира подтверждает владелец перед подбором.',
					),
					array(
						'q' => 'Можно ли проверить аккаунты до оплаты?',
						'a' => 'Да. Команда проверяет вход, спенд, USA, USD и доступ каждой позиции. После подтверждения оплачивает партию.',
					),
					array(
						'q' => 'Как передаются аккаунты?',
						'a' => 'На указанные почты или администратором в MCC. Команда заранее сообщает, куда передать каждую позицию.',
					),
					array(
						'q' => 'Когда действует замена?',
						'a' => 'Заменяем нерабочую или несоответствующую позицию, пока команда не внесла в неё изменения.',
					),
					array(
						'q' => 'Можно ли заказать десятки аккаунтов?',
						'a' => 'Да. Подбираем десятки и сотни позиций под залив. Оптовые условия обсуждаются индивидуально.',
					),
				),
				'cta_title' => 'НУЖНЫ АККАУНТЫ ПОД ЗАЛИВ?',
				'cta_text'  => 'Отправьте спенд, USA, USD, вертикаль и объём. Владелец impact. передаст подходящие аккаунты на проверку до оплаты.',
				'cta_label' => 'ПОЛУЧИТЬ АККАУНТЫ НА ПРОВЕРКУ',
				'cta_meta'  => '@founderads · проверка до оплаты · замена до изменений · поддержка 24/7',
			),
			'agency-accounts' => array(
				'items' => array(
					array(
						'q' => 'Для какого формата подходит эта страница?',
						'a' => 'Для медиабаинговых команд, которым нужны десятки аккаунтов под несколько связок в одном заказе.',
					),
					array(
						'q' => 'Можно ли собрать разные тиры в одном подборе?',
						'a' => 'Да. Например, 40 аккаунтов со спендом $2 000–3 000, 30 — $1 000–2 000 и ещё 30 — $500–1 000. Гео USA, валюта USD.',
					),
					array(
						'q' => 'Проверяется ли каждая позиция?',
						'a' => 'Да. Вход, спенд, USA, USD и доступ проверяются до оплаты. Несоответствующие позиции заменяются отдельно.',
					),
					array(
						'q' => 'Как формируется цена?',
						'a' => 'Цена каждой позиции зависит от тира. При крупных и регулярных закупках согласуем индивидуальные оптовые условия.',
					),
					array(
						'q' => 'Как передаются аккаунты?',
						'a' => 'На указанные email или администратором в MCC. Можно распределить позиции между сотрудниками команды.',
					),
					array(
						'q' => 'Кто ведёт заказ?',
						'a' => 'Лично владелец impact.: от подбора и проверки до оплаты, замены и повторной закупки. Telegram — @founderads.',
					),
				),
				'cta_title' => 'НУЖНЫ ДЕСЯТКИ АККАУНТОВ ПОД НЕСКОЛЬКО СВЯЗОК?',
				'cta_text'  => 'Отправьте параметры одним сообщением. Владелец соберёт список, подтвердит цены и передаст аккаунты на проверку.',
				'cta_label' => 'ЗАПРОСИТЬ ПОДБОР ДЛЯ МЕДИАБАИНГА',
				'cta_meta'  => '100 аккаунтов в одном заказе · проверка каждой позиции · поддержка владельца 24/7',
			),
			'team-supply' => array(
				'items' => array(
					array(
						'q' => 'Для каких команд подходит регулярная поставка?',
						'a' => 'Для медиабаинга, которому постоянно нужны сотни трастовых Google Ads аккаунтов под залив и масштабирование.',
					),
					array(
						'q' => 'Можете обеспечить сотни аккаунтов?',
						'a' => 'Да. Владелец подтверждает, сколько позиций есть в списке сейчас и какой объём нужно найти под запрос.',
					),
					array(
						'q' => 'Что, если всей партии нет сразу?',
						'a' => 'Передаём доступную часть, фиксируем срок поиска остатка или делим поставку на этапы. Параметры не меняем без согласования.',
					),
					array(
						'q' => 'Можно проверить партию до оплаты?',
						'a' => 'Да. Команда проверяет каждую позицию. Несоответствующие аккаунты заменяются до внесения изменений.',
					),
					array(
						'q' => 'Есть ли условия под объём?',
						'a' => 'Да. Оптовые цены согласуем индивидуально с учётом объёма, тиров и регулярности закупок.',
					),
					array(
						'q' => 'Как организовать повторные продажи?',
						'a' => 'Фиксируем типичные тиры, USA, USD и график. Перед каждой новой партией подтверждаем список, цену и срок.',
					),
				),
				'cta_title' => 'НУЖНА РЕГУЛЯРНАЯ ПОСТАВКА СОТЕН АККАУНТОВ?',
				'cta_text'  => 'Отправьте объём, тиры, USA, USD и график. Владелец impact. подтвердит список и срок первой поставки.',
				'cta_label' => 'СОГЛАСОВАТЬ ПОСТАВКУ',
				'cta_meta'  => 'Проверка до оплаты · условия под объём · замена позиции · @founderads 24/7',
			),
			'about' => array(
				'items' => array(
					array(
						'q' => 'Что такое impact.?',
						'a' => 'Поставщик трастовых Google Ads спенд-аккаунтов для медиабаинговых и арбитражных команд.',
					),
					array(
						'q' => 'Что подтверждает опыт компании?',
						'a' => '7 лет на рынке, 15 000 выданных аккаунтов и более 100 активных команд.',
					),
					array(
						'q' => 'Какие аккаунты вы продаёте?',
						'a' => 'Только Google Ads аккаунты с реальным спендом и белой историей. Основное гео USA, валюта USD.',
					),
					array(
						'q' => 'Почему покупка безопаснее?',
						'a' => 'Сначала команда получает аккаунты и проверяет параметры, потом оплачивает. Крупную сделку можно провести через гаранта.',
					),
					array(
						'q' => 'Как работает замена?',
						'a' => 'Нерабочую или несоответствующую позицию заменяем до внесения изменений со стороны команды.',
					),
					array(
						'q' => 'Кто отвечает за заказ?',
						'a' => 'Лично владелец impact. Подбор, проверка, оплата, замена и повторные продажи проходят в Telegram: @founderads.',
					),
				),
				'cta_title' => 'ГОТОВЫ ПРОВЕРИТЬ IMPACT. НА ПРАКТИКЕ?',
				'cta_text'  => 'Опишите спенд, USA, USD, вертикаль и объём. Получите трастовые аккаунты на проверку до оплаты.',
				'cta_label' => 'ПОДОБРАТЬ АККАУНТЫ',
				'cta_meta'  => '7 лет на рынке · 15 000 аккаунтов · 100+ команд · @founderads 24/7',
			),
		);
	}

	/**
	 * Render one FAQ section.
	 *
	 * @param array<string,mixed> $config FAQ config.
	 * @return string
	 */
	private function render_section( $config ) {
		$application_url = class_exists( 'IAC_Application_Page' ) ? IAC_Application_Page::url() : 'https://t.me/founderads';

		ob_start();
		?>
		<section class="iac-page-faq" aria-labelledby="iac-page-faq-title">
			<div class="iac-page-faq__shell">
				<header class="iac-page-faq__header">
					<div class="iac-page-faq__eyebrow"><span aria-hidden="true"></span>БАЗА ЗНАНИЙ · 6 ВОПРОСОВ</div>
					<h2 id="iac-page-faq-title" class="iac-page-faq__title"><strong>FAQ:</strong> ЧАСТЫЕ ВОПРОСЫ</h2>
				</header>

				<div class="iac-page-faq__list">
					<?php foreach ( $config['items'] as $index => $item ) : ?>
						<details class="iac-page-faq__item" style="--faq-index:<?php echo esc_attr( (string) $index ); ?>" <?php echo 0 === $index ? 'open' : ''; ?>>
							<summary class="iac-page-faq__question">
								<span class="iac-page-faq__index" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								<span class="iac-page-faq__question-text"><?php echo esc_html( $item['q'] ); ?></span>
								<span class="iac-page-faq__plus" aria-hidden="true"></span>
							</summary>
							<div class="iac-page-faq__answer"><p><?php echo esc_html( $item['a'] ); ?></p></div>
						</details>
					<?php endforeach; ?>
				</div>

				<aside class="iac-page-faq__cta" aria-label="Следующий шаг">
					<div class="iac-page-faq__cta-copy">
						<span class="iac-page-faq__cta-kicker">СЛЕДУЮЩИЙ ШАГ</span>
						<h3><?php echo esc_html( $config['cta_title'] ); ?></h3>
						<p><?php echo esc_html( $config['cta_text'] ); ?></p>
					</div>
					<div class="iac-page-faq__cta-action">
						<a class="iac-page-faq__cta-button" href="<?php echo esc_url( $application_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $config['cta_label'] ); ?></a>
						<div class="iac-page-faq__cta-meta"><?php echo esc_html( $config['cta_meta'] ); ?></div>
					</div>
				</aside>
			</div>
		</section>
		<?php
		return (string) ob_get_clean();
	}
}
