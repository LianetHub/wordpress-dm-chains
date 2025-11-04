<?php
$testing_block = get_field('testing_block');
?>

<?php if ($testing_block) : ?>
    <section class="testing">
        <div class="container">
            <?php if (!empty($testing_block['testing_title'])) : ?>
                <h2 class="testing__title title text-center">
                    <?= esc_html($testing_block['testing_title']); ?>
                </h2>
            <?php endif; ?>

            <?php if (!empty($testing_block['testing_text'])) : ?>
                <div class="testing__text">
                    <?= wp_kses_post($testing_block['testing_text']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($testing_block['testing_video'])) : ?>
                <div class="testing__video">
                    <?php
                    echo '<video src="' . esc_url($testing_block['testing_video']['url']) . '" controls></video>';
                    ?>
                </div>
            <?php endif; ?>

            <div class="testing__footer">
                <?php if (!empty($testing_block['testing_footer_text'])) : ?>
                    <div class="testing__footer-text">
                        <?= wp_kses_post($testing_block['testing_footer_text']); ?>
                    </div>
                <?php endif; ?>

                <a href="<?php echo get_permalink(65); ?>" class="testing__btn btn btn-primary btn-lg">Создать цепь</a>
            </div>
        </div>
    </section>
<?php endif; ?>