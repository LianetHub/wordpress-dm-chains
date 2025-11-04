<?php
$manufacturer_block = get_field('manufacturer_block');
?>

<?php if ($manufacturer_block) : ?>
    <section class="manufacturer">
        <div class="container">
            <?php if (!empty($manufacturer_block['manufacturer_title'])) : ?>
                <h2 class="manufacturer__title title text-center">
                    <?= esc_html($manufacturer_block['manufacturer_title']); ?>
                </h2>
            <?php endif; ?>

            <div class="manufacturer__body">
                <?php if (!empty($manufacturer_block['manufacturer_image'])) : ?>
                    <a href="<?= esc_url($manufacturer_block['manufacturer_image']['url']); ?>" data-fancybox class="manufacturer__image">
                        <img src="<?= esc_url($manufacturer_block['manufacturer_image']['url']); ?>" alt="<?= esc_attr($manufacturer_block['manufacturer_image']['alt']); ?>">
                    </a>
                <?php endif; ?>

                <?php if (!empty($manufacturer_block['manufacturer_text'])) : ?>
                    <div class="manufacturer__text">
                        <?= wp_kses_post($manufacturer_block['manufacturer_text']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>