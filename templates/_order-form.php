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

        <form action="#" aria-label="Форма заказа">

            <input type="hidden" name="order_datetime" id="order_datetime_input">
            <input type="hidden" name="total_price" id="total_price_input">
            <input type="hidden" name="price_product" id="price_product_input" value="50">
            <input type="hidden" name="price_delivery" id="price_delivery_input" value="0">
            <input type="hidden" name="order_delivery_address" id="order_delivery_address">
            <input type="hidden" name="product_list" id="product_list_input">

            <!-- cdek fields -->
            <input type="hidden" name="cdek_tariff_code" id="cdek_tariff_code">
            <input type="hidden" name="cdek_city_code" id="cdek_city_code">
            <input type="hidden" name="cdek_pvz_code" id="cdek_pvz_code">
            <!-- cdek fields -->

            <fieldset class="form__group">
                <legend class="form__group-caption">
                    Выберите тип доставки
                </legend>
                <div class="form__group-items">
                    <label class="form__radio-btn">
                        <input type="radio" name="delivery_type" value="yandex_pickup" class="form__radio-btn-input hidden" hidden>
                        <span class="form__radio-btn-field">Яндекс Доставка — Доставка до ПВЗ</span>
                    </label>
                    <label class="form__radio-btn">
                        <input type="radio" name="delivery_type" value="cdek" class="form__radio-btn-input hidden" hidden>
                        <span class="form__radio-btn-field">СДЭК — Доставка</span>
                    </label>
                    <label class="form__radio-btn">
                        <input type="radio" name="delivery_type" value="pickup_spb" class="form__radio-btn-input hidden" hidden>
                        <span class="form__radio-btn-field">Самовывоз (СПБ и ЛО)</span>
                    </label>
                    <div class="form__widget hidden" id="cdek-widget-container">
                        <div id="cdek-map" style="width:100%; height: 600px;"></div>
                    </div>
                    <div class="form__widget hidden" id="yandex-widget-container">
                        <div id="yandex-delivery-widget"></div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form__group">
                <legend class="form__group-caption">
                    Выберите тип оплаты
                </legend>
                <div class="form__group-items">
                    <label class="form__radio-btn">
                        <input type="radio" name="payment_type" value="individual_card" class="form__radio-btn-input hidden" hidden>
                        <span class="form__radio-btn-field">Я — физическое лицо (оплата картой)</span>
                    </label>
                    <label class="form__radio-btn">
                        <input type="radio" name="payment_type" value="business_invoice" class="form__radio-btn-input hidden" hidden>
                        <span class="form__radio-btn-field">Я — юридическое лицо или ИП (оплата по счёту)</span>
                    </label>

                    <div class="form__controls hidden" data-type="business">
                        <input class="form__control" placeholder="Введите ИНН" type="number" name="inn" disabled>
                        <input class="form__control" placeholder="Введите название организации" type="text" name="organization_name" disabled>
                        <input class="form__control" placeholder="Введите юридический адрес" type="text" name="legal_address" disabled>
                        <input class="form__control" placeholder="Введите фамилию" type="text" name="last_name_business" disabled>
                        <input class="form__control" placeholder="Введите имя" type="text" name="first_name_business" disabled>
                        <input class="form__control" placeholder="Введите отчество" type="text" name="middle_name_business" disabled>
                        <input class="form__control" placeholder="+7 (___) ___-__-__" type="tel" name="phone_business" disabled>
                    </div>

                    <div class="form__controls hidden" data-type="individual">
                        <input class="form__control" placeholder="Введите фамилию" type="text" name="last_name_individual" disabled>
                        <input class="form__control" placeholder="Введите имя" type="text" name="first_name_individual" disabled>
                        <input class="form__control" placeholder="Введите отчество" type="text" name="middle_name_individual" disabled>
                        <input class="form__control" placeholder="+7 (___) ___-__-__" type="tel" name="phone_individual" disabled>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form__group">
                <legend class="form__group-caption">
                    Выберите предпочтительный способ связи
                </legend>
                <div class="form__group-items">
                    <label class="form__radio-btn">
                        <input type="radio" name="contact_method" value="email" class="form__radio-btn-input hidden" hidden>
                        <span class="form__radio-btn-field">Электронная почта</span>
                    </label>
                    <label class="form__radio-btn">
                        <input type="radio" name="contact_method" value="telegram" class="form__radio-btn-input hidden" hidden>
                        <span class="form__radio-btn-field">Telegram</span>
                    </label>
                    <label class="form__radio-btn">
                        <input type="radio" name="contact_method" value="whatsapp" class="form__radio-btn-input hidden" hidden>
                        <span class="form__radio-btn-field">Whatsapp</span>
                    </label>

                    <div class="form__controls hidden" data-type="email">
                        <input class="form__control" placeholder="Введите адрес электронной почты" type="email" name="email" disabled>
                    </div>

                    <div class="form__controls hidden" data-type="telegram">
                        <input class="form__control" placeholder="Введите имя пользователя (@example)" type="text" name="telegram_user" disabled>
                    </div>

                    <div class="form__controls hidden" data-type="whatsapp">
                        <input class="form__control" placeholder="Введите номер телефона (+7 (___) ___-__-__)" type="tel" name="whatsapp_phone" disabled>
                    </div>
                </div>
            </fieldset>

            <div class="form__total hidden">
                <div class="form__total-caption">Итого:</div>
                <div class="form__total-value">0 ₽</div>
            </div>

            <div class="form__footer">
                <label class="form__radio-btn hidden">
                    <input type="checkbox" name="confirm_policies" class="form__radio-btn-input hidden" hidden>
                    <span class="form__radio-btn-field sm-text">
                        Соглашаюсь на <a href="/for-buyers#policy-politika-v-otnoshenii-obrabotki-personalnyh-dannyh">обработку персональных данных</a> в соответствии с политикой конфиденциальности и с <a href="/for-buyers#policy-obschie-polozheniya-i-predmet-publichnoy-oferty">условиями оферты</a>
                    </span>
                </label>
                <label class="form__submit btn btn-primary">
                    <input class="btn btn-primary" type="submit" value="Оставить заявку" disabled>
                </label>
            </div>
        </form>

    </div>
</div>

<div id="success-popup" class="popup">
    <div class="popup__content">
        <h3 class="popup__title title">Спасибо за ваш заказ!</h3>
        <p class="popup__subtitle">Ваша заявка успешно отправлена. Мы свяжемся с вами в ближайшее время для подтверждения деталей.</p>
        <button class="popup__btn btn btn-primary" data-fancybox-close>Закрыть</button>
    </div>
</div>