<?php if (have_rows('for_buyers_blocks')): ?>
    <section class="for-buyers">
        <h1 class="for-buyers__title title text-center">Покупателю</h1>
        <div class="for-buyers__body">
            <?php while (have_rows('for_buyers_blocks')): the_row();
                $caption = get_sub_field('caption');
                $text = get_sub_field('text');
                $text_size_class = get_sub_field('text_size');
            ?>
                <div class="for-buyers__block">
                    <h3 class="for-buyers__caption title-sm"><?php echo esc_html($caption); ?></h3>
                    <div class="for-buyers__text <?php echo esc_attr($text_size_class); ?>"><?php echo $text; ?></div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
<?php endif; ?>