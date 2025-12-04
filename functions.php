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


function woocommerce_support()
{
	add_theme_support('woocommerce');
}
add_action('after_setup_theme', 'woocommerce_support');


add_filter('woocommerce_register_post_type_shop_order', function ($args) {
	return array_merge($args, ['public' => false, 'show_ui' => false]);
});

add_filter('woocommerce_register_post_type_shop_coupon', function ($args) {
	return array_merge($args, ['public' => false, 'show_ui' => false]);
});
add_filter('woocommerce_register_post_type_shop_order_refund', function ($args) {
	return array_merge($args, ['public' => false, 'show_ui' => false]);
});


add_filter('woocommerce_payment_gateways', '__return_empty_array');



add_filter('woocommerce_get_settings_pages', function ($settings) {
	if (is_array($settings)) {
		foreach ($settings as $key => $page) {
			if (isset($page->id) && $page->id === 'checkout') {
				unset($settings[$key]);
			}
		}
	}
	return $settings;
});


add_action('wp_enqueue_scripts', function () {
	wp_dequeue_script('wc-checkout');
	wp_dequeue_script('wc-credit-card-form');
	wp_dequeue_script('wc-payment-method');
}, 100);



add_action('template_redirect', function () {
	if (is_shop() || is_product() || is_checkout() || is_account_page()) {
		wp_redirect(home_url('/'));
		exit;
	}
});


add_action('init', function () {
	remove_rewrite_tag('%product_cat%');
	remove_rewrite_tag('%product_tag%');
	remove_rewrite_tag('%product%');
	remove_rewrite_tag('%shop%');
	remove_rewrite_tag('%myaccount%');
});


add_action('wp_enqueue_scripts', function () {
	if (!is_cart()) {
		wp_dequeue_style('woocommerce-general');
		wp_dequeue_style('woocommerce-layout');
		wp_dequeue_style('woocommerce-smallscreen');
		wp_dequeue_script('wc-cart-fragments');
		wp_dequeue_script('woocommerce');
		wp_dequeue_script('wc-add-to-cart');
	}
}, 99);


add_action('admin_menu', function () {
	remove_menu_page('edit.php?post_type=shop_order');
	remove_menu_page('edit.php?post_type=shop_coupon');
});


wp_localize_script('your-script-handle', 'configurator_vars', array(
	'ajaxurl' => admin_url('admin-ajax.php')
));

function get_chain_combinations_data($product_id)
{
	if (! class_exists('WC_Product_Variable')) {
		return [];
	}

	$product = wc_get_product($product_id);

	if (! $product || ! $product->is_type('variable')) {
		return [];
	}

	$combinations = [];
	$variations = $product->get_children();

	$pitch_key = 'attribute_pitch';
	$thickness_key = 'attribute_thickness';
	$class_key = 'attribute_class';

	foreach ($variations as $variation_id) {
		$variation = wc_get_product($variation_id);

		if (! $variation) {
			continue;
		}

		$attributes = $variation->get_variation_attributes();


		if (isset($attributes[$pitch_key], $attributes[$thickness_key], $attributes[$class_key])) {

			$pitch = $attributes[$pitch_key];
			$thickness = $attributes[$thickness_key];
			$class = $attributes[$class_key];

			$normalized_pitch = str_replace(['-'], ['.'], $pitch);
			$normalized_thickness = str_replace(['мм', ','], ['', '.'], $thickness);
			$normalized_class = $class;

			$combinations[] = [
				'pitch'        => $normalized_pitch,
				'thickness'    => $normalized_thickness,
				'class'        => $normalized_class,
				'variation_id' => $variation_id,
				'price_html'   => $variation->get_price_html(),
				'image'        => wp_get_attachment_url($variation->get_image_id()) ?: ''
			];
		}
	}

	return $combinations;
}

