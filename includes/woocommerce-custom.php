<?php

class CustomWooCommerceSetup
{
    /**
     * Инициализация хуков и настроек.
     *
     * Вызывается для установки всех экшенов и фильтров.
     * @return void
     */
    public static function init(): void
    {
        // Настройка основных возможностей WooCommerce
        add_action('after_setup_theme', [self::class, 'add_woocommerce_support']);

        // Скрытие неиспользуемых типов записей
        add_filter('woocommerce_register_post_type_shop_order', [self::class, 'hide_order_post_type']);
        add_filter('woocommerce_register_post_type_shop_coupon', [self::class, 'hide_coupon_post_type']);
        add_filter('woocommerce_register_post_type_shop_order_refund', [self::class, 'hide_refund_post_type']);

        // Отключение платежных шлюзов и страницы оформления заказа
        add_filter('woocommerce_payment_gateways', [self::class, 'disable_payment_gateways']);
        add_filter('woocommerce_get_settings_pages', [self::class, 'remove_checkout_settings_page']);

        // Удаление ненужных скриптов и стилей
        add_action('wp_enqueue_scripts', [self::class, 'dequeue_checkout_scripts'], 100);
        add_action('wp_enqueue_scripts', [self::class, 'dequeue_general_styles_and_scripts'], 99);

        // Перенаправление страниц WooCommerce
        add_action('template_redirect', [self::class, 'redirect_woocommerce_pages']);

        // Удаление правил перезаписи (Rewrite Tags)
        add_action('init', [self::class, 'remove_rewrite_tags']);

        // Удаление пунктов меню в админке
        add_action('admin_menu', [self::class, 'remove_admin_menus']);

        // Локализация скрипта
        add_action('wp_enqueue_scripts', [self::class, 'localize_configurator_script']);

        // Логика для вариаций (Конфигуратор цепей)
        add_action('woocommerce_variation_options_pricing', [self::class, 'add_variation_count_links_min_field'], 10, 3);
        add_action('woocommerce_save_product_variation', [self::class, 'save_variation_count_links_min_field'], 10, 2);

        // AJAX-обработчики
        add_action('wp_ajax_get_product_data', [self::class, 'handle_get_product_data']);
        add_action('wp_ajax_nopriv_get_product_data', [self::class, 'handle_get_product_data']);
        add_action('wp_ajax_get_cart_total_custom', [self::class, 'get_cart_total_custom_ajax']);
        add_action('wp_ajax_nopriv_get_cart_total_custom', [self::class, 'get_cart_total_custom_ajax']);
        add_action('wp_ajax_remove_from_cart', [self::class, 'remove_from_cart_ajax']);
        add_action('wp_ajax_nopriv_remove_from_cart', [self::class, 'remove_from_cart_ajax']);
        add_action('wp_ajax_woocommerce_update_cart_item_quantity', [self::class, 'update_cart_item_quantity_custom_ajax']);
        add_action('wp_ajax_nopriv_woocommerce_update_cart_item_quantity', [self::class, 'update_cart_item_quantity_custom_ajax']);
        add_action('wp_ajax_clear_cart_after_order', [self::class, 'custom_clear_woocommerce_cart']);
        add_action('wp_ajax_nopriv_clear_cart_after_order', [self::class, 'custom_clear_woocommerce_cart']);
        add_action('wp_ajax_get_cart_popup_data', [self::class, 'get_popup_data_ajax']);
        add_action('wp_ajax_nopriv_get_cart_popup_data', [self::class, 'get_popup_data_ajax']);

        // Логика корзины и заказа
        add_filter('woocommerce_add_cart_item_data', [self::class, 'save_chain_links_count_to_cart_item_data'], 10, 2);
        add_filter('woocommerce_get_item_data', [self::class, 'display_chain_links_count_in_cart'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', [self::class, 'add_chain_links_count_to_order_items'], 10, 3);
        add_action('woocommerce_before_calculate_totals', [self::class, 'custom_recalculate_price_by_links'], 10, 1);
        add_action('wp_enqueue_scripts', [self::class, 'register_order_popup_script']);
    }

    /**
     * Включает поддержку WooCommerce в теме.
     * @return void
     */
    public static function add_woocommerce_support(): void
    {
        add_theme_support('woocommerce');
    }

    /**
     * Скрывает тип записи 'shop_order'.
     * @param array $args Аргументы регистрации типа записи.
     * @return array Обновленные аргументы.
     */
    public static function hide_order_post_type(array $args): array
    {
        return array_merge($args, ['public' => false, 'show_ui' => false]);
    }

    /**
     * Скрывает тип записи 'shop_coupon'.
     * @param array $args Аргументы регистрации типа записи.
     * @return array Обновленные аргументы.
     */
    public static function hide_coupon_post_type(array $args): array
    {
        return array_merge($args, ['public' => false, 'show_ui' => false]);
    }

    /**
     * Скрывает тип записи 'shop_order_refund'.
     * @param array $args Аргументы регистрации типа записи.
     * @return array Обновленные аргументы.
     */
    public static function hide_refund_post_type(array $args): array
    {
        return array_merge($args, ['public' => false, 'show_ui' => false]);
    }

    /**
     * Отключает все платежные шлюзы.
     * @return array Пустой массив.
     */
    public static function disable_payment_gateways(): array
    {
        return [];
    }

    /**
     * Удаляет страницу настроек Оформления заказа.
     * @param array $settings Массив страниц настроек.
     * @return array Обновленный массив страниц настроек.
     */
    public static function remove_checkout_settings_page(array $settings): array
    {
        if (is_array($settings)) {
            foreach ($settings as $key => $page) {
                if (isset($page->id) && $page->id === 'checkout') {
                    unset($settings[$key]);
                }
            }
        }
        return $settings;
    }

    /**
     * Отключает скрипты, связанные с оформлением заказа.
     * @return void
     */
    public static function dequeue_checkout_scripts(): void
    {
        wp_dequeue_script('wc-checkout');
        wp_dequeue_script('wc-credit-card-form');
        wp_dequeue_script('wc-payment-method');
    }

    /**
     * Перенаправляет на главную страницу, если пользователь пытается получить доступ к страницам
     * Магазина, Товара, Оформления заказа или Моего аккаунта.
     * @return void
     */
    public static function redirect_woocommerce_pages(): void
    {
        if (is_shop() || is_product() || is_checkout() || is_account_page()) {
            wp_redirect(home_url('/'));
            exit;
        }
    }

    /**
     * Удаляет правила перезаписи WooCommerce.
     * @return void
     */
    public static function remove_rewrite_tags(): void
    {
        remove_rewrite_tag('%product_cat%');
        remove_rewrite_tag('%product_tag%');
        remove_rewrite_tag('%product%');
        remove_rewrite_tag('%shop%');
        remove_rewrite_tag('%myaccount%');
    }

    /**
     * Удаляет общие стили и скрипты WooCommerce, если это не страница корзины.
     * @return void
     */
    public static function dequeue_general_styles_and_scripts(): void
    {
        if (!is_cart()) {
            wp_dequeue_style('woocommerce-general');
            wp_dequeue_style('woocommerce-layout');
            wp_dequeue_style('woocommerce-smallscreen');
            wp_dequeue_script('wc-cart-fragments');
            wp_dequeue_script('woocommerce');
            wp_dequeue_script('wc-add-to-cart');
        }
    }

    /**
     * Удаляет пункты меню Заказы и Купоны из админки.
     * @return void
     */
    public static function remove_admin_menus(): void
    {
        remove_menu_page('edit.php?post_type=shop_order');
        remove_menu_page('edit.php?post_type=shop_coupon');
    }

    /**
     * Локализует скрипт для передачи AJAX URL.
     * NOTE: 'your-script-handle' должен быть заменен на актуальный хендл вашего скрипта.
     * @return void
     */
    public static function localize_configurator_script(): void
    {
        wp_localize_script('your-script-handle', 'configurator_vars', [
            'ajaxurl' => admin_url('admin-ajax.php')
        ]);
    }

    /**
     * Добавляет поле для ввода минимального количества звеньев в настройках вариации товара.
     * @param int $loop Индекс вариации.
     * @param array $variation_data Данные вариации.
     * @param WP_Post $variation Объект поста вариации.
     * @return void
     */
    public static function add_variation_count_links_min_field(int $loop, array $variation_data, WP_Post $variation): void
    {
        echo '<p class="form-row form-row-full">';

        if (!function_exists('woocommerce_wp_text_input')) return;

        woocommerce_wp_text_input(
            [
                'id'              => "_count_links_min[{$loop}]",
                'name'            => "_count_links_min[{$loop}]",
                'value'           => get_post_meta($variation->ID, '_count_links_min', true),
                'label'           => __('Мин. количество звеньев (шт.)', 'text-domain'),
                'desc_tip'        => true,
                'description'     => __('Минимальное количество звеньев для этой цепи.', 'text-domain'),
                'data_type'       => 'decimal',
                'wrapper_class'   => 'form-row form-row-full',
                'custom_attributes' => [
                    'step' => 'any',
                    'min'  => '1'
                ]
            ]
        );

        echo '</p>';
    }

    /**
     * Сохраняет значение минимального количества звеньев для вариации.
     * @param int $variation_id ID вариации.
     * @param int $i Индекс формы.
     * @return void
     */
    public static function save_variation_count_links_min_field(int $variation_id, int $i): void
    {
        if (isset($_POST['_count_links_min'][$i])) {
            $value = sanitize_text_field($_POST['_count_links_min'][$i]);
            if (empty($value) || $value < 1) {
                $value = 1;
            }
            update_post_meta($variation_id, '_count_links_min', $value);
        }
    }

    /**
     * AJAX-обработчик для получения данных о продукте.
     * @return void
     */
    public static function handle_get_product_data(): void
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

        $response = [
            'price_html'     => $price_html,
            'product_id'     => $product_id,
            'price_per_item' => $price_per_item
        ];

        wp_send_json_success($response);
    }

    /**
     * AJAX-обработчик для получения общей суммы корзины.
     * @return void
     */
    public static function get_cart_total_custom_ajax(): void
    {
        if (!class_exists('WooCommerce')) {
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

    /**
     * Сохраняет количество звеньев в данных элемента корзины.
     * @param array $cart_item_data Данные элемента корзины.
     * @param int $product_id ID продукта.
     * @return array Обновленные данные элемента корзины.
     */
    public static function save_chain_links_count_to_cart_item_data(array $cart_item_data, int $product_id): array
    {
        if (isset($_POST['links_count']) && is_numeric($_POST['links_count'])) {
            $links_count = intval($_POST['links_count']);
            $cart_item_data['links_count'] = $links_count;
            $cart_item_data['unique_key'] = md5(microtime() . rand());
        }
        return $cart_item_data;
    }

    /**
     * Отображает количество звеньев в корзине и на странице оформления заказа.
     * @param array $item_data Массив отображаемых данных.
     * @param array $cart_item Данные элемента корзины.
     * @return array Обновленный массив отображаемых данных.
     */
    public static function display_chain_links_count_in_cart(array $item_data, array $cart_item): array
    {
        if (isset($cart_item['links_count'])) {
            $item_data[] = [
                'key'     => 'Кол-во зв.',
                'value'   => $cart_item['links_count'],
                'display' => '',
            ];
        }
        return $item_data;
    }

    /**
     * Добавляет метаданные о количестве звеньев к элементам заказа.
     * @param WC_Order_Item_Product $item Объект элемента заказа.
     * @param string $cart_item_key Ключ элемента корзины.
     * @param array $values Значения элемента корзины.
     * @return void
     */
    public static function add_chain_links_count_to_order_items(WC_Order_Item_Product $item, string $cart_item_key, array $values): void
    {
        if (isset($values['links_count'])) {
            $item->add_meta_data('Кол-во зв.', $values['links_count']);
        }
    }

    /**
     * AJAX-обработчик для удаления товара из корзины.
     * @return void
     */
    public static function remove_from_cart_ajax(): void
    {
        if (!class_exists('WooCommerce') || empty($_POST['cart_item_key'])) {
            wp_send_json_error(['message' => 'Некорректный запрос.']);
        }

        check_ajax_referer('woocommerce-cart', 'security');

        $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
        $removed = WC()->cart->remove_cart_item($cart_item_key);

        if ($removed) {
            WC()->cart->calculate_totals();

            wp_send_json_success([
                'fragments' => [],
                'total' => WC()->cart->get_cart_total(),
                'cart_count' => WC()->cart->get_cart_contents_count()
            ]);
        } else {
            wp_send_json_error(['message' => 'Не удалось удалить товар.']);
        }
    }

    /**
     * AJAX-обработчик для обновления количества товара в корзине.
     * @return void
     */
    public static function update_cart_item_quantity_custom_ajax(): void
    {
        if (!class_exists('WooCommerce') || empty($_POST['hash']) || !isset($_POST['quantity'])) {
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

            $attribute_map = [
                'pitch'     => 'Шаг',
                'thickness' => 'Толщина',
                'class'     => 'Класс',
            ];
            $links_count_label = 'Кол-во зв.';
            $product_quantity_label = 'Кол-во (шт)';

            $cart_items_list = self::build_cart_items_list($attribute_map, $links_count_label, $product_quantity_label);
            $cart_items_array = self::get_formatted_cart_items();

            wp_send_json_success([
                'new_price'    => wc_price($_product->get_price()),
                'new_subtotal' => wc_price($cart_item['line_total']),
                'total'        => $cart->get_cart_total(),
                'raw_total'    => $cart->get_total(),
                'quantity'     => $new_quantity,
                'cart_count'   => $cart->get_cart_contents_count(),
                'cart_items_list' => $cart_items_list,
                'cart_items'      => $cart_items_array,
                'woocommerce_total' => floatval($cart->get_cart_contents_total()),
            ]);
        } else {
            wp_send_json_error(['message' => 'Не удалось обновить количество.']);
        }
    }

    /**
     * AJAX-обработчик для очистки корзины.
     * @return void
     */
    public static function custom_clear_woocommerce_cart(): void
    {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            if (WC()->cart) {
                WC()->cart->empty_cart();
                wp_send_json_success();
            } else {
                wp_send_json_error(['message' => 'Корзина недоступна.']);
            }
        }
        wp_die();
    }

    /**
     * Пересчитывает цену товара в корзине на основе количества звеньев.
     * @param WC_Cart $cart Объект корзины.
     * @return void
     */
    public static function custom_recalculate_price_by_links(WC_Cart $cart): void
    {
        if (is_admin() && !defined('DOING_AJAX')) return;
        if (did_action('woocommerce_before_calculate_totals') >= 2) return;

        foreach ($cart->get_cart() as $cart_item) {
            if (isset($cart_item['links_count']) && is_numeric($cart_item['links_count'])) {
                $links_count = intval($cart_item['links_count']);
                /** @var WC_Product $product */
                $product = $cart_item['data'];

                $base_price = $product->get_sale_price() ?: $product->get_regular_price();

                if (!$base_price) {
                    $base_price = $product->get_price();
                }

                $new_price = (float)$base_price * $links_count;

                $product->set_price($new_price);
            }
        }
    }

    /**
     * Регистрирует и локализует скрипт для всплывающего окна заказа.
     * Собирает данные о товарах в корзине для передачи в JS.
     * @return void
     */
    public static function register_order_popup_script(): void
    {
        $woocommerce_total = (WC()->cart) ? WC()->cart->get_cart_contents_total() : 0;

        $attribute_map = [
            'pitch'     => 'Шаг',
            'thickness' => 'Толщина',
            'class'     => 'Класс',
        ];
        $links_count_label = 'Кол-во зв.';
        $product_quantity_label = 'Кол-во (шт)';

        $cart_items_list = self::build_cart_items_list($attribute_map, $links_count_label, $product_quantity_label);

        $cart_items_array = self::get_formatted_cart_items();

        $theme_uri = get_template_directory_uri();
        $script_handle = 'order-popup-script';

        wp_register_script($script_handle, "{$theme_uri}/assets/js/order.js", ['jquery'], '1.0', true);

        $script_data = [
            'cart_items_list'   => $cart_items_list,
            'cart_items'        => $cart_items_array,
            'woocommerce_total' => floatval($woocommerce_total),
            'ajax_url'          => admin_url('admin-ajax.php'),
            'cdek_api_key'      => defined('CDEK_YANDEX_MAPS_API_KEY') ? CDEK_YANDEX_MAPS_API_KEY : '',
            'yandex_platform_id' => defined('YANDEX_DELIVERY_PLATFORM_STATION_ID') ? YANDEX_DELIVERY_PLATFORM_STATION_ID : '',
            'cdek_service_path' => "{$theme_uri}/services/cdek-service.php",
        ];

        wp_localize_script($script_handle, 'OrderPopupData', $script_data);
        wp_enqueue_script($script_handle);
    }

    private static function get_formatted_cart_items(): array
    {
        $items = [];
        if (WC()->cart && !WC()->cart->is_empty()) {

            $attribute_map = [
                'pitch'     => 'Шаг',
                'thickness' => 'Толщина',
                'class'     => 'Класс',
            ];

            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                /** @var WC_Product $product */
                $product = $cart_item['data'];


                $weight = $product->get_weight() ? (float)$product->get_weight() * 1000 : 400;

                $name_parts = [$product->get_name()];

                if ($product->is_type('variation')) {
                    $attributes = $cart_item['variation'];
                    foreach ($attributes as $key => $value) {
                        $taxonomy = str_replace('attribute_', '', $key);
                        $attr_slug = str_replace('pa_', '', $taxonomy);
                        $attr_label = $attribute_map[$attr_slug] ?? wc_attribute_label($taxonomy, $product);

                        $name_parts[] = "{$attr_label}: " . ucwords(str_replace('-', ' ', $value));
                    }
                }

                $links_count = $cart_item['links_count'] ?? 0;
                if ($links_count > 0) {
                    $name_parts[] = "Кол-во зв.: " . $links_count;
                }

                $full_name = implode(', ', $name_parts);

                $line_total = $cart_item['line_total'];
                $quantity = $cart_item['quantity'];


                $cost_per_unit = $quantity > 0 ? (float)($line_total / $quantity) : 0;

                $cost_per_unit = round($cost_per_unit, 2);

                $items[] = [
                    'name' => $full_name,
                    'sku' => $product->get_sku(),
                    'quantity' => $quantity,
                    'cost_per_unit' => $cost_per_unit,
                    'length' => $product->get_length() ?: 15,
                    'width' => $product->get_width() ?: 10,
                    'height' => $product->get_height() ?: 5,
                ];
            }
        }
        return $items;
    }

    /**
     * Помощник для сборки строкового списка товаров в корзине.
     * @param array $attribute_map Карта атрибутов.
     * @param string $links_count_label Лейбл количества звеньев.
     * @param string $product_quantity_label Лейбл количества продукта.
     * @return string Строка со списком товаров.
     */
    private static function build_cart_items_list(array $attribute_map, string $links_count_label, string $product_quantity_label): string
    {
        $items_strings = [];

        if (WC()->cart && !WC()->cart->is_empty()) {
            $cart_items = WC()->cart->get_cart();

            foreach ($cart_items as $cart_item) {
                /** @var WC_Product $_product */
                $_product = $cart_item['data'];

                $product_name = $_product->get_name();
                $quantity = $cart_item['quantity'];
                $links_count = $cart_item['links_count'] ?? 0;

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

                if ($links_count > 0) {
                    $attr_parts[] = "{$links_count_label}: {$links_count}";
                }

                $attr_parts[] = "{$product_quantity_label}: {$quantity}";

                $attr_string = implode(', ', $attr_parts);

                $items_strings[] = "{$product_name} ({$attr_string})";
            }
        }

        return implode(' ; ', $items_strings);
    }


    public static function get_popup_data_ajax(): void
    {

        WC()->cart->calculate_totals();

        $attribute_map = [
            'pitch'     => 'Шаг',
            'thickness' => 'Толщина',
            'class'     => 'Класс',
        ];
        $links_count_label = 'Кол-во зв.';
        $product_quantity_label = 'Кол-во (шт)';

        $response_data = [
            'woocommerce_total' => WC()->cart->get_subtotal(),
            'cart_items_list'   => self::build_cart_items_list($attribute_map, $links_count_label, $product_quantity_label),
        ];

        wp_send_json_success($response_data);
    }
}

/**
 * Инициализация класса
 */
CustomWooCommerceSetup::init();


/**
 * Получает данные о комбинациях вариаций цепей для продукта.
 * @param int $product_id ID продукта.
 * @return array Массив комбинаций.
 */
function get_chain_combinations_data(int $product_id): array
{
    if (!class_exists('WC_Product_Variable')) {
        return [];
    }

    $product = wc_get_product($product_id);

    if (!$product || !$product->is_type('variable')) {
        return [];
    }

    $combinations = [];
    $variations = $product->get_children();

    $pitch_key = 'attribute_pitch';
    $thickness_key = 'attribute_thickness';
    $class_key = 'attribute_class';

    foreach ($variations as $variation_id) {
        $variation = wc_get_product($variation_id);

        if (!$variation) {
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

/**
 * Строит структурированную конфигурацию цепей из массива комбинаций.
 *
 * Сортирует и группирует данные для пошагового конфигуратора (pitch, thickness, class).
 * @param array $rows Сырые комбинации вариаций.
 * @return array Структурированный массив конфигурации.
 */
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
            'value'        => $t['value'],
            'label'        => $t['label'],
            'image'        => $t['image'],
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
            'value'        => $c['value'],
            'label'        => $c['label'],
            'image'        => $c['image'],
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
