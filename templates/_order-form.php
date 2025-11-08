<?php
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
        <script>
            $(function() {
                const $orderContainer = $('#order');
                const $deliveryRadios = $orderContainer.find('input[name="delivery_type"]');
                const $paymentRadios = $orderContainer.find('input[name="payment_type"]');
                const $contactRadios = $orderContainer.find('input[name="contact_method"]');


                const $businessControls = $orderContainer.find('[data-type="business"]');
                const $individualControls = $orderContainer.find('[data-type="individual"]');

                const $emailControl = $orderContainer.find('input[name="email"]').closest('.form__controls');
                const $telegramControl = $orderContainer.find('input[name="telegram_user"]').closest('.form__controls');
                const $whatsappControl = $orderContainer.find('.form__group').eq(2).find('.form__group-items > .form__controls').eq(2);

                const $priceProductInput = $('#price_product_input');
                const $priceDeliveryInput = $('#price_delivery_input');
                const $productListInput = $('#product_list_input');
                const $totalPriceInput = $('#total_price_input');
                const $orderDatetimeInput = $('#order_datetime_input');

                const $formTotal = $orderContainer.find('.form__total');
                const $formTotalValue = $orderContainer.find('.form__total-value');
                const $policyLabel = $orderContainer.find('input[name="confirm_policies"]').closest('.form__radio-btn');
                const $policyCheckbox = $orderContainer.find('input[name="confirm_policies"]');
                const $submitButton = $orderContainer.find('input[type="submit"]');

                const $cdekWidgetContainer = $('#cdek-widget-container');
                const $yandexWidgetContainer = $('#yandex-widget-container');

                let cdekWidgetInstance = null;
                let yandexWidgetInitialized = false;

                function disableFields($controls) {
                    $controls.find('input:not([type="radio"]):not([type="checkbox"]), textarea, select').prop('disabled', true);
                }

                function enableFields($controls) {
                    $controls.find('input:not([type="radio"]):not([type="checkbox"]), textarea, select').prop('disabled', false);
                }

                function clearFields($controls) {
                    $controls.find('input, textarea, select').each(function() {
                        const $field = $(this);
                        if ($field.is('input:not([type="radio"]):not([type="checkbox"]), textarea, select')) {
                            $field.val('');
                        }
                        $field.removeClass('wpcf7-not-valid');
                        $field.closest('.wpcf7-form-control-wrap').find('.wpcf7-not-valid-tip').remove();
                    });
                }

                function calculateTotal() {
                    const productPrice = parseInt($priceProductInput.val()) || 0;
                    const deliveryPrice = parseInt($priceDeliveryInput.val()) || 0;
                    const total = productPrice + deliveryPrice;


                    $totalPriceInput.val(total);
                    $formTotalValue.text(total.toLocaleString('ru-RU') + ' ₽');
                }

                function updateOrderData() {

                    const productNames = '<?php echo esc_js($cart_items_list); ?>';
                    const currentProductPrice = <?php echo floatval($woocommerce_total); ?>;

                    const now = new Date();
                    const dateTimeString = now.toLocaleDateString('ru-RU', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    $productListInput.val(productNames);
                    $orderDatetimeInput.val(dateTimeString);
                    $priceProductInput.val(currentProductPrice);

                    calculateTotal();
                }


                function initCdekWidget(isPickup) {
                    if (cdekWidgetInstance) {
                        return;
                    }

                    if (typeof window.CDEKWidget === 'undefined') {
                        console.error('Библиотека CDEKWidget не загружена.');
                        return;
                    }

                    const widgetConfig = {
                        lang: 'rus',
                        currency: 'RUB',
                        from: 'Санкт-Петербург',
                        root: 'cdek-map',
                        apiKey: '',
                        servicePath: '<?php echo get_template_directory_uri(); ?>/services/cdek-service.php',
                        defaultLocation: 'Москва',
                        goods: [{
                            weight: 1000,
                            length: 10,
                            width: 10,
                            height: 10
                        }],
                        debug: true,
                        onChoose(selectedService, selectedTariff, selectedAddress) {
                            if (selectedTariff && selectedTariff.delivery_sum) {

                                $('#price_delivery_input').val(selectedTariff.delivery_sum);
                            }
                            calculateTotal();
                        },
                    };

                    cdekWidgetInstance = new window.CDEKWidget(widgetConfig);
                }

                function initYandexWidget() {
                    if (yandexWidgetInitialized) return;
                    yandexWidgetInitialized = true;
                    // Здесь инициализация виджета Яндекс
                }

                function handleDeliveryChange() {
                    const selectedDelivery = $orderContainer.find('input[name="delivery_type"]:checked').val();

                    $cdekWidgetContainer.addClass('hidden');
                    clearFields($cdekWidgetContainer);
                    disableFields($cdekWidgetContainer);

                    $yandexWidgetContainer.addClass('hidden');
                    clearFields($yandexWidgetContainer);
                    disableFields($yandexWidgetContainer);


                    $priceDeliveryInput.val(0);

                    switch (selectedDelivery) {
                        case 'cdek':
                            $cdekWidgetContainer.removeClass('hidden');
                            enableFields($cdekWidgetContainer);
                            initCdekWidget();
                            break;
                        case 'yandex_pickup':
                            $yandexWidgetContainer.removeClass('hidden');
                            enableFields($yandexWidgetContainer);
                            initYandexWidget();
                            break;
                        case 'pickup_spb':
                            // Самовывоз: доставка = 0
                            break;
                    }

                    calculateTotal();
                    checkFormCompletion();
                }

                function handlePaymentChange() {
                    const selectedPayment = $orderContainer.find('input[name="payment_type"]:checked').val();

                    $businessControls.addClass('hidden');
                    clearFields($businessControls);
                    disableFields($businessControls);

                    $individualControls.addClass('hidden');
                    clearFields($individualControls);
                    disableFields($individualControls);

                    if (selectedPayment === 'individual_card') {
                        $individualControls.removeClass('hidden');
                        enableFields($individualControls);
                    } else if (selectedPayment === 'business_invoice') {
                        $businessControls.removeClass('hidden');
                        enableFields($businessControls);
                    }
                    checkFormCompletion();
                }

                function handleContactChange() {
                    const selectedContact = $orderContainer.find('input[name="contact_method"]:checked').val();

                    $emailControl.addClass('hidden');
                    clearFields($emailControl);
                    disableFields($emailControl);

                    $telegramControl.addClass('hidden');
                    clearFields($telegramControl);
                    disableFields($telegramControl);

                    $whatsappControl.addClass('hidden');
                    clearFields($whatsappControl);
                    disableFields($whatsappControl);

                    switch (selectedContact) {
                        case 'email':
                            $emailControl.removeClass('hidden');
                            enableFields($emailControl);
                            break;
                        case 'telegram':
                            $telegramControl.removeClass('hidden');
                            enableFields($telegramControl);
                            break;
                        case 'whatsapp':
                            $whatsappControl.removeClass('hidden');
                            enableFields($whatsappControl);
                            break;
                    }
                    checkFormCompletion();
                }

                function checkFormCompletion() {
                    const isDeliverySelected = $orderContainer.find('input[name="delivery_type"]:checked').length > 0;
                    const isPaymentSelected = $orderContainer.find('input[name="payment_type"]:checked').length > 0;
                    const isContactSelected = $orderContainer.find('input[name="contact_method"]:checked').length > 0;

                    if (isDeliverySelected && isPaymentSelected && isContactSelected) {
                        $formTotal.removeClass('hidden');
                        $policyLabel.removeClass('hidden');
                    } else {
                        $formTotal.addClass('hidden');
                        $policyLabel.addClass('hidden');
                        $policyCheckbox.prop('checked', false);
                        handlePolicyChange();
                    }
                }

                function handlePolicyChange() {
                    $submitButton.prop('disabled', !$policyCheckbox.prop('checked'));
                }


                updateOrderData();

                $deliveryRadios.on('change', handleDeliveryChange);
                $paymentRadios.on('change', handlePaymentChange);
                $contactRadios.on('change', handleContactChange);
                $policyCheckbox.on('change', handlePolicyChange);

                $priceDeliveryInput.on('change', calculateTotal);


                handleDeliveryChange();
                handlePaymentChange();
                handleContactChange();
                handlePolicyChange();
            });
        </script>
    </div>
</div>