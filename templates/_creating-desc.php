<?php
$creating_steps_block = get_field('creating_steps_block');
?>

<?php if ($creating_steps_block): ?>
    <section class="creating-steps">
        <div class="container">
            <div class="creating-steps__header">
                <?php if (!empty($creating_steps_block['steps_title'])): ?>
                    <h2 class="creating-steps__title title text-center">
                        <?php echo esc_html($creating_steps_block['steps_title']); ?>
                    </h2>
                <?php endif; ?>

                <?php if (!empty($creating_steps_block['steps_desc'])): ?>
                    <div class="creating-steps__desc">
                        <?php echo wp_kses_post($creating_steps_block['steps_desc']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($creating_steps_block['steps_image'])): ?>
                    <div class="creating-steps__image">
                        <img src="<?php echo esc_url($creating_steps_block['steps_image']['url']); ?>"
                            alt="<?php echo esc_attr($creating_steps_block['steps_image']['alt']); ?>">
                    </div>
                <?php endif; ?>

                <?php if (!empty($creating_steps_block['steps_warning'])): ?>
                    <div class="creating-steps__warning">
                        <?php echo wp_kses_post($creating_steps_block['steps_warning']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($creating_steps_block['steps_list'])): ?>
                <div class="creating-steps__body">
                    <?php foreach ($creating_steps_block['steps_list'] as $step): ?>
                        <div class="creating-steps__step">
                            <?php if (!empty($step['step_title'])): ?>
                                <div class="creating-steps__step-title title text-center">
                                    <?php echo esc_html($step['step_title']); ?>
                                </div>
                            <?php endif; ?>

                            <div class="creating-steps__step-body">
                                <?php if (!empty($step['step_slider'])): ?>
                                    <div class="creating-steps__slider">
                                        <div class="swiper">
                                            <div class="swiper-wrapper">
                                                <?php foreach ($step['step_slider'] as $slide): ?>
                                                    <?php if (!empty($slide['step_image'])): ?>
                                                        <div class="creating-steps__slide swiper-slide">
                                                            <img src="<?php echo esc_url($slide['step_image']['url']); ?>"
                                                                alt="<?php echo esc_attr($slide['step_image']['alt']); ?>">
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="creating-steps__pagination swiper-pagination"></div>
                                    </div>
                                <?php else: ?>
                                    <div class="creating-steps__elements">
                                        <div class="creating-steps__elements-image">
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/elements.png" alt="Фото звеньев">
                                            <div class="creating-steps__elements-items">
                                                <div class="creating-steps__elements-item">1</div>
                                                <div class="creating-steps__elements-item">2</div>
                                                <div class="creating-steps__elements-item">3</div>
                                                <div class="creating-steps__elements-item">4</div>
                                                <div class="creating-steps__elements-item">5</div>
                                                <div class="creating-steps__elements-item">6</div>
                                                <div class="creating-steps__elements-item">7</div>
                                                <div class="creating-steps__elements-item">8</div>
                                                <div class="creating-steps__elements-item">9</div>
                                                <div class="creating-steps__elements-item">10</div>
                                                <div class="creating-steps__elements-item">11</div>
                                                <div class="creating-steps__elements-item">12</div>
                                                <div class="creating-steps__elements-item">13</div>
                                                <div class="creating-steps__elements-item">14</div>
                                                <div class="creating-steps__elements-item">15</div>
                                                <div class="creating-steps__elements-item">16</div>
                                                <div class="creating-steps__elements-item">17</div>
                                                <div class="creating-steps__elements-item">18</div>
                                                <div class="creating-steps__elements-item">19</div>
                                                <div class="creating-steps__elements-item">20</div>
                                                <div class="creating-steps__elements-item">21</div>
                                                <div class="creating-steps__elements-item">22</div>
                                                <div class="creating-steps__elements-item">23</div>
                                                <div class="creating-steps__elements-item">24</div>
                                                <div class="creating-steps__elements-item">25</div>
                                                <div class="creating-steps__elements-item">26</div>
                                                <div class="creating-steps__elements-item">27</div>
                                                <div class="creating-steps__elements-item">28</div>
                                            </div>
                                        </div>
                                        <div class="creating-steps__elements-quantity">
                                            Количество звеньев: <span>0</span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($step['step_desc'])): ?>
                                    <div class="creating-steps__step-desc">
                                        <?php echo wp_kses_post($step['step_desc']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($creating_steps_block['steps_footer'])): ?>
                <div class="creating-steps__footer text-center">
                    <?php echo wp_kses_post($creating_steps_block['steps_footer']); ?>
                </div>
            <?php endif; ?>

        </div>
    </section>
<?php endif; ?>