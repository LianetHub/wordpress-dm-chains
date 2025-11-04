<?php

// =========================================================================
// 1. CONSTANTS
// =========================================================================

define('TEMPLATE_PATH', dirname(__FILE__) . '/templates/');


// =========================================================================
// 2. ENQUEUE STYLES AND SCRIPTS
// =========================================================================

// Enqueue theme styles (CSS)
function theme_enqueue_styles()
{
	wp_enqueue_style('swiper', get_template_directory_uri() . '/assets/css/libs/swiper-bundle.min.css');
	wp_enqueue_style('fancybox', get_template_directory_uri() . '/assets/css/libs/fancybox.css');
	wp_enqueue_style('input-tel', get_template_directory_uri() . '/assets/css/libs/input-tel.css');
	wp_enqueue_style('reset', get_template_directory_uri() . '/assets/css/reset.min.css');
	wp_enqueue_style('main-style', get_template_directory_uri() . '/assets/css/style.min.css');
}
add_action('wp_enqueue_scripts', 'theme_enqueue_styles');


// Enqueue theme scripts (JS)
function theme_enqueue_scripts()
{
	wp_deregister_script('jquery');
	wp_enqueue_script('jquery', get_template_directory_uri() . '/assets/js/libs/jquery-3.7.1.min.js', array(), null, true);
	wp_enqueue_script('swiper-js', get_template_directory_uri() . '/assets/js/libs/swiper-bundle.min.js', array(), null, true);
	wp_enqueue_script('fancybox-js', get_template_directory_uri() . '/assets/js/libs/fancybox.umd.js', array(), null, true);
	wp_enqueue_script('intlTelInput-js', get_template_directory_uri() . '/assets/js/libs/intlTelInput.min.js', array(), null, true);
	wp_enqueue_script('app-js', get_template_directory_uri() . '/assets/js/app.min.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'theme_enqueue_scripts');


// =========================================================================
// 3. THEME SUPPORT AND UTILITIES
// =========================================================================

// Allow SVG file uploads
function allow_svg_uploads($mimes)
{
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', 'allow_svg_uploads');


// Register navigation menus
function register_custom_menus()
{
	register_nav_menus(array(
		'primary_menu' => esc_html__('Primary Header Menu', 'dm-chains'),
	));
}
add_action('after_setup_theme', 'register_custom_menus');


// Enable Custom Logo feature
function  custom_logo_setup()
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


// ACF Page Settins
if (function_exists('acf_add_options_page')) {
	acf_add_options_page(array(
		'page_title'    => 'Общие поля для всего сайта',
		'menu_title'    => 'Настройки темы',
		'menu_slug'     => 'site-global-settings',
		'capability'    => 'edit_posts',
		'redirect'      => false
	));
}



// Declare WooCommerce theme support
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
	$normalizePitchValue = static function (?string $pitch): string {
		if ($pitch === null) return '';
		$p = trim(str_replace(['"', '”', '″', '“', ' '], '', $pitch));
		$p = str_replace(',', '.', $p);
		return $p;
	};

	$normalizeThicknessValue = static function (?string $th): string {
		if ($th === null) return '';
		$t = trim($th);
		$t = str_ireplace('мм', '', $t);
		$t = str_replace([' ', ','], ['', '.'], $t);
		return $t;
	};

	$pitchLabel = static function (string $rawPitch): string {

		return trim($rawPitch);
	};

	$thicknessLabel = static function (string $val): string {
		$withComma = str_replace('.', ',', $val);
		return $withComma . ' мм';
	};

	$pitchImagePath = static function (string $pitchValue): string {
		$slug = str_replace('/', '-', $pitchValue);
		return "/assets/img/chains/pitch-{$slug}.png";
	};

	$thicknessImagePath = static function (string $thVal): string {
		return "/assets/img/chains/thickness-{$thVal}.png";
	};

	$classImagePath = static function (string $classVal): string {
		return "/assets/img/chains/class-{$classVal}.png";
	};


	$pitchOnly = [];
	$thicknessSeen = [];
	$classSeen = [];
	$classAvailMatrix = [];
	$combinations = [];

	$thicknessAvailMap = [];

	foreach ($rows as $row) {
		$rawPitch = (string)($row['pitch'] ?? '');
		$rawThickness = (string)($row['thickness'] ?? '');
		$rawClass = (string)($row['class'] ?? '');

		$pitchVal = $normalizePitchValue($rawPitch);
		$thVal = $normalizeThicknessValue($rawThickness);
		$classVal = trim($rawClass);

		$hasPitch = $pitchVal !== '';
		$hasThickness = $thVal !== '';
		$hasClass = $classVal !== '';

		if ($hasPitch && !$hasThickness && !$hasClass) {
			if (!isset($pitchOnly[$pitchVal])) {
				$pitchOnly[$pitchVal] = [
					'value' => $pitchVal,
					'label' => $pitchLabel($rawPitch),
					'image' => $pitchImagePath($pitchVal),
				];
			}
		}

		if ($hasPitch && $hasThickness && !$hasClass) {
			if (!isset($thicknessAvailMap[$thVal])) {
				$thicknessAvailMap[$thVal] = [];
			}
			$thicknessAvailMap[$thVal][$pitchVal] = true;
		}

		if ($hasPitch && $hasThickness && $hasClass) {

			if (!isset($classAvailMatrix[$classVal])) {
				$classAvailMatrix[$classVal] = [];
			}
			if (!isset($classAvailMatrix[$classVal][$pitchVal])) {
				$classAvailMatrix[$classVal][$pitchVal] = [];
			}
			$classAvailMatrix[$classVal][$pitchVal][$thVal] = true;

			$combinations[] = [
				'pitch'        => $pitchVal,
				'thickness'    => $thVal,
				'class'        => $classVal,
				'variation_id' => $row['variation_id'] ?? null,
				'price_html'   => $row['price_html'] ?? '',
				'image'        => $row['image'] ?? '',
			];
		}
	}

	foreach ($thicknessAvailMap as $thVal => $pitchesSet) {

		if (!isset($thicknessSeen[$thVal])) {
			$thicknessSeen[$thVal] = [
				'value' => $thVal,
				'label' => $thicknessLabel($thVal),
				'image' => $thicknessImagePath($thVal),
				'availableFor' => [],
			];
		}

		foreach (array_keys($pitchesSet) as $pVal) {
			$thicknessSeen[$thVal]['availableFor'][] = ['pitch' => $pVal];
		}

		usort($thicknessSeen[$thVal]['availableFor'], static function ($a, $b) {
			return strcmp($a['pitch'], $b['pitch']);
		});
	}


	foreach ($classAvailMatrix as $cls => $pitchMap) {

		if (!isset($classSeen[$cls])) {
			$classSeen[$cls] = [
				'value' => $cls,
				'label' => $cls,
				'image' => $classImagePath($cls),
				'availableFor' => [],
			];
		}

		$entries = [];
		foreach ($pitchMap as $pVal => $thSet) {
			$ths = array_keys($thSet);
			sort($ths, SORT_NATURAL);
			$entries[] = [
				'pitch' => $pVal,
				'thickness' => $ths,
			];
		}

		usort($entries, static function ($a, $b) {
			return strcmp($a['pitch'], $b['pitch']);
		});

		$classSeen[$cls]['availableFor'] = $entries;
	}
	$sortByNumericLike = static function ($a, $b, $key) {
		return strcmp($a[$key], $b[$key]);
	};


	$pitchOptions = array_values($pitchOnly);
	usort($pitchOptions, static function ($a, $b) {
		return strcmp($a['value'], $b['value']);
	});


	$thicknessOptions = array_values($thicknessSeen);
	usort($thicknessOptions, static function ($a, $b) {

		$da = (float)$a['value'];
		$db = (float)$b['value'];
		return $da <=> $db;
	});


	$classOrder = ['A' => 1, 'B' => 2, 'C' => 3];
	$classOptions = array_values($classSeen);
	usort($classOptions, static function ($a, $b) use ($classOrder) {
		$wa = $classOrder[$a['value']] ?? 999;
		$wb = $classOrder[$b['value']] ?? 999;
		if ($wa === $wb) return strcmp($a['value'], $b['value']);
		return $wa <=> $wb;
	});


	return [
		'steps' => [
			'pitch' => [
				'title' => 'pitch',
				'options' => $pitchOptions,
			],
			'thickness' => [
				'title' => 'thickness',
				'options' => $thicknessOptions,
			],
			'class' => [
				'title' => 'class',
				'options' => $classOptions,
			],
		],
		'combinations' => $combinations,
	];
}

add_action('wp_ajax_get_product_data', 'handle_get_product_data');

add_action('wp_ajax_nopriv_get_product_data', 'handle_get_product_data');

function handle_get_product_data()
{
	if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
		wp_send_json_error('Недостаточно данных для обработки.');
	}

	$product_id = intval($_POST['product_id']);
	$quantity = intval($_POST['quantity']);

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
		'price_html'     => $price_html,
		'product_id'     => $product_id,
		'price_per_item' => $price_per_item
	);

	wp_send_json_success($response);


	wp_die();
}


// СF7 Settings

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
