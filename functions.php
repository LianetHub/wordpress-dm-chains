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
// 4. CONTACT FORM 7 INTEGRATION
// =========================================================================


add_filter('wpcf7_autop_or_not', '__return_false');

add_filter('wpcf7_form_tag', function ($tag) {

	if ($tag['name'] === 'price_product') {

		if (function_exists('WC') && WC()->cart) {
			$total = WC()->cart->get_total('edit');
		} else {
			$total = 0;
		}

		$tag['values'] = [$total];
	}

	return $tag;
});

function register_order_popup_script()
{
	$woocommerce_total = (WC()->cart) ? WC()->cart->get_subtotal() : 0;

	$attribute_map = [
		'pitch'     => 'Шаг',
		'thickness' => 'Толщина',
		'class'     => 'Класс',
	];
	$quantity_label = 'Кол-во звeньев';

	$cart_items_list = '';

	if (WC()->cart) {
		$cart_items = WC()->cart->get_cart();
		$items_strings = [];

		foreach ($cart_items as $cart_item_key => $cart_item) {
			$_product = $cart_item['data'];

			$product_name = $_product->get_name();
			$quantity = $cart_item['quantity'];

			$attr_parts = [];

			if ($_product->is_type('variation')) {
				$attributes = $cart_item['variation'];
				foreach ($attributes as $key => $value) {
					$taxonomy = str_replace('attribute_', '', $key);
					$attr_slug = str_replace('pa_', '', $taxonomy);
					$attr_label = $attribute_map[$attr_slug] ?? wc_attribute_label($taxonomy, $_product);

					$attr_parts[] = "{$attr_label}: " . ucwords(str_replace('-', ' ', $value));
				}
			}

			$attr_parts[] = "{$quantity_label}: {$quantity}";
			$attr_string = implode(', ', $attr_parts);

			$items_strings[] = "{$product_name} ({$attr_string})";
		}

		$cart_items_list = implode(' ; ', $items_strings);
	}

	$theme_uri = get_template_directory_uri();

	wp_register_script('order-popup-script', "{$theme_uri}/assets/js/order.js", ['jquery'], '1.0', true);

	$script_data = [
		'cart_items_list' => $cart_items_list,
		'woocommerce_total' => floatval($woocommerce_total),
		'ajax_url' => admin_url('admin-ajax.php'),
		'cdek_api_key' => defined('CDEK_YANDEX_MAPS_API_KEY') ? CDEK_YANDEX_MAPS_API_KEY : '',
		'yandex_platform_id' => defined('YANDEX_DELIVERY_PLATFORM_STATION_ID') ? YANDEX_DELIVERY_PLATFORM_STATION_ID : '',
		'cdek_service_path' => "{$theme_uri}/services/cdek-service.php",
	];

	wp_localize_script('order-popup-script', 'OrderPopupData', $script_data);

	wp_enqueue_script('order-popup-script');
}
add_action('wp_enqueue_scripts', 'register_order_popup_script');
