<?php

// =========================================================================
// 1. CONSTANTS
// =========================================================================

// Define the absolute path to the theme's templates directory
define('TEMPLATE_PATH', dirname(__FILE__) . '/templates/');


// =========================================================================
// 2. ENQUEUE STYLES AND SCRIPTS
// =========================================================================

function theme_enqueue_styles()
{
	wp_enqueue_style('swiper', get_template_directory_uri() . '/assets/css/libs/swiper-bundle.min.css');
	wp_enqueue_style('fancybox', get_template_directory_uri() . '/assets/css/libs/fancybox.css');
	wp_enqueue_style('input-tel', get_template_directory_uri() . '/assets/css/libs/input-tel.css');
	wp_enqueue_style('reset', get_template_directory_uri() . '/assets/css/reset.min.css');
	wp_enqueue_style('main-style', get_template_directory_uri() . '/assets/css/style.min.css');
}
add_action('wp_enqueue_scripts', 'theme_enqueue_styles');


function theme_enqueue_scripts()
{
	wp_deregister_script('jquery');
	wp_enqueue_script('jquery', get_template_directory_uri() . '/assets/js/libs/jquery-3.7.1.min.js', array(), null, true);
	wp_enqueue_script('swiper-js', get_template_directory_uri() . '/assets/js/libs/swiper-bundle.min.js', array('jquery'), null, true);
	wp_enqueue_script('fancybox-js', get_template_directory_uri() . '/assets/js/libs/fancybox.umd.js', array('jquery'), null, true);
	wp_enqueue_script('intlTelInput-js', get_template_directory_uri() . '/assets/js/libs/intlTelInput.min.js', array('jquery'), null, true);

	wp_enqueue_script('cdek-widget', 'https://cdn.jsdelivr.net/npm/@cdek-it/widget@3', array(), null, true);
	wp_enqueue_script('yandex-widget', 'https://ndd-widget.landpro.site/widget.js', array(), null, true);

	// -----------------------------------------------------------------
	// УСЛОВНАЯ ЗАГРУЗКА СКРИПТА КОНФИГУРАТОРА
	// -----------------------------------------------------------------

	if (is_page(65)) {


		$product_id = 127;
		$rows = get_chain_combinations_data($product_id);
		$config = build_chain_config($rows);

		$script_data = array(
			'chainConfig' => $config,
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'defaultImageSrc' => get_template_directory_uri() . '/assets/img/chains/pitch.png',
			'addToCartAction' => 'woocommerce_add_to_cart',
			'getProductDataAction' => 'get_product_data',
			'isWpAjax' => true,
		);


		wp_enqueue_script(
			'my-chain-configurator',
			get_template_directory_uri() . '/assets/js/chain-configurator.js',
			array('jquery'),
			'1.0',
			true
		);


		wp_localize_script(
			'my-chain-configurator',
			'ConfigData',
			$script_data
		);
	}


	wp_enqueue_script('app-js', get_template_directory_uri() . '/assets/js/app.min.js', array('jquery'), null, true);

	wp_localize_script('app-js', 'custom_ajax_params', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'cart_nonce' => wp_create_nonce('woocommerce-cart'),
	));
}
add_action('wp_enqueue_scripts', 'theme_enqueue_scripts');

function add_async_attribute($tag, $handle, $src)
{
	$async_handles = array('cdek-widget', 'yandex-widget');

	if (in_array($handle, $async_handles)) {
		return str_replace(' src', ' async src', $tag);
	}
	return $tag;
}
add_filter('script_loader_tag', 'add_async_attribute', 10, 3);


require_once('includes/woocommerce-custom.php');

// =========================================================================
// 3. THEME SUPPORT AND UTILITIES
// =========================================================================

