<?php
defined('ABSPATH') || exit;
?>
<?php
global $woocommerce;
?>
<div class="cart">
    <div class="container">
        <div class="cart__body">
            <div class="cart__content">
                <div class="cart__empty">
                    <div class="cart__empty-title title text-center">
                        Ваша корзина пуста <span class="text-nowrap">:(</span> <br>
                        Почему бы это не исправить?
                    </div>
                    <a href="<?php echo get_permalink(65); ?>" class="cart__empty-btn btn btn-primary btn-lg">Создать цепь</a>
                </div>
            </div>
            <div class="cart__total">
                <div class="cart__total-title">
                    Итого стоимость товаров:
                </div>
                <div class="cart__total-price">
                    <?php echo $woocommerce->cart->get_cart_total(); ?>
                </div>
                <button data-fancybox data-src="#order" disabled class="cart__total-btn btn btn-primary">Оформить заказ</button>
            </div>
        </div>
    </div>
</div>