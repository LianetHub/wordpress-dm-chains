"use strict";




//  init Fancybox
if (typeof Fancybox !== "undefined" && Fancybox !== null) {
    Fancybox.bind("[data-fancybox]", {
        Carousel: {
            touch: false,
        },
        dragToClose: false,
    });


    Fancybox.bind('[data-fancybox-saw]', {
        Carousel: {
            touch: false,
        },
        showClass: 'fancybox-animate-saw-in',
        hideClass: 'fancybox-animate-saw-out',
        dragToClose: false,
        closeButton: false
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

    if ($('.saw-block__slider').length) {
        $('.saw-block__slider').each(function (index, sliderBlock) {

            const slider = $(sliderBlock).find('.swiper')[0];
            const pagination = $(sliderBlock).find('.saw-block__pagination')[0];
            const prevBtn = $(sliderBlock).find('.saw-block__prev')[0];
            const nextBtn = $(sliderBlock).find('.saw-block__next')[0];

            new Swiper(slider, {
                watchOverflow: true,
                spaceBetween: 10,
                navigation: {
                    nextEl: nextBtn,
                    prevEl: prevBtn
                },
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
        return input.value.replace(/\D/g, '');
    };
    var onPhonePaste = function (e) {
        var input = e.target,
            inputNumbersValue = getInputNumbersValue(input);
        var pasted = e.clipboardData || window.clipboardData;
        if (pasted) {
            var pastedText = pasted.getData('Text');
            if (/\D/g.test(pastedText)) {
                input.value = inputNumbersValue;
                return;
            }
        }
    };
    var onPhoneInput = function (e) {
        var input = e.target,
            inputNumbersValue = getInputNumbersValue(input),
            selectionStart = input.selectionStart,
            formattedInputValue = "";
        if (!inputNumbersValue) {
            return input.value = "";
        }
        if (input.value.length != selectionStart) {
            if (e.data && /\D/g.test(e.data)) {
                input.value = inputNumbersValue;
            }
            return;
        }
        if (inputNumbersValue.length > 11) {
            inputNumbersValue = inputNumbersValue.substring(0, 11);
        }
        formattedInputValue = "+7 (";
        if (inputNumbersValue.length >= 2) {
            formattedInputValue += inputNumbersValue.substring(1, 4);
        }
        if (inputNumbersValue.length >= 5) {
            formattedInputValue += ") " + inputNumbersValue.substring(4, 7);
        }
        if (inputNumbersValue.length >= 8) {
            formattedInputValue += "-" + inputNumbersValue.substring(7, 9);
        }
        if (inputNumbersValue.length >= 10) {
            formattedInputValue += "-" + inputNumbersValue.substring(9, 11);
        }
        input.value = formattedInputValue;
    };
    var onPhoneKeyDown = function (e) {
        var inputValue = e.target.value.replace(/\D/g, '');
        if (e.keyCode == 8 && inputValue.length == 1) {
            e.target.value = "";
        }
    };
    for (var phoneInput of phoneInputs) {
        phoneInput.addEventListener('focus', function () {
            if (!this.value) {
                this.value = "+7 ";
            }
        });
        phoneInput.addEventListener('keydown', onPhoneKeyDown);
        phoneInput.addEventListener('input', onPhoneInput, false);
        phoneInput.addEventListener('paste', onPhonePaste, false);
    }




})


