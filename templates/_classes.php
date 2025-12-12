<section class="classes">
    <div class="container">
        <h2 class="classes__title title text-center">Классы</h2>
        <div class="classes__body">
            <div class="classes__text">
                <p>
                    Цепи ДМ Chains делятся на 3 класса
                    по цене и по качеству: <br>
                    А, В и С.
                </p>
                <p>
                    Нажмите на любую из коробок, чтобы подробнее с ними ознакомится
                </p>
            </div>
            <div class="classes__items">
                <a href="#class-A-info" data-fancybox-saw class="classes__item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/classes/class-A.png" alt="Фото класса А">
                </a>
                <a href="#class-B-info" data-fancybox-saw class="classes__item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/classes/class-B.png" alt="Фото класса B">
                </a>
                <a href="#class-C-info" data-fancybox-saw class="classes__item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/classes/class-C.png" alt="Фото класса C">
                </a>
            </div>
        </div>
    </div>
    <?php require_once(TEMPLATE_PATH . '_classes-modals.php'); ?>


</section>