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

class CdekApiService
{
	private $clientId;
	private $clientSecret;
	private $apiUrl = 'https://api.cdek.ru/v2';
	private $token;

	public function __construct($clientId, $clientSecret)
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
	}

	private function getAccessToken()
	{
		if ($this->token) return $this->token;

		$url = $this->apiUrl . '/oauth/token?parameters';
		$data = [
			'grant_type' => 'client_credentials',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		$result = json_decode($response, true);

		if (is_resource($ch) || $ch instanceof \CurlHandle) {
			curl_close($ch);
		}

		if (isset($result['access_token'])) {
			$this->token = $result['access_token'];
			return $this->token;
		}

		return null;
	}

	public function createOrder($orderData)
	{
		$token = $this->getAccessToken();

		if (!$token) {
			return ['requests' => [['errors' => [['message' => 'Auth failed: Could not get token']]]]];
		}

		$url = $this->apiUrl . '/orders';
		$jsonData = json_encode($orderData);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $token,
			'Content-Type: application/json',
			'Accept: application/json'
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);

		if (is_resource($ch) || $ch instanceof \CurlHandle) {
			curl_close($ch);
		}

		return json_decode($response, true);
	}

	public function deleteOrder($uuid)
	{
		$token = $this->getAccessToken();

		if (!$token) {
			return ['error' => 'Auth failed: Could not get token'];
		}

		$url = $this->apiUrl . '/orders/' . $uuid;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $token,
			'Accept: application/json'
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);

		if (is_resource($ch) || $ch instanceof \CurlHandle) {
			curl_close($ch);
		}

		return json_decode($response, true);
	}
}

class CdekOrderFormatter
{
	private $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function getCartItems()
	{
		$cart_items = [];
		if (!empty($this->data['cart_items'])) {
			$cart_items = json_decode(stripslashes($this->data['cart_items']), true);
		}
		return is_array($cart_items) ? $cart_items : [];
	}

	private function getRecipientName(bool $isBusiness)
	{
		$firstName = $isBusiness ? $this->data['first_name_business'] : $this->data['first_name_individual'];
		$lastName = $isBusiness ? $this->data['last_name_business'] : $this->data['last_name_individual'];
		return trim($firstName . ' ' . $lastName);
	}

	private function getRecipientPhone(bool $isBusiness)
	{
		return $isBusiness ? $this->data['phone_business'] : $this->data['phone_individual'];
	}

	public function getOrderData()
	{
		$cartItems = $this->getCartItems();
		$isBusiness = ($this->data['payment_type'] === 'business_invoice');

		$totalWeight = 0;
		$cdekItems = [];
		$firstItemName = 'Заказ с сайта';

		if (!empty($cartItems)) {
			$firstItemName = 'Заказ: ' . ($cartItems[0]['name'] ?? 'Товары');

			foreach ($cartItems as $index => $item) {

				$itemWeight = (int)($item['weight'] ?? 1000);
				$itemQuantity = (int)($item['quantity'] ?? 1);
				$itemCostPerUnit = (float)($item['cost_per_unit'] ?? 0);
				$itemName = $item['name'] ?? 'Товар без названия';

				$finalItemCost = round($itemCostPerUnit * $itemQuantity, 2);

				$totalWeight += $itemWeight * $itemQuantity;

				$cdekItems[] = [
					'name' => mb_substr($itemName, 0, 150),
					'ware_key' => $item['sku'] ?? ('goods-' . ($index + 1)),
					'payment' => [
						'value' => 0
					],
					'cost' => $finalItemCost,
					'weight' => $itemWeight,
					'amount' => $itemQuantity,
				];
			}
		}

		$packageLength = (int)($cartItems[0]['length'] ?? 20);
		$packageWidth = (int)($cartItems[0]['width'] ?? 15);
		$packageHeight = (int)($cartItems[0]['height'] ?? 10);

		$finalTotalWeight = max(100, $totalWeight);

		$orderData = [
			'type' => 1,
			'number' => uniqid('ORDER_'),
			'tariff_code' => (int)($this->data['cdek_tariff_code'] ?? 0),
			'comment' => 'Заказ с сайта. Состав: ' . mb_substr($this->data['product_list'], 0, 200),
			'shipment_point' => 'SPB12',
			'recipient' => [
				'name' => $this->getRecipientName($isBusiness),
				'phones' => [
					['number' => $this->getRecipientPhone($isBusiness)]
				]
			],
			'packages' => [
				[
					'number' => '1',
					'weight' => $finalTotalWeight,
					'length' => $packageLength,
					'width' => $packageWidth,
					'height' => $packageHeight,
					'comment' => $firstItemName,
					'items' => $cdekItems
				]
			]
		];

		$hasPvzCode = !empty($this->data['cdek_pvz_code']);
		$hasDeliveryAddress = !empty($this->data['order_delivery_address']);

		if ($hasPvzCode) {
			$orderData['delivery_point'] = $this->data['cdek_pvz_code'];
		} elseif ($hasDeliveryAddress) {
			$orderData['to_location'] = ['address' => $this->data['order_delivery_address']];
		}

		return $orderData;
	}
}

