<?php
/**
 * Footer template
 * 
 * @package ModernArchitect
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
    </div><!-- #page -->

    <footer class="site-footer" id="colophon">
        <div class="container">
            <div class="footer-widgets">
                <?php if (is_active_sidebar('footer-1')) : ?>
                    <aside class="footer-widget-area">
                        <?php dynamic_sidebar('footer-1'); ?>
                    </aside>
                <?php endif; ?>
            </div>
            
            <div class="site-info">
                <p>
                    &copy; <?php echo esc_html(date('Y')); ?> 
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>.
                    <?php esc_html_e('All rights reserved.', 'modern-architect'); ?>
                </p>
                <p class="powered-by">
                    <?php
                    printf(
                        /* translators: %s: WordPress link */
                        esc_html__('Proudly powered by %s', 'modern-architect'),
                        '<a href="https://wordpress.org/">WordPress</a>'
                    );
                    ?>
                </p>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>
