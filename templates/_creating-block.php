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
                <div class="creatin-block__quantity" style="display: none;">
                    <div class="quantity-block quantity-block--small">
                        <button type="button" class="quantity-block__down icon-minus" data-action="minus"></button>
                        <input type="number" name="quantity" class="quantity-block__input" value="1" min="1">
                        <button type="button" class="quantity-block__up icon-plus" data-action="plus"></button>
                    </div>
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
            $(function() {
                const chainConfig = <?php
                                    $product_id = 127;
                                    $rows = get_chain_combinations_data($product_id);
                                    $config = build_chain_config($rows);
                                    echo json_encode($config);
                                    ?>;

                const chainConfigurator = {
                    data: chainConfig,
                    state: {
                        pitch: null,
                        thickness: null,
                        class: null,
                        links_count: null,
                        quantity: 1,
                        currentStep: 1
                    },
                    selectedProduct: null
                };

                const $form = $('.creating-block__content');
                const stepKeys = ['pitch', 'thickness', 'class', 'links_count'];
                const $quizItems = $('.creating-quiz__item');
                const $quantityLinksQuantityInput = $('#links_quantity');
                const $addToCartButton = $('.creating-block__product-add-to-cart');
                const $productQuantityBlock = $('.creatin-block__quantity');
                const $productQuantityInput = $productQuantityBlock.find('input[name="quantity"]');
                const defaultImageSrc = '<?php echo get_template_directory_uri(); ?>/assets/img/chains/pitch.png';

                // -------------------
                // Функции конфигуратора
                // -------------------

                function resetSubsequentSteps(startIndex) {
                    for (let i = startIndex; i < stepKeys.length; i++) {
                        chainConfigurator.state[stepKeys[i]] = null;

                        if (i < 3) {
                            $quizItems.eq(i).find('.creating-quiz__body').empty();
                        }

                        $quizItems.eq(i).removeClass('completed');
                    }

                    if (startIndex <= stepKeys.length - 1) {
                        $quantityLinksQuantityInput.val('');
                    }

                    chainConfigurator.selectedProduct = null;
                    resetProductDisplay();
                }

                function updateState(key, value) {
                    const keyIndex = stepKeys.indexOf(key);
                    const isValueChanging = chainConfigurator.state[key] !== value;

                    chainConfigurator.state[key] = value;

                    if (keyIndex < 3 && isValueChanging) {
                        resetSubsequentSteps(keyIndex + 1);
                    }

                    updateResultsDisplay();

                    if (value !== null && keyIndex < 3) {
                        $quizItems.eq(keyIndex).addClass('completed');
                    } else if (key === 'links_count' && chainConfigurator.selectedProduct) {
                        if (parseInt(value) > 0) {
                            $quizItems.eq(keyIndex).addClass('completed');
                        } else {
                            $quizItems.eq(keyIndex).removeClass('completed');
                        }
                    }
                }

                function getFilteredOptions(stepKey) {
                    const options = chainConfigurator.data.steps[stepKey].options;
                    const state = chainConfigurator.state;
                    if (stepKey === 'pitch' || stepKey === 'links_count') return options;

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

                    $('.creating-quiz__back').prop('disabled', false);

                    if (stepIndex <= 3) {
                        const stepKey = stepKeys[stepIndex - 1];
                        if (stepIndex === 1 || chainConfigurator.state[stepKeys[stepIndex - 2]]) {
                            renderStepOptions(stepKey, $currentStepEl.find('.creating-quiz__body'));
                        } else {
                            $currentStepEl.find('.creating-quiz__body').html('<p>Сначала выберите опцию в предыдущем шаге.</p>');
                        }
                    } else if (stepIndex === 4) {
                        if (chainConfigurator.selectedProduct) {
                            const min = chainConfigurator.selectedProduct.countLinksMin || 1;
                            const max = chainConfigurator.selectedProduct.countLinksMax || Infinity;

                            let currentQuantityLinks = parseInt($quantityLinksQuantityInput.val());

                            if (chainConfigurator.state.links_count === null || isNaN(currentQuantityLinks) || currentQuantityLinks < min || currentQuantityLinks > max) {
                                currentQuantityLinks = min;
                                chainConfigurator.state.links_count = currentQuantityLinks;
                                $quantityLinksQuantityInput.val(currentQuantityLinks);
                            }

                            $quantityLinksQuantityInput.attr({
                                'min': min,
                                'max': max
                            });

                            updateQuantityButtons(min, max);
                            updateResultsDisplay();

                            if (currentQuantityLinks > 0) {
                                $quizItems.eq(stepIndex - 1).addClass('completed');
                            }
                        } else {
                            $currentStepEl.find('.creating-quiz__body').html('<p>Сначала выберите все параметры (Шаг 1-3).</p>');
                        }
                    }

                    $addToCartButton.prop('disabled', !(chainConfigurator.selectedProduct && stepIndex === 4));
                }

                function updateResultsDisplay() {
                    $('.creating-block__result-block').each(function(index) {
                        const key = stepKeys[index];
                        let value = chainConfigurator.state[key];

                        if (index < 3 && value) {
                            const option = chainConfigurator.data.steps[key].options.find(opt => opt.value === value);
                            value = option ? option.label : value;
                        }

                        if (key === 'links_count') {
                            value = chainConfigurator.state.links_count !== null ? chainConfigurator.state.links_count : '';
                        }

                        $(this).find('.creating-block__result-value').text(value || '');
                    });
                }

                function findCombinationAndPrice(isQuantityOnly = false) {
                    const state = chainConfigurator.state;

                    if (!state.pitch || !state.thickness || !state.class) {
                        chainConfigurator.selectedProduct = null;
                        $('#product_id_selected').val('');
                        resetProductDisplay();
                        return;
                    }

                    const selectedProduct = chainConfigurator.data.combinations.find(item =>
                        item.pitch === state.pitch &&
                        item.thickness === state.thickness &&
                        item.class === state.class
                    );

                    if (selectedProduct) {
                        const isProductChanging = !chainConfigurator.selectedProduct ||
                            chainConfigurator.selectedProduct.variation_id !== selectedProduct.variation_id;

                        chainConfigurator.selectedProduct = selectedProduct;
                        const productID = selectedProduct.variation_id;

                        if (!productID) {
                            console.error('Ошибка: variation_id не определен.');
                            $('.creating-block__product-price').html('Цена за шт.: Ошибка ID');
                            $addToCartButton.prop('disabled', true);
                            $('#product_id_selected').val('');
                            return;
                        }

                        $('#product_id_selected').val(productID);

                        const minQuantityLinks = selectedProduct.countLinksMin || 1;
                        const maxQuantityLinks = selectedProduct.countLinksMax || 100;

                        if (isProductChanging) {
                            chainConfigurator.state.links_count = minQuantityLinks;
                            $quantityLinksQuantityInput.val(minQuantityLinks);
                        }

                        let currentQuantityLinks = parseInt($quantityLinksQuantityInput.val());

                        if (isNaN(currentQuantityLinks) || currentQuantityLinks < minQuantityLinks) currentQuantityLinks = minQuantityLinks;
                        if (currentQuantityLinks > maxQuantityLinks) currentQuantityLinks = maxQuantityLinks;

                        chainConfigurator.state.links_count = currentQuantityLinks;

                        $quantityLinksQuantityInput.attr({
                            'value': currentQuantityLinks,
                            'min': minQuantityLinks,
                            'max': maxQuantityLinks
                        });

                        updateResultsDisplay();
                        updateQuantityButtons(minQuantityLinks, maxQuantityLinks);

                        // AJAX-запрос
                        $.ajax({
                            url: $form.attr('action'),
                            type: 'POST',
                            data: {
                                action: 'get_product_data',
                                product_id: productID,
                                quantity: chainConfigurator.state.quantity
                            },
                            success: function(response) {
                                if (response.success && response.data) {
                                    updateProductDisplay(response.data, selectedProduct);
                                } else {
                                    console.error('Ошибка WC AJAX:', response);
                                    $('.creating-block__product-price').html('Нет в наличии');
                                    $addToCartButton.prop('disabled', true);
                                    $('#product_id_selected').val('');
                                }
                            },
                            error: function() {
                                console.error('Ошибка AJAX-запроса цены.');
                                $addToCartButton.prop('disabled', true);
                            }
                        });


                        if (chainConfigurator.state.links_count > 0) {
                            $quizItems.eq(3).addClass('completed');
                        } else {
                            $quizItems.eq(3).removeClass('completed');
                        }
                        $addToCartButton.prop('disabled', chainConfigurator.state.currentStep !== 4);

                    } else {
                        chainConfigurator.selectedProduct = null;
                        $('.creating-block__product-price').html('Комбинация недоступна');
                        $addToCartButton.prop('disabled', true);
                        $('#product_id_selected').val('');
                        $quizItems.eq(3).removeClass('completed');
                        resetProductDisplay();
                    }
                }

                function updateProductDisplay(productData, selected) {
                    const $productBlock = $('.creating-block__product');
                    const $imageContainer = $productBlock.find('.creating-block__product-image');
                    const currentImageSrc = $imageContainer.find('img').attr('src');

                    const newImageSrc = selected.image && selected.image !== '' ? selected.image : defaultImageSrc;

                    if (currentImageSrc !== newImageSrc) {
                        $imageContainer.html(`<img src="${newImageSrc}" alt="Цепь">`);
                    }

                    $productBlock.find('.creating-block__product-price').html(`Цена за шт.: ${productData.price_html}`);

                    $productQuantityBlock.show();

                    if (chainConfigurator.state.currentStep === 4) {
                        $addToCartButton.prop('disabled', false);
                    }
                }

                function resetProductDisplay() {
                    const $imageContainer = $('.creating-block__product-image');
                    const currentImageSrc = $imageContainer.find('img').attr('src');
                    if (currentImageSrc !== defaultImageSrc) {
                        $imageContainer.html(`<img src="${defaultImageSrc}" alt="Фото цепи">`);
                    }
                    $('.creating-block__product-price').html('Цена за шт.:______________');
                    $addToCartButton.prop('disabled', true);
                    $productQuantityBlock.hide();
                }

                function resetConfigurator() {
                    chainConfigurator.state = {
                        pitch: null,
                        thickness: null,
                        class: null,
                        links_count: null,
                        quantity: 1,
                        currentStep: 1
                    };
                    chainConfigurator.selectedProduct = null;
                    $quizItems.find('.creating-quiz__body').empty();
                    $quizItems.removeClass('completed');
                    $quantityLinksQuantityInput.val('');
                    $productQuantityInput.val(1);
                    $('#product_id_selected').val('');
                    resetProductDisplay();
                    renderStep(1);
                    updateResultsDisplay();
                }

                function updateQuantityButtons(min, max) {
                    const currentQuantityLinks = parseInt($quantityLinksQuantityInput.val());
                    $quantityLinksQuantityInput.parent().find('.quantity-block__down').prop('disabled', isNaN(currentQuantityLinks) || currentQuantityLinks <= min);
                    $quantityLinksQuantityInput.parent().find('.quantity-block__up').prop('disabled', isNaN(currentQuantityLinks) || currentQuantityLinks >= max);
                }

                // -------------------
                // События
                // -------------------
                $('.creating-block__quiz').on('change', 'input[type="radio"]', function() {
                    const $input = $(this);
                    const stepKey = $input.attr('name');
                    const newValue = $input.val();
                    const currentStepIndex = $quizItems.index($input.closest('.creating-quiz__item')) + 1;

                    updateState(stepKey, newValue);

                    if (currentStepIndex === 3) {
                        findCombinationAndPrice();
                    }

                    const nextStepIndex = currentStepIndex + 1;
                    if (nextStepIndex <= $quizItems.length) {
                        renderStep(nextStepIndex);
                    }
                });

                $('.creating-quiz__header').on('click', '.creating-quiz__back', function() {
                    const $currentActiveStep = $(this).closest('.creating-quiz__item');
                    const currentStepIndex = $quizItems.index($currentActiveStep) + 1;

                    if (currentStepIndex > 1) {
                        const prevStepIndex = currentStepIndex - 1;
                        const currentKeyIndex = currentStepIndex - 1;

                        if (prevStepIndex <= 3) {
                            chainConfigurator.state[stepKeys[currentKeyIndex]] = null;
                            resetSubsequentSteps(currentKeyIndex);
                        } else if (prevStepIndex === 3) {
                            chainConfigurator.state.links_count = null;
                            $quizItems.eq(3).removeClass('completed');
                        }

                        renderStep(prevStepIndex);
                        updateResultsDisplay();
                    }
                });


                $('#links_quantity').closest('.quantity-block').on('click', '.quantity-block__up, .quantity-block__down', function() {
                    let currentQuantityLinks = parseInt($quantityLinksQuantityInput.val()) || 0;
                    const isPlus = $(this).hasClass('icon-plus');

                    let min = parseInt($quantityLinksQuantityInput.attr('min')) || 1;
                    let max = parseInt($quantityLinksQuantityInput.attr('max')) || Infinity;

                    if (isPlus && currentQuantityLinks < max) {
                        currentQuantityLinks++;
                    } else if (!isPlus && currentQuantityLinks > min) {
                        currentQuantityLinks--;
                    }

                    $quantityLinksQuantityInput.val(currentQuantityLinks);
                    updateState('links_count', currentQuantityLinks);

                    updateQuantityButtons(min, max);
                });

                $quantityLinksQuantityInput.on('change', function() {
                    let currentQuantityLinks = parseInt($(this).val());

                    let min = parseInt($quantityLinksQuantityInput.attr('min')) || 1;
                    let max = parseInt($quantityLinksQuantityInput.attr('max')) || Infinity;

                    if (isNaN(currentQuantityLinks) || currentQuantityLinks < 1) currentQuantityLinks = 1;

                    if (currentQuantityLinks < min) currentQuantityLinks = min;
                    if (currentQuantityLinks > max) currentQuantityLinks = max;

                    $(this).val(currentQuantityLinks);

                    updateState('links_count', currentQuantityLinks);
                    updateQuantityButtons(min, max);
                });

                $productQuantityBlock.on('click', '.quantity-block__up, .quantity-block__down', function() {
                    let currentQuantity = parseInt($productQuantityInput.val()) || 1;
                    const isPlus = $(this).data('action') === 'plus';

                    if (isPlus) {
                        currentQuantity++;
                    } else if (currentQuantity > 1) {
                        currentQuantity--;
                    }

                    $productQuantityInput.val(currentQuantity);
                    chainConfigurator.state.quantity = currentQuantity;
                });

                $productQuantityInput.on('change', function() {
                    let currentQuantity = parseInt($(this).val());

                    if (isNaN(currentQuantity) || currentQuantity < 1) currentQuantity = 1;

                    $(this).val(currentQuantity);
                    chainConfigurator.state.quantity = currentQuantity;
                });

                // -------------------
                // AJAX добавление в корзину
                // -------------------
                $addToCartButton.on('click', function(e) {
                    e.preventDefault();
                    const productID = $('#product_id_selected').val();
                    const linksCount = chainConfigurator.state.links_count;
                    const cartQuantity = chainConfigurator.state.quantity;

                    if (!productID) {
                        alert('Сначала выберите все параметры продукта.');
                        return;
                    }

                    const ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : '<?php echo admin_url('admin-ajax.php'); ?>';

                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'woocommerce_add_to_cart',
                            product_id: productID,
                            quantity: cartQuantity,
                            links_count: linksCount
                        },
                        success: function(response) {
                            if (response.error || !response.fragments) {
                                console.error('Ошибка добавления в корзину:', response);
                                alert('Ошибка при добавлении в корзину.');
                            } else {
                                if (response.fragments && response.fragments['a.header__cart']) {
                                    $('a.header__cart').replaceWith(response.fragments['a.header__cart']);
                                } else {
                                    const count = parseInt($('.header__cart').data('quantity')) || 0;
                                    $('.header__cart').data('quantity', count + cartQuantity).attr('data-quantity', count + cartQuantity);
                                }

                                resetConfigurator();

                                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Ошибка при добавлении в корзину:', status, error, xhr.responseText);
                            alert('Ошибка при добавлении в корзину.');
                        }
                    });
                });

                resetProductDisplay();
                renderStep(1);
                updateResultsDisplay();
            });
        </script>
    </div>
</div>