jQuery(function ($) {
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
    const $orderDeliveryAddress = $('#order_delivery_address');

    const $formTotal = $orderContainer.find('.form__total');
    const $formTotalValue = $orderContainer.find('.form__total-value');
    const $policyLabel = $orderContainer.find('input[name="confirm_policies"]').closest('.form__radio-btn');
    const $policyCheckbox = $orderContainer.find('input[name="confirm_policies"]');
    const $submitButton = $orderContainer.find('input[type="submit"]');

    const $cdekWidgetContainer = $('#cdek-widget-container');
    const $yandexWidgetContainer = $('#yandex-widget-container');

    const $successPopup = $('#success-popup');
    const $formElement = $orderContainer.find('form');

    let cdekWidgetInstance = null;
    let yandexWidgetInitialized = false;

    function disableFields($controls) {
        $controls.find('input:not([type="radio"]):not([type="checkbox"]), textarea, select').prop('disabled', true);
    }

    function enableFields($controls) {
        $controls.find('input:not([type="radio"]):not([type="checkbox"]), textarea, select').prop('disabled', false);
    }

    function clearFields($controls) {
        $controls.find('input, textarea, select').each(function () {
            const $field = $(this);
            if ($field.is('input:not([type="radio"]):not([type="checkbox"]), textarea, select')) {
                $field.val('');
            }
        });
    }

    function calculateTotal() {
        const productPrice = parseFloat($priceProductInput.val()) || 0;
        const deliveryPrice = parseFloat($priceDeliveryInput.val()) || 0;
        const total = productPrice + deliveryPrice;

        $totalPriceInput.val(total.toFixed(2));
        $formTotalValue.text(total.toLocaleString('ru-RU', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }) + ' ₽');
    }

    function updateOrderData() {
        if (typeof OrderPopupData === 'undefined') return;

        const productNames = OrderPopupData.cart_items_list;
        const currentProductPrice = parseFloat(OrderPopupData.woocommerce_total);

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
        $priceProductInput.val(currentProductPrice.toFixed(2));

        calculateTotal();
    }

    function initCdekWidget() {
        if (cdekWidgetInstance) return;
        if (typeof window.CDEKWidget === 'undefined') return;

        const widgetConfig = {
            lang: 'rus',
            currency: 'RUB',
            from: 'Санкт-Петербург',
            root: 'cdek-map',
            apiKey: OrderPopupData.cdek_api_key,
            servicePath: OrderPopupData.cdek_service_path,
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
                    $priceDeliveryInput.val(selectedTariff.delivery_sum);
                    $('#cdek_tariff_code').val(selectedTariff.tariff_code);
                }

                if (selectedAddress) {
                    $orderDeliveryAddress.val(selectedAddress.formatted);

                    if (selectedAddress.city) {
                        $('#cdek_city_code').val(selectedAddress.city);
                    }
                }

                if (selectedService && selectedService.pvz) {
                    $('#cdek_pvz_code').val(selectedService.pvz);
                } else {
                    $('#cdek_pvz_code').val('');
                }

                calculateTotal();
            },
        };

        cdekWidgetInstance = new window.CDEKWidget(widgetConfig);
    }

    function initYandexWidget() {
        if (yandexWidgetInitialized) return;

        if (typeof window.YaDelivery === 'undefined') {
            document.addEventListener('YaNddWidgetLoad', createYandexWidget);
        } else {
            createYandexWidget();
        }
        yandexWidgetInitialized = true;
    }

    function createYandexWidget() {
        window.YaDelivery.createWidget({
            containerId: 'yandex-delivery-widget',
            params: {
                city: "Москва",
                size: {
                    "height": "600px",
                    "width": "100%"
                },
                source_platform_station: OrderPopupData.yandex_platform_id,
                physical_dims_weight_gross: 10000,
                delivery_price: (price) => price + " руб",
                delivery_term: 3,
                show_select_button: false,
                filter: {
                    type: ["pickup_point", "terminal"],
                    is_yandex_branded: false,
                    payment_methods: ["already_paid", "card_on_receipt"],
                    payment_methods_filter: "or"
                }
            },
        });
    }

    document.addEventListener('YaNddWidgetPointSelected', function (data) {
        const fullAddress = data.detail.address.full_address;
        const pointId = data.detail.id;
        const priceElement = document.querySelector(`span[data-pickpoint-id="${pointId}"]`);

        if (priceElement) {
            setTimeout(() => {
                const priceText = priceElement.innerText;
                const priceMatch = priceText.match(/(\d+)\s*руб/);
                if (priceMatch) {
                    const deliveryPrice = parseFloat(priceMatch[1]);
                    $priceDeliveryInput.val(deliveryPrice.toFixed(2));
                    calculateTotal();
                }
            }, 5000)
        }

        $orderDeliveryAddress.val(fullAddress);
        calculateTotal();
    });

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

    function handleFormSubmit(e) {
        e.preventDefault();

        const formData = new FormData($formElement[0]);
        formData.append('action', 'send_order_form');


        if (typeof OrderPopupData !== 'undefined' && OrderPopupData.cart_items) {

            formData.append('cart_items', JSON.stringify(OrderPopupData.cart_items));
        } else {

            alert('Ошибка: Не удалось получить данные о товарах для отправки.');
            $submitButton.prop('disabled', false);
            return;
        }

        $submitButton.prop('disabled', true);

        $.ajax({
            url: OrderPopupData.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    $(document.body).trigger('wc_fragment_refresh');

                    if (typeof Fancybox !== 'undefined') {
                        Fancybox.close();
                        Fancybox.show([{
                            src: '#success-popup',
                            type: 'inline'
                        }]);
                    } else {
                        $orderContainer.hide();
                        $successPopup.show();
                    }

                    $formElement[0].reset();
                    $deliveryRadios.prop('checked', false);
                    $paymentRadios.prop('checked', false);
                    $contactRadios.prop('checked', false);

                    handleDeliveryChange();
                    handlePaymentChange();
                    handleContactChange();
                    updateOrderData();
                } else {
                    alert('Ошибка: ' + (response.data.message || 'Не удалось отправить заявку'));
                }
            },
            error: function () {
                alert('Произошла ошибка при отправке данных.');
            },
            complete: function () {
                $submitButton.prop('disabled', false);
            }
        });
    }

    function handleSuccessPopupClose(e) {
        e.preventDefault();
        $submitButton.prop('disabled', true);
        if (typeof Fancybox !== 'undefined') {
            Fancybox.close();
        } else {
            $successPopup.hide();
        }
    }

    function preventWidgetSubmit() {
        $orderContainer.on('click', '#yandex-widget-container button, #cdek-widget-container button', function (e) {
            if (!$(this).attr('type')) {
                e.preventDefault();
            }
        });
    }

    preventWidgetSubmit();
    updateOrderData();

    $deliveryRadios.on('change', handleDeliveryChange);
    $paymentRadios.on('change', handlePaymentChange);
    $contactRadios.on('change', handleContactChange);
    $policyCheckbox.on('change', handlePolicyChange);
    $priceDeliveryInput.on('change', calculateTotal);

    $formElement.on('submit', handleFormSubmit);

    $('#success-popup').on('click', '.popup__btn', handleSuccessPopupClose);

    $(document).on('refresh_order_data', function () {
        updateOrderData();
    });

    handleDeliveryChange();
    handlePaymentChange();
    handleContactChange();
    handlePolicyChange();
});