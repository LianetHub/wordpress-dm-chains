<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php';
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
?>


<div id="order" class="popup">
    <div class="popup__form form">

        <?php echo do_shortcode('[contact-form-7 id="1f99d13" title="Форма заказа"]') ?>

    </div>
</div>

<div id="success-popup" class="popup">
    <div class="popup__content">
        <h3 class="popup__title title">Спасибо за ваш заказ!</h3>
        <p class="popup__subtitle">Ваша заявка успешно отправлена. Мы свяжемся с вами в ближайшее время для подтверждения деталей.</p>
        <button class="popup__btn btn btn-primary" data-fancybox-close>Закрыть</button>
    </div>
</div>