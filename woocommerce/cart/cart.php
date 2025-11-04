<?php
defined('ABSPATH') || exit;

?>

<div class="cart">
    <div class="container">
        <div class="cart__body">
            <div class="cart__content">
                <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
                    <div class="cart__items">
                        <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                            $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

                            if ($_product && $_product->exists() && $cart_item['quantity'] > 0) :
                                $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                        ?>
                                <div class="cart__item" data-product_id="<?php echo esc_attr($product_id); ?>">
                                    <div class="cart__item-image">
                                        <?php
                                        $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
                                        echo $product_permalink ? sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail) : $thumbnail;
                                        ?>
                                    </div>
                                    <div class="cart__item-info">
                                        <div class="cart__item-name">
                                            <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)); ?>
                                        </div>
                                        <div class="cart__item-props">
                                            <?php wc_display_item_meta($cart_item); ?>
                                        </div>
                                        <div class="cart__item-calc">
                                            <?php echo WC()->cart->get_product_price($_product); ?> × <?php echo $cart_item['quantity']; ?>
                                        </div>
                                    </div>
                                    <div class="cart__item-quantity quantity-block">
                                        <?php

                                        echo woocommerce_quantity_input(
                                            array(
                                                'input_name'  => "cart[{$cart_item_key}][qty]",
                                                'input_value' => $cart_item['quantity'],
                                                'max_value'   => $_product->get_max_purchase_quantity(),
                                                'min_value'   => '0',
                                            ),
                                            $_product,
                                            false
                                        );
                                        ?>
                                    </div>
                                    <div class="cart__item-remove">
                                        <?php
                                        echo apply_filters(
                                            'woocommerce_cart_item_remove_link',
                                            sprintf(
                                                '<a href="%s" class="remove" aria-label="%s">&times;</a>',
                                                esc_url(wc_get_cart_remove_url($cart_item_key)),
                                                esc_html__('Удалить', 'woocommerce')
                                            ),
                                            $cart_item_key
                                        );
                                        ?>
                                    </div>
                                </div>
                        <?php endif;
                        endforeach; ?>
                    </div>

                    <?php do_action('woocommerce_cart_actions'); ?>

                    <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
                </form>
            </div>

            <div class="cart__total">
                <div class="cart__total-title">
                    <?php esc_html_e('Итого стоимость товаров:', 'woocommerce'); ?>
                </div>
                <div class="cart__total-price">
                    <?php wc_cart_totals_subtotal_html(); ?>
                </div>
                <div class="cart__total-btn">
                    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="btn btn-primary">Оформить заказ</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php do_action('woocommerce_after_cart'); ?>