class MailService
{
	private $data;
	private $cdekInfo;

	public function __construct(array $data, string $cdekInfo = '')
	{
		$this->data = $data;
		$this->cdekInfo = $cdekInfo;
	}

	private function getMapValue(array $map, string $key)
	{
		return $map[$this->data[$key]] ?? $this->data[$key];
	}

	private function getRecipients()
	{
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
		return empty($recipients) ? get_option('admin_email') : $recipients;
	}

	public function sendOrderEmail()
	{
		$deliveryMap = [
			'yandex_pickup' => 'Яндекс Доставка — Доставка до ПВЗ',
			'cdek'          => 'СДЭК — Доставка',
			'pickup_spb'    => 'Самовывоз (СПБ и ЛО)',
		];

		$paymentMap = [
			'individual_card'  => 'Я — физическое лицо (оплата картой)',
			'business_invoice' => 'Я — юридическое лицо или ИП (оплата по счёту)',
		];

		$contactMap = [
			'email'    => 'Электронная почта',
			'telegram' => 'Telegram',
			'whatsapp' => 'Whatsapp',
		];

		$deliveryType = $this->getMapValue($deliveryMap, 'delivery_type');
		$paymentType  = $this->getMapValue($paymentMap, 'payment_type');
		$contactMethod = $this->getMapValue($contactMap, 'contact_method');

		$isBusiness = ($this->data['payment_type'] === 'business_invoice');
		$firstName = $isBusiness ? $this->data['first_name_business'] : $this->data['first_name_individual'];
		$lastName = $isBusiness ? $this->data['last_name_business'] : $this->data['last_name_individual'];
		$middleName = $isBusiness ? $this->data['middle_name_business'] : $this->data['middle_name_individual'];
		$phone = $isBusiness ? $this->data['phone_business'] : $this->data['phone_individual'];

		$subject = 'Новый заказ с сайта';
		$headers = ['Content-Type: text/html; charset=UTF-8'];
		$to = $this->getRecipients();

		ob_start();
?>
		<h3>Информация о заказе</h3>
		<p>
			<strong>Дата и время заявки:</strong> <?php echo esc_html($this->data['order_datetime']); ?><br>
			<strong>Состав заказа:</strong> <?php echo esc_html($this->data['product_list']); ?>
		</p>
		<p>
			<strong>Сумма товаров:</strong> <?php echo esc_html($this->data['price_product']); ?> ₽<br>
			<strong>Стоимость доставки:</strong> <?php echo esc_html($this->data['price_delivery']); ?> ₽<br>
			<strong>ИТОГО:</strong> <strong><?php echo esc_html($this->data['total_price']); ?> ₽</strong>
		</p>

		<h3>Доставка и Оплата</h3>
		<p>
			<strong>Тип доставки:</strong> <?php echo esc_html($deliveryType); ?><br>
			<strong>Адрес доставки:</strong> <?php echo esc_html($this->data['order_delivery_address']); ?><br>
			<strong>Тип оплаты:</strong> <?php echo esc_html($paymentType); ?>
		</p>
		<? if ($this->cdekInfo !== ''): ?>
			<?php echo $this->cdekInfo; ?>
		<?php endif; ?>
		<?php if ($isBusiness): ?>
			<h3>Детали организации:</h3>
			<p>
				<strong>ИНН:</strong> <?php echo esc_html($this->data['inn']); ?><br>
				<strong>Название организации:</strong> <?php echo esc_html($this->data['organization_name']); ?><br>
				<strong>Юридический адрес:</strong> <?php echo esc_html($this->data['legal_address']); ?>
			</p>
		<?php endif; ?>

		<h3>Контактная информация</h3>
		<p>
			<strong>Имя:</strong> <?php echo esc_html($firstName); ?><br>
			<strong>Фамилия:</strong> <?php echo esc_html($lastName); ?><br>
			<strong>Отчество:</strong> <?php echo esc_html($middleName); ?><br>
			<strong>Телефон:</strong> <?php echo esc_html($phone); ?> <br>
		</p>

		<p>
			<strong>Предпочтительный способ связи:</strong> <?php echo esc_html($contactMethod); ?>
		</p>

		<?php if (!empty($this->data['email'])): ?>
			<p><strong>E-mail:</strong> <?php echo esc_html($this->data['email']); ?></p>
		<?php endif; ?>

		<?php if (!empty($this->data['telegram_user'])): ?>
			<p><strong>Имя пользователя Telegram:</strong> <?php echo esc_html($this->data['telegram_user']); ?></p>
		<?php endif; ?>

		<?php if (!empty($this->data['whatsapp_phone'])): ?>
			<p><strong>Номер для WhatsApp:</strong> <?php echo esc_html($this->data['whatsapp_phone']); ?></p>
		<?php endif; ?>

		<p>
			<strong>Согласие на обработку данных:</strong> <?php echo isset($this->data['confirm_policies']) ? 'Да' : 'Нет'; ?>
		</p>
<?php
		$message = ob_get_clean();

		return wp_mail($to, $subject, $message, $headers);
	}
}