function build_chain_config(array $rows): array
{


	$normalizePitchValue = function ($val) {

		$val = str_replace(['"', '”', '″', '“', ' '], '', $val);
		return trim(str_replace(',', '.', $val));
	};

	$formatThicknessLabel = function ($val) {

		return str_replace('.', ',', $val) . ' мм';
	};

	$formatPitchLabel = function ($val) {

		$p = trim($val);

		if (strpos($p, '"') === false && strpos($p, '”') === false && strpos($p, '″') === false && strpos($p, '“') === false) {
			$p .= '”';
		}

		if (strpos($p, '/') === false) {
			$p = str_replace('.', ',', $p);
		}
		return $p;
	};

	$getImagePath = function ($type, $val) {
		$slug = str_replace(['/', '.', ','], ['-', '_', '_'], $val);
		return "/assets/img/chains/{$type}-{$slug}.png";
	};

	$calculateQuantityRange = function ($pitchVal) {

		$pitch = 0.0;

		if (strpos($pitchVal, '/') !== false) {
			$parts = explode('/', $pitchVal);
			if (count($parts) === 2 && (float)$parts[1] !== 0.0) {
				$pitch = (float)$parts[0] / (float)$parts[1];
			}
		} else {
			$pitch = (float)$pitchVal;
		}

		if ($pitch <= 0.0) return [0, 0];

		$table = [
			'0.25'  => [10 => [56, 60], 12 => [64, 66], 14 => [68, 72], 16 => [72, 74], 18 => [74, 76], 20 => [80, 84]],
			'0.325' => [10 => [60, 64], 12 => [64, 68], 14 => [72, 76], 16 => [66, 68], 18 => [72, 74], 20 => [76, 81]],
			'0.375' => [10 => [54, 56], 12 => [56, 60], 14 => [66, 68], 16 => [64, 66], 18 => [68, 72], 20 => [72, 76]],
			'0.404' => [10 => [50, 52], 12 => [54, 56], 14 => [60, 62], 16 => [60, 62], 18 => [64, 66], 20 => [70, 74]],
		];

		$closest = null;
		foreach ($table as $p => $ranges) {
			if (abs((float)$p - $pitch) < 0.01) {
				$closest = $ranges;
				break;
			}
		}

		if (!$closest) {
			$lengthInches = 16;
			$DL = ((($lengthInches * 2.54) * 2) / ($pitch * 25.4));
			$qMin = (int)round($DL - 2);
			$qMax = (int)round($DL + 2);
			return [$qMin, $qMax];
		}

		if (isset($closest[16])) return $closest[16];
		return [0, 0];
	};

	$pitches = [];
	$thicknesses = [];
	$classes = [];
	$finalCombinations = [];

	foreach ($rows as $row) {

		$pVal = $row['pitch'] ?? '';
		$tVal = $row['thickness'] ?? '';
		$cVal = $row['class'] ?? '';

		$pitchForLabel = $pVal;

		if (empty($pVal) || empty($tVal) || empty($cVal)) continue;

		if (!isset($pitches[$pVal])) {
			$pitches[$pVal] = [
				'value' => $pVal,
				'label' => $formatPitchLabel($pitchForLabel),
				'image' => $getImagePath('pitch', $pVal)
			];
		}

		if (!isset($thicknesses[$tVal])) {
			$thicknesses[$tVal] = [
				'value' => $tVal,
				'label' => $formatThicknessLabel($tVal),
				'image' => $getImagePath('thickness', $tVal),
				'available_pitches' => []
			];
		}
		$thicknesses[$tVal]['available_pitches'][$pVal] = true;

		if (!isset($classes[$cVal])) {
			$classes[$cVal] = [
				'value' => $cVal,
				'label' => $cVal,
				'image' => $getImagePath('class', $cVal),
				'matrix' => []
			];
		}

		if (!isset($classes[$cVal]['matrix'][$pVal])) {
			$classes[$cVal]['matrix'][$pVal] = [];
		}
		if (!in_array($tVal, $classes[$cVal]['matrix'][$pVal], true)) {
			$classes[$cVal]['matrix'][$pVal][] = $tVal;
		}

		$qtyRange = $calculateQuantityRange($pVal);

		$finalCombinations[] = [
			'pitch'         => $pVal,
			'thickness'     => $tVal,
			'class'         => $cVal,
			'variation_id'  => $row['variation_id'] ?? null,
			'price_html'    => $row['price_html'] ?? '',
			'image'         => $row['image'] ?? '',
			'countLinksMin' => $qtyRange[0],
			'countLinksMax' => $qtyRange[1]
		];
	}

	$finalPitches = array_values($pitches);
	usort($finalPitches, fn($a, $b) => strnatcmp($a['value'], $b['value']));

	$finalThicknesses = [];
	foreach ($thicknesses as $t) {
		$avail = [];
		$pitchKeys = array_keys($t['available_pitches']);

		usort($pitchKeys, fn($a, $b) => strnatcmp($a, $b));

		foreach ($pitchKeys as $p) {
			$avail[] = ['pitch' => $p];
		}

		$finalThicknesses[] = [
			'value' => $t['value'],
			'label' => $t['label'],
			'image' => $t['image'],
			'availableFor' => $avail
		];
	}

	usort($finalThicknesses, fn($a, $b) => (float)$a['value'] <=> (float)$b['value']);


	$finalClasses = [];
	$classOrder = ['A' => 1, 'B' => 2, 'C' => 3];
	foreach ($classes as $c) {
		$avail = [];
		$pitchKeys = array_keys($c['matrix']);

		usort($pitchKeys, fn($a, $b) => strnatcmp($a, $b));

		foreach ($pitchKeys as $p) {
			$tList = $c['matrix'][$p];
			usort($tList, fn($a, $b) => (float)$a <=> (float)$b);
			$avail[] = [
				'pitch' => $p,
				'thickness' => $tList
			];
		}

		$finalClasses[] = [
			'value' => $c['value'],
			'label' => $c['label'],
			'image' => $c['image'],
			'availableFor' => $avail
		];
	}

	usort($finalClasses, fn($a, $b) => ($classOrder[$a['value']] ?? 999) <=> ($classOrder[$b['value']] ?? 999));

	return [
		'steps' => [
			'pitch' => [
				'title' => 'pitch',
				'options' => $finalPitches
			],
			'thickness' => [
				'title' => 'thickness',
				'options' => $finalThicknesses
			],
			'class' => [
				'title' => 'class',
				'options' => $finalClasses
			]
		],
		'combinations' => $finalCombinations
	];
}

