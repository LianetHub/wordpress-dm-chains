<div class="creating-block">
    <div class="container">
        <form action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" class="creating-block__content">
            <input type="hidden" name="action" value="get_product_data">
            <input type="hidden" name="product_id_selected" id="product_id_selected" value="">
            <div class="creating-block__quiz creating-quiz">
                <div class="creating-quiz__item active">
                    <div class="creating-quiz__header">
                        <div class="creating-quiz__step">01</div>
                        <div class="creating-quiz__name">Выберите шаг</div>
                        <button type="button" class="creating-quiz__back icon-chevron"></button>
                    </div>
                    <div class="creating-quiz__body"></div>
                </div>
                <div class="creating-quiz__item">
                    <div class="creating-quiz__header">
                        <div class="creating-quiz__step">02</div>
                        <div class="creating-quiz__name">Выберите толщину</div>
                        <button type="button" class="creating-quiz__back icon-chevron"></button>
                    </div>
                    <div class="creating-quiz__body"></div>
                </div>
                <div class="creating-quiz__item">
                    <div class="creating-quiz__header">
                        <div class="creating-quiz__step">03</div>
                        <div class="creating-quiz__name">Выберите класс</div>
                        <button type="button" class="creating-quiz__back icon-chevron"></button>
                    </div>
                    <div class="creating-quiz__body"></div>
                </div>
                <div class="creating-quiz__item">
                    <div class="creating-quiz__header">
                        <div class="creating-quiz__step">04</div>
                        <div class="creating-quiz__name sm-text">Выберите количество звеньев</div>
                        <button type="button" class="creating-quiz__back icon-chevron"></button>
                    </div>
                    <div class="creating-quiz__body">
                        <div class="creating-quiz__quantity-block quantity-block">
                            <button type="button" disabled class="quantity-block__down icon-minus"></button>
                            <input type="number" name="links_quantity" id="links_quantity" class="quantity-block__input" value="" min="" max="">
                            <button type="button" disabled class="quantity-block__up icon-plus"></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="creating-block__product">
                <div class="creating-block__product-image">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/chains/pitch.png" alt="Фото цепи">
                </div>
                <div class="creating-block__product-price">
                    Цена за шт.:______________
                </div>
                <div class="creating-block__footer">
                    <div class="creating-block__quantity" style="display: none;">
                        <div class="quantity-block quantity-block--small">
                            <button type="button" class="quantity-block__down icon-minus" data-action="minus"></button>
                            <input type="number" name="quantity" class="quantity-block__input" value="1" min="1">
                            <button type="button" class="quantity-block__up icon-plus" data-action="plus"></button>
                        </div>
                    </div>
                    <button type="submit" disabled class="creating-block__product-add-to-cart btn btn-primary">В корзину</button>
                </div>
            </div>
            <div class="creating-block__side">
                <div class="creating-block__classes hidden">
                    <div class="creating-block__classes-title">
                        Что такое класс цепи?
                    </div>
                    <a href="#class-A-info" data-fancybox-saw class="creating-block__classes-item hidden-item">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/classes/class-A.png" alt="Фото класса А">
                    </a>
                    <a href="#class-B-info" data-fancybox-saw class="creating-block__classes-item hidden-item">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/classes/class-B.png" alt="Фото класса B">
                    </a>
                    <a href="#class-C-info" data-fancybox-saw class="creating-block__classes-item hidden-item">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/classes/class-C.png" alt="Фото класса C">
                    </a>
                </div>
                <div class="creating-block__result">
                    <div class="creating-block__result-block">
                        <div class="creating-block__result-title">Шаг:</div>
                        <div class="creating-block__result-value"></div>
                    </div>
                    <div class="creating-block__result-block">
                        <div class="creating-block__result-title">Толщина:</div>
                        <div class="creating-block__result-value"></div>
                    </div>
                    <div class="creating-block__result-block">
                        <div class="creating-block__result-title">Класс:</div>
                        <div class="creating-block__result-value"></div>
                    </div>
                    <div class="creating-block__result-block sm-block">
                        <div class="creating-block__result-title">К-во звеньев:</div>
                        <div class="creating-block__result-value"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php require_once(TEMPLATE_PATH . '_classes-modals.php'); ?>