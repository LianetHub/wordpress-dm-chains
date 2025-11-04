<div class="cart">
    <div class="container">
        <div class="cart__body">
            <div class="cart__content">
                <div class="cart__items">
                    <div class="cart__item">
                        <div class="cart__item-image">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/chains/pitch.png" alt="Фото товара">
                        </div>
                        <div class="cart__item-info">
                            <div class="cart__item-name">Цепь 3/8”, 1.3мм, 50зв А</div>
                            <div class="cart__item-props">Шаг: 3/8”, Толщина: 1.3мм, Кол-во зв.: 50, Класс: А</div>
                            <div class="cart__item-calc">
                                500₽ х 3
                            </div>
                        </div>
                        <div class="cart__item-quantity quantity-block">
                            <button type="button" class="quantity-block__down icon-minus"></button>
                            <input type="number" name="quantity" class="quantity-block__input" value="1">
                            <button type="button" class="quantity-block__up icon-plus"></button>
                        </div>
                    </div>
                    <div class="cart__item">
                        <div class="cart__item-image">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/chains/pitch.png" alt="Фото товара">
                        </div>
                        <div class="cart__item-info">
                            <div class="cart__item-name">Цепь 3/8”, 1.3мм, 50зв А</div>
                            <div class="cart__item-props">Шаг: 3/8”, Толщина: 1.3мм, Кол-во зв.: 50, Класс: А</div>
                            <div class="cart__item-calc">
                                500₽ х 3
                            </div>
                        </div>
                        <div class="cart__item-quantity quantity-block">
                            <button type="button" class="quantity-block__down icon-minus"></button>
                            <input type="number" name="quantity" class="quantity-block__input" value="1">
                            <button type="button" class="quantity-block__up icon-plus"></button>
                        </div>
                    </div>
                    <div class="cart__item">
                        <div class="cart__item-image">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/chains/pitch.png" alt="Фото товара">
                        </div>
                        <div class="cart__item-info">
                            <div class="cart__item-name">Цепь 3/8”, 1.3мм, 50зв А</div>
                            <div class="cart__item-props">Шаг: 3/8”, Толщина: 1.3мм, Кол-во зв.: 50, Класс: А</div>
                            <div class="cart__item-calc">
                                500₽ х 3
                            </div>
                        </div>
                        <div class="cart__item-quantity quantity-block">
                            <button type="button" class="quantity-block__down icon-minus"></button>
                            <input type="number" name="quantity" class="quantity-block__input" value="1">
                            <button type="button" class="quantity-block__up icon-plus"></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cart__total">
                <div class="cart__total-title">
                    Итого стоимость товаров:
                </div>
                <div class="cart__total-price">
                    1212 12 12 10 ₽
                </div>
                <button data-fancybox data-src="#order" class="cart__total-btn btn btn-primary">Оформить заказ</button>
            </div>
        </div>
    </div>
</div>
<div id="order" class="popup">
    <div class="popup__form form">
        <?php echo do_shortcode('[contact-form-7 id="1f99d13" title="Форма заказа"]') ?>
    </div>
</div>