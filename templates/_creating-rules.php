<?php
$creating_rules_block = get_field('creating_rules_block');
?>

<?php if ($creating_rules_block) : ?>
    <section class="creating-rules">
        <div class="container">
            <?php if (!empty($creating_rules_block['rules_title'])): ?>
                <h1 class="creating-rules__title title text-center">
                    <?php echo esc_html($creating_rules_block['rules_title']); ?>
                </h1>
            <?php endif; ?>
            <div class="creating-rules__body">
                <?php if (!empty($creating_rules_block['rules_desc'])): ?>
                    <div class="creating-rules__desc">
                        <?php echo wp_kses_post($creating_rules_block['rules_desc']); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($creating_rules_block['rules_slider'])): ?>
                    <div class="creating-rules__slider ">
                        <div class="swiper">
                            <div class="swiper-wrapper">
                                <?php foreach ($creating_rules_block['rules_slider'] as $slide): ?>
                                    <?php if (!empty($slide['image'])): ?>
                                        <div class="creating-rules__slide swiper-slide">
                                            <img src="<?php echo esc_url($slide['image']['url']); ?>" alt="<?php echo esc_attr($slide['image']['alt']); ?>">
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="creating-rules__pagination swiper-pagination"></div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>
<?php endif; ?>