function allow_svg_uploads($mimes)
{
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', 'allow_svg_uploads');



function register_custom_menus()
{
	register_nav_menus(array(
		'primary_menu' => esc_html__('Primary Header Menu', 'dm-chains'),
	));
}
add_action('after_setup_theme', 'register_custom_menus');


function rus_to_lat($string)
{

	$string = mb_strtolower($string, 'UTF-8');

	$converter = array(
		'а' => 'a',
		'б' => 'b',
		'в' => 'v',
		'г' => 'g',
		'д' => 'd',
		'е' => 'e',
		'ё' => 'e',
		'ж' => 'zh',
		'з' => 'z',
		'и' => 'i',
		'й' => 'y',
		'к' => 'k',
		'л' => 'l',
		'м' => 'm',
		'н' => 'n',
		'о' => 'o',
		'п' => 'p',
		'р' => 'r',
		'с' => 's',
		'т' => 't',
		'у' => 'u',
		'ф' => 'f',
		'х' => 'h',
		'ц' => 'ts',
		'ч' => 'ch',
		'ш' => 'sh',
		'щ' => 'sch',
		'ь' => '',
		'ы' => 'y',
		'ъ' => '',
		'э' => 'e',
		'ю' => 'yu',
		'я' => 'ya',
		' ' => '-',
		'—' => '-',
		'_' => '-',
		',' => '',
		'.' => '',
		'/' => '-',
		':' => '',
		';' => '',
		'"' => '',
		"'" => '',
		'(' => '',
		')' => ''
	);


	$transliterated = strtr($string, $converter);
	$transliterated = preg_replace('/[^a-z0-9\-]/', '', $transliterated);
	$transliterated = preg_replace('/\-+/', '-', $transliterated);
	$transliterated = trim($transliterated, '-');

	return $transliterated;
}


function custom_logo_setup()
{
	add_theme_support('custom-logo', array(
		'height'      => 110,
		'width'       => 400,
		'flex-height' => true,
		'flex-width'  => true,
		'header-text' => array('site-title', 'site-description'),
	));
}
add_action('after_setup_theme', 'custom_logo_setup');


if (function_exists('acf_add_options_page')) {
	acf_add_options_page(array(
		'page_title'    => 'Общие поля для всего сайта',
		'menu_title'    => 'Настройки темы',
		'menu_slug'     => 'site-global-settings',
		'capability'    => 'edit_posts',
		'redirect'      => false
	));
}

// =========================================================================
// 4.  FORM SUBMITTING
// =========================================================================

add_action('phpmailer_init', 'configure_smtp_mailer');

function configure_smtp_mailer($phpmailer)
{
	if (!defined('SMTP_HOST') || !defined('SMTP_USERNAME') || !defined('SMTP_PASSWORD')) {
		return;
	}

	$phpmailer->isSMTP();
	$phpmailer->Host       = SMTP_HOST;
	$phpmailer->SMTPAuth   = true;
	$phpmailer->Port       = 465;
	$phpmailer->Username   = SMTP_USERNAME;
	$phpmailer->Password   = SMTP_PASSWORD;
	$phpmailer->SMTPSecure = 'ssl';
	$phpmailer->From       = SMTP_USERNAME;
	$phpmailer->FromName   = get_bloginfo('name');
}

add_action('wp_ajax_send_order_form', 'handle_send_order_form');
add_action('wp_ajax_nopriv_send_order_form', 'handle_send_order_form');

add_filter('wp_mail_from', 'custom_mail_from_email');
add_filter('wp_mail_from_name', 'custom_mail_from_name');

function custom_mail_from_email($original_email)
{
	return 'no-reply@dm-chains.ru';
}

function custom_mail_from_name($original_name)
{
	return 'Новая заявка DM-CHAINS';
}

function handle_send_order_form()
{
	$data = $_POST;

	$delivery_map = [
		'yandex_pickup' => 'Яндекс Доставка — Доставка до ПВЗ',
		'cdek'          => 'СДЭК — Доставка',
		'pickup_spb'    => 'Самовывоз (СПБ и ЛО)',
	];

	$payment_map = [
		'individual_card'  => 'Я — физическое лицо (оплата картой)',
		'business_invoice' => 'Я — юридическое лицо или ИП (оплата по счёту)',
	];

	$contact_map = [
		'email'    => 'Электронная почта',
		'telegram' => 'Telegram',
		'whatsapp' => 'Whatsapp',
	];

	$delivery_type = $delivery_map[$data['delivery_type']] ?? $data['delivery_type'];
	$payment_type  = $payment_map[$data['payment_type']] ?? $data['payment_type'];
	$contact_method = $contact_map[$data['contact_method']] ?? $data['contact_method'];

	$is_business = ($data['payment_type'] === 'business_invoice');

	$first_name = $is_business ? $data['first_name_business'] : $data['first_name_individual'];
	$last_name = $is_business ? $data['last_name_business'] : $data['last_name_individual'];
	$middle_name = $is_business ? $data['middle_name_business'] : $data['middle_name_individual'];
	$phone = $is_business ? $data['phone_business'] : $data['phone_individual'];

	$subject = 'Новый заказ с сайта';
	$headers = ['Content-Type: text/html; charset=UTF-8'];

	ob_start();
?>
	<h3>Информация о заказе</h3>
	<p>
		<strong>Дата и время заявки:</strong> <?php echo esc_html($data['order_datetime']); ?><br>
		<strong>Состав заказа:</strong> <?php echo esc_html($data['product_list']); ?>
	</p>
	<p>
		<strong>Сумма товаров:</strong> <?php echo esc_html($data['price_product']); ?> ₽<br>
		<strong>Стоимость доставки:</strong> <?php echo esc_html($data['price_delivery']); ?> ₽<br>
		<strong>ИТОГО:</strong> <strong><?php echo esc_html($data['total_price']); ?> ₽</strong>
	</p>

	<h3>Доставка и Оплата</h3>
	<p>
		<strong>Тип доставки:</strong> <?php echo esc_html($delivery_type); ?><br>
		<strong>Адрес доставки:</strong> <?php echo esc_html($data['order_delivery_address']); ?><br>
		<strong>Тип оплаты:</strong> <?php echo esc_html($payment_type); ?>
	</p>

	<?php if ($is_business): ?>
		<h3>Детали организации:</h3>
		<p>
			<strong>ИНН:</strong> <?php echo esc_html($data['inn']); ?><br>
			<strong>Название организации:</strong> <?php echo esc_html($data['organization_name']); ?><br>
			<strong>Юридический адрес:</strong> <?php echo esc_html($data['legal_address']); ?>
		</p>
	<?php endif; ?>

	<h3>Контактная информация</h3>
	<p>
		<strong>Имя:</strong> <?php echo esc_html($first_name); ?><br>
		<strong>Фамилия:</strong> <?php echo esc_html($last_name); ?><br>
		<strong>Отчество:</strong> <?php echo esc_html($middle_name); ?><br>
		<strong>Телефон:</strong> <?php echo esc_html($phone); ?> <br>
	</p>

	<p>
		<strong>Предпочтительный способ связи:</strong> <?php echo esc_html($contact_method); ?>
	</p>

	<?php if (!empty($data['email'])): ?>
		<p><strong>E-mail:</strong> <?php echo esc_html($data['email']); ?></p>
	<?php endif; ?>

	<?php if (!empty($data['telegram_user'])): ?>
		<p><strong>Имя пользователя Telegram:</strong> <?php echo esc_html($data['telegram_user']); ?></p>
	<?php endif; ?>

	<?php if (!empty($data['whatsapp_phone'])): ?>
		<p><strong>Номер для WhatsApp:</strong> <?php echo esc_html($data['whatsapp_phone']); ?></p>
	<?php endif; ?>

	<p>
		<strong>Согласие на обработку данных:</strong> <?php echo isset($data['confirm_policies']) ? 'Да' : 'Нет'; ?>
	</p>
<?php
	$message = ob_get_clean();

	$recipients = [];
	if (function_exists('get_field') && get_field('send_email', 'option')) {
		$emails_repeater = get_field('send_email', 'option');
		if (is_array($emails_repeater)) {
			foreach ($emails_repeater as $row) {
				if (!empty($row['email'])) {
					$recipients[] = sanitize_email($row['email']);
				}
			}
		}
	}

	if (empty($recipients)) {
		$to = get_option('admin_email');
	} else {
		$to = $recipients;
	}

	$mail_sent = wp_mail($to, $subject, $message, $headers);

	if ($mail_sent) {
		if (class_exists('WooCommerce') && WC()->cart) {
			WC()->cart->empty_cart();
		}

		// do_action('custom_order_form_sent', $data);

		wp_send_json_success(['message' => 'Заказ успешно отправлен']);
	} else {
		wp_send_json_error(['message' => 'Ошибка отправки письма']);
	}
}
