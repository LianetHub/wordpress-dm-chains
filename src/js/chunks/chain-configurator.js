jQuery(document).ready(function ($) {
    const { chainConfig, defaultImageSrc, ajaxUrl, addToCartAction, getProductDataAction } = ConfigData;

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
    const $productQuantityBlock = $('.creating-block__quantity');
    const $productQuantityInput = $productQuantityBlock.find('input[name="quantity"]');

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
        $('.creating-block__result-block').each(function (index) {
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

    function updateQuantityButtons(min, max) {
        const currentQuantityLinks = parseInt($quantityLinksQuantityInput.val());
        $quantityLinksQuantityInput.parent().find('.quantity-block__down').prop('disabled', isNaN(currentQuantityLinks) || currentQuantityLinks <= min);
        $quantityLinksQuantityInput.parent().find('.quantity-block__up').prop('disabled', isNaN(currentQuantityLinks) || currentQuantityLinks >= max);
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

    function findCombinationAndPrice() {
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
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: getProductDataAction,
                    product_id: productID,
                    quantity: chainConfigurator.state.quantity
                },
                success: function (response) {
                    if (response.success && response.data) {
                        updateProductDisplay(response.data, selectedProduct);
                    } else {
                        console.error('Ошибка WC AJAX:', response);
                        $('.creating-block__product-price').html('Нет в наличии');
                        $addToCartButton.prop('disabled', true);
                        $('#product_id_selected').val('');
                    }
                },
                error: function () {
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

    // -------------------
    // События
    // -------------------
    $('.creating-block__quiz').on('change', 'input[type="radio"]', function () {
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

    $('.creating-quiz__header').on('click', '.creating-quiz__back', function () {
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


    $('#links_quantity').closest('.quantity-block').on('click', '.quantity-block__up, .quantity-block__down', function () {
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

    $quantityLinksQuantityInput.on('change', function () {
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

    $productQuantityBlock.on('click', '.quantity-block__up, .quantity-block__down', function () {
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

    $productQuantityInput.on('change', function () {
        let currentQuantity = parseInt($(this).val());

        if (isNaN(currentQuantity) || currentQuantity < 1) currentQuantity = 1;

        $(this).val(currentQuantity);
        chainConfigurator.state.quantity = currentQuantity;
    });

    // -------------------
    // AJAX добавление в корзину
    // -------------------
    $addToCartButton.on('click', function (e) {
        e.preventDefault();
        const productID = $('#product_id_selected').val();
        const linksCount = chainConfigurator.state.links_count;
        const cartQuantity = chainConfigurator.state.quantity;

        if (!productID) {
            alert('Сначала выберите все параметры продукта.');
            return;
        }

        const finalAjaxUrl = typeof ajaxurl !== 'undefined' && ConfigData.isWpAjax ? ajaxurl : ConfigData.ajaxUrl;

        $.ajax({
            url: finalAjaxUrl,
            type: 'POST',
            data: {
                action: addToCartAction,
                product_id: productID,
                quantity: cartQuantity,
                links_count: linksCount
            },
            success: function (response) {
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
            error: function (xhr, status, error) {
                console.error('Ошибка при добавлении в корзину:', status, error, xhr.responseText);
                alert('Ошибка при добавлении в корзину.');
            }
        });
    });

    // Инициализация
    resetProductDisplay();
    renderStep(1);
    updateResultsDisplay();
});