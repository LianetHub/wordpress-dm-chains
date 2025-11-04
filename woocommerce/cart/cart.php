<div class="cart">
    <div class="container">
        <div class="cart__body">
            <div class="cart__content">
                <div class="cart__items">
                    <?php
                    global $woocommerce;
                    $cart_is_empty = $woocommerce->cart->is_empty();

                    if (!$cart_is_empty) :
                        $attribute_map = [
                            'pitch'     => 'Шаг',
                            'thickness' => 'Толщина',
                            'class'     => 'Класс',
                        ];
                        $quantity_label = 'Кол-во зв.';

                        foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) :
                            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

                            $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
                            $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);

                            $unit_price = $_product->get_price();
                            $formatted_unit_price = wc_price($unit_price);
                            $quantity = $cart_item['quantity'];
                            $remove_url = esc_url(wc_get_cart_remove_url($cart_item_key));

                            $attr_html = [];

                            if ($_product->is_type('variation')) {
                                $attributes = $cart_item['variation'];
                                foreach ($attributes as $key => $value) {
                                    $taxonomy = str_replace('attribute_', '', $key);

                                    $attr_slug = str_replace('pa_', '', $taxonomy);

                                    $attr_name = $attribute_map[$attr_slug] ?? wc_attribute_label($taxonomy, $_product);

                                    $attr_html[] = "{$attr_name}: " . ucwords(str_replace('-', ' ', $value));
                                }
                            }

                            $attr_html[] = "{$quantity_label}: {$quantity}";
                    ?>

                            <div class="cart__item" data-cart-key="<?php echo $cart_item_key; ?>">
                                <div class="cart__item-image">
                                    <?php echo $thumbnail; ?>
                                </div>
                                <div class="cart__item-info">
                                    <div class="cart__item-name"><?php echo $product_name; ?></div>
                                    <div class="cart__item-props">
                                        <?php echo implode(', ', $attr_html); ?>
                                    </div>
                                    <div class="cart__item-calc">
                                        <?php echo $formatted_unit_price; ?> х **<?php echo $quantity; ?>**
                                    </div>
                                </div>
                                <div class="cart__item-quantity quantity-block">
                                    <button type="button" class="quantity-block__down icon-minus" data-action="minus"></button>
                                    <input type="number" name="quantity" class="quantity-block__input" value="<?php echo $quantity; ?>" min="1" data-cart-key="<?php echo $cart_item_key; ?>">
                                    <button type="button" class="quantity-block__up icon-plus" data-action="plus"></button>
                                </div>
                                <a href="<?php echo $remove_url; ?>" class="cart__item-remove icon-close"></a>
                            </div>

                        <?php endforeach;
                    else : ?>
                        <p class="cart-empty-message">Ваша корзина пуста.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="cart__total">
                <div class="cart__total-title">
                    Итого стоимость товаров:
                </div>
                <div class="cart__total-price" id="cart-total-price">
                    <?php echo $woocommerce->cart->get_cart_total(); ?>
                </div>
                <?php if (!$cart_is_empty) : ?>
                    <button data-fancybox data-src="#order" class="cart__total-btn btn btn-primary">Оформить заказ</button>
                <?php else : ?>
                    <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="cart__total-btn btn btn-primary">Перейти в каталог</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div id="order" class="popup">
    <div class="popup__form form">
        <?php echo do_shortcode('[contact-form-7 id="1f99d13" title="Форма заказа"]') ?>
    </div>
</div>

<script>
    jQuery(function($) {

        const ajaxUrl = custom_ajax_params.ajax_url;
        const cartNonce = custom_ajax_params.cart_nonce;

        function updateCartTotalsDisplay(response) {
            if (response && response.fragments) {
                $.each(response.fragments, function(key, value) {
                    $(key).replaceWith(value);
                });
                if (response.fragments['.woocommerce-cart-form']) {
                    getNewCartTotal();
                }
            } else {
                getNewCartTotal();
            }

            if (typeof Fancybox !== 'undefined') {
                Fancybox.bind("[data-fancybox]", {});
            }
        }

        function getNewCartTotal() {
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_cart_total_custom'
                },
                success: function(response) {
                    if (response.success && response.data && response.data.total) {
                        $('#cart-total-price').html(response.data.total);
                    } else {
                        if (response.data && response.data.cart_is_empty) {
                            location.reload();
                        }
                    }
                }
            });
        }

        $('.cart__items').on('click', '.quantity-block__up, .quantity-block__down', function(e) {
            e.preventDefault();
            const $button = $(this);
            const $input = $button.siblings('.quantity-block__input');
            const cartKey = $input.data('cart-key');
            let currentQuantity = parseInt($input.val()) || 1;
            const isPlus = $button.data('action') === 'plus';

            if (isPlus) {
                currentQuantity++;
            } else if (currentQuantity > 1) {
                currentQuantity--;
            } else {
                return;
            }

            $input.val(currentQuantity).trigger('change');
        });

        $('.cart__items').on('change', '.quantity-block__input', function() {
            const $input = $(this);
            const cartKey = $input.data('cart-key');
            const newQuantity = parseInt($input.val()) || 1;

            $input.closest('.cart__item').addClass('updating-cart');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'woocommerce_update_cart_item_quantity',
                    hash: cartKey,
                    quantity: newQuantity,
                    security: cartNonce
                },
                success: function(response) {
                    if (response.success) {
                        const $item = $input.closest('.cart__item');
                        const new_unit_price = response.data.new_price;
                        const new_subtotal = response.data.new_subtotal;

                        if (new_unit_price) {
                            $item.find('.cart__item-calc').html(`${new_unit_price} х **${newQuantity}**`);
                        }

                        updateCartTotalsDisplay(response);

                    } else {
                        alert('Не удалось обновить количество товара.');
                        location.reload();
                    }
                },
                complete: function() {
                    $input.closest('.cart__item').removeClass('updating-cart');
                }
            });
        });

        $('.cart__items').on('click', '.cart__item-remove', function(e) {
            e.preventDefault();
            const $item = $(this).closest('.cart__item');
            const cartKey = $item.data('cart-key');
            const removeUrl = $(this).attr('href');

            $item.addClass('deleting-cart');

            $.ajax({
                url: removeUrl,
                type: 'GET',
                success: function(response) {

                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'remove_from_cart',
                            cart_item_key: cartKey,
                            security: cartNonce
                        },
                        success: function(response) {
                            if (response.success) {
                                $item.fadeOut(300, function() {
                                    $(this).remove();
                                    updateCartTotalsDisplay(response);

                                    if ($('.cart__item').length === 0) {
                                        location.reload();
                                    }
                                });
                            } else {
                                alert('Не удалось удалить товар.');
                                $item.removeClass('deleting-cart');
                            }
                        },
                        error: function() {
                            alert('Ошибка AJAX при удалении товара.');
                            $item.removeClass('deleting-cart');
                        }
                    });
                },
                error: function() {
                    alert('Ошибка при удалении товара (первый запрос).');
                    $item.removeClass('deleting-cart');
                }
            });
        });
    });
</script>