// =========================================================================
// 4. AJAX HANDLERS
// =========================================================================


add_action('wp_ajax_get_product_data', 'handle_get_product_data');
add_action('wp_ajax_nopriv_get_product_data', 'handle_get_product_data');

function handle_get_product_data()
{
	if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
		wp_send_json_error('Недостаточно данных для обработки.');
	}

	$product_id = intval($_POST['product_id']);

	if (!function_exists('wc_get_product')) {
		wp_send_json_error('WooCommerce не активен.');
	}

	$product = wc_get_product($product_id);

	if (!$product || !$product->is_in_stock()) {
		wp_send_json_error('Продукт не найден или недоступен.');
	}
	$price_html = $product->get_price_html();

	$price_per_item = wc_get_price_to_display($product);

	$response = array(
		'price_html'       => $price_html,
		'product_id'       => $product_id,
		'price_per_item' => $price_per_item
	);

	wp_send_json_success($response);


	wp_die();
}



function get_cart_total_custom_ajax()
{
	if (! class_exists('WooCommerce')) {
		wp_send_json_error(['message' => 'WooCommerce не активен.']);
	}

	if (WC()->cart->is_empty()) {
		wp_send_json_success([
			'total' => '0 ₽',
			'cart_is_empty' => true
		]);
	}

	wp_send_json_success([
		'total' => WC()->cart->get_cart_total()
	]);
}
add_action('wp_ajax_get_cart_total_custom', 'get_cart_total_custom_ajax');
add_action('wp_ajax_nopriv_get_cart_total_custom', 'get_cart_total_custom_ajax');



