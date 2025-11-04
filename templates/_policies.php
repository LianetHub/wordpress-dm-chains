<?php if (have_rows('policy_items')): ?>
    <div class="policy">
        <?php while (have_rows('policy_items')): the_row();
            $caption = get_sub_field('policy_caption');
            $content = get_sub_field('policy_content');
        ?>
            <div class="policy__block">
                <div class="policy__caption icon-chevron"><?php echo esc_html($caption); ?></div>
                <div class="policy__content"><?php echo $content; ?></div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>