class OrderProcessor
{
	private $data;
	private $cdekService;

	public function __construct(array $data)
	{
		$this->data = $data;
		$clientId = defined('CDEK_ID') ? CDEK_ID : '';
		$clientSecret =  defined('CDEK_PASSWORD') ? CDEK_PASSWORD : '';
		$this->cdekService = new CdekApiService($clientId, $clientSecret);
	}

	public function processOrder()
	{
		$cdekInfo = '';
		$mailSent = false;

		$deliveryType = $this->data['delivery_type'] ?? '';
		$cdekTariffCode = $this->data['cdek_tariff_code'] ?? '';

		if ($deliveryType === 'cdek' && !empty($cdekTariffCode)) {

			$formatter = new CdekOrderFormatter($this->data);

			if (empty($formatter->getCartItems())) {
				$cdekInfo = '<p style="color: red;"><strong>Ошибка СДЭК: Не удалось получить/декодировать товары из корзины. Массив [packages[0].items] пуст.</strong></p>';
			} else {
				$cdekOrderData = $formatter->getOrderData();

				if (!isset($cdekOrderData['delivery_point']) && !isset($cdekOrderData['to_location'])) {
					$cdekInfo = '<p style="color: red;"><strong>Ошибка СДЭК: Не указан пункт назначения.</strong></p>';
				} else {
					error_log('CDEK REQUEST DATA: ' . print_r($cdekOrderData, true));
					$cdekResult = $this->cdekService->createOrder($cdekOrderData);
					error_log('CDEK RESPONSE: ' . print_r($cdekResult, true));

					if (isset($cdekResult['entity']['uuid'])) {
						$cdekInfo = '<p style="color: green;"><strong>Заказ СДЭК создан! UUID: ' . $cdekResult['entity']['uuid'] . '</strong></p>';
					} else {
						$error_details = json_encode($cdekResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

						$error_msg = 'Неизвестная ошибка';
						if (isset($cdekResult['requests'][0]['errors'][0]['message'])) {
							$error_msg = $cdekResult['requests'][0]['errors'][0]['message'];
							if (isset($cdekResult['requests'][0]['errors'][0]['code'])) {
								$error_msg .= ' (' . $cdekResult['requests'][0]['errors'][0]['code'] . ')';
							}
						} elseif (isset($cdekResult['error'])) {
							$error_msg = $cdekResult['error'];
						}

						$cdekInfo = '<p style="color: red;"><strong>Ошибка СДЭК: ' . $error_msg . '</strong></p>';
						$cdekInfo .= '<h4>Полный ответ API СДЭК для отладки:</h4>';
						$cdekInfo .= '<pre>' . esc_html($error_details) . '</pre>';
					}
				}
			}
		}

		$mailService = new MailService($this->data, $cdekInfo);
		$mailSent = $mailService->sendOrderEmail();

		if ($mailSent) {
			if (class_exists('WooCommerce') && WC()->cart) {
				WC()->cart->empty_cart();
			}
			return ['status' => 'success', 'message' => 'Заказ успешно отправлен'];
		} else {
			return ['status' => 'error', 'message' => 'Ошибка отправки письма'];
		}
	}
}

add_action('wp_ajax_send_order_form', 'handle_send_order_form');
add_action('wp_ajax_nopriv_send_order_form', 'handle_send_order_form');

function handle_send_order_form()
{
	$processor = new OrderProcessor($_POST);
	$result = $processor->processOrder();

	if ($result['status'] === 'success') {
		wp_send_json_success(['message' => $result['message']]);
	} else {
		wp_send_json_error(['message' => $result['message']]);
	}
}