add_filter('woocommerce_add_cart_item_data', 'save_chain_links_count_to_cart_item_data', 10, 2);
function save_chain_links_count_to_cart_item_data($cart_item_data, $product_id)
{

	if (isset($_POST['links_count']) && is_numeric($_POST['links_count'])) {
		$links_count = intval($_POST['links_count']);

		$cart_item_data['links_count'] = $links_count;

		$cart_item_data['unique_key'] = md5(microtime() . rand());
	}
	return $cart_item_data;
}

add_filter('woocommerce_get_item_data', 'display_chain_links_count_in_cart', 10, 2);
function display_chain_links_count_in_cart($item_data, $cart_item)
{

	if (isset($cart_item['links_count'])) {
		$item_data[] = array(
			'key'     => 'Кол-во зв.',
			'value'   => $cart_item['links_count'],
			'display' => '',
		);
	}
	return $item_data;
}

add_action('woocommerce_checkout_create_order_line_item', 'add_chain_links_count_to_order_items', 10, 3);
function add_chain_links_count_to_order_items($item, $cart_item_key, $values)
{

	if (isset($values['links_count'])) {
		$item->add_meta_data('Кол-во зв.', $values['links_count']);
	}
}

function remove_from_cart_ajax()
{
	if (! class_exists('WooCommerce') || empty($_POST['cart_item_key'])) {
		wp_send_json_error(['message' => 'Некорректный запрос.']);
	}

	check_ajax_referer('woocommerce-cart', 'security');

	$cart_item_key = sanitize_text_field($_POST['cart_item_key']);
	$removed = WC()->cart->remove_cart_item($cart_item_key);

	if ($removed) {
		WC()->cart->calculate_totals();

		wp_send_json_success([
			'fragments' => [],
			'total' => WC()->cart->get_cart_total()
		]);
	} else {
		wp_send_json_error(['message' => 'Не удалось удалить товар.']);
	}
}
add_action('wp_ajax_remove_from_cart', 'remove_from_cart_ajax');
add_action('wp_ajax_nopriv_remove_from_cart', 'remove_from_cart_ajax');



function update_cart_item_quantity_ajax()
{
	if (! class_exists('WooCommerce') || empty($_POST['hash']) || ! isset($_POST['quantity'])) {
		wp_send_json_error(['message' => 'Некорректный запрос.']);
	}

	check_ajax_referer('woocommerce-cart', 'security');

	$cart_item_key = sanitize_text_field($_POST['hash']);
	$new_quantity  = intval($_POST['quantity']);

	$cart = WC()->cart;
	$updated = $cart->set_quantity($cart_item_key, $new_quantity, true);

	if ($updated) {
		$cart->calculate_totals();
		$cart_item = $cart->get_cart_item($cart_item_key);
		$_product  = $cart_item['data'];

		wp_send_json_success([
			'new_price'    => wc_price($_product->get_price()),
			'new_subtotal' => wc_price($cart_item['line_total']),
			'total'        => $cart->get_cart_total(),
			'raw_total'    => $cart->get_total()
		]);
	} else {
		wp_send_json_error(['message' => 'Не удалось обновить количество.']);
	}
}
add_action('wp_ajax_woocommerce_update_cart_item_quantity', 'update_cart_item_quantity_ajax');
add_action('wp_ajax_nopriv_woocommerce_update_cart_item_quantity', 'update_cart_item_quantity_ajax');



function custom_clear_woocommerce_cart()
{

	if (defined('DOING_AJAX') && DOING_AJAX) {
		WC()->cart->empty_cart();
		wp_send_json_success();
	}

	wp_die();
}


add_action('wp_ajax_clear_cart_after_order', 'custom_clear_woocommerce_cart');
add_action('wp_ajax_nopriv_clear_cart_after_order', 'custom_clear_woocommerce_cart');


// =========================================================================
// 5. CONTACT FORM 7 INTEGRATION
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
