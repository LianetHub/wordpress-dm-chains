"use strict";


//  init Fancybox
if (typeof Fancybox !== "undefined" && Fancybox !== null) {
    Fancybox.bind("[data-fancybox]", {
        dragToClose: false
    });
}

$(function () {

    // detect user OS
    const isMobile = {
        Android: () => /Android/i.test(navigator.userAgent),
        BlackBerry: () => /BlackBerry/i.test(navigator.userAgent),
        iOS: () => /iPhone|iPad|iPod/i.test(navigator.userAgent),
        Opera: () => /Opera Mini/i.test(navigator.userAgent),
        Windows: () => /IEMobile/i.test(navigator.userAgent),
        any: function () {
            return this.Android() || this.BlackBerry() || this.iOS() || this.Opera() || this.Windows();
        },
    };

    function getNavigator() {
        if (isMobile.any() || $(window).width() < 992) {
            $('body').removeClass('_pc').addClass('_touch');
        } else {
            $('body').removeClass('_touch').addClass('_pc');
        }
    }

    getNavigator();

    $(window).on('resize', () => {
        getNavigator();
    });




    // event handlers
    $(document).on('click', (e) => {
        const $target = $(e.target);

        // policies accordion
        if ($target.is('.policy__caption')) {
            $target.toggleClass("active");
            $target.next().slideToggle();
        }

        // menu 
        if ($target.closest('.header__menu-toggler').length) {
            $('.header__menu-toggler').toggleClass('active');
            $('.header').toggleClass('open-mobile-menu');
            $('body').toggleClass('lock-mobile-menu');
        }

        if (!$target.closest('.header').length && $('.header').hasClass('open-mobile-menu')) {
            $('.header__menu-toggler').removeClass('active');
            $('.header').removeClass('open-mobile-menu');
            $('body').removeClass('lock-mobile-menu');
        }

    });


    // scroll to policy

    const hash = decodeURIComponent(window.location.hash);
    if (hash && hash.startsWith('#policy-')) {
        const $target = $(hash);
        if ($target.length) {
            const $caption = $target.find('.policy__caption');
            const $content = $target.find('.policy__content');


            if ($content.is(':hidden')) {
                $caption.addClass('active');
                $content.stop().slideDown(300);
            }


            $('html, body').animate({
                scrollTop: $target.offset().top
            }, 600);
        }
    }

    //  sliders

    if ($('.promo').length) {
        new Swiper('.promo__slider', {
            effect: "fade",
            fadeEffect: {
                crossFade: true
            },
            autoplay: {
                delay: 3000
            },
            loop: true,
            allowTouchMove: false,
            preventClicks: true,
            preventClicksPropagation: true,
            simulateTouch: false,
            noSwiping: true,
        })
    }

    if ($('.creating-rules__slider').length) {
        new Swiper('.creating-rules__slider .swiper', {
            pagination: {
                el: '.creating-rules__pagination',
                clickable: true
            }
        })
    }

    if ($('.creating-steps__slider').length) {
        $('.creating-steps__slider').each(function (index, sliderBlock) {

            const slider = $(sliderBlock).find('.swiper')[0];
            const pagination = $(sliderBlock).find('.creating-steps__pagination')[0];

            new Swiper(slider, {
                watchOverflow: true,
                pagination: {
                    el: pagination,
                    clickable: true
                }
            });
        });
    }


    // Анимация со звеньями

    if ($('.creating-steps__elements').length) {
        const timeout = 300;

        $('.creating-steps__elements-item, .creating-steps__elements-quantity').removeClass('visible');

        function animateCreatingSteps(entries, observer) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const $items = $(entry.target).find('.creating-steps__elements-item');
                    const $quantity = $(entry.target).find('.creating-steps__elements-quantity');

                    $quantity.addClass('visible');

                    $items.each(function (index) {
                        setTimeout(() => {
                            $(this).addClass('visible');
                            $quantity.find('span').text(index + 1);
                        }, index * timeout);
                    });

                    observer.unobserve(entry.target);
                }
            });
        }

        const observer = new IntersectionObserver(animateCreatingSteps, {
            threshold: 0.5
        });

        const target = document.querySelector('.creating-steps__elements');
        observer.observe(target);
    }

    var phoneInputs = document.querySelectorAll('input[type="tel"]');

    var getInputNumbersValue = function (input) {
        // Return stripped input value — just numbers
        return input.value.replace(/\D/g, '');
    }

    var onPhonePaste = function (e) {
        var input = e.target,
            inputNumbersValue = getInputNumbersValue(input);
        var pasted = e.clipboardData || window.clipboardData;
        if (pasted) {
            var pastedText = pasted.getData('Text');
            if (/\D/g.test(pastedText)) {
                // Attempt to paste non-numeric symbol — remove all non-numeric symbols,
                // formatting will be in onPhoneInput handler
                input.value = inputNumbersValue;
                return;
            }
        }
    }

    var onPhoneInput = function (e) {
        var input = e.target,
            inputNumbersValue = getInputNumbersValue(input),
            selectionStart = input.selectionStart,
            formattedInputValue = "";

        if (!inputNumbersValue) {
            return input.value = "";
        }

        if (input.value.length != selectionStart) {
            // Editing in the middle of input, not last symbol
            if (e.data && /\D/g.test(e.data)) {
                // Attempt to input non-numeric symbol
                input.value = inputNumbersValue;
            }
            return;
        }

        if (["7", "8", "9"].indexOf(inputNumbersValue[0]) > -1) {
            if (inputNumbersValue[0] == "9") inputNumbersValue = "7" + inputNumbersValue;
            var firstSymbols = (inputNumbersValue[0] == "8") ? "8" : "+7";
            formattedInputValue = input.value = firstSymbols + " ";
            if (inputNumbersValue.length > 1) {
                formattedInputValue += '(' + inputNumbersValue.substring(1, 4);
            }
            if (inputNumbersValue.length >= 5) {
                formattedInputValue += ') ' + inputNumbersValue.substring(4, 7);
            }
            if (inputNumbersValue.length >= 8) {
                formattedInputValue += '-' + inputNumbersValue.substring(7, 9);
            }
            if (inputNumbersValue.length >= 10) {
                formattedInputValue += '-' + inputNumbersValue.substring(9, 11);
            }
        } else {
            formattedInputValue = '+' + inputNumbersValue.substring(0, 16);
        }
        input.value = formattedInputValue;
    }
    var onPhoneKeyDown = function (e) {
        // Clear input after remove last symbol
        var inputValue = e.target.value.replace(/\D/g, '');
        if (e.keyCode == 8 && inputValue.length == 1) {
            e.target.value = "";
        }
    }
    for (var phoneInput of phoneInputs) {
        phoneInput.addEventListener('keydown', onPhoneKeyDown);
        phoneInput.addEventListener('input', onPhoneInput, false);
        phoneInput.addEventListener('paste', onPhonePaste, false);
    }



    if ($('#order').length) {

        const $orderContainer = $('#order');
        const $deliveryRadios = $orderContainer.find('input[name="delivery_type"]');
        const $paymentRadios = $orderContainer.find('input[name="payment_type"]');
        const $contactRadios = $orderContainer.find('input[name="contact_method"]');

        const $addressControl = $orderContainer.find('input[name="address"]').closest('.form__controls');
        const $businessControls = $orderContainer.find('[data-type="business"]');
        const $individualControls = $orderContainer.find('[data-type="individual"]');

        const $emailControl = $orderContainer.find('input[name="email"]').closest('.form__controls');
        const $telegramControl = $orderContainer.find('input[name="telegram_user"]').closest('.form__controls');
        const $whatsappControl = $orderContainer.find('.form__group').eq(2).find('.form__group-items > .form__controls').eq(2);

        const $priceProductInput = $('#price_product_input');
        const $priceDeliveryInput = $('#price_delivery_input');
        const $formTotal = $orderContainer.find('.form__total');
        const $formTotalValue = $orderContainer.find('.form__total-value');
        const $policyLabel = $orderContainer.find('input[name="confirm_policies"]').closest('.form__radio-btn');
        const $policyCheckbox = $orderContainer.find('input[name="confirm_policies"]');
        const $submitButton = $orderContainer.find('input[type="submit"]');


        function calculateTotal() {

            const productPrice = parseInt($priceProductInput.val()) || 0;
            const deliveryPrice = parseInt($priceDeliveryInput.val()) || 0;

            const total = productPrice + deliveryPrice;

            $formTotalValue.text(total.toLocaleString('ru-RU') + ' ₽');
        }

        function handleDeliveryChange() {
            const selectedDelivery = $orderContainer.find('input[name="delivery_type"]:checked').val();

            if (selectedDelivery === 'cdek_courier') {
                $addressControl.removeClass('hidden');
            } else {
                $addressControl.addClass('hidden');
            }

            $priceDeliveryInput.val(0);
            calculateTotal();
            checkFormCompletion();
        }

        function handlePaymentChange() {
            const selectedPayment = $orderContainer.find('input[name="payment_type"]:checked').val();

            $businessControls.addClass('hidden');
            $individualControls.addClass('hidden');

            if (selectedPayment === 'individual_card') {
                $individualControls.removeClass('hidden');
            } else if (selectedPayment === 'business_invoice') {
                $businessControls.removeClass('hidden');
            }
            checkFormCompletion();
        }

        function handleContactChange() {
            const selectedContact = $orderContainer.find('input[name="contact_method"]:checked').val();

            $emailControl.addClass('hidden');
            $telegramControl.addClass('hidden');
            $whatsappControl.addClass('hidden');

            switch (selectedContact) {
                case 'email':
                    $emailControl.removeClass('hidden');
                    break;
                case 'telegram':
                    $telegramControl.removeClass('hidden');
                    break;
                case 'whatsapp':
                    $whatsappControl.removeClass('hidden');
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


        $deliveryRadios.on('change', handleDeliveryChange);
        $paymentRadios.on('change', handlePaymentChange);
        $contactRadios.on('change', handleContactChange);
        $policyCheckbox.on('change', handlePolicyChange);

        handleDeliveryChange();
        handlePaymentChange();
        handleContactChange();
        handlePolicyChange();
    }

})


