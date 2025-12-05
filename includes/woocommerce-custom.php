<?php

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

            $min_links = $variation->get_meta('_count_links_min', true);
            $min_links = is_numeric($min_links) ? (int)$min_links : 1;

            $combinations[] = [
                'pitch'          => $normalized_pitch,
                'thickness'      => $normalized_thickness,
                'class'          => $normalized_class,
                'variation_id'   => $variation_id,
                'price_html'     => $variation->get_price_html(),
                'image'          => wp_get_attachment_url($variation->get_image_id()) ?: '',
                'countLinksMin'  => $min_links
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

        $finalCombinations[] = [
            'pitch'          => $pVal,
            'thickness'      => $tVal,
            'class'          => $cVal,
            'variation_id'   => $row['variation_id'] ?? null,
            'price_html'     => $row['price_html'] ?? '',
            'image'          => $row['image'] ?? '',
            'countLinksMin'  => $row['countLinksMin'] ?? 1
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

function add_variation_count_links_min_field($loop, $variation_data, $variation)
{
    echo '<p class="form-row form-row-full">';

    woocommerce_wp_text_input(
        array(
            'id'            => "_count_links_min[{$loop}]",
            'name'          => "_count_links_min[{$loop}]",
            'value'         => get_post_meta($variation->ID, '_count_links_min', true),
            'label'         => __('Мин. количество звеньев (шт.)', 'text-domain'),
            'desc_tip'      => true,
            'description'   => __('Минимальное количество звеньев для этой цепи.', 'text-domain'),
            'data_type'     => 'decimal',
            'wrapper_class' => 'form-row form-row-full',
            'custom_attributes' => array(
                'step' => 'any',
                'min'  => '1'
            )
        )
    );

    echo '</p>';
}
add_action('woocommerce_variation_options_pricing', 'add_variation_count_links_min_field', 10, 3);

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

function save_variation_count_links_min_field($variation_id, $i)
{
    if (isset($_POST['_count_links_min'][$i])) {
        $value = sanitize_text_field($_POST['_count_links_min'][$i]);
        if (empty($value) || $value < 1) {
            $value = 1;
        }
        update_post_meta($variation_id, '_count_links_min', $value);
    }
}
add_action('woocommerce_save_product_variation', 'save_variation_count_links_min_field', 10, 2);

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

function update_cart_item_quantity_custom_ajax()
{
    if (! class_exists('WooCommerce') || empty($_POST['hash']) || ! isset($_POST['quantity'])) {
        wp_send_json_error(['message' => 'Некорректный запрос.']);
    }

    check_ajax_referer('woocommerce-cart', 'security');

    $cart_item_key = sanitize_text_field($_POST['hash']);
    $new_quantity  = intval($_POST['quantity']);

    if ($new_quantity < 1) $new_quantity = 1;

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
            'raw_total'    => $cart->get_total(),
            'quantity'     => $new_quantity
        ]);
    } else {
        wp_send_json_error(['message' => 'Не удалось обновить количество.']);
    }
}
add_action('wp_ajax_woocommerce_update_cart_item_quantity', 'update_cart_item_quantity_custom_ajax');
add_action('wp_ajax_nopriv_woocommerce_update_cart_item_quantity', 'update_cart_item_quantity_custom_ajax');

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


add_action('woocommerce_before_calculate_totals', 'custom_recalculate_price_by_links', 10, 1);
function custom_recalculate_price_by_links($cart)
{
    if (is_admin() && !defined('DOING_AJAX')) return;

    if (did_action('woocommerce_before_calculate_totals') >= 2) return;

    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['links_count']) && is_numeric($cart_item['links_count'])) {
            $links_count = intval($cart_item['links_count']);

            $product = $cart_item['data'];

            $base_price = $product->get_sale_price() ? $product->get_sale_price() : $product->get_regular_price();

            if (!$base_price) {
                $base_price = $product->get_price();
            }

            $new_price = (float)$base_price * $links_count;

            $product->set_price($new_price);
        }
    }
}
