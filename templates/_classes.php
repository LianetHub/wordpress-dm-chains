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


    <?php
    $classes = get_field('classes_block');
    // echo var_dump($classes['class_c']['gallery']);
    function render_class_popup($id, $color, $data)
    {
        if (!$data) return;
    ?>
        <div id="class-<?php echo esc_attr($id); ?>-info" class="popup popup--saw popup--<?php echo esc_attr($color); ?>">
            <button type="button" data-fancybox-close class="popup__close icon-close"></button>
            <div class="saw-block">
                <div class="container">

                    <h2 class="saw-block__title title text-center">Класс <?php echo strtoupper($id); ?></h2>

                    <div class="saw-block__body">
                        <?php if (!empty($data['desc'])): ?>
                            <div class="saw-block__desc">
                                <?php echo wp_kses_post($data['desc']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($data['gallery'])): ?>
                            <div class="saw-block__slider">
                                <div class="swiper">
                                    <div class="swiper-wrapper">
                                        <?php foreach ($data['gallery'] as $img): ?>
                                            <?php
                                            if (is_array($img)) {
                                                $img_url = $img['url'] ?? '';
                                                $img_alt = $img['alt'] ?? '';
                                            } else {
                                                $img_url = wp_get_attachment_image_url($img, 'large');
                                                $img_alt = get_post_meta($img, '_wp_attachment_image_alt', true);
                                            }
                                            ?>
                                            <?php if ($img_url): ?>
                                                <div class="saw-block__slide swiper-slide">
                                                    <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($img_alt); ?>">
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="saw-block__controls">
                                    <button type="button" class="saw-block__prev swiper-button-prev icon-prev"></button>
                                    <div class="saw-block__pagination swiper-pagination"></div>
                                    <button type="button" class="saw-block__next swiper-button-next icon-next"></button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($data['tests'])): ?>
                        <div class="saw-block__tests">
                            <div class="saw-block__tests-title">Данные тестов</div>
                            <ul class="saw-block__tests-list">
                                <?php foreach ($data['tests'] as $test): ?>
                                    <li class="saw-block__tests-item">
                                        <div class="saw-block__tests-property"><?php echo esc_html($test['property']); ?></div>
                                        <div class="saw-block__tests-value"><?php echo esc_html($test['value']); ?></div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php

    if ($classes) {
        render_class_popup('A', 'dark', $classes['class_a']);
        render_class_popup('B', 'grey', $classes['class_b']);
        render_class_popup('C', 'blue', $classes['class_c']);
    }
    ?>
</section>