<div class="header__main">
    <?php if (has_custom_logo()) { ?>
        <?php the_custom_logo(); ?>
    <?php } ?>
    <nav class="header__menu menu">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'primary_menu',
            'container'      => false,
            'menu_class'     => 'menu__list',
            'echo'           => true,
            'fallback_cb'    => false,
            'depth'          => 1,
        ));
        ?>
    </nav>
    <div class="header__actions">
        <a href="<?php echo wc_get_cart_url(); ?>"
            data-quantity="<?php echo WC()->cart->get_cart_contents_count(); ?>"
            class="header__cart icon-cart">
        </a>
        <button type="button" aria-label="Меню" class="header__menu-toggler icon-menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</div>