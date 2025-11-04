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
                            <button type="button" class="quantity-block__down icon-minus"></button>
                            <input type="number" name="quantity" class="quantity-block__input" value="1">
                            <button type="button" class="quantity-block__up icon-plus"></button>
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
                <button type="submit" disabled class="creating-block__product-add-to-cart btn btn-primary">В корзину</button>
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
        </form>
        <script>
            const chainConfig = <?php
                                $product_id = 127;
                                $rows = get_chain_combinations_data($product_id);
                                $config = build_chain_config($rows);
                                echo json_encode($config);
                                ?>;

            jQuery(function($) {
                const chainConfigurator = {
                    data: chainConfig,
                    state: {
                        pitch: null,
                        thickness: null,
                        class: null,
                        quantity: 1,
                        currentStep: 1
                    },
                    selectedProduct: null,
                    availabilityChecked: false,
                    available: false
                };

                const $form = $('.creating-block__content');
                const stepKeys = ['pitch', 'thickness', 'class', 'quantity'];
                const $quizItems = $('.creating-quiz__item');
                const $quantityInput = $('.quantity-block__input');
                const $addToCartButton = $('.creating-block__product-add-to-cart');

                function updateState(key, value) {
                    const keyIndex = stepKeys.indexOf(key);
                    const isValueChanging = chainConfigurator.state[key] !== value;
                    chainConfigurator.state[key] = value;
                    if (keyIndex < 3 && isValueChanging) {
                        for (let i = keyIndex + 1; i < stepKeys.length - 1; i++) {
                            chainConfigurator.state[stepKeys[i]] = null;
                            $quizItems.eq(i).find('.creating-quiz__body').empty();
                        }
                        chainConfigurator.selectedProduct = null;
                        $('.creating-block__product-image').html('<img src="<?php echo get_template_directory_uri(); ?>/assets/img/chains/pitch.png" alt="Фото цепи">');
                        $('.creating-block__product-price').html('Цена за шт.:______________');
                        $addToCartButton.prop('disabled', true);
                        $('#product_id_selected').val('');
                    } else if (key === 'quantity') {
                        if (chainConfigurator.selectedProduct) {
                            findCombinationAndPrice(true);
                        }
                    }
                    updateResultsDisplay();
                }

                function getFilteredOptions(stepKey) {
                    const options = chainConfigurator.data.steps[stepKey].options;
                    const state = chainConfigurator.state;
                    if (stepKey === 'pitch' || stepKey === 'quantity') {
                        return options;
                    }
                    if (stepKey === 'thickness') {
                        const availableThicknesses = chainConfigurator.data.combinations
                            .filter(item => item.pitch === state.pitch)
                            .map(item => item.thickness);
                        return options.filter(opt => availableThicknesses.includes(opt.value));
                    }
                    if (stepKey === 'class') {
                        const availableClasses = chainConfigurator.data.combinations
                            .filter(item => item.pitch === state.pitch && item.thickness === state.thickness)
                            .map(item => item.class);
                        return options.filter(opt => availableClasses.includes(opt.value));
                    }
                    return options;
                }

                function renderStepOptions(stepKey, $body) {
                    const options = getFilteredOptions(stepKey);
                    let html = '<div class="creating-quiz__items">';
                    if (options.length === 0) {
                        html += '<p>Нет доступных опций для текущей комбинации.</p>';
                        $body.html(html + '</div>');
                        return;
                    }
                    options.forEach(option => {
                        const checked = option.value === chainConfigurator.state[stepKey] ? 'checked' : '';
                        const label = option.label || option.value;
                        html += `
                            <label class="radio-btn">
                                <input type="radio" name="${stepKey}" value="${option.value}" class="radio-btn__input hidden" hidden ${checked}>
                                <span class="radio-btn__btn">${label}</span>
                            </label>
                        `;
                    });
                    $body.html(html + '</div>');
                }

                function renderStep(stepIndex) {
                    $quizItems.removeClass('active');
                    const $currentStepEl = $quizItems.eq(stepIndex - 1);
                    $currentStepEl.addClass('active');
                    chainConfigurator.state.currentStep = stepIndex;
                    if (stepIndex <= 3) {
                        const stepKey = stepKeys[stepIndex - 1];
                        renderStepOptions(stepKey, $currentStepEl.find('.creating-quiz__body'));
                    }
                    if (stepIndex < 4 || !chainConfigurator.selectedProduct) {
                        $addToCartButton.prop('disabled', true);
                    } else if (chainConfigurator.selectedProduct) {
                        $addToCartButton.prop('disabled', false);
                    }
                }

                function updateResultsDisplay() {
                    $('.creating-block__result-block').each(function(index) {
                        const key = stepKeys[index];
                        let value = chainConfigurator.state[key];
                        if (index < 3 && value) {
                            const option = chainConfigurator.data.steps[key].options.find(opt => opt.value === value);
                            value = option ? option.label : value;
                        }
                        $(this).find('.creating-block__result-value').text(value || '');
                    });
                }

                function findCombinationAndPrice(isQuantityOnly = false) {
                    const state = chainConfigurator.state;
                    if (!state.pitch || !state.thickness || !state.class) {
                        chainConfigurator.selectedProduct = null;
                        $('#product_id_selected').val('');
                        $('.creating-block__product-price').html('Цена за шт.:______________');
                        $addToCartButton.prop('disabled', true);
                        return;
                    }
                    const selectedProduct = chainConfigurator.data.combinations.find(item =>
                        item.pitch === state.pitch &&
                        item.thickness === state.thickness &&
                        item.class === state.class
                    );

                    if (selectedProduct) {
                        chainConfigurator.selectedProduct = selectedProduct;

                        // ИСПОЛЬЗУЕМ variation_id ВМЕСТО product_id
                        const productID = selectedProduct.variation_id;

                        if (typeof productID === 'undefined' || productID === null || productID === '') {
                            console.error('Ошибка: variation_id не определен для найденной комбинации.');
                            $('.creating-block__product-price').html('Цена за шт.: **Ошибка ID**');
                            $addToCartButton.prop('disabled', true);
                            $('#product_id_selected').val('');
                            return;
                        }

                        $('#product_id_selected').val(productID);

                        $.ajax({
                            url: $form.attr('action'),
                            type: 'POST',
                            data: {
                                action: 'get_product_data',
                                product_id: productID,
                                quantity: state.quantity
                            },
                            success: function(response) {
                                if (response.success && response.data) {
                                    updateProductDisplay(response.data, selectedProduct);
                                } else {
                                    console.error('Ошибка WC AJAX (нет данных/нет успеха):', response);
                                    $('.creating-block__product-price').html('Цена за шт.: **Нет в наличии**');
                                    $addToCartButton.prop('disabled', true);
                                    $('#product_id_selected').val('');
                                }
                            },
                            error: function() {
                                console.error('Ошибка AJAX-запроса цены.');
                                $addToCartButton.prop('disabled', true);
                            }
                        });
                    } else {
                        chainConfigurator.selectedProduct = null;
                        $('.creating-block__product-price').html('Цена за шт.: **Комбинация недоступна**');
                        $addToCartButton.prop('disabled', true);
                        $('#product_id_selected').val('');
                    }
                }

                function updateProductDisplay(productData, selected) {
                    const $productBlock = $('.creating-block__product');
                    // Используем заглушку, если image не пришел
                    const imageSrc = selected.image && selected.image !== '' ? selected.image : '<?php echo get_template_directory_uri(); ?>/assets/img/chains/pitch.png';

                    $productBlock.find('.creating-block__product-image').html(
                        `<img src="${imageSrc}" alt="Цепь">`
                    );

                    $productBlock.find('.creating-block__product-price').html(
                        `Цена за шт.: **${productData.price_html}**`
                    );
                    if (chainConfigurator.state.currentStep >= 4) {
                        $addToCartButton.prop('disabled', false);
                    }
                }

                $('.creating-block__quiz').on('change', 'input[type="radio"]', function() {
                    const $input = $(this);
                    const stepKey = $input.attr('name');
                    const newValue = $input.val();
                    const currentStepIndex = $quizItems.index($input.closest('.creating-quiz__item')) + 1;
                    updateState(stepKey, newValue);
                    let nextStepIndex = currentStepIndex + 1;
                    if (stepKey === 'class') {
                        findCombinationAndPrice();
                    }
                    if (nextStepIndex <= $quizItems.length) {
                        renderStep(nextStepIndex);
                    }
                });

                $('.creating-quiz__back').on('click', function() {
                    const $currentStepEl = $(this).closest('.creating-quiz__item');
                    const currentStepIndex = $quizItems.index($currentStepEl) + 1;
                    if (currentStepIndex > 1) {
                        renderStep(currentStepIndex - 1);
                    }
                });

                $('.quantity-block').on('click', '.quantity-block__up, .quantity-block__down', function() {
                    let currentQuantity = parseInt($quantityInput.val()) || 1;
                    const isPlus = $(this).hasClass('icon-plus');
                    if (isPlus) {
                        currentQuantity++;
                    } else if (currentQuantity > 1) {
                        currentQuantity--;
                    }
                    $quantityInput.val(currentQuantity);
                    updateState('quantity', currentQuantity);
                    if (chainConfigurator.selectedProduct) {
                        findCombinationAndPrice(true);
                    }
                });

                $addToCartButton.on('click', function(e) {
                    e.preventDefault();
                    const productID = $('#product_id_selected').val();
                    const quantity = chainConfigurator.state.quantity;
                    if (!productID) {
                        alert('Сначала выберите все параметры продукта.');
                        return;
                    }

                    // Использование стандартного WP AJAX URL
                    const ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : '<?php echo admin_url('admin-ajax.php'); ?>';

                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            // Правильный экшен для добавления в корзину WooCommerce
                            action: 'woocommerce_add_to_cart',
                            product_id: productID,
                            quantity: quantity
                        },
                        success: function(response) {
                            // В отличие от woocommerce_ajax_add_to_cart, woocommerce_add_to_cart может вернуть не JSON,
                            // но мы полагаемся на стандартный триггер для обновления корзины.
                            if (response.error || !response.fragments) {
                                console.error('Ошибка добавления в корзину:', response);
                                alert('Ошибка при добавлении в корзину.');
                            } else {
                                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
                                alert('Товар добавлен в корзину!');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Ошибка при отправке запроса добавления в корзину:', status, error, xhr.responseText);
                            alert('Ошибка при отправке запроса добавления в корзину.');
                        }
                    });
                });

                renderStep(1);
                updateResultsDisplay();
            })
        </script>
    </div>
</div>