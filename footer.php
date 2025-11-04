<?php
$email_address = get_field('email_address', 'option');
$social_telegram = get_field('social_telegram', 'option');
$social_whatsapp = get_field('social_whatsapp', 'option');
?>

</main>
<footer class="footer">
    <div class="container">

        <div class="footer__main">
            <?php if ($email_address) : ?>
                <a href="mailto:<?php echo esc_attr($email_address); ?>" class="footer__email">
                    <?php echo esc_html($email_address); ?>
                </a>
            <?php endif; ?>

            <?php if ($social_telegram || $social_whatsapp) : ?>
                <div class="footer__socials socials">
                    <?php if ($social_telegram) : ?>
                        <a href="<?php echo esc_url($social_telegram); ?>" class="socials__link icon-telegram" target="_blank" rel="noopener noreferrer"></a>
                    <?php endif; ?>

                    <?php if ($social_whatsapp) : ?>
                        <a href="<?php echo esc_url($social_whatsapp); ?>" class="socials__link icon-whatsapp" target="_blank" rel="noopener noreferrer"></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</footer>
</div>
<?php wp_footer(); ?>
</body>

</html>