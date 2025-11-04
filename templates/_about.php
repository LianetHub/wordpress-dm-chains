<?php
$about_block = get_field('about_block');
?>


<?php if ($about_block) : ?>
    <section class="about">
        <div class="container">
            <div class="about__body">
                <?php if (!empty($about_block['about_title'])) : ?>
                    <h1 class="about__title title text-center">
                        <?= esc_html($about_block['about_title']); ?>
                    </h1>
                <?php endif; ?>

                <?php if (!empty($about_block['about_text'])) : ?>
                    <div class="about__text">
                        <?= wp_kses_post($about_block['about_text']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($about_block['about_image'])) : ?>
                    <div class="about__image">
                        <img src="<?= esc_url($about_block['about_image']['url']); ?>" alt="<?= esc_attr($about_block['about_image']['alt']); ?>">
                    </div>
                <?php endif; ?>

                <?php if (!empty($about_block['about_tagline'])) : ?>
                    <div class="about__tagline">
                        <?= esc_html($about_block['about_tagline']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($about_block['about_hint'])) : ?>
                    <div class="about__hint">
                        <?= esc_html($about_block['about_hint']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>